<?php

namespace App\Http\Controllers;

use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackingApiController extends Controller
{
    public function __construct(
        private readonly PackingService $packingService
    ) {}

    /**
     * Load a packing list by QR token.
     * No auth required — token acts as authentication.
     */
    public function show(string $qrToken): JsonResponse
    {
        $list = PackingList::withoutGlobalScopes()
            ->where('qr_token', $qrToken)
            ->with(['family:id,family_name,family_number,number_of_family_members', 'items.category'])
            ->first();

        if (!$list) {
            return response()->json(['error' => 'Packing list not found.'], 404);
        }

        return response()->json([
            'id' => $list->id,
            'family' => [
                'name' => $list->family?->family_name,
                'number' => $list->family?->family_number,
                'members' => $list->family?->number_of_family_members,
            ],
            'status' => $list->status->value,
            'status_label' => $list->status->label(),
            'progress' => $list->progressSummary(),
            'items' => $list->items->map(fn (PackingItem $item) => [
                'id' => $item->id,
                'description' => $item->description,
                'category' => $item->category?->name,
                'category_type' => $item->category?->type,
                'quantity_needed' => $item->quantity_needed,
                'quantity_packed' => $item->quantity_packed,
                'status' => $item->status->value,
                'status_label' => $item->status->label(),
                'child_id' => $item->child_id,
                'sort_order' => $item->sort_order,
            ]),
            'notes' => $list->notes,
        ]);
    }

    /**
     * Scan a barcode into a packing list.
     */
    public function scan(string $listId, Request $request): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list) {
            return response()->json(['error' => 'Packing list not found.'], 404);
        }

        $request->validate(['barcode' => 'required|string']);

        $packer = auth()->user();
        $result = $this->packingService->scanItemIntoPack($list, $request->barcode, $packer);

        return response()->json($result);
    }

    /**
     * Quick-pack: mark an item as packed without barcode scanning.
     */
    public function quickPack(string $listId, PackingItem $packingItem): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list) {
            return response()->json(['error' => 'Packing list not found.'], 404);
        }

        if ($packingItem->packing_list_id !== $list->id) {
            return response()->json(['error' => 'Item does not belong to this list.'], 404);
        }

        if ($packingItem->status === PackingItemStatus::Unfulfilled) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot pack an unfulfilled item.',
            ]);
        }

        if ($packingItem->quantity_packed >= $packingItem->quantity_needed) {
            return response()->json([
                'success' => true,
                'warning' => true,
                'message' => 'Item already fully packed.',
                'item' => $packingItem->toArray(),
            ]);
        }

        $packer = auth()->user() ?? new User();
        $result = $this->packingService->markItemPacked($packingItem, $packer);

        return response()->json($result);
    }

    /**
     * Get substitute candidates for a packing item.
     */
    public function substitutes(string $listId, PackingItem $packingItem): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list || $packingItem->packing_list_id !== $list->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $candidates = $this->packingService->suggestSubstitutes($packingItem);

        return response()->json($candidates);
    }

    /**
     * Record a substitution for a packing item.
     */
    public function substitute(string $listId, PackingItem $packingItem, Request $request): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list || $packingItem->packing_list_id !== $list->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $request->validate([
            'notes' => 'required|string|max:500',
            'new_item_id' => 'nullable|integer|exists:warehouse_items,id',
        ]);

        $newItem = null;
        if ($request->new_item_id) {
            $newItem = \App\Models\WarehouseItem::find($request->new_item_id);
        }

        $packer = auth()->user() ?? new User();
        $this->packingService->substituteItem($packingItem, $newItem, $request->notes, $packer);

        return response()->json([
            'success' => true,
            'message' => 'Substitution recorded.',
            'item' => $packingItem->fresh()->toArray(),
        ]);
    }

    /**
     * Mark a packing list as volunteer-complete.
     */
    public function complete(string $listId): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list) {
            return response()->json(['error' => 'Packing list not found.'], 404);
        }

        if ($list->isComplete()) {
            $list->update([
                'status' => PackingStatus::Complete,
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Packing list marked as complete.',
                'status' => $list->fresh()->status->value,
            ]);
        }

        $pending = $list->items()
            ->whereIn('status', [PackingItemStatus::Pending->value, PackingItemStatus::Unfulfilled->value])
            ->count();

        return response()->json([
            'success' => false,
            'message' => "Cannot complete — {$pending} items still pending.",
            'pending_count' => $pending,
        ]);
    }

    /**
     * Coordinator verification of a completed list.
     */
    public function verify(string $listId, Request $request): JsonResponse
    {
        $list = $this->resolveList($listId);
        if (!$list) {
            return response()->json(['error' => 'Packing list not found.'], 404);
        }

        $verifier = auth()->user();
        if (!$verifier) {
            return response()->json(['error' => 'Authentication required for verification.'], 401);
        }

        $success = $this->packingService->verifyPackingList($list, $verifier);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Packing list verified.',
                'status' => 'verified',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot verify — not all items are packed.',
        ]);
    }

    /**
     * Dashboard stats for the packing system.
     */
    public function stats(): JsonResponse
    {
        if (Setting::get('packing_system_enabled', '1') !== '1') {
            return response()->json(['enabled' => false]);
        }

        return response()->json($this->packingService->getDashboardStats());
    }

    /**
     * Clock in to start a packing session.
     */
    public function clockIn(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        try {
            $session = $this->packingService->clockIn($user);
            return response()->json([
                'success' => true,
                'session' => $session->toArray(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Clock out of an active packing session.
     */
    public function clockOut(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        try {
            $session = $this->packingService->clockOut($user, $request->input('notes'));
            return response()->json([
                'success' => true,
                'session' => $session->toArray(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get the current user's active packing session.
     */
    public function activeSession(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        $session = \App\Models\PackingSession::activeFor($user);

        return response()->json([
            'active' => $session !== null,
            'session' => $session?->toArray(),
        ]);
    }

    /**
     * Resolve a packing list by ID (from QR token or direct ID).
     */
    private function resolveList(string $listId): ?PackingList
    {
        return PackingList::withoutGlobalScopes()->find($listId);
    }
}
