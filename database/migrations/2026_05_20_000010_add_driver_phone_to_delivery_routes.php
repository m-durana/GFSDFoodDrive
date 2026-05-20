<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REL-05: store the driver's SMS-capable phone on the route so DriverRouteReady
 * has a destination. We don't have a phone column on `users`, and dragging
 * one in for this single use-case would touch too many forms.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->string('driver_phone', 32)->nullable()->after('driver_name');
            $table->timestamp('driver_notified_at')->nullable()->after('driver_phone');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropColumn(['driver_phone', 'driver_notified_at']);
        });
    }
};
