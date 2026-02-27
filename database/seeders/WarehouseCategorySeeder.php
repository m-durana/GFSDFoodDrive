<?php

namespace Database\Seeders;

use App\Models\WarehouseCategory;
use Illuminate\Database\Seeder;

class WarehouseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Food Box (Large)', 'type' => 'food', 'unit' => 'box', 'barcode_prefix' => 'FB-L', 'sort_order' => 1],
            ['name' => 'Food Box (Small)', 'type' => 'food', 'unit' => 'box', 'barcode_prefix' => 'FB-S', 'sort_order' => 2],
            ['name' => 'Canned Goods', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'CAN', 'sort_order' => 3],
            ['name' => 'Dry Goods', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'DRY', 'sort_order' => 4],
            ['name' => 'Baby Supplies', 'type' => 'baby', 'unit' => 'item', 'barcode_prefix' => 'BABY', 'sort_order' => 5],
            ['name' => 'Diapers', 'type' => 'baby', 'unit' => 'pack', 'barcode_prefix' => 'DIAP', 'sort_order' => 6],
            ['name' => 'Formula', 'type' => 'baby', 'unit' => 'can', 'barcode_prefix' => 'FORM', 'sort_order' => 7],
            ['name' => 'Gift - Boy Under 6', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB6', 'sort_order' => 8],
            ['name' => 'Gift - Boy 6-12', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB12', 'sort_order' => 9],
            ['name' => 'Gift - Boy 13-17', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB17', 'sort_order' => 10],
            ['name' => 'Gift - Girl Under 6', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG6', 'sort_order' => 11],
            ['name' => 'Gift - Girl 6-12', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG12', 'sort_order' => 12],
            ['name' => 'Gift - Girl 13-17', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG17', 'sort_order' => 13],
            ['name' => 'Gift - Neutral', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GN', 'sort_order' => 14],
            ['name' => 'Hygiene Bundle', 'type' => 'supply', 'unit' => 'bundle', 'barcode_prefix' => 'HYG', 'sort_order' => 15],
            ['name' => 'Pet Supplies', 'type' => 'supply', 'unit' => 'item', 'barcode_prefix' => 'PET', 'sort_order' => 16],
        ];

        foreach ($categories as $cat) {
            WarehouseCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
