<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DynamicQrService
{
    const ROTATION_SECONDS = 20;
    const TOLERANCE_SECONDS = 35; // Allow reasonable network delay for mobile capture

    /**
     * Generate the current valid dynamic QR payload and expiration info.
     */
    public static function generateToken(): array
    {
        $now = Carbon::now();
        $timestamp = $now->timestamp;
        $window = floor($timestamp / self::ROTATION_SECONDS);
        $expiresIn = self::ROTATION_SECONDS - ($timestamp % self::ROTATION_SECONDS);

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
            'interval'    => self::ROTATION_SECONDS,
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
            // Extract token if full URL was scanned by camera
            if (str_contains($token, 'checkin_token=')) {
                parse_str(parse_url($token, PHP_URL_QUERY) ?? '', $queryParams);
                if (!empty($queryParams['checkin_token'])) {
                    $token = $queryParams['checkin_token'];
                }
            }

            $decoded = base64_decode($token, true);
            if (!$decoded) {
                return false;
            }

            $json = json_decode($decoded, true);
            if (!$json || !isset($json['w'], $json['t'], $json['sig'])) {
                return false;
            }

            $currentTimestamp = Carbon::now()->timestamp;
            $tokenTimestamp = (int)$json['t'];

            // Check if token timestamp is older than allowable tolerance window
            if (($currentTimestamp - $tokenTimestamp) > self::TOLERANCE_SECONDS || ($tokenTimestamp - $currentTimestamp) > 10) {
                return false;
            }

            $window = (int)$json['w'];
            $currentWindow = floor($currentTimestamp / self::ROTATION_SECONDS);

            // Allow current window or immediately preceding window (tolerance for 20s transition)
            if (abs($currentWindow - $window) > 1) {
                return false;
            }

            $secret = config('app.key', 'ntti-qr-secret-key');
            $data = "ntti-dynamic-qr:{$window}";
            $expectedSignature = substr(hash_hmac('sha256', $data, $secret), 0, 16);

            return hash_equals($expectedSignature, $json['sig']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
