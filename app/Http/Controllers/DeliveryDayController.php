<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\DeliveryTeam;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDayController extends Controller
{
    public function index(Request $request): View
    {
        // Routes with families and driver info
        $routes = DeliveryRoute::with(['driver', 'families' => fn($q) => $q->orderBy('route_order')])
            ->get();

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
            'picked_up' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::PickedUp)->count(),
        ];

        return view('delivery-day.index', compact(
            'routes', 'families', 'stats'
        ));
    }

    public function updateStatus(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered,picked_up'],
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
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered,picked_up'],
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
            'status' => ['required', 'string', 'in:delivered,left_at_door,no_answer,attempted,picked_up,note'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Auto-update family delivery_status for terminal statuses
        if (in_array($request->status, ['delivered', 'picked_up'])) {
            $family->update(['delivery_status' => $request->status]);
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

        // Route polylines — ordered stops per route with team color
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

            $polyline = [];
            if ($route->start_lat && $route->start_lng) {
                $polyline[] = [(float) $route->start_lat, (float) $route->start_lng];
            }
            foreach ($route->families as $f) {
                $polyline[] = [(float) $f->latitude, (float) $f->longitude];
            }
            if ($route->start_lat && $route->start_lng) {
                $polyline[] = [(float) $route->start_lat, (float) $route->start_lng];
            }

            return [
                'id' => $route->id,
                'name' => $route->name,
                'color' => $color,
                'team_id' => $teamId,
                'polyline' => $polyline,
            ];
        });

        return response()->json([
            'families' => $families,
            'volunteers' => $volunteers,
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

        // If we have a starting point, sort by distance to it
        if ($request->filled('start_lat') && $request->filled('start_lng')) {
            $lat = $request->start_lat;
            $lng = $request->start_lng;
            $query->orderByRaw("(latitude - ?) * (latitude - ?) + (longitude - ?) * (longitude - ?) ASC", [$lat, $lat, $lng, $lng]);
        } else {
            $query->orderBy('family_number');
        }

        $families = $query->limit($batchSize)->get();

        if ($families->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No undelivered families available.'], 422);
        }

        // Create route
        $route = DeliveryRoute::create([
            'name' => $request->driver_name . ' - ' . now()->format('g:ia'),
            'driver_name' => $request->driver_name,
            'start_lat' => $request->start_lat,
            'start_lng' => $request->start_lng,
            'stop_count' => $families->count(),
            'access_token' => \Illuminate\Support\Str::random(32),
            'season_year' => (int) Setting::get('season_year', date('Y')),
        ]);

        // Assign families to route
        foreach ($families as $i => $family) {
            $family->update([
                'delivery_route_id' => $route->id,
                'route_order' => $i + 1,
                'delivery_status' => DeliveryStatus::Pending,
            ]);
        }

        // Try to optimize via ORS if key available
        $orsKey = Setting::get('openrouteservice_key', '');
        if ($orsKey && $families->count() >= 2) {
            try {
                app(\App\Http\Controllers\DeliveryRouteController::class)->optimizeRoute($route, $orsKey);
            } catch (\Exception $e) {
                // Non-fatal - route just won't be optimized
            }
        }

        $driverUrl = route('delivery.driverView', $route->access_token);

        return response()->json([
            'ok' => true,
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'stop_count' => $families->count(),
                'driver_url' => $driverUrl,
                'access_token' => $route->access_token,
            ],
        ]);
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
