<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maps legacy `child` table to Laravel `children` table.
     *
     * Legacy columns: ChildID, FamilyID, Gender, Age, School,
     * ClothesSize, ClothingStyles, ClothingOptions, GiftPreferences
     */
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();                                                          // ChildID
            $table->foreignId('family_id')->constrained()->onDelete('cascade');    // FamilyID FK
            $table->string('gender');                                              // Gender (M, F, Other)
            $table->string('age');                                                 // Age (kept as string)
            $table->string('school')->nullable();                                  // School (MWE, MCE, GFMS, GFHS, CRHS, None/Other)
            $table->text('clothes_size')->nullable();                              // ClothesSize
            $table->text('clothing_styles')->nullable();                           // ClothingStyles
            $table->text('clothing_options')->nullable();                          // ClothingOptions
            $table->text('gift_preferences')->nullable();                          // GiftPreferences
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
