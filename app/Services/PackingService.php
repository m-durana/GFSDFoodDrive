<?php

namespace App\Services;

use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\Child;
use App\Models\Family;
use App\Models\GiftBankItem;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\PackingSession;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PackingService
{
    /**
     * Map grocery item categories to warehouse category names.
     * Grocery items use short category strings; warehouse categories use full names.
     */
    private const GROCERY_TO_WAREHOUSE_MAP = [
        'canned' => 'Canned Goods',
        'dry' => 'Dry Goods',
        'pasta' => 'Pasta/Rice/Grains',
        'rice' => 'Pasta/Rice/Grains',
        'grains' => 'Pasta/Rice/Grains',
        'soup' => 'Soups/Broths',
        'soups' => 'Soups/Broths',
        'condiments' => 'Condiments/Sauces',
        'sauces' => 'Condiments/Sauces',
        'breakfast' => 'Breakfast Items',
        'frozen' => 'Frozen Foods',
        'dairy' => 'Dairy/Refrigerated',
        'refrigerated' => 'Dairy/Refrigerated',
        'produce' => 'Produce',
        'protein' => 'Protein/Meat',
        'meat' => 'Protein/Meat',
        'beverages' => 'Beverages',
        'drinks' => 'Beverages',
        'snacks' => 'Snacks',
        'baking' => 'Baking Supplies',
        'personal' => 'Personal Care',
        'hygiene' => 'Hygiene Bundle',
        'pet' => 'Pet Supplies',
    ];

    /**
     * Generate a packing list for a family.
     * Idempotent: returns existing list if one already exists for this family+season.
     */
    public function generatePackingList(Family $family, ?string $seasonYear = null): PackingList
    {
        $seasonYear = $seasonYear ?? Setting::get('season_year', date('Y'));

        return DB::transaction(function () use ($family, $seasonYear) {
            $list = PackingList::withoutGlobalScopes()->firstOrCreate(
                ['family_id' => $family->id, 'season_year' => $seasonYear],
                ['status' => PackingStatus::Pending]
            );

            if ($list->wasRecentlyCreated) {
                $this->buildFoodItems($list, $family);
                $this->buildGiftItems($list, $family);
                $this->buildBabyItems($list, $family);
                $this->applyPickPathSort($list);
            }

            return $list->load('items');
        });
    }

    /**
     * Refresh a packing list: remove unpacked items and rebuild.
     * Preserves items that have already been packed/verified/substituted.
     */
    public function refreshPackingList(PackingList $list): PackingList
    {
        return DB::transaction(function () use ($list) {
            $family = $list->family;

            // Delete only unpacked items (pending, unfulfilled)
            $list->items()
                ->whereIn('status', [
                    PackingItemStatus::Pending->value,
                    PackingItemStatus::Unfulfilled->value,
                ])
                ->delete();

            // Rebuild for gaps
            $this->buildFoodItems($list, $family);
            $this->buildGiftItems($list, $family);
            $this->buildBabyItems($list, $family);
            $this->applyPickPathSort($list);

            // Reset status if needed
            if ($list->status === PackingStatus::Complete && !$list->isComplete()) {
                $list->update(['status' => PackingStatus::InProgress]);
            }

            return $list->load('items');
        });
    }

    /**
     * Generate packing lists for all families in a season.
     */
    public function generateAllPackingLists(?string $seasonYear = null, ?string $statusFilter = null): int
    {
        $seasonYear = $seasonYear ?? Setting::get('season_year', date('Y'));
        $count = 0;

        $query = Family::where('season_year', $seasonYear);

        if ($statusFilter) {
            $query->whereHas('packingList', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });
        }

        $query->each(function (Family $family) use ($seasonYear, &$count) {
            $this->generatePackingList($family, $seasonYear);
            $count++;
        });

        return $count;
    }

    /**
     * Mark a packing item as packed.
     */
    public function markItemPacked(PackingItem $item, User $packer): array
    {
        $newQuantity = min($item->quantity_needed, $item->quantity_packed + 1);
        $newStatus = $newQuantity >= $item->quantity_needed
            ? PackingItemStatus::Packed
            : PackingItemStatus::Pending;

        $item->update([
            'quantity_packed' => $newQuantity,
            'packed_by' => $packer->id,
            'packed_at' => now(),
            'status' => $newStatus,
        ]);

        $activeSession = PackingSession::activeFor($packer);
        if ($activeSession) {
            $activeSession->increment('items_packed');
        }

        $this->syncListStatus($item->packingList);

        return [
            'success' => true,
            'item' => $item->fresh(),
            'message' => 'Item packed successfully.',
        ];
    }

    /**
     * Record a substitution when the expected item is unavailable.
     */
    public function substituteItem(PackingItem $item, WarehouseItem|int|null $newItem, string $notes, User $packer): void
    {
        $newItemId = $newItem instanceof WarehouseItem ? $newItem->id : $newItem;
        $description = $item->description;

        // Update description with substitute name if a warehouse item is provided
        if ($newItem instanceof WarehouseItem) {
            $originalDesc = preg_replace('/\s*\[Substituted:.*\]$/', '', $item->description);
            $description = $originalDesc . ' [Substituted: ' . $newItem->name . ']';
        }

        $item->update([
            'item_id' => $newItemId,
            'description' => $description,
            'status' => PackingItemStatus::Substituted,
            'packed_by' => $packer->id,
            'packed_at' => now(),
            'quantity_packed' => $item->quantity_needed,
            'substitute_notes' => $notes,
        ]);

        $this->syncListStatus($item->packingList);
    }

    /**
     * Suggest substitute warehouse items in the same category that are active.
     */
    public function suggestSubstitutes(PackingItem $item): array
    {
        if (!$item->category_id) {
            return [];
        }

        return WarehouseItem::where('category_id', $item->category_id)
            ->where('active', true)
            ->when($item->item_id, fn ($q) => $q->where('id', '!=', $item->item_id))
            ->orderBy('name')
            ->get()
            ->map(fn (WarehouseItem $wi) => [
                'id' => $wi->id,
                'name' => $wi->name,
                'barcode' => $wi->barcode,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Scan a barcode and match it to a packing list item.
     * Returns result array with match info and suggested item.
     */
    public function scanItemIntoPack(PackingList $list, string $barcode, ?User $packer = null): array
    {
        $warehouseService = app(WarehouseService::class);
        $warehouseItem = $warehouseService->lookupBarcode($barcode);

        if (!$warehouseItem) {
            return [
                'match' => false,
                'message' => 'Barcode not found in inventory.',
                'barcode' => $barcode,
                'suggestion' => null,
            ];
        }

        // Find a matching packing item by warehouse item or category
        $packingItem = $list->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->where(function ($q) use ($warehouseItem) {
                $q->where('item_id', $warehouseItem->id)
                    ->orWhere('category_id', $warehouseItem->category_id);
            })
            ->orderByRaw("CASE WHEN item_id = ? THEN 0 ELSE 1 END", [$warehouseItem->id])
            ->first();

        if (!$packingItem) {
            // Check if any item in this category exists (even if already packed)
            $categoryMatch = $list->items()
                ->where('category_id', $warehouseItem->category_id)
                ->first();

            $suggestion = $this->suggestAlternative($list, $warehouseItem);

            return [
                'match' => false,
                'message' => $categoryMatch
                    ? 'This item\'s category is already fulfilled on this list.'
                    : 'This item is not on this family\'s packing list.',
                'barcode' => $barcode,
                'scanned_item' => $warehouseItem->name,
                'suggestion' => $suggestion,
                'suggestion_item_id' => $warehouseItem->id,
            ];
        }

        // Link the warehouse item and pack it
        $packingItem->update(['item_id' => $warehouseItem->id]);
        $result = $this->markItemPacked($packingItem, $packer ?? new User());

        return [
            'match' => true,
            'message' => 'Item matched and packed.',
            'item' => $packingItem->fresh()->toArray(),
            'scanned_item' => $warehouseItem->name,
        ];
    }

    /**
     * Suggest an alternative packing item for a scanned warehouse item.
     */
    private function suggestAlternative(PackingList $list, WarehouseItem $scannedItem): ?array
    {
        $suggestion = $list->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->where('category_id', $scannedItem->category_id)
            ->first();

        if ($suggestion) {
            return [
                'item_id' => $suggestion->id,
                'description' => $suggestion->description,
                'message' => "Did you mean: {$suggestion->description}?",
            ];
        }

        return null;
    }

    /**
     * Perform final QA verification of a completed packing list.
     */
    public function verifyPackingList(PackingList $list, User $verifier): bool
    {
        if (!$list->isComplete()) {
            return false;
        }

        $list->update([
            'status' => PackingStatus::Verified,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        $list->items()
            ->whereIn('status', [
                PackingItemStatus::Packed->value,
                PackingItemStatus::Substituted->value,
            ])
            ->update(['status' => PackingItemStatus::Verified->value]);

        return true;
    }

    /**
     * Compute dashboard stats for the packing system.
     */
    public function getDashboardStats(): array
    {
        $total = PackingList::count();
        $packed = PackingList::where('status', PackingStatus::Complete)->count();
        $verified = PackingList::where('status', PackingStatus::Verified)->count();
        $inProgress = PackingList::where('status', PackingStatus::InProgress)->count();
        $notStarted = PackingList::where('status', PackingStatus::Pending)->count();

        // Category breakdown: food / gift / baby each with total + packed counts
        $categoryBreakdown = DB::table('packing_items')
            ->join('warehouse_categories', 'packing_items.category_id', '=', 'warehouse_categories.id')
            ->select(
                'warehouse_categories.type',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN packing_items.status IN ('packed', 'verified', 'substituted') THEN 1 ELSE 0 END) as packed")
            )
            ->groupBy('warehouse_categories.type')
            ->get()
            ->keyBy('type');

        $categories = [];
        foreach (['food', 'gift', 'baby'] as $type) {
            $row = $categoryBreakdown->get($type);
            $categories[$type] = [
                'total' => (int) ($row->total ?? 0),
                'packed' => (int) ($row->packed ?? 0),
            ];
        }

        // Volunteer metrics: per-volunteer stats for today
        $todayStart = now()->startOfDay();
        $volunteers = DB::table('packing_items')
            ->join('users', 'packing_items.packed_by', '=', 'users.id')
            ->where('packing_items.packed_at', '>=', $todayStart)
            ->whereIn('packing_items.status', ['packed', 'verified', 'substituted'])
            ->select(
                'users.id',
                DB::raw("COALESCE(users.first_name, '') || ' ' || COALESCE(users.last_name, '') as name"),
                DB::raw('COUNT(*) as items_packed'),
                DB::raw('COUNT(DISTINCT packing_items.packing_list_id) as lists_worked'),
                DB::raw('MIN(packing_items.packed_at) as first_packed_at'),
                DB::raw('MAX(packing_items.packed_at) as last_packed_at')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get()
            ->map(function ($v) {
                $first = $v->first_packed_at ? \Carbon\Carbon::parse($v->first_packed_at) : null;
                $last = $v->last_packed_at ? \Carbon\Carbon::parse($v->last_packed_at) : null;
                $hours = ($first && $last && $first->lt($last))
                    ? max($first->diffInMinutes($last) / 60, 0.1)
                    : 0.1;
                return [
                    'id' => $v->id,
                    'name' => trim($v->name),
                    'items_packed' => (int) $v->items_packed,
                    'lists_worked' => (int) $v->lists_worked,
                    'items_per_hour' => round($v->items_packed / $hours, 1),
                    'first_packed_at' => $v->first_packed_at,
                    'last_packed_at' => $v->last_packed_at,
                ];
            })
            ->values()
            ->toArray();

        // Global items packed today + overall rate
        $totalItemsToday = DB::table('packing_items')
            ->where('packed_at', '>=', $todayStart)
            ->whereIn('status', ['packed', 'verified', 'substituted'])
            ->count();

        $firstPackedToday = DB::table('packing_items')
            ->where('packed_at', '>=', $todayStart)
            ->whereIn('status', ['packed', 'verified', 'substituted'])
            ->min('packed_at');

        $overallHours = 0.1;
        if ($firstPackedToday) {
            $minutes = \Carbon\Carbon::parse($firstPackedToday)->diffInMinutes(now());
            $overallHours = max($minutes / 60, 0.1);
        }

        // Recently completed lists (last 60 seconds)
        $recentlyCompleted = PackingList::with('family:id,family_name,family_number')
            ->where('status', PackingStatus::Complete)
            ->where('completed_at', '>=', now()->subSeconds(60))
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'family_name' => $l->family?->family_name,
                'family_number' => $l->family?->family_number,
                'completed_at' => $l->completed_at->toIso8601String(),
            ])
            ->values()
            ->toArray();

        // Unfulfilled metrics
        $unfulfilledItems = PackingItem::where('status', PackingItemStatus::Unfulfilled)->count();
        $unfulfilledFamilies = PackingList::whereHas('items', function ($q) {
            $q->where('status', PackingItemStatus::Unfulfilled);
        })->count();

        $fulfillmentRate = $total > 0 ? round((($packed + $verified) / $total) * 100, 1) : 0;
        $fulfillmentThreshold = (float) Setting::get('packing_fulfillment_alert_threshold', '80');
        $trend = $this->getVolunteerTrend();

        return [
            'total_families' => $total,
            'packed' => $packed,
            'verified' => $verified,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
            'fulfillment_rate' => $fulfillmentRate,
            'categories' => $categories,
            'volunteers' => $volunteers,
            'total_items_packed_today' => $totalItemsToday,
            'overall_items_per_hour' => round($totalItemsToday / $overallHours, 1),
            'recently_completed' => $recentlyCompleted,
            'unfulfilled_items' => $unfulfilledItems,
            'unfulfilled_families' => $unfulfilledFamilies,
            'trend' => $trend,
            'fulfillment_alert' => $fulfillmentRate < $fulfillmentThreshold,
            'fulfillment_threshold' => $fulfillmentThreshold,
        ];
    }

    /**
     * Aggregate shopping deficits from packing lists.
     * Single query: SUM(quantity_needed) grouped by grocery_item_id + category.
     * Subtract warehouse stock per category.
     */
    public function getShoppingDeficits(): array
    {
        $warehouseService = app(WarehouseService::class);
        $stock = $warehouseService->currentStock();

        $items = DB::table('packing_items')
            ->join('packing_lists', 'packing_items.packing_list_id', '=', 'packing_lists.id')
            ->leftJoin('grocery_items', 'packing_items.grocery_item_id', '=', 'grocery_items.id')
            ->leftJoin('warehouse_categories', 'packing_items.category_id', '=', 'warehouse_categories.id')
            ->whereNotNull('packing_items.grocery_item_id')
            ->select(
                'packing_items.grocery_item_id',
                'grocery_items.name as grocery_item_name',
                'grocery_items.category as grocery_category',
                'packing_items.category_id',
                'warehouse_categories.name as category_name',
                DB::raw('SUM(packing_items.quantity_needed) as total_needed')
            )
            ->groupBy('packing_items.grocery_item_id', 'grocery_items.name', 'grocery_items.category', 'packing_items.category_id', 'warehouse_categories.name')
            ->get();

        return $items->map(function ($row) use ($stock) {
            $onHand = (int) ($stock[$row->category_id] ?? 0);
            return [
                'grocery_item_id' => $row->grocery_item_id,
                'grocery_item_name' => $row->grocery_item_name,
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'category' => $row->grocery_category,
                'total_needed' => (int) $row->total_needed,
                'on_hand' => max($onHand, 0),
                'deficit' => max((int) $row->total_needed - $onHand, 0),
            ];
        })->toArray();
    }

    /**
     * Auto-substitute all pending packing items that reference a removed warehouse item.
     * For each: find a substitute in the same category. If none found, mark unfulfilled.
     */
    public function autoSubstituteRemovedItem(WarehouseItem $removedItem, User $coordinator): int
    {
        $affectedItems = PackingItem::where('item_id', $removedItem->id)
            ->where('status', PackingItemStatus::Pending)
            ->get();

        $count = 0;
        foreach ($affectedItems as $item) {
            $candidates = $this->suggestSubstitutes($item);

            if (!empty($candidates)) {
                $substitute = WarehouseItem::find($candidates[0]['id']);
                if ($substitute) {
                    $this->substituteItem($item, $substitute, "Auto-substituted: {$removedItem->name} removed from inventory", $coordinator);
                    $count++;
                    continue;
                }
            }

            // No substitute available — mark unfulfilled
            $item->update([
                'status' => PackingItemStatus::Unfulfilled,
                'description' => $item->description . ' [ITEM REMOVED — No substitute found]',
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Get reconciliation data comparing shopping checks vs kiosk receipts.
     * Returns per-item comparison: what NINJAs checked off vs what was received at kiosk.
     */
    public function getShoppingReconciliation(?string $seasonYear = null): array
    {
        $seasonYear = $seasonYear ?? Setting::get('season_year', date('Y'));

        // Aggregate shopping checks across all assignments (what NINJAs say they purchased)
        $purchased = DB::table('shopping_checks')
            ->join('shopping_assignments', 'shopping_checks.shopping_assignment_id', '=', 'shopping_assignments.id')
            ->select('shopping_checks.item_key', DB::raw('COUNT(*) as purchased_qty'))
            ->groupBy('shopping_checks.item_key')
            ->get()
            ->keyBy('item_key');

        // Aggregate kiosk receive transactions for the season (what was actually scanned in)
        $received = DB::table('warehouse_transactions')
            ->join('warehouse_items', 'warehouse_transactions.item_id', '=', 'warehouse_items.id')
            ->join('warehouse_categories', 'warehouse_items.category_id', '=', 'warehouse_categories.id')
            ->where('warehouse_transactions.season_year', $seasonYear)
            ->where('warehouse_transactions.transaction_type', 'in')
            ->select(
                DB::raw("warehouse_categories.name || '|' || warehouse_items.name as item_key"),
                'warehouse_items.name as item_name',
                'warehouse_categories.name as category_name',
                DB::raw('SUM(warehouse_transactions.quantity) as received_qty')
            )
            ->groupBy('warehouse_items.name', 'warehouse_categories.name')
            ->get()
            ->keyBy('item_key');

        // Merge into reconciliation array
        $allKeys = $purchased->keys()->merge($received->keys())->unique();
        $reconciliation = [];

        foreach ($allKeys as $key) {
            $purchasedQty = (int) ($purchased->get($key)?->purchased_qty ?? 0);
            $receivedQty = (int) ($received->get($key)?->received_qty ?? 0);
            $parts = explode('|', $key, 2);

            $reconciliation[] = [
                'item_key' => $key,
                'item_name' => $received->get($key)?->item_name ?? ($parts[1] ?? $key),
                'category_name' => $received->get($key)?->category_name ?? ($parts[0] ?? ''),
                'purchased_qty' => $purchasedQty,
                'received_qty' => $receivedQty,
                'discrepancy' => $purchasedQty - $receivedQty,
            ];
        }

        return $reconciliation;
    }

    /**
     * Clock in a volunteer for a packing session.
     */
    public function clockIn(User $user): PackingSession
    {
        $existing = PackingSession::activeFor($user);
        if ($existing) {
            throw new \RuntimeException('Already clocked in.');
        }

        return PackingSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
    }

    /**
     * Clock out a volunteer, ending their active session.
     */
    public function clockOut(User $user, ?string $notes = null): PackingSession
    {
        $session = PackingSession::activeFor($user);
        if (!$session) {
            throw new \RuntimeException('No active session found.');
        }

        // Count distinct lists worked during the session timespan
        $listsWorked = PackingItem::where('packed_by', $user->id)
            ->where('packed_at', '>=', $session->started_at)
            ->where('packed_at', '<=', now())
            ->distinct('packing_list_id')
            ->count('packing_list_id');

        $session->update([
            'ended_at' => now(),
            'lists_worked' => $listsWorked,
            'notes' => $notes,
        ]);

        return $session->fresh();
    }

    /**
     * Get volunteer trend data comparing today vs yesterday.
     */
    public function getVolunteerTrend(): array
    {
        $todayStart = now()->startOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        $todaySessions = PackingSession::whereNotNull('ended_at')
            ->where('started_at', '>=', $todayStart)
            ->get();

        $yesterdaySessions = PackingSession::whereNotNull('ended_at')
            ->where('started_at', '>=', $yesterdayStart)
            ->where('started_at', '<=', $yesterdayEnd)
            ->get();

        $todayAvg = $todaySessions->count() > 0
            ? $todaySessions->avg(fn ($s) => $s->itemsPerHour())
            : 0;

        $yesterdayAvg = $yesterdaySessions->count() > 0
            ? $yesterdaySessions->avg(fn ($s) => $s->itemsPerHour())
            : 0;

        $diff = $todayAvg - $yesterdayAvg;
        $direction = abs($diff) <= 2 ? 'flat' : ($diff > 0 ? 'up' : 'down');

        $activeSessions = PackingSession::whereNull('ended_at')->count();

        return [
            'today_avg_items_per_hour' => round($todayAvg, 1),
            'yesterday_avg_items_per_hour' => round($yesterdayAvg, 1),
            'trend_direction' => $direction,
            'today_sessions_count' => $todaySessions->count(),
            'active_sessions' => $activeSessions,
        ];
    }

    /**
     * Get end-of-day summary report for a given date.
     */
    public function getEndOfDaySummary(Carbon $date): array
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        // Families packed (completed during this date)
        $familiesPacked = PackingList::with('family:id,family_name,family_number')
            ->where('completed_at', '>=', $dayStart)
            ->where('completed_at', '<=', $dayEnd)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'family_name' => $l->family?->family_name,
                'family_number' => $l->family?->family_number,
                'completed_at' => $l->completed_at?->toDateTimeString(),
            ])
            ->values()
            ->toArray();

        // Volunteer sessions for the day
        $sessions = PackingSession::with('user:id,first_name,last_name')
            ->where('started_at', '>=', $dayStart)
            ->where('started_at', '<=', $dayEnd)
            ->get();

        $volunteerStats = $sessions->map(fn ($s) => [
            'name' => trim(($s->user?->first_name ?? '') . ' ' . ($s->user?->last_name ?? '')),
            'hours' => round($s->durationInHours(), 1),
            'items_packed' => $s->items_packed,
            'items_per_hour' => $s->itemsPerHour(),
        ])->values()->toArray();

        $totalHours = $sessions->sum(fn ($s) => $s->durationInHours());
        $totalSessionItems = $sessions->sum('items_packed');

        // Total items packed on this date (from packing_items)
        $totalItemsPacked = PackingItem::where('packed_at', '>=', $dayStart)
            ->where('packed_at', '<=', $dayEnd)
            ->whereIn('status', ['packed', 'verified', 'substituted'])
            ->count();

        // Substitutions count
        $substitutionsCount = PackingItem::where('packed_at', '>=', $dayStart)
            ->where('packed_at', '<=', $dayEnd)
            ->where('status', 'substituted')
            ->count();

        // Unfulfilled count
        $unfulfilledCount = PackingItem::where('status', 'unfulfilled')->count();

        // Category breakdown
        $categoryBreakdown = DB::table('packing_items')
            ->join('warehouse_categories', 'packing_items.category_id', '=', 'warehouse_categories.id')
            ->where('packing_items.packed_at', '>=', $dayStart)
            ->where('packing_items.packed_at', '<=', $dayEnd)
            ->whereIn('packing_items.status', ['packed', 'verified', 'substituted'])
            ->select('warehouse_categories.type', DB::raw('COUNT(*) as count'))
            ->groupBy('warehouse_categories.type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'date' => $date->toDateString(),
            'families_packed' => $familiesPacked,
            'families_packed_count' => count($familiesPacked),
            'volunteers' => $volunteerStats,
            'total_volunteers' => $sessions->pluck('user_id')->unique()->count(),
            'total_hours' => round($totalHours, 1),
            'total_items_packed' => $totalItemsPacked,
            'substitutions_count' => $substitutionsCount,
            'unfulfilled_count' => $unfulfilledCount,
            'category_breakdown' => [
                'food' => (int) ($categoryBreakdown['food'] ?? 0),
                'gift' => (int) ($categoryBreakdown['gift'] ?? 0),
                'baby' => (int) ($categoryBreakdown['baby'] ?? 0),
            ],
        ];
    }

    /**
     * Apply pick-path sorting to food items on a packing list.
     * Sorts by warehouse location (zone/shelf/bin), items without locations sort last.
     */
    private function applyPickPathSort(PackingList $list): void
    {
        // Only sort food items (sort_order < 1000)
        $foodItems = $list->items()
            ->where('sort_order', '<', 1000)
            ->get()
            ->load('warehouseItem');

        if ($foodItems->isEmpty()) {
            return;
        }

        $sorted = $foodItems->sort(function ($a, $b) {
            $locA = $a->warehouseItem;
            $locB = $b->warehouseItem;

            $zoneA = $locA?->location_zone ?? 'zzz';
            $zoneB = $locB?->location_zone ?? 'zzz';
            $shelfA = $locA?->location_shelf ?? 'zzz';
            $shelfB = $locB?->location_shelf ?? 'zzz';
            $binA = $locA?->location_bin ?? 'zzz';
            $binB = $locB?->location_bin ?? 'zzz';

            return strcmp($zoneA, $zoneB)
                ?: strcmp($shelfA, $shelfB)
                ?: strcmp($binA, $binB)
                ?: strcmp($a->description, $b->description);
        })->values();

        $order = 0;
        foreach ($sorted as $item) {
            if ($item->sort_order !== $order) {
                $item->update(['sort_order' => $order]);
            }
            $order++;
        }
    }

    // --- Private builders ---

    private function buildFoodItems(PackingList $list, Family $family): void
    {
        $groceryItems = GroceryItem::orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $familySize = max($family->number_of_family_members ?? 1, 1);

        // Cache warehouse category lookups
        $categoryCache = [];

        $sortOrder = 0;
        foreach ($groceryItems as $groceryItem) {
            // Skip conditional items the family doesn't qualify for
            if ($groceryItem->conditional && $groceryItem->condition_field) {
                if (!$this->familyMatchesCondition($family, $groceryItem->condition_field)) {
                    continue;
                }
            }

            $qty = $groceryItem->quantityForSize($familySize);
            if ($qty <= 0) {
                continue;
            }

            // Check if this food item already exists on the list (from a previous build)
            $existing = $list->items()
                ->where('grocery_item_id', $groceryItem->id)
                ->first();

            if ($existing) {
                continue;
            }

            // Map grocery category to warehouse category
            $warehouseCategoryId = $this->resolveWarehouseCategory(
                $groceryItem->category,
                $categoryCache
            );

            // Check dietary compatibility
            $dietaryRestrictions = $family->dietary_restrictions ?? [];
            $isCompatible = $groceryItem->isCompatibleWith($dietaryRestrictions);

            $list->items()->create([
                'category_id' => $warehouseCategoryId,
                'grocery_item_id' => $groceryItem->id,
                'description' => $isCompatible
                    ? $groceryItem->name
                    : $groceryItem->name . ' [DIETARY CONFLICT — Needs Coordinator Review]',
                'quantity_needed' => $qty,
                'status' => $isCompatible ? PackingItemStatus::Pending : PackingItemStatus::Unfulfilled,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function buildGiftItems(PackingList $list, Family $family): void
    {
        $children = $family->children()->get();
        $sortOrder = 1000; // Gift items sort after food items

        foreach ($children as $child) {
            // Check if gift item already exists for this child
            $existing = $list->items()
                ->where('child_id', $child->id)
                ->first();

            if ($existing) {
                continue;
            }

            $giftCategoryId = $this->resolveGiftCategory($child);
            $description = $this->buildGiftDescription($child);
            $status = PackingItemStatus::Pending;
            $itemId = null;

            // Check adoption status
            if ($child->isAdopted() && $child->gift_dropped_off) {
                // Gift already received — slot is pending for packing
                $status = PackingItemStatus::Pending;
            } elseif ($child->isAdopted()) {
                // Adopted but gift not yet dropped off — still pending
                $description .= ' [Adopted - awaiting gift drop-off]';
                $status = PackingItemStatus::Pending;
            } else {
                // Not adopted — try gift bank match
                $giftBankItem = $this->findGiftBankMatch($child);
                if ($giftBankItem) {
                    $description .= ' [Gift Bank: ' . $giftBankItem->description . ']';
                    // Reserve the gift bank item
                    $giftBankItem->update([
                        'assigned_child_id' => $child->id,
                        'assigned_at' => now(),
                    ]);
                } else {
                    $status = PackingItemStatus::Unfulfilled;
                    $description .= ' [No gift matched - needs coordinator attention]';
                }
            }

            $list->items()->create([
                'category_id' => $giftCategoryId,
                'child_id' => $child->id,
                'description' => $description,
                'quantity_needed' => 1,
                'status' => $status,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function buildBabyItems(PackingList $list, Family $family): void
    {
        $needsBaby = $family->needs_baby_supplies || ($family->infants ?? 0) > 0;
        if (!$needsBaby) {
            return;
        }

        $babyCategories = WarehouseCategory::active()
            ->ofType('baby')
            ->orderBy('sort_order')
            ->get();

        $sortOrder = 2000; // Baby items sort after gifts

        foreach ($babyCategories as $category) {
            // Check if already exists
            $existing = $list->items()
                ->where('category_id', $category->id)
                ->whereNull('child_id')
                ->whereNull('grocery_item_id')
                ->first();

            if ($existing) {
                continue;
            }

            $list->items()->create([
                'category_id' => $category->id,
                'description' => $category->name,
                'quantity_needed' => 1,
                'status' => PackingItemStatus::Pending,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    /**
     * Sync packing list status based on item states.
     */
    private function syncListStatus(PackingList $list): void
    {
        $list->refresh();

        if ($list->status === PackingStatus::Pending) {
            $hasPackedItems = $list->items()
                ->whereNotIn('status', [
                    PackingItemStatus::Pending->value,
                    PackingItemStatus::Unfulfilled->value,
                ])
                ->exists();

            if ($hasPackedItems) {
                $list->update([
                    'status' => PackingStatus::InProgress,
                    'started_at' => $list->started_at ?? now(),
                ]);
            }
        }

        if ($list->status === PackingStatus::InProgress && $list->isComplete()) {
            $list->update([
                'status' => PackingStatus::Complete,
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Resolve a grocery item category string to a warehouse_categories ID.
     */
    private function resolveWarehouseCategory(string $groceryCategory, array &$cache): ?int
    {
        $key = strtolower(trim($groceryCategory));

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        // Try direct map first
        $warehouseName = self::GROCERY_TO_WAREHOUSE_MAP[$key] ?? null;

        if ($warehouseName) {
            $category = WarehouseCategory::where('name', $warehouseName)->first();
            $cache[$key] = $category?->id;
            return $cache[$key];
        }

        // Fuzzy match: try to find a warehouse category whose name contains the grocery category
        $category = WarehouseCategory::where('type', 'food')
            ->where('name', 'LIKE', '%' . $groceryCategory . '%')
            ->first();

        $cache[$key] = $category?->id;
        return $cache[$key];
    }

    /**
     * Resolve the gift warehouse category for a child based on age and gender.
     */
    private function resolveGiftCategory(Child $child): ?int
    {
        $age = (int) $child->age;
        $gender = strtolower($child->gender ?? '');

        // Map gender
        $genderLabel = match (true) {
            str_contains($gender, 'male') && !str_contains($gender, 'female') => 'Boy',
            str_contains($gender, 'female') => 'Girl',
            str_contains($gender, 'boy') => 'Boy',
            str_contains($gender, 'girl') => 'Girl',
            default => 'Neutral',
        };

        // Map age range
        $ageRange = match (true) {
            $age < 6 => 'Under 6',
            $age <= 12 => '6-12',
            $age <= 17 => '13-17',
            default => '13-17',
        };

        if ($genderLabel === 'Neutral') {
            $categoryName = 'Gift - Neutral';
        } else {
            $categoryName = "Gift - {$genderLabel} {$ageRange}";
        }

        return WarehouseCategory::where('name', $categoryName)->first()?->id
            ?? WarehouseCategory::where('name', 'General Gifts')->first()?->id;
    }

    /**
     * Build a human-readable gift description for a child.
     */
    private function buildGiftDescription(Child $child): string
    {
        $gender = $child->gender ?? 'child';
        $age = $child->age ?? '?';
        $desc = "Gift for {$gender}, age {$age}";

        $prefs = $child->gift_preferences ?? $child->toy_ideas;
        if ($prefs) {
            $desc .= " — " . \Illuminate\Support\Str::limit($prefs, 60);
        }

        return $desc;
    }

    /**
     * Find a matching unassigned gift bank item for a child.
     */
    private function findGiftBankMatch(Child $child): ?GiftBankItem
    {
        $age = (int) $child->age;
        $ageRange = match (true) {
            $age <= 5 => '0-5',
            $age <= 12 => '6-12',
            default => '13-17',
        };

        $gender = strtolower($child->gender ?? 'neutral');
        $genderValue = match (true) {
            str_contains($gender, 'male') && !str_contains($gender, 'female') => 'male',
            str_contains($gender, 'female') => 'female',
            str_contains($gender, 'boy') => 'male',
            str_contains($gender, 'girl') => 'female',
            default => 'neutral',
        };

        return GiftBankItem::unassigned()
            ->forAgeRange($ageRange)
            ->forGender($genderValue)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Check if a family matches a conditional grocery item field.
     */
    private function familyMatchesCondition(Family $family, string $field): bool
    {
        return match ($field) {
            'needs_baby_supplies' => (bool) $family->needs_baby_supplies,
            'has_infants' => ($family->infants ?? 0) > 0,
            'has_pets' => !empty($family->pet_information),
            'has_female_adults' => ($family->female_adults ?? 0) > 0,
            default => false,
        };
    }
}
