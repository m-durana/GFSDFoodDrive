<?php

namespace App\Console\Commands;

use Database\Seeders\GroceryItemSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolRangeSeeder;
use Database\Seeders\TestDataSeeder;
use Illuminate\Console\Command;

class SeedTestData extends Command
{
    protected $signature = 'fooddrive:seed
                            {--fresh : Wipe the database first (migrate:fresh)}
                            {--no-grocery : Skip grocery item seeding}';

    protected $description = 'Seed the database with test data for development and feature testing';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Wiping database and re-running migrations...');
            $this->call('migrate:fresh');
            $this->newLine();
        }

        $this->info('Seeding GFSD Food Drive test data...');
        $this->newLine();

        // 1. Roles (required before users)
        $this->components->task('Seeding roles', function () {
            $this->callSilently('db:seed', ['--class' => RoleSeeder::class]);
        });

        // 2. School ranges (required before family number assignment)
        $this->components->task('Seeding school ranges (GFSD defaults)', function () {
            $this->callSilently('db:seed', ['--class' => SchoolRangeSeeder::class]);
        });

        // 3. Grocery items
        if (! $this->option('no-grocery')) {
            $this->components->task('Seeding grocery items (110+ items from 2019 data)', function () {
                $this->callSilently('db:seed', ['--class' => GroceryItemSeeder::class]);
            });
        }

        // 4. Test families, children, and users
        $this->components->task('Seeding test users, families & children', function () {
            $this->callSilently('db:seed', ['--class' => TestDataSeeder::class]);
        });

        $this->newLine();
        $this->info('Done! Test data is ready.');
        $this->newLine();

        $this->table(
            ['Login', 'Username', 'Password', 'Role'],
            [
                ['Santa (Admin)', 'santa_admin', 'password', 'santa (9)'],
                ['Family Advisor', 'family_advisor', 'password', 'family (7)'],
                ['Coordinator', 'coord_01', 'password', 'coordinator (8)'],
            ]
        );

        $this->newLine();
        $this->line('  25 families seeded (15 with numbers, 10 without)');
        $this->line('  70+ children with realistic data across all schools');
        $this->line('  110+ grocery items for shopping companion');
        $this->newLine();
        $this->line('  School ranges: Crossroads (1-99), GFHS (100-199), GFMS (200-299),');
        $this->line('                 Monte Cristo (300-399), Mountain Way (400-499), Special Case (500-599)');
        $this->newLine();

        return Command::SUCCESS;
    }
}
