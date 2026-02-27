<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create delivery_teams table
        Schema::create('delivery_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->nullable(); // hex e.g. #dc2626
            $table->foreignId('driver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('driver_name')->nullable();
            $table->text('notes')->nullable();
            $table->smallInteger('season_year')->nullable()->index();
            $table->timestamps();
        });

        // 2. Add delivery_team_id FK to families
        Schema::table('families', function (Blueprint $table) {
            $table->foreignId('delivery_team_id')->nullable()->after('delivery_team')->constrained('delivery_teams')->nullOnDelete();
        });

        // 3. Migrate existing delivery_team string values → delivery_teams records
        $seasonYear = DB::table('settings')->where('key', 'season_year')->value('value') ?? date('Y');

        $existingTeams = DB::table('families')
            ->select('delivery_team')
            ->whereNotNull('delivery_team')
            ->where('delivery_team', '!=', '')
            ->distinct()
            ->pluck('delivery_team');

        foreach ($existingTeams as $teamName) {
            $teamId = DB::table('delivery_teams')->insertGetId([
                'name' => $teamName,
                'season_year' => $seasonYear,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('families')
                ->where('delivery_team', $teamName)
                ->update(['delivery_team_id' => $teamId]);
        }

        // 4. Add indexes to families
        Schema::table('families', function (Blueprint $table) {
            $table->index('delivery_status');
            $table->index(['delivery_route_id', 'route_order']);
            $table->index('delivery_team_id');
        });

        // 5. Add compound index to delivery_logs
        Schema::table('delivery_logs', function (Blueprint $table) {
            $table->index(['family_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('delivery_logs', function (Blueprint $table) {
            $table->dropIndex(['family_id', 'created_at']);
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropIndex(['delivery_status']);
            $table->dropIndex(['delivery_route_id', 'route_order']);
            $table->dropIndex(['delivery_team_id']);
            $table->dropForeign(['delivery_team_id']);
            $table->dropColumn('delivery_team_id');
        });

        Schema::dropIfExists('delivery_teams');
    }
};
