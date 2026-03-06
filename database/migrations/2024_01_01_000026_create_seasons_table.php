<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->unique();
            $table->unsignedInteger('total_families')->default(0);
            $table->unsignedInteger('total_children')->default(0);
            $table->unsignedInteger('total_family_members')->default(0);
            $table->unsignedInteger('total_adults')->default(0);
            $table->unsignedInteger('gifts_level_0')->default(0);
            $table->unsignedInteger('gifts_level_1')->default(0);
            $table->unsignedInteger('gifts_level_2')->default(0);
            $table->unsignedInteger('gifts_level_3')->default(0);
            $table->unsignedInteger('deliveries_completed')->default(0);
            $table->unsignedInteger('pickups_completed')->default(0);
            $table->unsignedInteger('tags_adopted')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
