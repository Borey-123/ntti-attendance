<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DynamicQrService
{
    public static function getRotationInterval(): int
    {
        try {
            $val = (int)\App\Models\Setting::getValue('kiosk_qr_rotation', 20);
            return ($val >= 10 && $val <= 120) ? $val : 20;
        } catch (\Throwable $e) {
            return 20;
        }
    }

    /**
     * Generate the current valid dynamic QR payload and expiration info.
     */
    public static function generateToken(): array
    {
        $rotation = self::getRotationInterval();
        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $window = floor($timestamp / $rotation);
        $expiresIn = $rotation - ($timestamp % $rotation);

        $secret = config('app.key', 'ntti-qr-secret-key');
        $data = "ntti-dynamic-qr:{$window}";
        $signature = hash_hmac('sha256', $data, $secret);

        $payload = base64_encode(json_encode([
            'type' => 'NTTI_DYNAMIC_QR',
            'w'    => $window,
            't'    => $timestamp,
            'sig'  => substr($signature, 0, 16),
            'salt' => substr(md5($window . $secret), 0, 6),
        ]));

        return [
            'token'       => $payload,
            'url'         => url('/portal?checkin_token=' . urlencode($payload)),
            'expires_in'  => (int)$expiresIn,
            'interval'    => $rotation,
            'window'      => $window,
            'server_time' => $now->toIso8601String(),
        ];
    }

    /**
     * Validate a scanned dynamic QR token.
     */
    public static function validateToken(string $token): bool
    {
        try {
            $token = trim($token);
            if (empty($token)) {
                return false;
            }

            // 1. Extract token if full URL or deep link was scanned by camera
            if (str_contains($token, 'checkin_token=')) {
                $query = parse_url($token, PHP_URL_QUERY);
                if ($query) {
                    parse_str($query, $queryParams);
                    if (!empty($queryParams['checkin_token'])) {
                        $token = $queryParams['checkin_token'];
                    }
                }
            }

            // 2. Fix base64 spacing (+ often turns into space in URL params)
            $token = str_replace(' ', '+', $token);
            $token = urldecode($token);
            $token = str_replace(' ', '+', $token);

            $decoded = base64_decode($token, true);
            if (!$decoded) {
                // Try padding if truncated
                $padLen = 4 - (strlen($token) % 4);
                if ($padLen < 4) {
                    $token .= str_repeat('=', $padLen);
                    $decoded = base64_decode($token, true);
                }
            }

            if (!$decoded) {
                return false;
            }

            $json = json_decode($decoded, true);
            if (!$json || !isset($json['w'], $json['t'], $json['sig'])) {
                return false;
            }

            $currentTimestamp = Carbon::now()->timestamp;
            $tokenTimestamp = (int)$json['t'];
            $rotation = self::getRotationInterval();

            // 3. Generous tolerance window: allow at least 180 seconds (3 minutes) or up to 6 rotation cycles
            $maxAge = max($rotation * 5, 180);

            // Allow future clock skew up to 120 seconds in case phone/kiosk clocks are slightly desynced
            if (($currentTimestamp - $tokenTimestamp) > $maxAge || ($tokenTimestamp - $currentTimestamp) > 120) {
                return false;
            }

            $window = (int)$json['w'];
            $currentWindow = floor($currentTimestamp / $rotation);

            // Allow up to 6 rotation windows of difference
            if (abs($currentWindow - $window) > 6) {
                return false;
            }

            // 4. Verify HMAC signature against configured app key and common fallback keys
            $data = "ntti-dynamic-qr:{$window}";
            $keysToTest = array_filter([
                config('app.key'),
                'base64:QUJTygmqFptS3f2YATeU3IO3ors51VqPbfqHWrF4Irk=', // Local key
                'base64:yg1qAcfZboEFi0ekLt8X/uOt/paHNqyxFc7dUFHz77s=', // Vultr live key
                'ntti-qr-secret-key',
            ]);

            foreach ($keysToTest as $key) {
                $expected = substr(hash_hmac('sha256', $data, $key), 0, 16);
                if (hash_equals($expected, $json['sig'])) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
