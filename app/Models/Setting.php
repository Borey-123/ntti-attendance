<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, optionally providing a default.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return ($setting && !empty($setting->value)) ? $setting->value : $default;
    }

    /**
     * Get asset URL for a setting key, validating file existence if stored in storage directory.
     */
    public static function getAssetUrl($key, $default = null)
    {
        $val = self::getValue($key, null);
        if ($val) {
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
