<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\ResetPassword;
use App\Models\OneTimeToken;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserLogin;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\TokenRepository;

class AuthController extends Controller
{
    public function static()
    {
        return response()->json(['message' => 'This is a static endpoint']);
    }

    // --------------------------------------------

    public function generate(CaptchaService $captcha)
    {
        return response()->json($captcha->generate());
    }

    // --------------------------------------------

    public function login(Request $request, CaptchaService $captcha)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'enteredCaptcha' => 'required',
            'captchaKey' => 'required',
        ], [
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
            'enteredCaptcha.required' => 'Captcha is required',
            'captchaKey.required' => 'Captcha key is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $valid = $captcha->validate(
            $request->captchaKey,
            $request->enteredCaptcha
        );

        if (!$valid) {
            return response()->json(['errors' => ['Captcha is incorrect']], Response::HTTP_UNAUTHORIZED);
        }

        $username = base64_decode($request->username);
        $password = base64_decode($request->password);

        try {
            DB::beginTransaction();

            if (!Auth::attempt([
                'email' => $username,
                'password' => $password,
                'organisation' => $request->organisation,
            ])) {
                return response()->json(['errors' => ['Incorrect credentials']], Response::HTTP_UNAUTHORIZED);
            }

            $user = Auth::user();

            Cache::forget($request->captchaKey);

            $tokenResult = $user->createToken('AuthToken');
            $accessToken = $tokenResult->accessToken;
            $accessTokenId = $tokenResult->token->id;

            $plainRefreshToken = bin2hex(random_bytes(64));
            $hashedRefreshToken = hash('sha256', $plainRefreshToken);

            $familyId = (string) Str::uuid();
            $ttlDays = 3;

            RefreshToken::create([
                'user_id' => $user->id,
                'token_hash' => $hashedRefreshToken,
                'family_id' => $familyId,
                'organisation' => $request->organisation,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'expires_at' => now()->addDays($ttlDays),
                'access_token_id' => $accessTokenId,
            ]);

            $cookieName = $request->organisation === 'sports'
                ? 'refresh_sports'
                : 'refresh_services';
            $accessCookieName = $request->organisation === 'sports'
                ? 'access_sports'
                : 'access_services';

            $refreshCookie = cookie(
                $cookieName,
                $plainRefreshToken,
                $ttlDays * 24 * 60,
                '/api',
                null,
                false,   // secure
                true,   // httpOnly
                false,
                'Lax'
            );

            $accessCookie = cookie(
                $accessCookieName,
                $accessToken,
                15,
                '/api',
                null,
                false,
                true,
                false,
                'Lax'
            );

            DB::commit();

            return response()->json([
                'user' => new UserResource($user),
            ])->withCookie($accessCookie)
                ->withCookie($refreshCookie);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function refresh(Request $request, TokenRepository $tokenRepo, string $organisation)
    {
        $cookieName = $organisation === 'sports'
            ? 'refresh_sports'
            : 'refresh_services';
        $accessCookieName = $organisation === 'sports'
            ? 'access_sports'
            : 'access_services';

        $plainToken = $request->cookie($cookieName);

        if (!$plainToken) {
            return response()->json(['message' => 'Missing token'], Response::HTTP_UNAUTHORIZED);
        }
        $hashed = hash('sha256', $plainToken);
        $stored = RefreshToken::where('token_hash', $hashed)->first();

        if (!$stored || $stored->expires_at->isPast()) {
            return response()->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        if ($stored->revoked) {
            RefreshToken::where('family_id', $stored->family_id)
                ->update(['revoked' => true]);

            return response()->json(['message' => 'Token reuse detected'], Response::HTTP_UNAUTHORIZED);
        }

        $stored->update(['revoked' => true]);

        if ($stored->access_token_id) {
            $tokenRepo->revokeAccessToken($stored->access_token_id);
        }

        $user = $stored->user;

        $tokenResult = $user->createToken('AuthToken');
        $accessToken = $tokenResult->accessToken;
        $newAccessTokenId = $tokenResult->token->id;

        $newPlain = bin2hex(random_bytes(64));
        $newHash = hash('sha256', $newPlain);

        $ttlDays = 3;

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => $newHash,
            'family_id' => $stored->family_id,
            'organisation' => $stored->organisation,

            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),

            'expires_at' => now()->addDays($ttlDays),
            'access_token_id' => $newAccessTokenId,
        ]);

        $refreshCookie = cookie(
            $cookieName,
            $newPlain,
            $ttlDays * 24 * 60,
            '/api',
            null,
            false,
            true,
            false,
            'Lax'
        );

        $accessCookie = cookie(
            $accessCookieName,
            $accessToken,
            15,
            '/api',
            null,
            false,
            true,
            false,
            'Lax'
        );

        return response()->json([
            'user' => new UserResource($user),
        ])->withCookie($accessCookie)
            ->withCookie($refreshCookie);
    }


    // --------------------------------------------

    public function logout(Request $request, TokenRepository $tokenRepo, string $organisation)
    {
        $user = Auth::user();

        RefreshToken::where('user_id', $user->id)
            ->where('organisation', $organisation)
            ->update([
                'revoked' => true
            ]);

        $tokenRepo->revokeAccessToken($user->token()->id);

        $cookieName = $organisation === 'sports'
            ? 'refresh_sports'
            : 'refresh_services';

        return response()->json(['message' => 'Logged out'])
            ->cookie(
                $cookieName,
                '',
                -1,
                '/api',
                null,
                false,
                true,
                false,
                'Lax'
            );
    }

    // --------------------------------------------

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'email.exists' => 'Email not found',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $toEmail = $request->email;
            $fromEmail = $request->organisation === 'services' ? 'noreply@wbyouthservices.gov.in' : 'noreply@wbsportsandyouth.gov.in';
            $domain = $request->organisation === 'services' ? 'wbyouthservices' : 'wbsportsandyouth';
            $fromName = $request->organisation === 'services' ? 'Department of Youth Services & Sports (Services Wing)' : 'Department of Youth Services & Sports (Sports Wing)';
            $body = 'Click the link below to reset your password:';
            $link = 'https://172.25.150.159/' . $domain . '/cms/reset-password/' . Crypt::encrypt($request->email);
            $subject = 'Reset Password - ' . $fromName;

            Mail::to($toEmail)->send(new ResetPassword($fromEmail, $fromName, $body, $link, $subject));

            DB::commit();

            return response()->json(['message' => 'Reset password link sent to your email'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newPassword' => 'required|min:6',
            'confirmPassword' => 'required|min:6',
        ], [
            '*.required' => ':Attribute is required',
            '*.min' => ':Attribute must be at least 6 characters',
        ], [
            'newPassword' => 'new password',
            'confirmPassword' => 'confirm password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->newPassword !== $request->confirmPassword) {
            return response()->json(['errors' => ['confirmPassword' => ['Passwords do not match']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $email = Crypt::decrypt($request->email);
        $prevPassword = User::where('email', $email)->value('password');
        if (password_verify($request->newPassword, $prevPassword)) {
            return response()->json(['errors' => ['newPassword' => ['New password cannot be the same as the previous password']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        User::where('email', $email)->update(['password' => bcrypt($request->newPassword)]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function me()
    {
        $user = Auth::user();
        return UserResource::make($user);
    }

    // --------------------------------------------

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required',
            'newPassword' => 'required|min:6',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'oldPassword.required' => 'Old password is required',
            'newPassword.required' => 'New password is required',
            'newPassword.min' => 'New password must be at least 6 characters',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();
        $prevPassword = User::where('id', $user->id)->value('password');

        if (!password_verify($request->oldPassword, $user->password)) {
            return response()->json(['errors' => ['oldPassword' => ['Old password is incorrect']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (password_verify($request->newPassword, $prevPassword)) {
            return response()->json(['errors' => ['newPassword' => ['New password cannot be the same as the previous password']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newPassword = bcrypt($request->newPassword);
        User::where('id', $user->id)->update(['password' => $newPassword]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function profileUpdate(ProfileUpdateRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if ($request->hasFile('profileImg') && $request->file('profileImg')->getSize() > 0) {
                $file = $request->file('profileImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/profiles';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($user) {
                    $deletePath = str_replace('/storage', '', $user->userDetails->profile_img);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            User::where('id', $user->id)->update([
                'name' => trim($request->name),
                'email' => $request->email,
            ]);

            UserDetail::where('user_id', $user->id)->update([
                'mobile' => $request->mobile,
                'profile_img' => $request->hasFile('profileImg') ? Storage::url($filePath) : $user->userDetails->profile_img ?? null,
            ]);

            DB::commit();
            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
