<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Services\RoutePlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryRouteController extends Controller
{
    public function __construct(
        private readonly RoutePlanningService $routePlanning
    ) {}

    /**
     * Route management page for Santa.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('delivery.index', ['tab' => 'routes']);
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
            $this->routePlanning->refreshRouteGeometry($route->fresh());
        }

        return redirect()->route('delivery.index', ['tab' => 'routes'])
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

        return redirect()->route('delivery.index', ['tab' => 'routes'])
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
            return redirect()->route('delivery.index', ['tab' => 'routes'])
                ->with('error', 'OpenRouteService API key not configured. Set it in Settings.');
        }

        $routes = DeliveryRoute::whereIn('id', $request->route_ids)
            ->with('families')
            ->get();

        foreach ($routes as $i => $route) {
            $geocodedFamilies = $route->families->filter(fn($family) => $family->latitude && $family->longitude)->count();
            if ($geocodedFamilies === 0) {
                return redirect()->route('delivery.index', ['tab' => 'routes'])
                    ->with('error', "Route '{$route->name}' has no geocoded families.");
            }

            $ok = $this->routePlanning->optimizeRoute(
                $route,
                (float) $request->start_lat,
                (float) $request->start_lng
            );

            if (! $ok && $geocodedFamilies >= 2) {
                return redirect()->route('delivery.index', ['tab' => 'routes'])
                    ->with('error', "Could not optimize route '{$route->name}'. Check the ORS key or route coordinates.");
            }
        }

        return redirect()->route('delivery.index', ['tab' => 'routes'])
            ->with('success', 'Routes optimized successfully!');
    }

    /**
     * Recalculate route geometry without changing stop order.
     */
    public function recalculate(DeliveryRoute $deliveryRoute): JsonResponse
    {
        $orsUsed = $this->routePlanning->refreshRouteGeometry($deliveryRoute->fresh());
        $deliveryRoute->refresh();

        return response()->json([
            'ok' => true,
            'ors' => $orsUsed,
            'message' => $orsUsed ? 'Route geometry updated from ORS' : 'ORS unavailable — using straight-line fallback',
            'distance' => $deliveryRoute->formattedDistance(),
            'duration' => $deliveryRoute->formattedDuration(),
        ]);
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
        $this->routePlanning->refreshRouteGeometry($deliveryRoute->fresh());

        return redirect()->route('delivery.index', ['tab' => 'routes'])
            ->with('success', "Route '{$deliveryRoute->name}' updated.");
    }

    // ── Public driver route view ─────────────────────────────────────

    /**
     * Driver's mobile route view (public, token-secured).
     */
    public function driverView(string $token): View
    {
        $route = DeliveryRoute::where('access_token', $token)
            ->with(['families' => fn($q) => $q
                ->orderByRaw("CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END")
                ->orderBy('route_order')
            ])
            ->firstOrFail();

        return view('delivery-routes.driver', compact('route'));
    }

    /**
     * Mark a stop as delivered from the driver view.
     */
    public function completeStop(Request $request, string $token, Family $family): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'family_id' => $family->id,
                'status' => 'delivered',
            ]);
        }

        return redirect()->route('delivery.driverView', $token)
            ->with('success', "#{$family->family_number} marked as delivered.");
    }

    /**
     * Update driver location from the public driver view (token-authenticated).
     */
    public function updateDriverLocation(Request $request, string $token): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();

        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Update the route's driver user if there is one
        if ($route->driver_user_id) {
            User::where('id', $route->driver_user_id)->update([
                'last_lat' => $request->latitude,
                'last_lng' => $request->longitude,
                'last_location_at' => now(),
            ]);
        }

        $route->update([
            'driver_lat' => $request->latitude,
            'driver_lng' => $request->longitude,
            'driver_location_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Mark a stop as in transit when the driver clicks Navigate.
     */
    public function markHeading(Request $request, string $token, Family $family): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        if ($family->delivery_route_id !== $route->id) {
            abort(403);
        }

        $family->update(['delivery_status' => DeliveryStatus::InTransit]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => $route->driver_user_id,
            'status' => 'in_transit',
            'notes' => 'Driver started navigation.',
        ]);

        return response()->json([
            'ok' => true,
            'family_id' => $family->id,
            'status' => 'in_transit',
        ]);
    }

    /**
     * Driver marks route as returning (all stops done, heading back).
     */
    public function markReturning(Request $request, string $token): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        $route->update(['returning_at' => now()]);

        return response()->json(['ok' => true, 'route_status' => $route->route_status]);
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
                'stop_count' => (int) $route->stop_count,
                'start_lat' => (float) $route->start_lat,
                'start_lng' => (float) $route->start_lng,
                'polyline' => $this->routePlanning->polylineForRoute($route),
            ],
            'stops' => $stops,
        ]);
    }
}
