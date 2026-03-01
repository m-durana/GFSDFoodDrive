<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->boolean('is_severe_need')->default(false)->after('severe_need');
            $table->text('severe_need_notes')->nullable()->after('is_severe_need');
        });

        // Migrate existing severe_need text data to the boolean + notes
        DB::table('families')
            ->whereNotNull('severe_need')
            ->where('severe_need', '!=', '')
            ->where('severe_need', '!=', '0')
            ->where('severe_need', '!=', 'No')
            ->update([
                'is_severe_need' => true,
                'severe_need_notes' => DB::raw('severe_need'),
            ]);
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn(['is_severe_need', 'severe_need_notes']);
        });
    }
};
