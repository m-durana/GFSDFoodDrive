<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEYS = [
        'openrouteservice_key',
        'twilio_sid',
        'twilio_token',
    ];

    public function up(): void
    {
        foreach (self::KEYS as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();
            if (! $setting || $setting->value === null || $setting->value === '' || $this->isEncrypted((string) $setting->value)) {
                continue;
            }

            DB::table('settings')
                ->where('key', $key)
                ->update(['value' => Crypt::encryptString((string) $setting->value)]);
        }

        \App\Models\Setting::clearCache();
    }

    public function down(): void
    {
        foreach (self::KEYS as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();
            if (! $setting || $setting->value === null || $setting->value === '') {
                continue;
            }

            try {
                $value = Crypt::decryptString((string) $setting->value);
            } catch (\Throwable) {
                continue;
            }

            DB::table('settings')
                ->where('key', $key)
                ->update(['value' => $value]);
        }

        \App\Models\Setting::clearCache();
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
