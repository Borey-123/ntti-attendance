<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static $cache = [];

    protected static function booted()
    {
        static::saved(function ($setting) {
            self::$cache[$setting->key] = $setting->value;
        });
        static::deleted(function ($setting) {
            unset(self::$cache[$setting->key]);
        });
    }

    /**
     * Get a setting value by key, optionally providing a default.
     * Uses in-memory caching to prevent redundant database queries in the same request.
     */
    public static function getValue($key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        $setting = self::where('key', $key)->first();
        $value = ($setting && !empty($setting->value)) ? $setting->value : null;
        self::$cache[$key] = $value;

        return $value !== null ? $value : $default;
    }

    /**
     * Get asset URL for a setting key, validating file existence if stored in storage directory.
     */
    public static function getAssetUrl($key, $default = null)
    {
        $val = self::getValue($key, null);
        if ($val) {
            if (str_starts_with($val, 'data:image/') || str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                return $val;
            }
            $relativePath = ltrim($val, '/');
            if (str_starts_with($relativePath, 'storage/')) {
                $publicPath = public_path($relativePath);
                if (file_exists($publicPath)) {
                    return url($val);
                }
                // File does not exist on disk, fallback to default
                return $default ? url($default) : null;
            }
            return url($val);
        }
        return $default ? url($default) : null;
    }
}
