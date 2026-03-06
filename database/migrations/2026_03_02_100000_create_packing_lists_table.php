<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('season_year', 10);
            $table->string('status', 20)->default('pending');
            $table->foreignId('assigned_volunteer')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('qr_token')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['family_id', 'season_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_lists');
    }
};
