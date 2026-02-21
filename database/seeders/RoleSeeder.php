<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Create the application roles.
     *
     * Legacy permission mapping:
     *   7 -> Family (family entry volunteers)
     *   8 -> Coordinator (section coordinators)
     *   9 -> Santa (admin / full access)
     */
    public function run(): void
    {
        $this->command->info('Creating roles...');

        Role::findOrCreate('family', 'web');
        Role::findOrCreate('coordinator', 'web');
        Role::findOrCreate('santa', 'web');

        $this->command->info('Roles created: family, coordinator, santa');
    }
}
