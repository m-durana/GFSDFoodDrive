<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->string('volunteer_name', 200)->nullable()->after('scanned_by');
            $table->string('ip_address', 45)->nullable()->after('volunteer_name');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropColumn(['volunteer_name', 'ip_address']);
        });
    }
};
