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
            return redirect('/login');
        });
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect('/login')->with('error', 'Your session expired. Please try logging in again.');
        });
        $exceptions->renderable(function (\Illuminate\Foundation\Http\Exceptions\TokenMismatchException $e, $request) {
            return redirect('/login')->with('error', 'Your session expired. Please try logging in again.');
        });
        $exceptions->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return response('The uploaded file is too large for the server limits. Please upload a smaller file or increase post_max_size in php.ini. <br><br><a href="javascript:history.back()">Click here to go back</a>', 413);
        });
        $exceptions->renderable(function (\PDOException $e, $request) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, '2002') || str_contains($msg, 'refused')) {
                return response('Database Connection Error: Could not connect to Database server. Please ensure MySQL or your DB service is running.', 500);
            }
        });
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'no such table') || str_contains($msg, "doesn't exist")) {
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                    return redirect()->to('/login')->with('success', 'Database tables automatically initialized!');
                } catch (\Throwable $ex) {
                    return response('Database Schema Error: Table missing (' . $e->getMessage() . '). Auto-migration failed: ' . $ex->getMessage(), 500);
                }
            }
            if (str_contains($msg, '2002') || str_contains($msg, 'refused')) {
                return response('Database Connection Error: Could not connect to Database server.', 500);
            }
        });
    })->create();
