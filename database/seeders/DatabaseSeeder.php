<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            WarehouseCategorySeeder::class,
            GroceryItemSeeder::class,
            SchoolRangeSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                TestDataSeeder::class,
            ]);
        }

        // To import legacy data, run: php artisan db:seed --class=LegacyDataSeeder
    }
}
