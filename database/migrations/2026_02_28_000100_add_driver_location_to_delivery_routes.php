<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->decimal('driver_lat', 10, 7)->nullable()->after('start_lng');
            $table->decimal('driver_lng', 10, 7)->nullable()->after('driver_lat');
            $table->timestamp('driver_location_at')->nullable()->after('driver_lng');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropColumn(['driver_lat', 'driver_lng', 'driver_location_at']);
        });
    }
};
