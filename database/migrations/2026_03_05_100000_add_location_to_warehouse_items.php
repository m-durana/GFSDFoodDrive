<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->string('location_zone', 10)->nullable()->after('active');
            $table->string('location_shelf', 10)->nullable()->after('location_zone');
            $table->string('location_bin', 20)->nullable()->after('location_shelf');
            $table->index(['location_zone', 'location_shelf', 'location_bin'], 'warehouse_items_location_index');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropIndex('warehouse_items_location_index');
            $table->dropColumn(['location_zone', 'location_shelf', 'location_bin']);
        });
    }
};
