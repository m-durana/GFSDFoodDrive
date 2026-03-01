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
            ['name' => 'Condiments/Sauces', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'COND', 'sort_order' => 5],
            ['name' => 'Beverages', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'BEV', 'sort_order' => 6],
            ['name' => 'Snacks', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'SNK', 'sort_order' => 7],
            ['name' => 'Baking Supplies', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'BAK', 'sort_order' => 8],
            ['name' => 'Baby Supplies', 'type' => 'baby', 'unit' => 'item', 'barcode_prefix' => 'BABY', 'sort_order' => 9],
            ['name' => 'Diapers', 'type' => 'baby', 'unit' => 'pack', 'barcode_prefix' => 'DIAP', 'sort_order' => 10],
            ['name' => 'Formula', 'type' => 'baby', 'unit' => 'can', 'barcode_prefix' => 'FORM', 'sort_order' => 11],
            ['name' => 'Gift - Boy Under 6', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB6', 'sort_order' => 12],
            ['name' => 'Gift - Boy 6-12', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB12', 'sort_order' => 13],
            ['name' => 'Gift - Boy 13-17', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GB17', 'sort_order' => 14],
            ['name' => 'Gift - Girl Under 6', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG6', 'sort_order' => 15],
            ['name' => 'Gift - Girl 6-12', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG12', 'sort_order' => 16],
            ['name' => 'Gift - Girl 13-17', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GG17', 'sort_order' => 17],
            ['name' => 'Gift - Neutral', 'type' => 'gift', 'unit' => 'item', 'barcode_prefix' => 'GN', 'sort_order' => 18],
            ['name' => 'Frozen Foods', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'FRZ', 'sort_order' => 19],
            ['name' => 'Dairy/Refrigerated', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'DAI', 'sort_order' => 20],
            ['name' => 'Pasta/Rice/Grains', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'PAS', 'sort_order' => 21],
            ['name' => 'Soups/Broths', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'SOUP', 'sort_order' => 22],
            ['name' => 'Breakfast Items', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'BRK', 'sort_order' => 23],
            ['name' => 'Produce', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'PRD', 'sort_order' => 24],
            ['name' => 'Protein/Meat', 'type' => 'food', 'unit' => 'item', 'barcode_prefix' => 'PROT', 'sort_order' => 25],
            ['name' => 'Personal Care', 'type' => 'supply', 'unit' => 'item', 'barcode_prefix' => 'CARE', 'sort_order' => 26],
            ['name' => 'Hygiene Bundle', 'type' => 'supply', 'unit' => 'bundle', 'barcode_prefix' => 'HYG', 'sort_order' => 27],
            ['name' => 'Pet Supplies', 'type' => 'supply', 'unit' => 'item', 'barcode_prefix' => 'PET', 'sort_order' => 28],
        ];

        foreach ($categories as $cat) {
            WarehouseCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
