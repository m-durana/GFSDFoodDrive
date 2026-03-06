<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShoppingAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'ninja_name',
        'split_type',
        'categories',
        'family_start',
        'family_end',
        'config',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'config' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (empty($assignment->token)) {
                $assignment->token = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(ShoppingCheck::class);
    }

    public function getDisplayName(): string
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }
        return $this->ninja_name ?? 'Unknown';
    }

    /**
     * Get the aggregate shopping list for this assignment.
     * Uses packing list deficits instead of per-family recalculation.
     */
    public function getShoppingList(): array
    {
        $packingService = app(\App\Services\PackingService::class);

        if ($this->split_type === 'deficit') {
            return $this->formatDeficitsAsShoppingList($packingService->getShoppingDeficits());
        }

        if ($this->split_type === 'category') {
            $deficits = $packingService->getShoppingDeficits();
            $filtered = array_filter($deficits, fn ($d) => in_array($d['category'], $this->categories ?? []));
            return $this->formatDeficitsAsShoppingList($filtered);
        }

        if ($this->split_type === 'smart_split') {
            return $this->computeSmartSplitList($packingService);
        }

        if ($this->split_type === 'subcategory') {
            return $this->computeSubcategoryList($packingService);
        }

        // family_range: aggregate from packing items for the assigned family range
        return $this->computeRangeDeficits($packingService);
    }

    /**
     * Compute deficits for a family range, subtracting proportional stock.
     */
    private function computeRangeDeficits(\App\Services\PackingService $packingService): array
    {
        $warehouseService = app(\App\Services\WarehouseService::class);
        $stock = $warehouseService->currentStock();

        // Get total demand across ALL families (for proportional stock subtraction)
        $allDeficits = $packingService->getShoppingDeficits();
        $totalDemandByCategory = [];
        foreach ($allDeficits as $d) {
            $totalDemandByCategory[$d['category_id']] = ($totalDemandByCategory[$d['category_id']] ?? 0) + $d['total_needed'];
        }

        // Get demand for just this family range
        $rangeItems = \Illuminate\Support\Facades\DB::table('packing_items')
            ->join('packing_lists', 'packing_items.packing_list_id', '=', 'packing_lists.id')
            ->join('families', 'packing_lists.family_id', '=', 'families.id')
            ->leftJoin('grocery_items', 'packing_items.grocery_item_id', '=', 'grocery_items.id')
            ->whereNotNull('packing_items.grocery_item_id')
            ->whereBetween('families.family_number', [$this->family_start, $this->family_end])
            ->select(
                'grocery_items.name as item_name',
                'grocery_items.category as category',
                'packing_items.category_id',
                \Illuminate\Support\Facades\DB::raw('SUM(packing_items.quantity_needed) as total_needed')
            )
            ->groupBy('grocery_items.name', 'grocery_items.category', 'packing_items.category_id')
            ->get();

        $aggregated = [];
        foreach ($rangeItems as $row) {
            $catKey = $row->category;
            $totalCatDemand = $totalDemandByCategory[$row->category_id] ?? 1;
            $rangeShare = $totalCatDemand > 0 ? $row->total_needed / $totalCatDemand : 0;
            $proportionalStock = (int) round(($stock[$row->category_id] ?? 0) * $rangeShare);
            $needed = max((int) $row->total_needed - $proportionalStock, 0);

            if ($needed > 0) {
                if (!isset($aggregated[$catKey])) {
                    $aggregated[$catKey] = [];
                }
                $aggregated[$catKey][$row->item_name] = $needed;
            }
        }

        return $aggregated;
    }

    /**
     * Format deficit array into the shopping list format: ['Category' => ['Item' => qty]]
     */
    private function formatDeficitsAsShoppingList(array $deficits): array
    {
        $aggregated = [];
        foreach ($deficits as $d) {
            $catKey = $d['category'];
            if ($d['deficit'] > 0) {
                if (!isset($aggregated[$catKey])) {
                    $aggregated[$catKey] = [];
                }
                $aggregated[$catKey][$d['grocery_item_name']] = $d['deficit'];
            }
        }
        return $aggregated;
    }

    /**
     * Count total items in this assignment.
     */
    public function getTotalItems(): int
    {
        $list = $this->getShoppingList();
        $total = 0;
        foreach ($list as $items) {
            $total += array_sum($items);
        }
        return $total;
    }

    /**
     * Get a human-readable description of what this assignment covers.
     */
    public function getDescription(): string
    {
        if ($this->split_type === 'category') {
            return 'Categories: ' . implode(', ', array_map('ucfirst', $this->categories ?? []));
        }
        if ($this->split_type === 'smart_split') {
            $config = $this->config ?? [];
            $group = $config['group_number'] ?? '?';
            $total = $config['total_groups'] ?? '?';
            $familyCount = count($config['family_ids'] ?? []);
            return "Smart split group {$group}/{$total} ({$familyCount} families)";
        }
        if ($this->split_type === 'subcategory') {
            $config = $this->config ?? [];
            $catName = $config['category_name'] ?? 'Unknown';
            $itemCount = count($config['item_ids'] ?? []);
            return ucfirst($catName) . ": {$itemCount} selected items";
        }
        if ($this->split_type === 'deficit') {
            return 'Full deficit buy — all items needing purchase';
        }
        return "Families #{$this->family_start}–#{$this->family_end}";
    }

    /**
     * Compute shopping list for a smart_split assignment (specific family IDs).
     */
    private function computeSmartSplitList(\App\Services\PackingService $packingService): array
    {
        $config = $this->config ?? [];
        $familyIds = $config['family_ids'] ?? [];

        if (empty($familyIds)) {
            return [];
        }

        $warehouseService = app(\App\Services\WarehouseService::class);
        $stock = $warehouseService->currentStock();

        $allDeficits = $packingService->getShoppingDeficits();
        $totalDemandByCategory = [];
        foreach ($allDeficits as $d) {
            $totalDemandByCategory[$d['category_id']] = ($totalDemandByCategory[$d['category_id']] ?? 0) + $d['total_needed'];
        }

        $rangeItems = \Illuminate\Support\Facades\DB::table('packing_items')
            ->join('packing_lists', 'packing_items.packing_list_id', '=', 'packing_lists.id')
            ->leftJoin('grocery_items', 'packing_items.grocery_item_id', '=', 'grocery_items.id')
            ->whereNotNull('packing_items.grocery_item_id')
            ->whereIn('packing_lists.family_id', $familyIds)
            ->select(
                'grocery_items.name as item_name',
                'grocery_items.category as category',
                'packing_items.category_id',
                \Illuminate\Support\Facades\DB::raw('SUM(packing_items.quantity_needed) as total_needed')
            )
            ->groupBy('grocery_items.name', 'grocery_items.category', 'packing_items.category_id')
            ->get();

        $aggregated = [];
        foreach ($rangeItems as $row) {
            $catKey = $row->category;
            $totalCatDemand = $totalDemandByCategory[$row->category_id] ?? 1;
            $rangeShare = $totalCatDemand > 0 ? $row->total_needed / $totalCatDemand : 0;
            $proportionalStock = (int) round(($stock[$row->category_id] ?? 0) * $rangeShare);
            $needed = max((int) $row->total_needed - $proportionalStock, 0);

            if ($needed > 0) {
                if (!isset($aggregated[$catKey])) {
                    $aggregated[$catKey] = [];
                }
                $aggregated[$catKey][$row->item_name] = $needed;
            }
        }

        return $aggregated;
    }

    /**
     * Compute shopping list for a subcategory assignment (specific items within a category).
     */
    private function computeSubcategoryList(\App\Services\PackingService $packingService): array
    {
        $config = $this->config ?? [];
        $itemIds = $config['item_ids'] ?? [];
        $categoryName = $config['category_name'] ?? '';

        if (empty($itemIds)) {
            return [];
        }

        $deficits = $packingService->getShoppingDeficits();
        $filtered = array_filter($deficits, function ($d) use ($itemIds) {
            return in_array($d['grocery_item_id'] ?? null, $itemIds);
        });

        return $this->formatDeficitsAsShoppingList($filtered);
    }
}
