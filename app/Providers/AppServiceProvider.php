<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->secure() || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}

if (!function_exists('to_asset_url')) {
    function to_asset_url($path) {
        if (empty($path)) return '';
        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $trimmed = ltrim($path, '/');
        if (str_starts_with($trimmed, 'teachers/') || str_starts_with($trimmed, 'portal/')) {
            return url('storage/' . $trimmed);
        }
        return url($trimmed);
    }
}
