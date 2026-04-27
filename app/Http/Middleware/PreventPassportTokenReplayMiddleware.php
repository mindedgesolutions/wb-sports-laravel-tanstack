<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use App\Models\UsedNonce;
use App\Models\DpopProof;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class PreventPassportTokenReplayMiddleware
{
    protected int $allowedSkew = 30;      // seconds
    protected int $minNonceLength = 32;   // chars

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tokenModel = $user ? $user->token() : null;

        if (!$user || !$tokenModel) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $tokenId = (string) $tokenModel->id;
        $tokenRow = AccessToken::where('jti', $tokenId)->first();
        $tokenDpopJkt = $tokenRow->dpop_jkt ?? null;

        $dpopHeader = $request->header('DPoP');
        $payload = null;
        $dpopInserted = false;

        if ($tokenDpopJkt) {
            if (!$dpopHeader) {
                return response()->json(['error' => 'DPoP required for this token'], 401);
            }

            try {
                [$headerB64, $payloadB64] = explode('.', $dpopHeader) + [null, null];
                if (!$headerB64 || !$payloadB64) {
                    return response()->json(['error' => 'Invalid DPoP header format'], 400);
                }

                $header = json_decode(base64url_decode($headerB64), true);
                if (empty($header['jwk']) || !is_array($header['jwk'])) {
                    return response()->json(['error' => 'DPoP jwk missing or malformed'], 400);
                }

                $jwk = $header['jwk'];

                $jkt = compute_jwk_thumbprint($jwk);
                if (!hash_equals((string)$jkt, (string)$tokenDpopJkt)) {
                    Log::warning('DPoP jkt mismatch', [
                        'user_id' => $user->id ?? null,
                        'token_jkt' => substr((string)$tokenDpopJkt, 0, 16) . '...',
                        'incoming_jkt' => substr((string)$jkt, 0, 16) . '...'
                    ]);
                    return response()->json(['error' => 'DPoP key mismatch'], 401);
                }

                $payload = verify_dpop_jwt($dpopHeader, $jwk, $request->method(), $request->getRequestUri(), $this->allowedSkew);

                $jti = $payload['jti'] ?? null;
                if (!$jti) {
                    return response()->json(['error' => 'DPoP missing jti'], 400);
                }

                DB::beginTransaction();
                try {
                    $dp = DpopProof::create([
                        'jti' => $jti,
                        'jkt' => $jkt,
                        'expires_at' => Carbon::now()->addSeconds(60),
                    ]);
                    $dpopInserted = true;
                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollBack();
                    Log::info('DPoP proof replay detected', ['jti' => substr($jti, 0, 16) . '...', 'err' => $e->getMessage()]);
                    return response()->json(['error' => 'DPoP proof replay detected'], 401);
                }
            } catch (\Throwable $e) {
                Log::error('DPoP verification failed', ['err' => $e->getMessage()]);
                return response()->json(['error' => 'Invalid DPoP proof'], 401);
            }
        } else {
        }

        $nonce = $request->header('X-Request-Nonce');
        $tsHeader = $request->header('X-Request-Ts');

        if (!$nonce || !$tsHeader) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json(['error' => 'Missing nonce or timestamp header'], 400);
        }

        if (!is_numeric($tsHeader)) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json(['error' => 'Invalid timestamp header'], 400);
        }

        $ts = (int) $tsHeader;
        $now = time();

        if (abs($now - $ts) > $this->allowedSkew) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json(['error' => 'Invalid timestamp/nonce (time skew)'], 400);
        }

        if (mb_strlen($nonce) < $this->minNonceLength) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json(['error' => 'Nonce too short'], 400);
        }

        $tokenExpiresAt = $tokenModel->expires_at ?? null;
        $secondsUntilTokenExpires = null;
        if ($tokenExpiresAt instanceof Carbon) {
            $secondsUntilTokenExpires = $tokenExpiresAt->diffInSeconds(Carbon::now());
        } elseif ($tokenExpiresAt) {
            try {
                $secondsUntilTokenExpires = Carbon::parse($tokenExpiresAt)->diffInSeconds(Carbon::now());
            } catch (\Throwable $e) {
                $secondsUntilTokenExpires = null;
            }
        }

        $ttlSeconds = max(5 * 60, $secondsUntilTokenExpires ?? (5 * 60));
        $expiresAt = Carbon::now()->addSeconds($ttlSeconds);

        try {
            UsedNonce::create([
                'token_id' => $tokenId,
                'nonce' => $nonce,
                'expires_at' => $expiresAt,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            $sqlState = $e->errorInfo[0] ?? null;
            $message = $e->getMessage();

            if ($sqlState === '23505' || $sqlState === '23000' || stripos($message, 'duplicate') !== false || stripos($message, 'unique') !== false) {
                Log::info('Replay detected', [
                    'user_id' => $user->id ?? null,
                    'token_id' => (strlen($tokenId) > 16 ? substr($tokenId, 0, 16) . '...' : $tokenId),
                    'nonce' => (strlen($nonce) > 16 ? substr($nonce, 0, 16) . '...' : $nonce),
                    'sqlstate' => $sqlState,
                ]);
                return response()->json(['error' => 'Replay detected'], 401);
            }

            Log::error('DB error inserting used nonce', [
                'message' => $message,
                'errorInfo' => $e->errorInfo ?? null,
                'user_id' => $user->id ?? null,
                'token_id' => (strlen($tokenId) > 16 ? substr($tokenId, 0, 16) . '...' : $tokenId),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Log::error('Unhandled exception in PreventPassportTokenReplayMiddleware', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }

        if (DB::transactionLevel() > 0) {
            try {
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to commit transaction in middleware', ['err' => $e->getMessage()]);
                return response()->json(['error' => 'Internal Server Error'], 500);
            }
        }

        return $next($request);
    }
}
