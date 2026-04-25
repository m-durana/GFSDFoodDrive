<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->string('driver_pin_hash')->nullable()->after('access_token');
            $table->text('driver_pin_encrypted')->nullable()->after('driver_pin_hash');
        });

        DB::table('delivery_routes')
            ->whereNull('driver_pin_hash')
            ->orderBy('id')
            ->each(function ($route) {
                $pin = (string) random_int(100000, 999999);
                DB::table('delivery_routes')->where('id', $route->id)->update([
                    'driver_pin_hash' => Hash::make($pin),
                    'driver_pin_encrypted' => Crypt::encryptString($pin),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropColumn(['driver_pin_hash', 'driver_pin_encrypted']);
        });
    }
};
