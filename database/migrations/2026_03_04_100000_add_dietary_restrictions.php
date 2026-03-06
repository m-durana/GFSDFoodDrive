<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->json('dietary_restrictions')->nullable()->after('pet_information');
            $table->text('dietary_notes')->nullable()->after('dietary_restrictions');
        });

        Schema::table('grocery_items', function (Blueprint $table) {
            $table->json('dietary_flags')->nullable()->after('sort_order');
            $table->json('dietary_tags')->nullable()->after('dietary_flags');
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn(['dietary_restrictions', 'dietary_notes']);
        });

        Schema::table('grocery_items', function (Blueprint $table) {
            $table->dropColumn(['dietary_flags', 'dietary_tags']);
        });
    }
};
