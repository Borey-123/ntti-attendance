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
            $locale = session('portal_locale', $request->cookie('portal_locale', config('app.locale')));
            App::setLocale($locale);
        } elseif ($request->is('live*') || $request->is('api-live*')) {
            $locale = session('live_locale', $request->cookie('live_locale', config('app.locale')));
            App::setLocale($locale);
        } else {
            if (session()->has('locale')) {
                App::setLocale(session()->get('locale'));
            } elseif ($request->hasCookie('locale')) {
                App::setLocale($request->cookie('locale'));
                session(['locale' => $request->cookie('locale')]);
            }
        }

        return $next($request);
    }
}
