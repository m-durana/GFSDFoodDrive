<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add season_year to families
        Schema::table('families', function (Blueprint $table) {
            $table->smallInteger('season_year')->nullable()->index()->after('id');
        });

        // Add season_year to children
        Schema::table('children', function (Blueprint $table) {
            $table->smallInteger('season_year')->nullable()->index()->after('id');
        });

        // Backfill existing records with current season year
        $currentYear = DB::table('settings')->where('key', 'season_year')->value('value') ?? date('Y');
        DB::table('families')->whereNull('season_year')->update(['season_year' => $currentYear]);
        DB::table('children')->whereNull('season_year')->update(['season_year' => $currentYear]);

        // Make season_year NOT NULL after backfill
        // SQLite doesn't support ALTER COLUMN, so we just leave it nullable but always set it

        // Replace unique index on family_number with composite (family_number, season_year)
        // SQLite requires raw SQL for dropping indexes
        try {
            DB::statement('DROP INDEX IF EXISTS families_family_number_unique');
        } catch (\Exception $e) {
            // Index may not exist
        }

        Schema::table('families', function (Blueprint $table) {
            $table->unique(['family_number', 'season_year'], 'families_number_season_unique');
        });
    }

    public function down(): void
    {
        try {
            DB::statement('DROP INDEX IF EXISTS families_number_season_unique');
        } catch (\Exception $e) {
            // ignore
        }

        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn('season_year');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('season_year');
        });
    }
};
