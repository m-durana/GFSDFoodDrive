<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Http\Requests\OptimizeDeliveryRoutesRequest;
use App\Http\Requests\StoreDeliveryRouteRequest;
use App\Http\Requests\UpdateDeliveryRouteFamiliesRequest;
use App\Http\Requests\UpdateDriverLocationRequest;
use App\Http\Requests\VerifyDriverPinRequest;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Services\RoutePlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function store(StoreDeliveryRouteRequest $request): RedirectResponse
    {
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
            \App\Jobs\RefreshRouteGeometryJob::dispatch($route->id);
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
    public function optimize(OptimizeDeliveryRoutesRequest $request): RedirectResponse
    {
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
    public function updateFamilies(UpdateDeliveryRouteFamiliesRequest $request, DeliveryRoute $deliveryRoute): RedirectResponse
    {
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

        if (! $this->driverRouteVerified($route)) {
            return view('delivery-routes.verify', compact('route'));
        }

        return view('delivery-routes.driver', compact('route'));
    }

    public function verifyDriverPin(VerifyDriverPinRequest $request, string $token): RedirectResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();

        if (! $route->verifyDriverPin($request->input('pin'))) {
            return back()->withErrors(['pin' => 'Invalid route PIN.'])->withInput();
        }

        $request->session()->put($this->driverRouteSessionKey($route), true);

        return redirect()->route('delivery.driverView', $token);
    }

    /**
     * Mark a stop as delivered from the driver view.
     */
    public function completeStop(Request $request, string $token, int $stopOrder): RedirectResponse|JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        $this->abortUnlessDriverRouteVerified($route);

        $family = $this->familyForStopOrder($route, $stopOrder);
        if (! $family) {
            abort(403);
        }

        DB::transaction(function () use ($route, $family) {
            $family = Family::whereKey($family->id)->lockForUpdate()->firstOrFail();

            $family->update(['delivery_status' => DeliveryStatus::Delivered]);

            DeliveryLog::create([
                'family_id' => $family->id,
                'user_id' => $route->driver_user_id,
                'status' => 'delivered',
                'notes' => 'Marked delivered via driver route view.',
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'stop_order' => $family->route_order,
                'status' => 'delivered',
            ]);
        }

        return redirect()->route('delivery.driverView', $token)
            ->with('success', "Stop {$family->route_order} marked as delivered.");
    }

    /**
     * Update driver location from the public driver view (token-authenticated).
     */
    public function updateDriverLocation(UpdateDriverLocationRequest $request, string $token): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        $this->abortUnlessDriverRouteVerified($route);

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
    public function markHeading(Request $request, string $token, int $stopOrder): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        $this->abortUnlessDriverRouteVerified($route);

        $family = $this->familyForStopOrder($route, $stopOrder);
        if (! $family) {
            abort(403);
        }

        DB::transaction(function () use ($route, $family) {
            $family = Family::whereKey($family->id)->lockForUpdate()->firstOrFail();

            $family->update(['delivery_status' => DeliveryStatus::InTransit]);

            DeliveryLog::create([
                'family_id' => $family->id,
                'user_id' => $route->driver_user_id,
                'status' => 'in_transit',
                'notes' => 'Driver started navigation.',
            ]);
        });

        return response()->json([
            'ok' => true,
            'stop_order' => $family->route_order,
            'status' => 'in_transit',
        ]);
    }

    /**
     * Driver marks route as returning (all stops done, heading back).
     */
    public function markReturning(Request $request, string $token): JsonResponse
    {
        $route = DeliveryRoute::where('access_token', $token)->firstOrFail();
        $this->abortUnlessDriverRouteVerified($route);
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
        $this->abortUnlessDriverRouteVerified($route);

        $stops = $route->families->map(fn($f) => [
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

    private function driverRouteVerified(DeliveryRoute $route): bool
    {
        return session()->get($this->driverRouteSessionKey($route)) === true;
    }

    private function abortUnlessDriverRouteVerified(DeliveryRoute $route): void
    {
        abort_unless($this->driverRouteVerified($route), 403);
    }

    private function driverRouteSessionKey(DeliveryRoute $route): string
    {
        return 'driver_route_verified:' . $route->id;
    }

    private function familyForStopOrder(DeliveryRoute $route, int $stopOrder): ?Family
    {
        return Family::where('delivery_route_id', $route->id)
            ->where('route_order', $stopOrder)
            ->first();
    }
}
