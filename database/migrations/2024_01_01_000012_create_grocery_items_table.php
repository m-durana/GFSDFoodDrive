<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');  // canned, dry, personal, condiment
            $table->unsignedSmallInteger('qty_1')->default(0);
            $table->unsignedSmallInteger('qty_2')->default(0);
            $table->unsignedSmallInteger('qty_3')->default(0);
            $table->unsignedSmallInteger('qty_4')->default(0);
            $table->unsignedSmallInteger('qty_5')->default(0);
            $table->unsignedSmallInteger('qty_6')->default(0);
            $table->unsignedSmallInteger('qty_7')->default(0);
            $table->unsignedSmallInteger('qty_8')->default(0);
            $table->boolean('conditional')->default(false); // only include when family flags match
            $table->string('condition_field')->nullable(); // e.g. 'needs_baby_supplies', 'pet_information'
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grocery_items');
    }
};
