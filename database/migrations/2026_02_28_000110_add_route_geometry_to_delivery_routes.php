<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->json('route_geometry')->nullable()->after('driver_location_at');
            $table->timestamp('geometry_updated_at')->nullable()->after('route_geometry');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropColumn(['route_geometry', 'geometry_updated_at']);
        });
    }
};
