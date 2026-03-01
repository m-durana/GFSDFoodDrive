<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->timestamp('returning_at')->nullable()->after('driver_location_at');
            $table->timestamp('completed_at')->nullable()->after('returning_at');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropColumn(['returning_at', 'completed_at']);
        });
    }
};
