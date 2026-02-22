<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add columns discovered from PDF spec + 2019-2021 database analysis.
     */
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('preferred_language')->nullable()->after('email');
            $table->boolean('needs_baby_supplies')->default(false)->after('has_gfhs_children');
            $table->text('delivery_reason')->nullable()->after('delivery_time');
            $table->string('delivery_team')->nullable()->after('delivery_reason');
            $table->string('delivery_status')->nullable()->after('delivery_team');
            $table->boolean('family_done')->default(false)->after('other_questions');
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_language',
                'needs_baby_supplies',
                'delivery_reason',
                'delivery_team',
                'delivery_status',
                'family_done',
            ]);
        });
    }
};
