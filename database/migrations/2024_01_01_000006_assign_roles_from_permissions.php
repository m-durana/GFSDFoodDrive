<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Converts legacy integer permission levels to Spatie roles.
 *
 * This migration:
 * 1. Ensures the roles exist
 * 2. Assigns roles based on the permission column value
 * 3. Keeps the permission column for backward compatibility
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ensure roles exist
        Role::findOrCreate('family', 'web');
        Role::findOrCreate('coordinator', 'web');
        Role::findOrCreate('santa', 'web');

        // Map permission integers to role names
        $permissionMap = [
            7 => 'family',
            8 => 'coordinator',
            9 => 'santa',
        ];

        foreach ($permissionMap as $level => $roleName) {
            $users = User::where('permission', $level)->get();
            foreach ($users as $user) {
                $user->assignRole($roleName);
            }
        }
    }

    public function down(): void
    {
        // Remove all role assignments (permission column still has the data)
        DB::table('model_has_roles')->truncate();
    }
};
