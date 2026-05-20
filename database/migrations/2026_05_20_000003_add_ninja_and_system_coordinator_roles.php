<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! class_exists(\Spatie\Permission\Models\Role::class)) {
            return;
        }

        $Role = \Spatie\Permission\Models\Role::class;
        $Role::findOrCreate('ninja', 'web');
        $Role::findOrCreate('system_coordinator', 'web');
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles') || ! class_exists(\Spatie\Permission\Models\Role::class)) {
            return;
        }

        $Role = \Spatie\Permission\Models\Role::class;
        foreach (['ninja', 'system_coordinator'] as $name) {
            $role = $Role::where('name', $name)->where('guard_name', 'web')->first();
            if ($role) {
                $role->delete();
            }
        }
    }
};
