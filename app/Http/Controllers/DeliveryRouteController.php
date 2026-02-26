<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryRouteController extends Controller
{
    /**
     * Route management page for Santa.
     */
    public function index(): View
    {
        $routes = DeliveryRoute::with(['driver', 'families' => fn($q) => $q->orderBy('route_order')])
            ->get();

        // Families eligible for routing (have coordinates, need delivery)
        $unroutedFamilies = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('delivery_route_id')
            ->where(function ($q) {
                $q->where('delivery_preference', 'like', '%deliver%')
                    ->orWhereNull('delivery_preference');
            })
            ->orderBy('family_number')
            ->get();

        // Drivers = users with coordinator or santa role
        $drivers = User::where(function ($q) {
            $q->where('permission', 8)->orWhere('permission', 9);
        })->orderBy('first_name')->get();

        $orsKey = Setting::get('openrouteservice_key', '');

        return view('santa.delivery-routes.index', compact('routes', 'unroutedFamilies', 'drivers', 'orsKey'));
    }

    /**
     * Create a new route manually.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'driver_user_id' => ['nullable', 'exists:users,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'family_ids' => ['nullable', 'array'],
            'family_ids.*' => ['exists:families,id'],
        ]);

        $route = DeliveryRoute::create([
            'name' => $request->name,
            'driver_user_id' => $request->driver_user_id,
            'driver_name' => $request->driver_name,
        ]);

        if ($request->has('family_ids')) {
            foreach ($request->family_ids as $order => $familyId) {
                Family::where('id', $familyId)->update([
                    'delivery_route_id' => $route->id,
                    'route_order' => $order + 1,
                ]);
            }
            $route->update(['stop_count' => count($request->family_ids)]);
        }

        return redirect()->route('santa.deliveryRoutes.index')
            ->with('success', "Route '{$route->name}' created.");
    }

    /**
     * Delete a route and unassign its families.
     */
    public function destroy(DeliveryRoute $deliveryRoute): RedirectResponse
    {
        Family::where('delivery_route_id', $deliveryRoute->id)->update([
            'delivery_route_id' => null,
            'route_order' => null,
        ]);

        $name = $deliveryRoute->name;
        $deliveryRoute->delete();

        return redirect()->route('santa.deliveryRoutes.index')
            ->with('success', "Route '{$name}' deleted.");
    }

    /**
     * Optimize routes using OpenRouteService VROOM API.
     */
    public function optimize(Request $request): RedirectResponse
    {
        $request->validate([
            'route_ids' => ['required', 'array', 'min:1'],
            'route_ids.*' => ['exists:delivery_routes,id'],
            'start_lat' => ['required', 'numeric'],
            'start_lng' => ['required', 'numeric'],
        ]);

        $orsKey = Setting::get('openrouteservice_key', '');
        if (empty($orsKey)) {
            return redirect()->route('santa.deliveryRoutes.index')
                ->with('error', 'OpenRouteService API key not configured. Set it in Settings.');
        }

        $routes = DeliveryRoute::whereIn('id', $request->route_ids)
            ->with('families')
            ->get();

        // Build VROOM problem
        $vehicles = [];
        $jobs = [];
        $familyIndex = []; // job index → family_id

        foreach ($routes as $i => $route) {
            $startLat = $route->start_lat ?? $request->start_lat;
            $startLng = $route->start_lng ?? $request->start_lng;

            $vehicles[] = [
                'id' => $route->id,
                'profile' => 'driving-car',
                'start' => [(float) $startLng, (float) $startLat],
                'end' => [(float) $startLng, (float) $startLat],
            ];

            foreach ($route->families as $family) {
                if ($family->latitude && $family->longitude) {
                    $jobId = $family->id;
                    $jobs[] = [
                        'id' => $jobId,
                        'location' => [(float) $family->longitude, (float) $family->latitude],
                        'service' => 300, // 5 min per stop
                    ];
                    $familyIndex[$jobId] = $route->id; // assign to this vehicle
                }
            }
        }

        if (empty($jobs)) {
            return redirect()->route('santa.deliveryRoutes.index')
                ->with('error', 'No geocoded families in selected routes.');
        }

        // Call ORS optimization API
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => $orsKey])
                ->post('https://api.openrouteservice.org/optimization', [
                    'jobs' => $jobs,
                    'vehicles' => $vehicles,
                ]);

            if (! $response->successful()) {
                $body = $response->json();
                $msg = $body['error']['message'] ?? $response->body();
                return redirect()->route('santa.deliveryRoutes.index')
                    ->with('error', "ORS API error: {$msg}");
            }

            $result = $response->json();
        } catch (\Exception $e) {
            return redirect()->route('santa.deliveryRoutes.index')
                ->with('error', 'ORS API request failed: ' . $e->getMessage());
        }

        // Apply optimized order to families
        foreach ($result['routes'] ?? [] as $vRoute) {
            $routeId = $vRoute['vehicle'];
            $route = $routes->firstWhere('id', $routeId);
            if (! $route) continue;

            $order = 1;
            foreach ($vRoute['steps'] as $step) {
                if ($step['type'] !== 'job') continue;
                Family::where('id', $step['id'])->update([
                    'delivery_route_id' => $routeId,
                    'route_order' => $order++,
                ]);
            }

            $route->update([
                'total_distance_meters' => $vRoute['distance'] ?? null,
                'total_duration_seconds' => $vRoute['duration'] ?? null,
                'stop_count' => $order - 1,
                'start_lat' => $request->start_lat,
                'start_lng' => $request->start_lng,
            ]);
        }

        // Handle any unassigned jobs
        $unassigned = collect($result['unassigned'] ?? []);
        if ($unassigned->isNotEmpty()) {
            $msg = $unassigned->count() . ' families could not be routed.';
        } else {
            $msg = 'Routes optimized successfully!';
        }

        return redirect()->route('santa.deliveryRoutes.index')
            ->with('success', $msg);
    }

    /**
     * Add/remove families from a route.
     */
    public function updateFamilies(Request $request, DeliveryRoute $deliveryRoute): RedirectResponse
    {
        $request->validate([
            'family_ids' => ['required', 'array'],
            'family_ids.*' => ['exists:families,id'],
        ]);

        // Unassign current families
        Family::where('delivery_route_id', $deliveryRoute->id)->update([
            'delivery_route_id' => null,
            'route_order' => null,
        ]);

        // Assign new families
        foreach ($request->family_ids as $order => $familyId) {
            Family::where('id', $familyId)->update([
                'delivery_route_id' => $deliveryRoute->id,
                'route_order' => $order + 1,
            ]);
        }

        $deliveryRoute->update(['stop_count' => count($request->family_ids)]);

        return redirect()->route('santa.deliveryRoutes.index')
            ->with('success', "Route '{$deliveryRoute->name}' updated.");
    }

    // ── Public driver route view ─────────────────────────────────────

    /**
     * Driver's mobile route view (public, token-secured).
     */
    public function driverView(string $token): View
    {
        $route = DeliveryRoute::where('access_token', $token)
            ->with(['families' => fn($q) => $q->orderBy('route_order')])
            ->firstOrFail();

        return view('delivery-routes.driver', compact('route'));
    }

    /**
     * Mark a stop as delivered from the driver view.
     */
    public function completeStop(Request $request, string $token, Family $family): RedirectResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();

        if ($family->delivery_route_id !== $route->id) {
            abort(403);
        }

        $family->update(['delivery_status' => DeliveryStatus::Delivered]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => $route->driver_user_id,
            'status' => 'delivered',
            'notes' => 'Marked delivered via driver route view.',
        ]);

        return redirect()->route('delivery.driverView', $token)
            ->with('success', "#{$family->family_number} marked as delivered.");
    }

    /**
     * Get route data as JSON (for map display).
     */
    public function routeData(string $token): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)
            ->with(['families' => fn($q) => $q->orderBy('route_order')])
            ->firstOrFail();

        $stops = $route->families->map(fn($f) => [
            'id' => $f->id,
            'number' => $f->family_number,
            'name' => $f->family_name,
            'address' => $f->address,
            'lat' => (float) $f->latitude,
            'lng' => (float) $f->longitude,
            'order' => $f->route_order,
            'status' => $f->delivery_status?->value ?? 'pending',
            'nav_url' => "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($f->address),
        ]);

        return response()->json([
            'route' => [
                'name' => $route->name,
                'distance' => $route->formattedDistance(),
                'duration' => $route->formattedDuration(),
                'start_lat' => (float) $route->start_lat,
                'start_lng' => (float) $route->start_lng,
            ],
            'stops' => $stops,
        ]);
    }
}
