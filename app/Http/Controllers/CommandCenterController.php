<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Enums\PackingStatus;
use App\Models\Child;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\PackingList;
use App\Models\ShoppingAssignment;
use App\Models\ShoppingCheck;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransaction;
use App\Services\RoutePlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommandCenterController extends Controller
{
    public function __construct(
        private readonly RoutePlanningService $routePlanning
    ) {}

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
            'stock' => $this->stockStats(),
            'recent_activity' => $this->recentActivity(),
            'delivery_map' => $this->deliveryMapData(),
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

        $total = $delivered + $inTransit + $pending;
        $done = $delivered;
        $pct = $total > 0 ? round(($done / $total) * 100) : 0;

        // Routes summary
        $palette = ['#dc2626', '#2563eb', '#16a34a', '#9333ea', '#f97316', '#0ea5e9', '#22c55e', '#a855f7'];
        $routes = DeliveryRoute::withCount(['families as completed_count' => function ($q) {
            $q->where('delivery_status', DeliveryStatus::Delivered);
        }])->withCount('families')
        ->with(['families' => fn($q) => $q
            ->where('delivery_status', DeliveryStatus::InTransit)
            ->orderBy('route_order')
            ->limit(1)
        ])->get()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->display_name,
            'driver' => $r->driver ? $r->driver->first_name : ($r->driver_name ?? '—'),
            'meta' => $r->formattedMeta(),
            'total' => $r->families_count,
            'completed' => $r->completed_count,
            'pct' => $r->families_count > 0 ? round(($r->completed_count / $r->families_count) * 100) : 0,
            'color' => $palette[$r->id % count($palette)],
            'access_token' => $r->access_token ?? null,
            'heading_to' => $r->families->first()
                ? "#{$r->families->first()->family_number} {$r->families->first()->family_name}"
                : null,
        ]);

        $dispatchQueue = Family::whereNotNull('family_number')
            ->whereNull('delivery_route_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) {
                $q->where('delivery_status', DeliveryStatus::Pending)
                    ->orWhereNull('delivery_status');
            })
            ->where('delivery_preference', 'like', '%deliver%')
            ->orderBy('family_number')
            ->limit(8)
            ->get()
            ->map(fn($f) => [
                'number' => $f->family_number,
                'name' => $f->family_name,
                'address' => $f->address,
                'distance_hint' => 'Awaiting assignment',
            ]);

        return [
            'needs_delivery' => $needsDelivery,
            'pending' => $pending,
            'in_transit' => $inTransit,
            'delivered' => $delivered,
            'done' => $done,
            'total' => $total,
            'pct' => $pct,
            'routes' => $routes,
            'dispatch_queue' => $dispatchQueue,
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

    private function deliveryMapData(): array
    {
        $palette = ['#dc2626', '#2563eb', '#16a34a', '#9333ea', '#f97316', '#0ea5e9', '#22c55e', '#a855f7'];

        $routes = DeliveryRoute::with(['driver', 'families' => fn($q) => $q
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('route_order')
            ->select('id', 'delivery_route_id', 'latitude', 'longitude', 'route_order'),
        ])->get();

        $routeColors = [];
        $routesData = $routes->map(function ($route, $idx) use ($palette, &$routeColors) {
            $color = $palette[$route->id % count($palette)];
            $routeColors[$route->id] = $color;

            return [
                'id' => $route->id,
                'name' => $route->name,
                'color' => $color,
                'polyline' => $this->routePlanning->polylineForRoute($route),
            ];
        })->values()->all();

        $families = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'family_number', 'family_name', 'address', 'latitude', 'longitude', 'delivery_status', 'delivery_route_id')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'number' => $f->family_number,
                'name' => $f->family_name,
                'address' => $f->address,
                'lat' => (float) $f->latitude,
                'lng' => (float) $f->longitude,
                'status' => $f->delivery_status?->value ?? 'pending',
                'route_id' => $f->delivery_route_id,
            ])
            ->toArray();

        $drivers = $routes->map(function ($route) use ($routeColors) {
            $color = $routeColors[$route->id] ?? '#3b82f6';
            if ($route->driver_lat && $route->driver_lng && $route->driver_location_at) {
                return [
                    'route_id' => $route->id,
                    'name' => $route->driver?->first_name ?? $route->driver_name ?? 'Driver',
                    'lat' => (float) $route->driver_lat,
                    'lng' => (float) $route->driver_lng,
                    'updated' => $route->driver_location_at->diffForHumans(),
                    'color' => $color,
                ];
            }
            if ($route->driver_user_id && $route->driver && $route->driver->last_lat && $route->driver->last_lng) {
                return [
                    'route_id' => $route->id,
                    'name' => $route->driver->first_name . ' ' . $route->driver->last_name,
                    'lat' => (float) $route->driver->last_lat,
                    'lng' => (float) $route->driver->last_lng,
                    'updated' => $route->driver->last_location_at?->diffForHumans() ?? 'just now',
                    'color' => $color,
                ];
            }
            if ($route->start_lat && $route->start_lng) {
                return [
                    'route_id' => $route->id,
                    'name' => $route->driver?->first_name ?? $route->driver_name ?? 'Driver',
                    'lat' => (float) $route->start_lat,
                    'lng' => (float) $route->start_lng,
                    'updated' => 'awaiting live location',
                    'color' => $color,
                ];
            }
            return null;
        })->filter()->values()->all();

        return [
            'routes' => $routesData,
            'families' => $families,
            'drivers' => $drivers,
        ];
    }

    private function stockStats(): array
    {
        // Warehouse inventory by category
        $categories = WarehouseCategory::active()
            ->withCount(['items as total_items' => fn($q) => $q->where('active', true)])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($cat) => [
                'name' => $cat->name,
                'type' => $cat->type,
                'count' => $cat->total_items,
            ]);

        $totalOnHand = WarehouseItem::where('active', true)->count();

        // Receipts today
        $receiptsToday = WarehouseTransaction::where('type', 'receipt')
            ->whereDate('created_at', today())
            ->count();

        // Packing stats
        $packingPending = PackingList::where('status', PackingStatus::Pending)->count();
        $packingInProgress = PackingList::where('status', PackingStatus::InProgress)->count();
        $packingComplete = PackingList::where('status', PackingStatus::Complete)->count();
        $packingVerified = PackingList::where('status', PackingStatus::Verified)->count();
        $packingTotal = $packingPending + $packingInProgress + $packingComplete + $packingVerified;
        $packingPct = $packingTotal > 0 ? round((($packingComplete + $packingVerified) / $packingTotal) * 100) : 0;

        // Gift intake
        $giftsReceived = Child::whereHas('family', fn($q) => $q->whereNotNull('family_number'))
            ->where('gift_dropped_off', true)->count();
        $totalChildren = Child::whereHas('family', fn($q) => $q->whereNotNull('family_number'))->count();

        return [
            'warehouse' => [
                'categories' => $categories,
                'total_on_hand' => $totalOnHand,
                'receipts_today' => $receiptsToday,
            ],
            'packing' => [
                'pending' => $packingPending,
                'in_progress' => $packingInProgress,
                'complete' => $packingComplete,
                'verified' => $packingVerified,
                'total' => $packingTotal,
                'pct' => $packingPct,
            ],
            'gifts' => [
                'received' => $giftsReceived,
                'total_children' => $totalChildren,
            ],
        ];
    }
}
