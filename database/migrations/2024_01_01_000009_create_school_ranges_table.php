<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * School-to-number-range mapping for family number assignment.
     * Ranges are configurable because schools may change year to year.
     */
    public function up(): void
    {
        Schema::create('school_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->unsignedInteger('range_start');
            $table->unsignedInteger('range_end');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_ranges');
    }
};
