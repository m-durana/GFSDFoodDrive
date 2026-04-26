<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static array $cache = [];

    private const ENCRYPTED_KEYS = [
        'google_client_secret',
        'openrouteservice_key',
        'twilio_sid',
        'twilio_token',
    ];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        $value = $setting ? static::decodeValue($key, $setting->value) : $default;
        static::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value by key (create or update).
     */
    public static function set(string $key, mixed $value): void
    {
        $oldValue = static::get($key);
        $storedValue = static::encodeValue($key, $value);

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue]
        );

        static::$cache[$key] = $value;
        static::audit($key, 'set', $oldValue, $value);
    }

    public static function forget(string $key): void
    {
        $oldValue = static::get($key);

        static::where('key', $key)->delete();
        unset(static::$cache[$key]);

        static::audit($key, 'delete', $oldValue, null);
    }

    /**
     * Clear the in-memory cache.
     */
    public static function clearCache(): void
    {
        static::$cache = [];
    }

    public static function shouldEncrypt(string $key): bool
    {
        return in_array($key, self::ENCRYPTED_KEYS, true);
    }

    private static function encodeValue(string $key, mixed $value): mixed
    {
        if (! static::shouldEncrypt($key) || $value === null || $value === '') {
            return $value;
        }

        return Crypt::encryptString((string) $value);
    }

    private static function decodeValue(string $key, mixed $value): mixed
    {
        if (! static::shouldEncrypt($key) || $value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            return $value;
        }
    }

    private static function audit(string $key, string $action, mixed $oldValue, mixed $newValue): void
    {
        if (! Schema::hasTable('setting_audit_logs') || $oldValue === $newValue) {
            return;
        }

        SettingAuditLog::create([
            'user_id' => auth()->id(),
            'key' => $key,
            'action' => $action,
            'old_value' => static::auditValue($key, $oldValue),
            'new_value' => static::auditValue($key, $newValue),
        ]);
    }

    private static function auditValue(string $key, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (static::isSensitiveKey($key)) {
            return '[redacted]';
        }

        return (string) $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        return static::shouldEncrypt($key)
            || str_contains($key, 'secret')
            || str_contains($key, 'token')
            || str_contains($key, 'sid');
    }
}
