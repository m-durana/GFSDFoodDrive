<?php

namespace App\Services;

use App\Enums\GiftLevel;
use App\Enums\TransactionType;
use App\Models\Child;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WarehouseService
{
    private const OFF_CATEGORY_MAP = [
        // Canned goods
        'en:canned-foods' => 'Canned Goods',
        'en:canned-food' => 'Canned Goods',
        'en:canned-vegetables' => 'Canned Goods',
        'en:canned-fruits' => 'Canned Goods',
        'en:canned-soups' => 'Canned Goods',
        'en:canned-meals' => 'Canned Goods',
        'en:canned-meat' => 'Canned Goods',
        'en:canned-seafood' => 'Canned Goods',
        'en:canned-beans' => 'Canned Goods',
        'en:tinned-foods' => 'Canned Goods',
        'en:canned-fish' => 'Canned Goods',
        'en:canned-tomatoes' => 'Canned Goods',
        'en:canned-corn' => 'Canned Goods',

        // Dry goods
        'en:breakfast-cereals' => 'Dry Goods',
        'en:cereals' => 'Dry Goods',
        'en:cereals-and-their-products' => 'Dry Goods',
        'en:cereals-and-potatoes' => 'Dry Goods',
        'en:ready-to-eat-cereals' => 'Dry Goods',
        'en:extruded-cereals' => 'Dry Goods',
        'en:puffed-rice-cereals' => 'Dry Goods',
        'en:flaked-cereals' => 'Dry Goods',
        'en:oatmeals' => 'Dry Goods',
        'en:breads' => 'Dry Goods',
        'en:bread' => 'Dry Goods',
        'en:dry-soups' => 'Dry Goods',
        'en:instant-noodles' => 'Dry Goods',
        'en:noodles' => 'Dry Goods',
        'en:dried-products' => 'Dry Goods',
        'en:legumes' => 'Dry Goods',
        'en:dried-beans' => 'Dry Goods',
        'en:lentils' => 'Dry Goods',
        'en:flour' => 'Dry Goods',

        // Pasta/Rice/Grains
        'en:pasta' => 'Pasta/Rice/Grains',
        'en:pastas' => 'Pasta/Rice/Grains',
        'en:pasta-dishes' => 'Pasta/Rice/Grains',
        'en:rice' => 'Pasta/Rice/Grains',
        'en:grains' => 'Pasta/Rice/Grains',
        'en:whole-grains' => 'Pasta/Rice/Grains',
        'en:quinoa' => 'Pasta/Rice/Grains',
        'en:couscous' => 'Pasta/Rice/Grains',
        'en:macaroni' => 'Pasta/Rice/Grains',
        'en:spaghetti' => 'Pasta/Rice/Grains',

        // Soups/Broths
        'en:soups' => 'Soups/Broths',
        'en:broths' => 'Soups/Broths',
        'en:bouillons' => 'Soups/Broths',
        'en:soup-mixes' => 'Soups/Broths',
        'en:condensed-soups' => 'Soups/Broths',
        'en:instant-soups' => 'Soups/Broths',

        // Condiments & sauces
        'en:condiments' => 'Condiments/Sauces',
        'en:sauces' => 'Condiments/Sauces',
        'en:ketchup' => 'Condiments/Sauces',
        'en:mustard' => 'Condiments/Sauces',
        'en:mustards' => 'Condiments/Sauces',
        'en:mayonnaise' => 'Condiments/Sauces',
        'en:mayonnaises' => 'Condiments/Sauces',
        'en:salad-dressings' => 'Condiments/Sauces',
        'en:spices' => 'Condiments/Sauces',
        'en:seasonings' => 'Condiments/Sauces',
        'en:vinegars' => 'Condiments/Sauces',
        'en:hot-sauces' => 'Condiments/Sauces',
        'en:barbecue-sauces' => 'Condiments/Sauces',
        'en:spreads' => 'Condiments/Sauces',
        'en:peanut-butter' => 'Condiments/Sauces',
        'en:peanut-butters' => 'Condiments/Sauces',
        'en:jams' => 'Condiments/Sauces',
        'en:jams-and-marmalades' => 'Condiments/Sauces',
        'en:honey' => 'Condiments/Sauces',
        'en:honeys' => 'Condiments/Sauces',
        'en:syrups' => 'Condiments/Sauces',
        'en:tomato-sauces' => 'Condiments/Sauces',
        'en:pasta-sauces' => 'Condiments/Sauces',
        'en:soy-sauces' => 'Condiments/Sauces',

        // Beverages
        'en:beverages' => 'Beverages',
        'en:juices' => 'Beverages',
        'en:fruit-juices' => 'Beverages',
        'en:soft-drinks' => 'Beverages',
        'en:sodas' => 'Beverages',
        'en:waters' => 'Beverages',
        'en:bottled-waters' => 'Beverages',
        'en:coffee' => 'Beverages',
        'en:coffees' => 'Beverages',
        'en:tea' => 'Beverages',
        'en:teas' => 'Beverages',
        'en:drink-powders' => 'Beverages',
        'en:energy-drinks' => 'Beverages',
        'en:sports-drinks' => 'Beverages',
        'en:cocoa-and-chocolate-powders' => 'Beverages',
        'en:hot-chocolate-mixes' => 'Beverages',

        // Snacks
        'en:snacks' => 'Snacks',
        'en:chips' => 'Snacks',
        'en:crisps' => 'Snacks',
        'en:crackers' => 'Snacks',
        'en:cookies' => 'Snacks',
        'en:biscuits' => 'Snacks',
        'en:candy' => 'Snacks',
        'en:chocolate' => 'Snacks',
        'en:chocolates' => 'Snacks',
        'en:nuts' => 'Snacks',
        'en:popcorn' => 'Snacks',
        'en:granola-bars' => 'Snacks',
        'en:salty-snacks' => 'Snacks',
        'en:sweet-snacks' => 'Snacks',
        'en:pretzels' => 'Snacks',
        'en:trail-mixes' => 'Snacks',
        'en:dried-fruits' => 'Snacks',
        'en:fruit-snacks' => 'Snacks',
        'en:rice-cakes' => 'Snacks',

        // Baking
        'en:baking' => 'Baking Supplies',
        'en:baking-mixes' => 'Baking Supplies',
        'en:baking-powders' => 'Baking Supplies',
        'en:baking-sodas' => 'Baking Supplies',
        'en:flours' => 'Baking Supplies',
        'en:sugars' => 'Baking Supplies',
        'en:yeasts' => 'Baking Supplies',
        'en:cake-mixes' => 'Baking Supplies',
        'en:pancake-mixes' => 'Baking Supplies',

        // Baby
        'en:baby-foods' => 'Baby Supplies',
        'en:baby' => 'Baby Supplies',
        'en:infant-formulas' => 'Baby Supplies',
        'en:baby-cereals' => 'Baby Supplies',
        'en:baby-snacks' => 'Baby Supplies',

        // Frozen Foods
        'en:frozen-foods' => 'Frozen Foods',
        'en:frozen-meals' => 'Frozen Foods',
        'en:frozen-vegetables' => 'Frozen Foods',
        'en:frozen-fruits' => 'Frozen Foods',
        'en:frozen-pizzas' => 'Frozen Foods',
        'en:frozen-desserts' => 'Frozen Foods',
        'en:ice-creams' => 'Frozen Foods',

        // Dairy/Refrigerated
        'en:dairy-products' => 'Dairy/Refrigerated',
        'en:dairies' => 'Dairy/Refrigerated',
        'en:cheeses' => 'Dairy/Refrigerated',
        'en:milks' => 'Dairy/Refrigerated',
        'en:yogurts' => 'Dairy/Refrigerated',
        'en:butters' => 'Dairy/Refrigerated',
        'en:eggs' => 'Dairy/Refrigerated',
        'en:cream' => 'Dairy/Refrigerated',

        // Breakfast Items
        'en:breakfast' => 'Breakfast Items',
        'en:breakfast-foods' => 'Breakfast Items',
        'en:pancakes' => 'Breakfast Items',
        'en:waffles' => 'Breakfast Items',
        'en:muesli' => 'Breakfast Items',

        // Protein/Meat
        'en:meats' => 'Protein/Meat',
        'en:meat' => 'Protein/Meat',
        'en:fish' => 'Protein/Meat',
        'en:seafood' => 'Protein/Meat',
        'en:poultry' => 'Protein/Meat',
        'en:beef' => 'Protein/Meat',
        'en:pork' => 'Protein/Meat',
        'en:chicken' => 'Protein/Meat',
        'en:tuna' => 'Protein/Meat',
        'en:salmon' => 'Protein/Meat',
        'en:jerky' => 'Protein/Meat',

        // Personal Care
        'en:body-care' => 'Personal Care',
        'en:shampoos' => 'Personal Care',
        'en:soaps' => 'Personal Care',
        'en:toothpastes' => 'Personal Care',
        'en:deodorants' => 'Personal Care',
    ];

    private const OFF_TAG_KEYWORDS = [
        'canned' => 'Canned Goods',
        'tinned' => 'Canned Goods',
        'pasta' => 'Pasta/Rice/Grains',
        'spaghetti' => 'Pasta/Rice/Grains',
        'macaroni' => 'Pasta/Rice/Grains',
        'noodle' => 'Pasta/Rice/Grains',
        'rice' => 'Pasta/Rice/Grains',
        'grain' => 'Pasta/Rice/Grains',
        'quinoa' => 'Pasta/Rice/Grains',
        'cereal' => 'Dry Goods',
        'oat' => 'Dry Goods',
        'bread' => 'Dry Goods',
        'legume' => 'Dry Goods',
        'lentil' => 'Dry Goods',
        'bean' => 'Dry Goods',
        'soup' => 'Soups/Broths',
        'broth' => 'Soups/Broths',
        'bouillon' => 'Soups/Broths',
        'condiment' => 'Condiments/Sauces',
        'sauce' => 'Condiments/Sauces',
        'ketchup' => 'Condiments/Sauces',
        'mustard' => 'Condiments/Sauces',
        'mayo' => 'Condiments/Sauces',
        'dressing' => 'Condiments/Sauces',
        'spice' => 'Condiments/Sauces',
        'seasoning' => 'Condiments/Sauces',
        'vinegar' => 'Condiments/Sauces',
        'peanut butter' => 'Condiments/Sauces',
        'jam' => 'Condiments/Sauces',
        'honey' => 'Condiments/Sauces',
        'syrup' => 'Condiments/Sauces',
        'beverage' => 'Beverages',
        'juice' => 'Beverages',
        'soda' => 'Beverages',
        'coffee' => 'Beverages',
        'tea' => 'Beverages',
        'drink' => 'Beverages',
        'snack' => 'Snacks',
        'chips' => 'Snacks',
        'cracker' => 'Snacks',
        'cookie' => 'Snacks',
        'candy' => 'Snacks',
        'chocolate' => 'Snacks',
        'popcorn' => 'Snacks',
        'granola' => 'Snacks',
        'pretzel' => 'Snacks',
        'nut' => 'Snacks',
        'baking' => 'Baking Supplies',
        'flour' => 'Baking Supplies',
        'sugar' => 'Baking Supplies',
        'yeast' => 'Baking Supplies',
        'cake mix' => 'Baking Supplies',
        'baby' => 'Baby Supplies',
        'infant' => 'Baby Supplies',
        'formula' => 'Baby Supplies',
        'diaper' => 'Baby Supplies',
        'frozen' => 'Frozen Foods',
        'ice cream' => 'Frozen Foods',
        'dairy' => 'Dairy/Refrigerated',
        'cheese' => 'Dairy/Refrigerated',
        'milk' => 'Dairy/Refrigerated',
        'yogurt' => 'Dairy/Refrigerated',
        'butter' => 'Dairy/Refrigerated',
        'meat' => 'Protein/Meat',
        'chicken' => 'Protein/Meat',
        'beef' => 'Protein/Meat',
        'tuna' => 'Protein/Meat',
        'salmon' => 'Protein/Meat',
        'jerky' => 'Protein/Meat',
        'shampoo' => 'Personal Care',
        'soap' => 'Personal Care',
        'toothpaste' => 'Personal Care',
    ];

    /**
     * Get current stock grouped by category.
     * Returns collection keyed by category_id with on_hand totals.
     */
    public function currentStock(?int $seasonYear = null): Collection
    {
        $seasonYear = $seasonYear ?? (int) Setting::get('season_year', date('Y'));

        return WarehouseTransaction::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->selectRaw('category_id, SUM(CASE WHEN transaction_type IN (?, ?) THEN quantity ELSE -quantity END) as on_hand', [
                TransactionType::In->value, TransactionType::Return->value
            ])
            ->groupBy('category_id')
            ->pluck('on_hand', 'category_id');
    }

    /**
     * Compare stock on hand vs family needs.
     * Returns array of deficit info per category.
     */
    public function categoryDeficits(?int $seasonYear = null): array
    {
        $seasonYear = $seasonYear ?? (int) Setting::get('season_year', date('Y'));
        $stock = $this->currentStock($seasonYear);
        $categories = WarehouseCategory::active()->orderBy('sort_order')->get();

        // Calculate needs from families
        $totalBoxes = Family::sum('number_of_boxes') ?: 0;
        $babyFamilies = Family::where('needs_baby_supplies', true)->count();

        // Children by gender/age for gift categories
        $children = Child::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->whereHas('family', fn($q) => $q->withoutGlobalScopes()->where('season_year', $seasonYear)->whereNotNull('family_number'))
            ->get(['gender', 'age']);

        $giftNeeds = [
            'Gift - Boy Under 6' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'male' && ($c->age ?? 0) < 6)->count(),
            'Gift - Boy 6-12' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'male' && ($c->age ?? 0) >= 6 && ($c->age ?? 0) <= 12)->count(),
            'Gift - Boy 13-17' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'male' && ($c->age ?? 0) >= 13)->count(),
            'Gift - Girl Under 6' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'female' && ($c->age ?? 0) < 6)->count(),
            'Gift - Girl 6-12' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'female' && ($c->age ?? 0) >= 6 && ($c->age ?? 0) <= 12)->count(),
            'Gift - Girl 13-17' => $children->filter(fn($c) => strtolower($c->gender ?? '') === 'female' && ($c->age ?? 0) >= 13)->count(),
            'Gift - Neutral' => 0,
        ];

        $deficits = [];
        foreach ($categories as $cat) {
            $needed = 0;
            if (str_contains($cat->name, 'Food Box (Large)')) {
                $needed = $totalBoxes;
            } elseif ($cat->type === 'baby') {
                $needed = $babyFamilies;
            } elseif (isset($giftNeeds[$cat->name])) {
                $needed = $giftNeeds[$cat->name];
            }

            $onHand = (int) ($stock[$cat->id] ?? 0);
            $deficits[] = [
                'category' => $cat,
                'needed' => $needed,
                'on_hand' => $onHand,
                'deficit' => $needed - $onHand,
            ];
        }

        return $deficits;
    }

    /**
     * Record a receipt (incoming item).
     */
    public function recordReceipt(array $data, ?User $user = null): WarehouseTransaction
    {
        return WarehouseTransaction::create([
            'category_id' => $data['category_id'],
            'item_id' => $data['item_id'] ?? null,
            'transaction_type' => TransactionType::In,
            'quantity' => $data['quantity'] ?? 1,
            'source' => $data['source'] ?? null,
            'donor_name' => $data['donor_name'] ?? null,
            'barcode_scanned' => $data['barcode_scanned'] ?? null,
            'notes' => $data['notes'] ?? null,
            'scanned_by' => $user?->id,
            'volunteer_name' => $data['volunteer_name'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
        ]);
    }

    /**
     * Look up an item by barcode.
     */
    public function lookupBarcode(string $barcode): ?WarehouseItem
    {
        $variants = $this->barcodeVariants($barcode);
        return WarehouseItem::whereIn('barcode', $variants)->first();
    }

    /**
     * Look up a barcode via the Open Food Facts API.
     * Returns product name if found, null otherwise.
     */
    public function lookupBarcodeExternal(string $barcode): ?array
    {
        try {
            $normalized = $this->normalizeBarcode($barcode);
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'GFSDFoodDrive/1.0',
            ])->timeout(5)->get("https://world.openfoodfacts.org/api/v0/product/{$normalized}.json");

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? 0) === 1) {
                    return $this->formatOffProduct($data['product'] ?? [], $normalized);
                }
                if (($data['status'] ?? 0) === 0) {
                    return null;
                }
            }

            // Fallback to v2 endpoint if v0 fails
            $v2 = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'GFSDFoodDrive/1.0',
            ])->timeout(5)->get("https://world.openfoodfacts.org/api/v2/product/{$normalized}.json");

            if ($v2->successful()) {
                $data = $v2->json();
                if (($data['status'] ?? 0) === 1) {
                    return $this->formatOffProduct($data['product'] ?? [], $normalized);
                }
            }

            return [
                'error' => true,
                'message' => 'OFF response ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function mapOffCategoryTags(array $tags, ?string $name = null, ?string $brand = null): ?WarehouseCategory
    {
        if (empty($tags)) {
            $tags = [];
        }

        $normalized = array_values(array_filter(array_map(
            fn($tag) => is_string($tag) ? strtolower(trim($tag)) : '',
            $tags
        )));

        foreach ($normalized as $tag) {
            if (isset(self::OFF_CATEGORY_MAP[$tag])) {
                return WarehouseCategory::where('name', self::OFF_CATEGORY_MAP[$tag])->first();
            }
        }

        foreach ($normalized as $tag) {
            foreach (self::OFF_TAG_KEYWORDS as $needle => $categoryName) {
                if (str_contains($tag, $needle)) {
                    return WarehouseCategory::where('name', $categoryName)->first();
                }
            }
        }

        // Pass 3: Strip "en:" prefix, replace dashes with spaces, re-check keywords
        foreach ($normalized as $tag) {
            $slug = str_contains($tag, ':') ? substr($tag, strpos($tag, ':') + 1) : $tag;
            $slug = str_replace('-', ' ', $slug);
            foreach (self::OFF_TAG_KEYWORDS as $needle => $categoryName) {
                if (str_contains($slug, $needle)) {
                    return WarehouseCategory::where('name', $categoryName)->first();
                }
            }
        }

        $text = strtolower(trim(($name ?? '') . ' ' . ($brand ?? '')));
        if (!empty($text)) {
            foreach (self::OFF_TAG_KEYWORDS as $needle => $categoryName) {
                if (str_contains($text, $needle)) {
                    return WarehouseCategory::where('name', $categoryName)->first();
                }
            }
            if (str_contains($text, 'cola') || str_contains($text, 'coke') || str_contains($text, 'soda')) {
                return WarehouseCategory::where('name', 'Beverages')->first();
            }
        }

        return null;
    }

    private function formatOffProduct(array $product, string $normalized): ?array
    {
        $name = $product['product_name'] ?? null;
        if (empty($name)) $name = $product['product_name_en'] ?? null;
        if (empty($name)) $name = $product['generic_name'] ?? null;
        if (empty($name)) $name = $product['generic_name_en'] ?? null;
        if (empty($name)) $name = $product['abbreviated_product_name'] ?? null;
        if (empty($name)) {
            return null;
        }

        $tags = $product['categories_tags'] ?? [];
        $categoryText = $product['categories'] ?? '';
        if (!empty($categoryText)) {
            $extraTags = array_filter(array_map('trim', explode(',', strtolower($categoryText))));
            $tags = array_merge($tags, $extraTags);
        }

        $suggested = $this->mapOffCategoryTags($tags, $name, $product['brands'] ?? null);
        return [
            'name' => $name,
            'brand' => $product['brands'] ?? null,
            'image' => $product['image_small_url'] ?? null,
            'categories_tags' => $tags,
            'barcode_normalized' => $normalized,
            'suggested_category_id' => $suggested?->id,
            'suggested_category_name' => $suggested?->name,
        ];
    }

    private function normalizeBarcode(string $barcode): string
    {
        $clean = preg_replace('/\D+/', '', $barcode) ?? '';

        // Expand UPC-E (6 digits) to UPC-A (12 digits)
        if (strlen($clean) === 6) {
            $clean = $this->expandUpcE($clean);
        }
        // Expand 8-digit UPC-E (with system + check digits) to UPC-A
        if (strlen($clean) === 8 && $clean[0] === '0') {
            $clean = $this->expandUpcE(substr($clean, 1, 6));
        }

        // Pad 12-digit UPC-A to 13-digit EAN-13 (OFF uses EAN-13)
        if (strlen($clean) === 12) {
            return '0' . $clean;
        }
        return $clean ?: trim($barcode);
    }

    /**
     * Expand a 6-digit UPC-E code to 12-digit UPC-A per GS1 standard.
     */
    private function expandUpcE(string $upcE): string
    {
        if (strlen($upcE) !== 6) {
            return $upcE;
        }
        $d = str_split($upcE);
        $lastDigit = (int) $d[5];

        $expanded = match ($lastDigit) {
            0, 1, 2 => "0{$d[0]}{$d[1]}{$d[5]}0000{$d[2]}{$d[3]}{$d[4]}",
            3 => "0{$d[0]}{$d[1]}{$d[2]}00000{$d[3]}{$d[4]}",
            4 => "0{$d[0]}{$d[1]}{$d[2]}{$d[3]}00000{$d[4]}",
            default => "0{$d[0]}{$d[1]}{$d[2]}{$d[3]}{$d[4]}0000{$d[5]}",
        };

        // expandUpcE returns 11 digits (no check digit), pad to 12
        return str_pad($expanded, 12, '0');
    }

    private function barcodeVariants(string $barcode): array
    {
        $clean = preg_replace('/\D+/', '', $barcode) ?? '';
        $variants = [
            trim($barcode),
            $clean,
        ];
        $trimmed = ltrim($clean, '0');
        if ($trimmed !== '') {
            $variants[] = $trimmed;
        }
        if (strlen($clean) === 12) {
            $variants[] = '0' . $clean;
        }
        if (strlen($clean) === 11) {
            $variants[] = '00' . $clean;
        }
        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * Register a new barcode for an item.
     */
    public function registerBarcode(string $barcode, int $categoryId, string $name): WarehouseItem
    {
        return WarehouseItem::create([
            'barcode' => $barcode,
            'category_id' => $categoryId,
            'name' => $name,
        ]);
    }

    /**
     * Confirm gift drop-off for a child.
     * Sets gift_dropped_off = true, bumps gift_level to at least Moderate, creates IN transaction.
     */
    public function confirmGiftDropoff(Child $child, User $user, ?string $giftsReceived = null, array $items = []): WarehouseTransaction
    {
        // Determine gift category based on child gender/age
        $categoryName = $this->giftCategoryForChild($child);
        $category = WarehouseCategory::where('name', $categoryName)->first()
            ?? WarehouseCategory::where('name', 'Gift - Neutral')->first();

        // Compute gifts_received text from items if provided
        if (!empty($items)) {
            $itemNames = collect($items)->pluck('name')->filter()->implode(', ');
            $giftsReceived = $itemNames ?: $giftsReceived;
        }

        $child->update([
            'gift_dropped_off' => true,
            'gift_level' => max($child->gift_level?->value ?? 0, GiftLevel::Moderate->value),
            'gifts_received' => $giftsReceived ?: $child->gifts_received,
        ]);

        // Create per-item transactions if items provided
        $lastTransaction = null;
        if (!empty($items)) {
            foreach ($items as $item) {
                $lastTransaction = WarehouseTransaction::create([
                    'category_id' => $category->id,
                    'family_id' => $child->family_id,
                    'child_id' => $child->id,
                    'transaction_type' => TransactionType::In,
                    'quantity' => 1,
                    'source' => 'Gift Drop-off',
                    'scanned_by' => $user->id,
                    'notes' => $item['name'] ?? null,
                    'barcode_scanned' => $item['barcode'] ?? null,
                ]);
            }
        }

        // Fallback: single summary transaction if no items array
        if (empty($items)) {
            $lastTransaction = WarehouseTransaction::create([
                'category_id' => $category->id,
                'family_id' => $child->family_id,
                'child_id' => $child->id,
                'transaction_type' => TransactionType::In,
                'quantity' => 1,
                'source' => 'Gift Drop-off',
                'scanned_by' => $user->id,
                'notes' => $giftsReceived,
            ]);
        }

        return $lastTransaction;
    }

    /**
     * Get recent transactions.
     */
    public function recentTransactions(int $limit = 20): Collection
    {
        return WarehouseTransaction::with(['category', 'item', 'scanner'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get donation source breakdown for the season.
     */
    public function sourceBreakdown(?int $seasonYear = null): Collection
    {
        $seasonYear = $seasonYear ?? (int) Setting::get('season_year', date('Y'));

        return WarehouseTransaction::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->where('transaction_type', TransactionType::In->value)
            ->selectRaw('source, COUNT(*) as count, SUM(quantity) as total_qty')
            ->groupBy('source')
            ->orderByDesc('total_qty')
            ->get();
    }

    /**
     * Gift progress grouped by age/gender category.
     */
    public function giftProgressByAge(?int $seasonYear = null): array
    {
        $seasonYear = $seasonYear ?? (int) Setting::get('season_year', date('Y'));

        $giftCategories = WarehouseCategory::where('type', 'gift')->orderBy('sort_order')->get();
        $stock = $this->currentStock($seasonYear);
        $deficits = collect($this->categoryDeficits($seasonYear))->keyBy(fn($d) => $d['category']->id);

        $progress = [];
        foreach ($giftCategories as $cat) {
            $needed = $deficits[$cat->id]['needed'] ?? 0;
            $onHand = (int) ($stock[$cat->id] ?? 0);
            $progress[] = [
                'category' => $cat->name,
                'needed' => $needed,
                'on_hand' => $onHand,
                'percent' => $needed > 0 ? min(100, round(($onHand / $needed) * 100)) : 100,
            ];
        }

        return $progress;
    }

    /**
     * Determine gift category name for a child based on gender/age.
     */
    private function giftCategoryForChild(Child $child): string
    {
        $gender = strtolower($child->gender ?? '');
        $age = (int) ($child->age ?? 0);

        if ($gender === 'male') {
            if ($age < 6) return 'Gift - Boy Under 6';
            if ($age <= 12) return 'Gift - Boy 6-12';
            return 'Gift - Boy 13-17';
        } elseif ($gender === 'female') {
            if ($age < 6) return 'Gift - Girl Under 6';
            if ($age <= 12) return 'Gift - Girl 6-12';
            return 'Gift - Girl 13-17';
        }

        return 'Gift - Neutral';
    }
}
