<?php

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $setting = DB::table('settings')->where('key', 'google_client_secret')->first();
        if (! $setting || $setting->value === null || $setting->value === '') {
            return;
        }

        try {
            Crypt::decryptString($setting->value);
            return;
        } catch (DecryptException) {
            DB::table('settings')
                ->where('key', 'google_client_secret')
                ->update(['value' => Crypt::encryptString($setting->value)]);
            Setting::clearCache();
        }
    }

    public function down(): void
    {
        $setting = DB::table('settings')->where('key', 'google_client_secret')->first();
        if (! $setting || $setting->value === null || $setting->value === '') {
            return;
        }

        try {
            DB::table('settings')
                ->where('key', 'google_client_secret')
                ->update(['value' => Crypt::decryptString($setting->value)]);
            Setting::clearCache();
        } catch (DecryptException) {
            // Already plaintext.
        }
    }
};
