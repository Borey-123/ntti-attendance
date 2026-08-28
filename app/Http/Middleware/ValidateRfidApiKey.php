<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SecurityLog;

class ValidateRfidApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawKeys = env('RFID_API_KEY', 'SCANNER_01,SCANNER_02');
        $allowedKeys = array_filter(array_map('trim', explode(',', $rawKeys)));
        $providedKey = $request->input('device_id');

        if (empty($providedKey) || !in_array($providedKey, $allowedKeys)) {
            SecurityLog::record('Unauthorized API Access', $request->ip(), "Provided device_id: " . ($providedKey ?? 'none'));
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized device.'
            ], 401);
        }

        return $next($request);
    }
}
