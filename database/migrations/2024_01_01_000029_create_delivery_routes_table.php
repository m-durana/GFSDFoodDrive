<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('driver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('driver_name')->nullable();
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->unsignedInteger('total_distance_meters')->nullable();
            $table->unsignedInteger('total_duration_seconds')->nullable();
            $table->unsignedSmallInteger('stop_count')->default(0);
            $table->string('access_token', 32)->unique();
            $table->smallInteger('season_year')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('families', function (Blueprint $table) {
            $table->foreignId('delivery_route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->unsignedSmallInteger('route_order')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_route_id');
            $table->dropColumn('route_order');
        });
        Schema::dropIfExists('delivery_routes');
    }
};
