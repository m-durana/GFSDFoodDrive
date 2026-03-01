<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_bank_items', function (Blueprint $table) {
            $table->id();
            $table->integer('season_year');
            $table->string('description', 500);
            $table->string('age_range', 50)->nullable();
            $table->string('gender_suitability', 20)->nullable();
            $table->string('gift_type', 100)->nullable();
            $table->string('donor_name', 200)->nullable();
            $table->integer('quantity')->default(1);
            $table->foreignId('assigned_child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_bank_items');
    }
};
