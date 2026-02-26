<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\Child;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\ShoppingAssignment;
use App\Models\ShoppingCheck;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommandCenterController extends Controller
{
    /**
     * Full-screen command center dashboard.
     */
    public function index(Request $request): View
    {
        $mode = $request->query('mode', 'auto');
        return view('santa.command-center', compact('mode'));
    }

    /**
     * JSON data endpoint for auto-refreshing dashboard.
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'overview' => $this->overviewStats(),
            'shopping' => $this->shoppingStats(),
            'delivery' => $this->deliveryStats(),
            'gifts' => $this->giftStats(),
            'recent_activity' => $this->recentActivity(),
            'drivers' => $this->driverLocations(),
            'timestamp' => now()->format('g:i:s A'),
        ]);
    }

    private function overviewStats(): array
    {
        $totalFamilies = Family::whereNotNull('family_number')->count();
        $totalChildren = Child::whereHas('family', fn($q) => $q->whereNotNull('family_number'))->count();
        $totalMembers = Family::whereNotNull('family_number')->sum('number_of_family_members');

        return [
            'total_families' => $totalFamilies,
            'total_children' => $totalChildren,
            'total_members' => $totalMembers,
        ];
    }

    private function shoppingStats(): array
    {
        $assignments = ShoppingAssignment::with(['user', 'checks'])->get();

        $ninjas = [];
        $totalItems = 0;
        $checkedItems = 0;

        foreach ($assignments as $a) {
            $items = $a->getTotalItems();
            $checked = $a->checks->count();
            $totalItems += $items;
            $checkedItems += $checked;

            $ninjas[] = [
                'name' => $a->getDisplayName(),
                'description' => $a->getDescription(),
                'total_items' => $items,
                'checked_items' => $checked,
                'pct' => $items > 0 ? round(($checked / $items) * 100) : 0,
            ];
        }

        return [
            'ninjas' => $ninjas,
            'total_items' => $totalItems,
            'checked_items' => $checkedItems,
            'pct' => $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0,
        ];
    }

    private function deliveryStats(): array
    {
        $base = Family::whereNotNull('family_number');

        $needsDelivery = (clone $base)->where('delivery_preference', 'like', '%deliver%')->count();
        $pending = (clone $base)->where(function ($q) {
            $q->where('delivery_status', DeliveryStatus::Pending)->orWhereNull('delivery_status');
        })->count();
        $inTransit = (clone $base)->where('delivery_status', DeliveryStatus::InTransit)->count();
        $delivered = (clone $base)->where('delivery_status', DeliveryStatus::Delivered)->count();
        $pickedUp = (clone $base)->where('delivery_status', DeliveryStatus::PickedUp)->count();

        $total = $delivered + $pickedUp + $inTransit + $pending;
        $done = $delivered + $pickedUp;
        $pct = $total > 0 ? round(($done / $total) * 100) : 0;

        // Routes summary
        $routes = DeliveryRoute::withCount(['families as completed_count' => function ($q) {
            $q->where('delivery_status', DeliveryStatus::Delivered)
                ->orWhere('delivery_status', DeliveryStatus::PickedUp);
        }])->withCount('families')->get()->map(fn($r) => [
            'name' => $r->name,
            'driver' => $r->driver ? $r->driver->first_name : ($r->driver_name ?? '—'),
            'total' => $r->families_count,
            'completed' => $r->completed_count,
            'pct' => $r->families_count > 0 ? round(($r->completed_count / $r->families_count) * 100) : 0,
        ]);

        return [
            'needs_delivery' => $needsDelivery,
            'pending' => $pending,
            'in_transit' => $inTransit,
            'delivered' => $delivered,
            'picked_up' => $pickedUp,
            'done' => $done,
            'total' => $total,
            'pct' => $pct,
            'routes' => $routes,
        ];
    }

    private function giftStats(): array
    {
        $children = Child::whereHas('family', fn($q) => $q->whereNotNull('family_number'));
        $total = (clone $children)->count();
        $level0 = (clone $children)->where('gift_level', 0)->orWhereNull('gift_level')->count();
        $level1 = (clone $children)->where('gift_level', 1)->count();
        $level2 = (clone $children)->where('gift_level', 2)->count();
        $level3 = (clone $children)->where('gift_level', 3)->count();
        $adopted = (clone $children)->whereNotNull('adopter_name')->count();

        return [
            'total' => $total,
            'level_0' => $level0,
            'level_1' => $level1,
            'level_2' => $level2,
            'level_3' => $level3,
            'adopted' => $adopted,
            'pct_covered' => $total > 0 ? round((($level1 + $level2 + $level3) / $total) * 100) : 0,
        ];
    }

    private function recentActivity(): array
    {
        return DeliveryLog::with(['family', 'user'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($log) => [
                'family' => $log->family ? "#{$log->family->family_number} {$log->family->family_name}" : '—',
                'user' => $log->user ? $log->user->first_name : '—',
                'status' => ucfirst(str_replace('_', ' ', $log->status)),
                'notes' => $log->notes,
                'time' => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function driverLocations(): array
    {
        return User::whereNotNull('last_lat')
            ->whereNotNull('last_lng')
            ->where('last_location_at', '>=', now()->subMinutes(15))
            ->get()
            ->map(fn($v) => [
                'name' => $v->first_name . ' ' . $v->last_name,
                'lat' => (float) $v->last_lat,
                'lng' => (float) $v->last_lng,
                'updated' => $v->last_location_at->diffForHumans(),
            ])
            ->toArray();
    }
}
