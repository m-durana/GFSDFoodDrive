<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dismissed_duplicates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_a_id')->constrained('families')->cascadeOnDelete();
            $table->foreignId('family_b_id')->constrained('families')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['family_a_id', 'family_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dismissed_duplicates');
    }
};
