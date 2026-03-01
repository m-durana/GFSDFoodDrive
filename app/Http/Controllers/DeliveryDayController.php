<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\DeliveryTeam;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Services\RoutePlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDayController extends Controller
{
    public function __construct(
        private readonly RoutePlanningService $routePlanning
    ) {}

    public function index(Request $request): View
    {
        // Routes with families and driver info
        $routes = DeliveryRoute::with(['driver', 'families' => fn($q) => $q->orderBy('route_order')])
            ->get();
        $routes->each(function ($route) {
            $sorted = $route->families->sortBy([
                fn($f) => ($f->delivery_status?->value ?? 'pending') === 'delivered' ? 1 : 0,
                fn($f) => $f->route_order ?? 9999,
            ])->values();
            $route->setRelation('families', $sorted);
        });

        // All delivery families for dispatch board
        $query = Family::whereNotNull('family_number')
            ->with(['children', 'volunteer', 'deliveryRoute',
                'deliveryLogs' => fn($q) => $q->latest()->limit(5),
                'deliveryLogs.user',
            ]);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'needs_delivery') {
                $query->where('delivery_preference', 'like', '%deliver%')
                    ->where(function ($q) {
                        $q->where('delivery_status', DeliveryStatus::Pending)
                            ->orWhereNull('delivery_status');
                    });
            } else {
                $query->where('delivery_status', $request->status);
            }
        }

        $families = $query->orderBy('family_number')->get();

        // Stats
        $allDeliveryFamilies = Family::whereNotNull('family_number');
        $stats = [
            'total' => (clone $allDeliveryFamilies)->count(),
            'needs_delivery' => (clone $allDeliveryFamilies)
                ->where('delivery_preference', 'like', '%deliver%')
                ->count(),
            'pending' => (clone $allDeliveryFamilies)
                ->where(function ($q) {
                    $q->where('delivery_status', DeliveryStatus::Pending)
                        ->orWhereNull('delivery_status');
                })
                ->count(),
            'in_transit' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::InTransit)->count(),
            'delivered' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::Delivered)->count(),
        ];

        // Routing eligibility stats (for clearer UI messaging)
        $allNumbered = Family::whereNotNull('family_number');
        $eligibleForRouting = (clone $allNumbered)
            ->whereNull('delivery_route_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) {
                $q->where('delivery_status', DeliveryStatus::Pending)
                    ->orWhereNull('delivery_status');
            })
            ->where(function ($q) {
                $q->where('delivery_preference', 'like', '%deliver%')
                    ->orWhereNull('delivery_preference');
            });

        $routingStats = [
            'eligible' => (clone $eligibleForRouting)->count(),
            'with_numbers' => (clone $allNumbered)->count(),
            'missing_coords' => (clone $allNumbered)
                ->where(function ($q) {
                    $q->whereNull('latitude')->orWhereNull('longitude');
                })->count(),
            'pickup_only' => (clone $allNumbered)
                ->where('delivery_preference', 'like', '%pick%')
                ->count(),
            'already_routed' => (clone $allNumbered)
                ->whereNotNull('delivery_route_id')
                ->count(),
            'not_pending' => (clone $allNumbered)
                ->whereNotNull('delivery_status')
                ->whereNotIn('delivery_status', [DeliveryStatus::Pending->value])
                ->count(),
        ];

        $unroutedEligible = $families->filter(function ($f) {
            $isPending = ($f->delivery_status?->value ?? 'pending') === 'pending';
            $isDelivery = !$f->delivery_preference || str_contains(strtolower($f->delivery_preference), 'deliver');
            return !$f->delivery_route_id && $f->latitude && $f->longitude && $isPending && $isDelivery;
        });

        $drivers = User::where('permission', '>=', 8)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('delivery-day.index', compact(
            'routes', 'families', 'stats', 'routingStats', 'unroutedEligible', 'drivers'
        ));
    }

    public function updateStatus(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered'],
        ]);

        $family->update(['delivery_status' => $request->delivery_status]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->delivery_status,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->back()
            ->with('success', "Status updated for '{$family->family_name}'.");
    }

    public function updateStatusAjax(Request $request, Family $family): JsonResponse
    {
        $request->validate([
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $family->update(['delivery_status' => $request->delivery_status]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->delivery_status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'ok' => true,
            'status' => $request->delivery_status,
            'label' => DeliveryStatus::from($request->delivery_status)->label(),
            'family_id' => $family->id,
        ]);
    }

    public function updateTeam(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'delivery_team' => ['nullable', 'string', 'max:255'],
            'delivery_team_id' => ['nullable', 'exists:delivery_teams,id'],
        ]);

        $family->update($request->only('delivery_team', 'delivery_team_id'));

        return redirect()->back()
            ->with('success', "Team assigned for '{$family->family_name}'.");
    }

    public function bulkAssignTeam(Request $request): JsonResponse
    {
        $request->validate([
            'family_ids' => ['required', 'array', 'min:1'],
            'family_ids.*' => ['exists:families,id'],
            'delivery_team_id' => ['nullable', 'exists:delivery_teams,id'],
        ]);

        Family::whereIn('id', $request->family_ids)
            ->update(['delivery_team_id' => $request->delivery_team_id]);

        return response()->json([
            'ok' => true,
            'count' => count($request->family_ids),
        ]);
    }

    public function addLog(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:delivered,left_at_door,no_answer,attempted,note'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Auto-update family delivery_status for terminal statuses
        if ($request->status === 'delivered') {
            $family->update(['delivery_status' => 'delivered']);
        } elseif ($request->status === 'attempted' || $request->status === 'left_at_door') {
            $family->update(['delivery_status' => 'in_transit']);
        }

        return redirect()->back()
            ->with('success', "Log added for '{$family->family_name}'.");
    }

    public function map(): View
    {
        $teams = DeliveryTeam::select('id', 'name', 'color')->get();
        $routes = DeliveryRoute::select('id', 'name')->get();

        return view('delivery-day.map', compact('teams', 'routes'));
    }

    public function mapData(): JsonResponse
    {
        // Family pins with delivery status
        $families = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'family_number', 'family_name', 'address', 'phone1',
                'latitude', 'longitude', 'delivery_status', 'delivery_team_id',
                'delivery_route_id', 'route_order')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'number' => $f->family_number,
                'name' => $f->family_name,
                'address' => $f->address,
                'phone' => $f->phone1,
                'lat' => (float) $f->latitude,
                'lng' => (float) $f->longitude,
                'status' => $f->delivery_status?->value ?? 'pending',
                'team_id' => $f->delivery_team_id,
                'route_id' => $f->delivery_route_id,
                'route_order' => $f->route_order,
            ]);

        // Volunteer locations (updated in last 10 minutes)
        $volunteers = User::whereNotNull('last_lat')
            ->whereNotNull('last_lng')
            ->where('last_location_at', '>=', now()->subMinutes(10))
            ->select('id', 'first_name', 'last_name', 'last_lat', 'last_lng', 'last_location_at')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->first_name . ' ' . $v->last_name,
                'initial' => strtoupper(substr($v->first_name, 0, 1)),
                'lat' => (float) $v->last_lat,
                'lng' => (float) $v->last_lng,
                'updated' => $v->last_location_at->diffForHumans(),
            ]);

        // Route polylines — real route geometry when available
        $routes = DeliveryRoute::with(['families' => fn($q) => $q
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('route_order')
            ->select('id', 'delivery_route_id', 'latitude', 'longitude', 'route_order', 'delivery_team_id'),
        ])->get()->map(function ($route) {
            // Get team color from the first family's team, or default
            $teamId = $route->families->first()?->delivery_team_id;
            $color = $teamId
                ? DeliveryTeam::where('id', $teamId)->value('color') ?? '#dc2626'
                : '#dc2626';

            return [
                'id' => $route->id,
                'name' => $route->name,
                'color' => $color,
                'team_id' => $teamId,
                'polyline' => $this->routePlanning->polylineForRoute($route),
            ];
        });

        $drivers = DeliveryRoute::with('driver')->get()->map(function ($route) {
            if ($route->driver_lat && $route->driver_lng) {
                return [
                    'route_id' => $route->id,
                    'name' => $route->driver?->first_name ?? $route->driver_name ?? 'Driver',
                    'lat' => (float) $route->driver_lat,
                    'lng' => (float) $route->driver_lng,
                    'updated' => $route->driver_location_at?->diffForHumans() ?? 'just now',
                ];
            }
            if ($route->start_lat && $route->start_lng) {
                return [
                    'route_id' => $route->id,
                    'name' => $route->driver?->first_name ?? $route->driver_name ?? 'Driver',
                    'lat' => (float) $route->start_lat,
                    'lng' => (float) $route->start_lng,
                    'updated' => 'awaiting live location',
                ];
            }

            return null;
        })->filter()->values();

        return response()->json([
            'families' => $families,
            'volunteers' => $volunteers,
            'drivers' => $drivers,
            'routes' => $routes,
        ]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $request->user()->update([
            'last_lat' => $request->latitude,
            'last_lng' => $request->longitude,
            'last_location_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function track(): View
    {
        return view('delivery-day.track');
    }

    /**
     * Auto-assign a batch of nearby undelivered families to a new driver route.
     */
    public function quickAssign(Request $request): JsonResponse
    {
        $request->validate([
            'driver_name' => ['required', 'string', 'max:255'],
            'driver_user_id' => ['nullable', 'exists:users,id'],
            'batch_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'start_lat' => ['nullable', 'numeric'],
            'start_lng' => ['nullable', 'numeric'],
        ]);

        $batchSize = $request->input('batch_size', 5);

        // Find undelivered families with coordinates, not already on a route
        $query = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('delivery_route_id')
            ->where(function ($q) {
                $q->where('delivery_status', DeliveryStatus::Pending)
                    ->orWhereNull('delivery_status');
            })
            ->where(function ($q) {
                $q->where('delivery_preference', 'like', '%deliver%')
                    ->orWhereNull('delivery_preference');
            });

        $eligible = $query->get();
        $families = $this->selectNearbyFamilies($eligible, $batchSize, $request->start_lat, $request->start_lng);

        if ($families->isEmpty()) {
            $totalWithNumber = Family::whereNotNull('family_number')->count();
            $withCoords = Family::whereNotNull('family_number')->whereNotNull('latitude')->whereNotNull('longitude')->count();
            $onRoutes = Family::whereNotNull('family_number')->whereNotNull('delivery_route_id')->count();

            if ($totalWithNumber === 0) {
                $reason = 'No families have been assigned numbers yet.';
            } elseif ($withCoords === 0) {
                $reason = 'No families have GPS coordinates. Run geocoding from Santa > Settings first.';
            } elseif ($onRoutes >= $withCoords) {
                $reason = 'All geocoded families are already assigned to routes.';
            } else {
                $pendingDelivery = Family::whereNotNull('family_number')
                    ->where(function ($q) {
                        $q->where('delivery_status', DeliveryStatus::Pending)
                            ->orWhereNull('delivery_status');
                    })
                    ->where(function ($q) {
                        $q->where('delivery_preference', 'like', '%deliver%')
                            ->orWhereNull('delivery_preference');
                    })
                    ->count();
                $reason = 'No eligible families found. Eligible = pending delivery, GPS coordinates, and not already routed. ' .
                    $pendingDelivery . ' are pending delivery, ' . $withCoords . ' have coordinates, ' . $onRoutes . ' already routed.';
            }

            return response()->json(['ok' => false, 'message' => $reason], 422);
        }

        // Create route
        $route = DeliveryRoute::create([
            'name' => $request->driver_name . ' - ' . now()->format('g:ia'),
            'driver_name' => $request->driver_name,
            'driver_user_id' => $request->driver_user_id,
            'start_lat' => $request->start_lat,
            'start_lng' => $request->start_lng,
            'stop_count' => $families->count(),
            'access_token' => \Illuminate\Support\Str::random(32),
            'season_year' => (int) Setting::get('season_year', date('Y')),
        ]);

        // Assign families to route (nearest-neighbor order for coherence)
        $ordered = $this->orderByNearestNeighbor($families, $request->start_lat, $request->start_lng);
        foreach ($ordered as $i => $family) {
            $family->update([
                'delivery_route_id' => $route->id,
                'route_order' => $i + 1,
                'delivery_status' => DeliveryStatus::Pending,
            ]);
        }

        // Try to optimize via ORS if key available
        if ($families->count() >= 2) {
            $this->routePlanning->optimizeRoute($route, $request->start_lat, $request->start_lng);
        } else {
            $this->routePlanning->refreshRouteGeometry($route);
        }

        $driverUrl = route('delivery.driverView', $route->access_token);
        $suggested = $this->suggestNearbyFamilies($eligible, $families, $route);

        return response()->json([
            'ok' => true,
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'stop_count' => $families->count(),
                'driver_url' => $driverUrl,
                'access_token' => $route->access_token,
            ],
            'suggested' => $suggested,
        ]);
    }

    public function addFamiliesToRoute(Request $request, DeliveryRoute $deliveryRoute): JsonResponse
    {
        $request->validate([
            'family_ids' => ['required', 'array', 'min:1'],
            'family_ids.*' => ['exists:families,id'],
        ]);

        $maxOrder = Family::where('delivery_route_id', $deliveryRoute->id)->max('route_order') ?? 0;
        $added = 0;

        foreach ($request->family_ids as $familyId) {
            $family = Family::where('id', $familyId)
                ->whereNull('delivery_route_id')
                ->first();
            if (! $family) {
                continue;
            }
            $maxOrder++;
            $family->update([
                'delivery_route_id' => $deliveryRoute->id,
                'route_order' => $maxOrder,
                'delivery_status' => DeliveryStatus::Pending,
            ]);
            $added++;
        }

        $deliveryRoute->update(['stop_count' => Family::where('delivery_route_id', $deliveryRoute->id)->count()]);
        $this->routePlanning->refreshRouteGeometry($deliveryRoute->fresh());

        return response()->json([
            'ok' => true,
            'added' => $added,
            'stop_count' => $deliveryRoute->stop_count,
        ]);
    }

    public function markRouteReturning(Request $request, DeliveryRoute $deliveryRoute): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $deliveryRoute->update(['returning_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'route_status' => $deliveryRoute->route_status]);
        }

        return redirect()->route('delivery.index')
            ->with('success', "Route {$deliveryRoute->name} marked as returning.");
    }

    private function selectNearbyFamilies($eligible, int $batchSize, ?float $startLat, ?float $startLng)
    {
        if ($eligible->isEmpty()) {
            return collect();
        }

        if ($startLat !== null && $startLng !== null) {
            return $eligible->sortBy(fn($f) => $this->distanceSq($startLat, $startLng, (float) $f->latitude, (float) $f->longitude))
                ->take($batchSize)
                ->values();
        }

        $seed = $eligible->first();
        return $eligible->sortBy(fn($f) => $this->distanceSq((float) $seed->latitude, (float) $seed->longitude, (float) $f->latitude, (float) $f->longitude))
            ->take($batchSize)
            ->values();
    }

    private function orderByNearestNeighbor($families, ?float $startLat, ?float $startLng)
    {
        $remaining = $families->values();
        if ($remaining->isEmpty()) {
            return $remaining;
        }

        $ordered = collect();
        if ($startLat !== null && $startLng !== null) {
            $currentLat = $startLat;
            $currentLng = $startLng;
        } else {
            $seed = $remaining->first();
            $currentLat = (float) $seed->latitude;
            $currentLng = (float) $seed->longitude;
        }

        while ($remaining->isNotEmpty()) {
            $nearestIndex = 0;
            $nearestDist = null;
            foreach ($remaining as $idx => $f) {
                $dist = $this->distanceSq($currentLat, $currentLng, (float) $f->latitude, (float) $f->longitude);
                if ($nearestDist === null || $dist < $nearestDist) {
                    $nearestDist = $dist;
                    $nearestIndex = $idx;
                }
            }
            $next = $remaining->pull($nearestIndex);
            $ordered->push($next);
            $currentLat = (float) $next->latitude;
            $currentLng = (float) $next->longitude;
        }

        return $ordered;
    }

    private function suggestNearbyFamilies($eligible, $selected, DeliveryRoute $route): array
    {
        $selectedIds = $selected->pluck('id')->all();
        if (empty($selectedIds)) {
            return [];
        }

        $avgLat = $selected->avg('latitude');
        $avgLng = $selected->avg('longitude');
        $thresholdMiles = 1.0;

        return $eligible->filter(fn($f) => !in_array($f->id, $selectedIds, true))
            ->map(function ($f) use ($avgLat, $avgLng) {
                $dist = $this->distanceMiles($avgLat, $avgLng, (float) $f->latitude, (float) $f->longitude);
                return [
                    'id' => $f->id,
                    'number' => $f->family_number,
                    'name' => $f->family_name,
                    'address' => $f->address,
                    'distance_miles' => round($dist, 2),
                ];
            })
            ->filter(fn($row) => $row['distance_miles'] <= $thresholdMiles)
            ->sortBy('distance_miles')
            ->take(6)
            ->values()
            ->all();
    }

    private function distanceSq(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = $lat1 - $lat2;
        $dLng = $lng1 - $lng2;
        return ($dLat * $dLat) + ($dLng * $dLng);
    }

    private function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3958.8;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function logs(Request $request): View
    {
        $query = DeliveryLog::with(['family', 'user'])->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        $logs = $query->paginate(50);

        $logDates = DeliveryLog::selectRaw('DATE(created_at) as log_date')
            ->distinct()
            ->orderByDesc('log_date')
            ->pluck('log_date');

        return view('delivery-day.logs', compact('logs', 'logDates'));
    }
}
