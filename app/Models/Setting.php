<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static array $cache = [];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        $value = $setting ? $setting->value : $default;
        static::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value by key (create or update).
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        static::$cache[$key] = $value;
    }

    /**
     * Clear the in-memory cache.
     */
    public static function clearCache(): void
    {
        static::$cache = [];
    }
}
