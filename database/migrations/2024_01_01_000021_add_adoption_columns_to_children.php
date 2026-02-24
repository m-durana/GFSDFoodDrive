<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->timestamp('adopted_at')->nullable();
            $table->string('adoption_token', 64)->nullable()->unique();
            $table->date('adoption_deadline')->nullable();
            $table->boolean('gift_dropped_off')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['adopted_at', 'adoption_token', 'adoption_deadline', 'gift_dropped_off']);
        });
    }
};
