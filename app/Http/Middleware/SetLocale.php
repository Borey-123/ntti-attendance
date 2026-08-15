<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('portal*') || $request->is('api-web/portal*')) {
            App::setLocale(session('portal_locale', config('app.locale')));
        } elseif ($request->is('live*') || $request->is('api-live*')) {
            App::setLocale(session('live_locale', config('app.locale')));
        } else {
            if (session()->has('locale')) {
                App::setLocale(session()->get('locale'));
            }
        }

        return $next($request);
    }
}
