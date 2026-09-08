<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    protected static array $cache = [];

    /**
     * Get a payroll setting value, with in-memory cache.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        $setting = static::where('key', $key)->first();
        $value = $setting?->value;
        self::$cache[$key] = $value;

        return $value !== null ? $value : $default;
    }

    /**
     * Get typed value (casts based on 'type' field).
     */
    public static function getTyped(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match($setting->type) {
            'integer' => (int) $setting->value,
            'decimal' => (float) $setting->value,
            'boolean' => (bool) $setting->value,
            default   => $setting->value,
        };
    }

    /**
     * Set a payroll setting value.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache[$key] = $value;
    }

    /**
     * Get all settings as key => value map.
     */
    public static function getAllMap(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
