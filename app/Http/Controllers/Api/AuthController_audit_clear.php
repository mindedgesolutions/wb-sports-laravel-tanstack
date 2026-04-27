<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\ResetPassword;
use App\Models\AccessToken;
use App\Models\DPopProof;
use App\Models\OneTimeToken;
use App\Models\RefreshToken;
use App\Models\UsedNonce;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function static()
    {
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'captchaEnter' => 'required',
        ], [
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
            'captchaEnter.required' => 'Captcha is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->captchaEnter !== $request->captchaText) {
            return response()->json(['errors' => ['Captcha is incorrect']], Response::HTTP_UNAUTHORIZED);
        }

        $username = base64_decode($request->username);
        $password = base64_decode($request->password);

        $check = User::where('email', $username)
            ->where('organisation', $request->organisation)
            ->first();

        // if ($check && $check->logged_in === true) {
        //     return response()->json(['errors' => ['User is logged in from another device']], Response::HTTP_UNAUTHORIZED);
        // }

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

            // $dpopHeader = $request->header('DPoP');
            // $dpopJkt = null;

            // if ($dpopHeader) {
            //     [$hdrB64] = explode('.', $dpopHeader) + [null];
            //     if (!$hdrB64) {
            //         DB::rollBack();
            //         return response()->json(['error' => 'Invalid DPoP header'], 400);
            //     }
            //     $hdrJson = base64url_decode($hdrB64);
            //     $hdr = json_decode($hdrJson, true);
            //     if (empty($hdr['jwk'])) {
            //         DB::rollBack();
            //         return response()->json(['error' => 'DPoP jwk missing'], 400);
            //     }

            //     $jwk = $hdr['jwk'];

            //     try {
            //         $payload = verify_dpop_jwt($dpopHeader, $jwk, $request->method(), $request->fullUrl());
            //         $dpopJkt = compute_jwk_thumbprint($jwk);
            //     } catch (\Throwable $e) {
            //         DB::rollBack();
            //         return response()->json(['error' => 'Invalid DPoP proof: ' . $e->getMessage()], 400);
            //     }
            // }

            $tokenResult = $user->createToken('AuthToken');
            $accessToken = $tokenResult->accessToken;
            $tokenModel = $tokenResult->token;
            $tokenId = (string) $tokenModel->id;
            $expiresAt = $tokenModel->expires_at ?? Carbon::now()->addMinutes(5);

            AccessToken::firstOrCreate(
                [
                    'jti' => $tokenId,
                    'user_id' => $user->id,
                ],
                [
                    'jti' => $tokenId,
                    'user_id' => $user->id,
                    'issued_at' => now(),
                    'expires_at' => $expiresAt,
                    // 'dpop_jkt' => $dpopJkt
                ]
            );

            $refreshToken = Str::random(64);
            $name = $request->organisation === 'sports' ? 'refresh_sports' : 'refresh_services';

            RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $refreshToken),
                'expires_at' => now()->addDays(3),
                'revoked' => false,
                'access_jti' => $tokenId,
            ]);

            // one-time token for authentication
            $oneTimeToken = Str::random(64);
            OneTimeToken::insert([
                'user_id' => $user->id,
                'token_id' => $oneTimeToken,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $refreshCookie = cookie(
                $name,              // name
                $refreshToken,      // value
                60 * 24 * 15,       // expiry in minutes (15 days)
                '/api',             // path
                null,               // domain (null = current)
                true,              // secure (set true in HTTPS)
                true,               // httpOnly
                false,              // raw
                'Lax'               // SameSite (important for cross-origin React app)
            );

            User::where('id', $user->id)->update(['logged_in' => true]);

            UserLogin::updateOrInsert(['user_id' => $user->id], ['login_at' => now()]);

            $responseData = [
                'data' => new UserResource($user),
                'token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 15 * 60, // 15 minutes in seconds
                'one_time_pass' => $oneTimeToken,
            ];

            DB::commit();

            return response()->json($responseData)->cookie($refreshCookie);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function deleteOneTimeToken($token)
    {
        $check = OneTimeToken::where('token_id', $token)->where('status', 'active')->first();
        if ($check) {
            OneTimeToken::where('token_id', $token)->update(['status' => 'used']);
            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } else {
            return response()->json(['errors' => ['Token not found']], Response::HTTP_UNAUTHORIZED);
        }
    }

    // --------------------------------------------

    public function renew(Request $request, string $organisation)
    {
        $refreshToken = $organisation === 'sports'
            ? $request->cookie('refresh_sports')
            : $request->cookie('refresh_services');

        if (!$refreshToken) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $stored = RefreshToken::where('token', hash('sha256', $refreshToken))
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$stored) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // $dpopHeader = $request->header('DPoP');
        // if (!$dpopHeader) {
        //     return response()->json(['error' => 'DPoP required for refresh'], 401);
        // }

        // try {
        //     [$hdrB64] = explode('.', $dpopHeader) + [null];
        //     if (!$hdrB64) {
        //         return response()->json(['error' => 'Invalid DPoP header'], 400);
        //     }

        //     $hdr = json_decode(base64url_decode($hdrB64), true);
        //     if (empty($hdr['jwk'])) {
        //         return response()->json(['error' => 'DPoP jwk missing'], 400);
        //     }

        //     $jwk = $hdr['jwk'];
        //     $payload = verify_dpop_jwt($dpopHeader, $jwk, $request->method(), $request->fullUrl());
        //     $incomingJkt = compute_jwk_thumbprint($jwk);
        // } catch (\Throwable $e) {
        //     Log::warning('DPoP validation failed on refresh', ['err' => $e->getMessage()]);
        //     return response()->json(['error' => 'Invalid DPoP proof'], 401);
        // }

        // $oldJti = $stored->access_jti;
        // $oldAccess = $oldJti ? AccessToken::where('jti', $oldJti)->first() : null;

        // if ($oldAccess) {
        //     if (
        //         !empty($oldAccess->dpop_jkt) &&
        //         !hash_equals((string)$oldAccess->dpop_jkt, (string)$incomingJkt)
        //     ) {
        //         Log::warning('DPoP mismatch on refresh', [
        //             'stored_access_jti' => $oldJti,
        //             'old_dpop_jkt' => substr((string)$oldAccess->dpop_jkt, 0, 16) . '...',
        //             'incoming_jkt' => substr((string)$incomingJkt, 0, 16) . '...',
        //         ]);
        //         return response()->json(['error' => 'DPoP key mismatch for refresh'], 401);
        //     }
        // } else {
        //     Log::info('Refresh: previous access token not found (possibly revoked/deleted)', [
        //         'old_jti' => $oldJti,
        //     ]);
        // }

        try {
            DB::beginTransaction();

            // if ($oldJti) {
            //     AccessToken::where('jti', $oldJti)->update(['revoked' => true]);
            //     AccessToken::where('jti', $oldJti)->delete();
            //     UsedNonce::where('token_id', $oldJti)->delete();
            //     if ($oldAccess && !empty($oldAccess->dpop_jkt)) {
            //         DPopProof::where('jkt', $oldAccess->dpop_jkt)->delete();
            //     }
            // }

            $user = User::find($stored->user_id);
            if (!$user) {
                DB::rollBack();
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $tokenResult = $user->createToken('AuthToken');
            $tokenModel = $tokenResult->token;
            $tokenId = (string) $tokenModel->id;
            $expiresAt = $tokenModel->expires_at ?? now()->addMinutes(5);

            AccessToken::updateOrCreate(
                ['jti' => $tokenId],
                [
                    'user_id'    => $user->id,
                    'issued_at'  => now(),
                    'expires_at' => $expiresAt,
                    // 'dpop_jkt'   => $incomingJkt,
                ]
            );

            $newRefreshToken = Str::random(64);
            RefreshToken::whereId($stored->id)->update([
                'token'      => hash('sha256', $newRefreshToken),
                'expires_at' => now()->addDays(3),
                'access_jti' => $tokenId,
            ]);

            DB::commit();

            $cookieName = $organisation === 'sports' ? 'refresh_sports' : 'refresh_services';
            $refreshCookie = cookie(
                $cookieName,
                $newRefreshToken,
                60 * 24 * 15,   // 15 days
                '/api',
                null,
                true,          // secure=false for local; true for HTTPS
                true,           // HttpOnly
                false,
                'Lax'
            );

            return response()->json([
                'data'        => new UserResource($user),
                'token'       => $tokenResult->accessToken,
                'token_type'  => 'Bearer',
                'expires_in'  => 15 * 60,
            ])->cookie($refreshCookie);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Refresh error', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    // --------------------------------------------

    public function logout(Request $request, $organisation)
    {
        UserLogin::where(['user_id' => Auth::id()])->delete();

        $user = $request->user();
        User::where('id', $user->id)->update(['logged_in' => false]);
        $tokenModel = $user ? $user->token() : null;

        if ($tokenModel) {
            $tokenId = $tokenModel->id;
            $tokenModel->revoke();
            AccessToken::where('jti', $tokenId)->update(['revoked' => true]);
            AccessToken::where('jti', $tokenId)->delete();
            UsedNonce::where('token_id', $tokenId)->delete();
            $jkt = $tokenModel->dpop_jkt ?? null;
            if ($jkt) DpopProof::where('jkt', $jkt)->delete();
        }

        $request->user()->token()->revoke();

        $refreshToken = $organisation == 'sports' ? $request->cookie('refresh_sports') : $request->cookie('refresh_services');

        if ($refreshToken) {
            RefreshToken::where('token', hash('sha256', $refreshToken))
                ->where('revoked', false)
                ->update(['revoked' => true]);
            RefreshToken::where('token', hash('sha256', $refreshToken))->delete();
        }
        $cookieName = $organisation === 'sports' ? 'refresh_sports' : 'refresh_services';
        $forgetCookie = cookie()->forget($cookieName, '/api', null, false, true, false, 'Lax');

        return response()->json(['message' => 'Logged out successfully'])
            ->withCookie($forgetCookie);
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

            // Log::info("Reset link: " . $link);

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
        return UserResource::make(Auth::user());
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
