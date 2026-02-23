<?php

namespace Database\Seeders;

use App\Models\SchoolRange;
use Illuminate\Database\Seeder;

class SchoolRangeSeeder extends Seeder
{
    public function run(): void
    {
        $ranges = [
            // Standard GFSD schools — edit names here if schools change year-to-year
            ['school_name' => 'Crossroads', 'range_start' => 1, 'range_end' => 99, 'sort_order' => 1],
            ['school_name' => 'GFHS', 'range_start' => 100, 'range_end' => 199, 'sort_order' => 2],
            ['school_name' => 'GFMS', 'range_start' => 200, 'range_end' => 299, 'sort_order' => 3],
            ['school_name' => 'Monte Cristo', 'range_start' => 300, 'range_end' => 399, 'sort_order' => 4],
            ['school_name' => 'Mountain Way', 'range_start' => 400, 'range_end' => 499, 'sort_order' => 5],
            ['school_name' => 'Special Case', 'range_start' => 500, 'range_end' => 599, 'sort_order' => 6],
        ];

        foreach ($ranges as $range) {
            SchoolRange::firstOrCreate(
                ['school_name' => $range['school_name']],
                $range
            );
        }
    }
}
