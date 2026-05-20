<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that gain soft-delete support: users, delivery_routes, delivery_teams,
     * children, grocery_items, school_ranges, shopping_assignments.
     */
    private array $tables = [
        'users',
        'delivery_routes',
        'delivery_teams',
        'children',
        'grocery_items',
        'school_ranges',
        'shopping_assignments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->softDeletes();
                $t->index('deleted_at', "{$table}_deleted_at_idx");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex("{$table}_deleted_at_idx");
                $t->dropSoftDeletes();
            });
        }
    }
};
