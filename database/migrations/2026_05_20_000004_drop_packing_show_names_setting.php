<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'packing_show_names')->delete();
        }
    }

    public function down(): void
    {
        // Intentionally empty — the setting was replaced by User::canSeePii(),
        // so re-adding the row on rollback would not restore any behaviour.
    }
};
