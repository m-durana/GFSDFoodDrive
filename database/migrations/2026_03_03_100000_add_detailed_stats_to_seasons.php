<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            // Only add columns that don't already exist
            if (!Schema::hasColumn('seasons', 'families_severe_need')) {
                $table->integer('families_severe_need')->default(0)->after('tags_adopted');
            }
            if (!Schema::hasColumn('seasons', 'families_with_pets')) {
                $table->integer('families_with_pets')->default(0)->after('families_severe_need');
            }
            if (!Schema::hasColumn('seasons', 'families_needing_baby_supplies')) {
                $table->integer('families_needing_baby_supplies')->default(0)->after('families_with_pets');
            }
            if (!Schema::hasColumn('seasons', 'children_by_age_group')) {
                $table->json('children_by_age_group')->nullable()->after('families_needing_baby_supplies');
            }
            if (!Schema::hasColumn('seasons', 'families_by_school')) {
                $table->json('families_by_school')->nullable()->after('children_by_age_group');
            }
            if (!Schema::hasColumn('seasons', 'families_by_size')) {
                $table->json('families_by_size')->nullable()->after('families_by_school');
            }
            if (!Schema::hasColumn('seasons', 'families_by_language')) {
                $table->json('families_by_language')->nullable()->after('families_by_size');
            }
            if (!Schema::hasColumn('seasons', 'families_by_delivery_date')) {
                $table->json('families_by_delivery_date')->nullable()->after('families_by_language');
            }
            if (!Schema::hasColumn('seasons', 'warehouse_stats')) {
                $table->json('warehouse_stats')->nullable()->after('families_by_delivery_date');
            }
            if (!Schema::hasColumn('seasons', 'avg_family_size')) {
                $table->decimal('avg_family_size', 4, 1)->default(0)->after('warehouse_stats');
            }
            if (!Schema::hasColumn('seasons', 'avg_children_per_family')) {
                $table->decimal('avg_children_per_family', 4, 1)->default(0)->after('avg_family_size');
            }
            if (!Schema::hasColumn('seasons', 'adoption_rate')) {
                $table->decimal('adoption_rate', 5, 1)->default(0)->after('avg_children_per_family');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $cols = [
                'families_severe_need', 'families_with_pets', 'families_needing_baby_supplies',
                'children_by_age_group', 'families_by_school', 'families_by_size',
                'families_by_language', 'families_by_delivery_date', 'warehouse_stats',
                'avg_family_size', 'avg_children_per_family', 'adoption_rate',
            ];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('seasons', $c));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};
