<?php
// required at top of file:
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Decode base64url to binary string
 */
function base64url_decode(string $data): string
{
  $remainder = strlen($data) % 4;
  if ($remainder) $data .= str_repeat('=', 4 - $remainder);
  return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Convert raw ECDSA signature (r|s) to ASN.1/DER encoded signature
 * Input: $sig is binary string of length 64 (r(32) || s(32)) for P-256.
 * Returns DER binary string.
 */
function ecdsa_raw_to_der(string $sig): string
{
  // Split into r and s
  $len = strlen($sig);
  if ($len % 2 !== 0) {
    throw new \Exception('Invalid ECDSA raw signature length');
  }
  $half = $len / 2;
  $r = substr($sig, 0, $half);
  $s = substr($sig, $half);

  // Trim leading zero bytes
  $r = ltrim($r, "\x00");
  $s = ltrim($s, "\x00");

  // Ensure MSB not set: if it is, prefix 0x00
  if (strlen($r) === 0 || (ord($r[0]) & 0x80)) $r = "\x00" . $r;
  if (strlen($s) === 0 || (ord($s[0]) & 0x80)) $s = "\x00" . $s;

  $rb = "\x02" . chr(strlen($r)) . $r;
  $sb = "\x02" . chr(strlen($s)) . $s;
  $seq = $rb . $sb;
  return "\x30" . chr(strlen($seq)) . $seq;
}

/**
 * Build a PEM formatted EC public key (P-256) from JWK (x,y base64url)
 */
function jwk_ec_to_pem(array $jwk): string
{
  if (!isset($jwk['kty']) || $jwk['kty'] !== 'EC') {
    throw new \Exception('JWK is not EC');
  }
  if (!isset($jwk['crv'], $jwk['x'], $jwk['y'])) {
    throw new \Exception('JWK missing crv/x/y');
  }
  // Only P-256 supported in this helper
  if ($jwk['crv'] !== 'P-256') {
    throw new \Exception('Unsupported EC curve: ' . $jwk['crv']);
  }

  $x = base64url_decode($jwk['x']);
  $y = base64url_decode($jwk['y']);

  // Uncompressed EC point
  $pubKeyBytes = "\x04" . $x . $y;

  // SubjectPublicKeyInfo prefix for P-256 (ASN.1 DER)
  // Prefix is constant for EC public key (OID for prime256v1)
  $spkiPrefix = hex2bin(
    '3059301306072a8648ce3d020106082a8648ce3d030107034200'
  ); // DER header before the public key bytes

  $der = $spkiPrefix . $pubKeyBytes;
  $pem = "-----BEGIN PUBLIC KEY-----\n" .
    chunk_split(base64_encode($der), 64, "\n") .
    "-----END PUBLIC KEY-----\n";

  return $pem;
}

/**
 * Verify DPoP JWT using supplied JWK (EC P-256 expected).
 *
 * @param string $dpopJwt - compact JWS
 * @param array $jwk - associative array (kty, crv, x, y)
 * @param string $method - expected HTTP method (GET/POST)
 * @param string $htu - expected htu claim (absolute URL) or base path (normalize accordingly)
 * @param int $allowedSkewSeconds - allowed iat skew
 * @return array decoded payload
 * @throws \Exception on invalid signature / claims
 */
function verify_dpop_jwt(string $dpopJwt, array $jwk, string $method, string $htu, int $allowedSkewSeconds = 60): array
{
  // split token
  $parts = explode('.', $dpopJwt);
  if (count($parts) !== 3) {
    throw new \Exception('Invalid DPoP JWT format');
  }
  list($h_b64, $p_b64, $s_b64) = $parts;

  $headerJson = base64url_decode($h_b64);
  $payloadJson = base64url_decode($p_b64);
  $sig = base64url_decode($s_b64);

  $header = json_decode($headerJson, true);
  $payload = json_decode($payloadJson, true);

  if (!is_array($header) || !is_array($payload)) {
    throw new \Exception('Invalid DPoP JWT header or payload JSON');
  }

  if (empty($header['jwk']) || !is_array($header['jwk'])) {
    throw new \Exception('DPoP missing jwk in header');
  }

  $alg = $header['alg'] ?? ($jwk['alg'] ?? 'ES256');
  if ($alg !== 'ES256') {
    throw new \Exception('Unsupported alg: ' . $alg);
  }
  if (($jwk['kty'] ?? null) !== 'EC') {
    throw new \Exception('Unsupported JWK kty: ' . ($jwk['kty'] ?? 'null'));
  }
  if (($jwk['crv'] ?? null) !== 'P-256') {
    throw new \Exception('Unsupported crv: ' . ($jwk['crv'] ?? 'null'));
  }

  // Verify signature
  $pem = jwk_ec_to_pem($jwk);
  $derSig = ecdsa_raw_to_der($sig);
  $signedInput = $h_b64 . '.' . $p_b64;

  $ok = openssl_verify($signedInput, $derSig, $pem, OPENSSL_ALGO_SHA256);
  if ($ok !== 1) {
    throw new \Exception('DPoP signature verification failed');
  }

  // Validate claims
  if (empty($payload['htm']) || strcasecmp($payload['htm'], $method) !== 0) {
    throw new \Exception('DPoP htm (method) mismatch');
  }

  if (empty($payload['htu'])) {
    throw new \Exception('DPoP htu missing');
  }

  /**
   * Normalize and compare paths more flexibly
   */
  $normalize = function (string $url): string {
    $path = parse_url($url, PHP_URL_PATH) ?: '/';
    $path = rawurldecode($path);
    // remove "/api" prefix if present
    $path = preg_replace('#^/api#', '', $path);
    // remove trailing slashes except root
    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
  };

  $reqPath = $normalize($htu);
  $payloadPath = $normalize($payload['htu']);

  if ($reqPath !== $payloadPath) {
    Log::info('DPoP htu mismatch', [
      'expected' => $reqPath,
      'received' => $payloadPath,
      'raw_htu' => $htu,
      'payload_htu' => $payload['htu']
    ]);
    throw new \Exception('DPoP htu (path) mismatch');
  }

  $iat = isset($payload['iat']) ? (int)$payload['iat'] : null;
  if ($iat === null || abs(time() - $iat) > $allowedSkewSeconds) {
    throw new \Exception('DPoP iat invalid or too old');
  }

  if (empty($payload['jti'])) {
    throw new \Exception('DPoP jti missing');
  }

  return $payload;
}

/**
 * Compute JWK thumbprint per RFC7638 (sha256, base64url).
 * Supports EC (kty=EC, members: crv, kty, x, y) and RSA (kty=RSA, members: e, kty, n).
 *
 * @param array $jwk  Associative array of the JWK (decoded JSON)
 * @return string base64url-encoded thumbprint
 * @throws \Exception on unsupported key type or missing members
 */
function compute_jwk_thumbprint(array $jwk): string
{
  if (empty($jwk['kty'])) {
    throw new \Exception('JWK missing kty');
  }

  if ($jwk['kty'] === 'EC') {
    // required members: crv, kty, x, y (order matters lexicographically)
    foreach (['crv', 'kty', 'x', 'y'] as $m) {
      if (!array_key_exists($m, $jwk)) {
        throw new \Exception("JWK missing required member for EC: {$m}");
      }
    }
    // canonical JSON must have keys in lexicographic order per RFC7638
    $members = [
      'crv' => $jwk['crv'],
      'kty' => 'EC',
      'x'   => $jwk['x'],
      'y'   => $jwk['y'],
    ];
  } elseif ($jwk['kty'] === 'RSA') {
    foreach (['e', 'kty', 'n'] as $m) {
      if (!array_key_exists($m, $jwk)) {
        throw new \Exception("JWK missing required member for RSA: {$m}");
      }
    }
    $members = [
      'e'   => $jwk['e'],
      'kty' => 'RSA',
      'n'   => $jwk['n'],
    ];
  } else {
    throw new \Exception('Unsupported JWK kty for thumbprint: ' . $jwk['kty']);
  }

  // JSON with no extra spaces and unescaped slashes
  $json = json_encode($members, JSON_UNESCAPED_SLASHES);

  // sha256 raw digest, then base64url encode
  $digest = hash('sha256', $json, true);
  $b64 = rtrim(strtr(base64_encode($digest), '+/', '-_'), '=');
  return $b64;
}
