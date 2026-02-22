<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

/**
 * Converts legacy integer permission levels to Spatie roles.
 *
 * This migration only runs if spatie/laravel-permission is installed
 * and the roles table exists. Safe to skip if Spatie isn't available yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Skip if Spatie permission tables don't exist yet
        if (! Schema::hasTable('roles') || ! class_exists(\Spatie\Permission\Models\Role::class)) {
            return;
        }

        $Role = \Spatie\Permission\Models\Role::class;

        // Ensure roles exist
        $Role::findOrCreate('family', 'web');
        $Role::findOrCreate('coordinator', 'web');
        $Role::findOrCreate('santa', 'web');

        // Map permission integers to role names
        $permissionMap = [
            7 => 'family',
            8 => 'coordinator',
            9 => 'santa',
        ];

        foreach ($permissionMap as $level => $roleName) {
            $users = User::where('permission', $level)->get();
            foreach ($users as $user) {
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole($roleName);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->truncate();
        }
    }
};
