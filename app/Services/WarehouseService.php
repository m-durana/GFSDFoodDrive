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
        return WarehouseItem::where('barcode', $barcode)->first();
    }

    /**
     * Look up a barcode via the Open Food Facts API.
     * Returns product name if found, null otherwise.
     */
    public function lookupBarcodeExternal(string $barcode): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'GFSDFoodDrive/1.0',
            ])->timeout(5)->get("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json");

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? 0) === 1 && !empty($data['product']['product_name'])) {
                    return [
                        'name' => $data['product']['product_name'],
                        'brand' => $data['product']['brands'] ?? null,
                        'image' => $data['product']['image_small_url'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fall through
        }

        return null;
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
    public function confirmGiftDropoff(Child $child, User $user): WarehouseTransaction
    {
        $child->update([
            'gift_dropped_off' => true,
            'gift_level' => max($child->gift_level?->value ?? 0, GiftLevel::Moderate->value),
        ]);

        // Determine gift category based on child gender/age
        $categoryName = $this->giftCategoryForChild($child);
        $category = WarehouseCategory::where('name', $categoryName)->first()
            ?? WarehouseCategory::where('name', 'Gift - Neutral')->first();

        return WarehouseTransaction::create([
            'category_id' => $category->id,
            'family_id' => $child->family_id,
            'child_id' => $child->id,
            'transaction_type' => TransactionType::In,
            'quantity' => 1,
            'source' => 'Gift Drop-off',
            'scanned_by' => $user->id,
        ]);
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
