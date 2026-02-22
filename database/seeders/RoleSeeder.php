<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Create the application roles.
     *
     * Requires spatie/laravel-permission to be installed.
     * Run `composer update` first if you haven't already.
     *
     * Legacy permission mapping:
     *   6 -> Self-Service (families self-registering, toggleable by admin)
     *   7 -> Family (advisor volunteers who enter data for families)
     *   8 -> Coordinator (section coordinators, run reports/mail merges)
     *   9 -> Santa (admin / full access)
     */
    public function run(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Role::class)) {
            $this->command->warn('Spatie Permission not installed. Run `composer update` first. Skipping role creation.');
            return;
        }

        $this->command->info('Creating roles...');

        $Role = \Spatie\Permission\Models\Role::class;
        $Role::findOrCreate('self_service', 'web');
        $Role::findOrCreate('family', 'web');
        $Role::findOrCreate('coordinator', 'web');
        $Role::findOrCreate('santa', 'web');

        $this->command->info('Roles created: self_service, family, coordinator, santa');
    }
}
