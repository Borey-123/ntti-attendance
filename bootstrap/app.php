<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\RestrictSchoolIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Contracts\Encryption\DecryptException $e, $request) {
            return redirect('/login')->withCookie(cookie()->forget(config('session.cookie')));
        });
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->back()->with('error', 'Your session expired. Please try submitting again.');
        });
        $exceptions->renderable(function (\Illuminate\Foundation\Http\Exceptions\TokenMismatchException $e, $request) {
            return redirect()->back()->with('error', 'Your session expired. Please try submitting again.');
        });
        $exceptions->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return redirect()->back()->with('error', 'The uploaded file is too large for server limits. Please upload a smaller SQL file or increase upload_max_filesize in php.ini.');
        });
        $exceptions->renderable(function (\PDOException $e, $request) {
            if (str_contains($e->getMessage(), '2002') || str_contains(strtolower($e->getMessage()), 'refused')) {
                return redirect()->back()->with('error', 'Database Connection Error: Could not connect to MySQL. Please make sure MySQL is started in XAMPP Control Panel.');
            }
        });
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if (str_contains($e->getMessage(), '2002') || str_contains(strtolower($e->getMessage()), 'refused')) {
                return redirect()->back()->with('error', 'Database Connection Error: Could not connect to MySQL. Please make sure MySQL is started in XAMPP Control Panel.');
            }
        });
    })->create();
