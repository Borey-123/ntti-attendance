<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSchoolIp
{
    /**
     * Handle an incoming request.
     * Restricts Admin Login and Protected Dashboard routes to the designated School Wi-Fi IP address.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIpsEnv = env('SCHOOL_WIFI_IP');

        // If SCHOOL_WIFI_IP is not set, empty, or disabled, allow all access (e.g., during testing)
        if (empty($allowedIpsEnv)) {
            return $next($request);
        }

        // Support multiple comma-separated IPs (e.g. "203.144.1.2, 110.74.5.6")
        $allowedIps = array_map('trim', explode(',', $allowedIpsEnv));
        
        // Retrieve client IP address
        $clientIp = $request->header('X-Forwarded-For') 
            ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) 
            : $request->ip();

        // Always allow localhost loopbacks for local testing/CLI
        if (in_array($clientIp, ['127.0.0.1', '::1'])) {
            return $next($request);
        }

        if (!in_array($clientIp, $allowedIps) && !in_array('*', $allowedIps)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access Denied: Admin system can only be accessed from the official School Wi-Fi Network.'
                ], 403);
            }

            return response()->view('errors.403_school_ip', [
                'clientIp' => $clientIp
            ], 403);
        }

        return $next($request);
    }
}
