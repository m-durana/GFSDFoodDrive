<?php

namespace App\Services;

use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class RoutePlanningService
{
    public function optimizeRoute(DeliveryRoute $route, ?float $startLat = null, ?float $startLng = null): bool
    {
        $route->load(['families' => fn($q) => $q->orderBy('route_order')]);
        $families = $route->families->filter(fn($f) => $f->latitude && $f->longitude)->values();
        if ($families->isEmpty()) {
            return false;
        }

        $startLat ??= (float) ($route->start_lat ?? $families->first()->latitude);
        $startLng ??= (float) ($route->start_lng ?? $families->first()->longitude);

        $route->update([
            'start_lat' => $startLat,
            'start_lng' => $startLng,
            'stop_count' => $families->count(),
        ]);

        if ($families->count() < 2) {
            $this->refreshRouteGeometry($route->fresh());
            return true;
        }

        $orsKey = (string) Setting::get('openrouteservice_key', '');
        if ($orsKey === '') {
            $this->refreshRouteGeometry($route->fresh());
            return false;
        }

        $jobs = $families->map(fn($family) => [
            'id' => $family->id,
            'location' => [(float) $family->longitude, (float) $family->latitude],
            'service' => 300,
        ])->all();

        $response = $this->client($orsKey)->post('https://api.openrouteservice.org/optimization', [
            'jobs' => $jobs,
            'vehicles' => [[
                'id' => $route->id,
                'profile' => 'driving-car',
                'start' => [$startLng, $startLat],
                'end' => [$startLng, $startLat],
            ]],
        ]);

        if (! $response->successful()) {
            return false;
        }

        $optimized = collect($response->json('routes', []))->first();
        if (! $optimized) {
            return false;
        }

        $order = 1;
        foreach ($optimized['steps'] ?? [] as $step) {
            if (($step['type'] ?? null) !== 'job') {
                continue;
            }

            Family::where('id', $step['id'])->update([
                'delivery_route_id' => $route->id,
                'route_order' => $order++,
            ]);
        }

        $route->update([
            'start_lat' => $startLat,
            'start_lng' => $startLng,
            'stop_count' => $order - 1,
            'total_distance_meters' => $optimized['distance'] ?? null,
            'total_duration_seconds' => $optimized['duration'] ?? null,
        ]);

        $this->refreshRouteGeometry($route->fresh());

        return true;
    }

    public function refreshRouteGeometry(DeliveryRoute $route): bool
    {
        $orsKey = (string) Setting::get('openrouteservice_key', '');
        $route->load(['families' => fn($q) => $q->orderBy('route_order')]);
        $families = $route->families->filter(fn($f) => $f->latitude && $f->longitude)->values();

        if ($orsKey === '' || $families->isEmpty()) {
            $route->update([
                'route_geometry' => $this->fallbackPolyline($route, $families),
                'geometry_updated_at' => now(),
            ]);
            return false;
        }

        $coordinates = $this->coordinatesForRoute($route, $families);
        if (count($coordinates) < 2) {
            $route->update([
                'route_geometry' => $this->fallbackPolyline($route, $families),
                'geometry_updated_at' => now(),
            ]);
            return false;
        }

        $response = $this->client($orsKey)->post('https://api.openrouteservice.org/v2/directions/driving-car/geojson', [
            'coordinates' => $coordinates,
            'instructions' => false,
        ]);

        if (! $response->successful()) {
            $route->update([
                'route_geometry' => $this->fallbackPolyline($route, $families),
                'geometry_updated_at' => now(),
            ]);
            return false;
        }

        $feature = collect($response->json('features', []))->first();
        $geometry = collect($feature['geometry']['coordinates'] ?? [])
            ->map(fn($pair) => [(float) $pair[1], (float) $pair[0]])
            ->values()
            ->all();

        $summary = $feature['properties']['summary'] ?? [];
        $route->update([
            'route_geometry' => $geometry,
            'geometry_updated_at' => now(),
            'total_distance_meters' => $summary['distance'] ?? $route->total_distance_meters,
            'total_duration_seconds' => $summary['duration'] ?? $route->total_duration_seconds,
        ]);

        return true;
    }

    public function polylineForRoute(DeliveryRoute $route): array
    {
        if (empty($route->route_geometry)) {
            $this->refreshRouteGeometry($route);
            $route->refresh();
        }

        if (is_array($route->route_geometry) && ! empty($route->route_geometry)) {
            return $route->route_geometry;
        }

        $route->loadMissing(['families' => fn($q) => $q->orderBy('route_order')]);
        return $this->fallbackPolyline(
            $route,
            $route->families->filter(fn($f) => $f->latitude && $f->longitude)->values()
        );
    }

    private function client(string $orsKey)
    {
        return Http::timeout(30)
            ->withoutVerifying()
            ->withHeaders([
                'Authorization' => $orsKey,
                'Accept' => 'application/json',
            ]);
    }

    private function coordinatesForRoute(DeliveryRoute $route, Collection $families): array
    {
        $coordinates = [];

        if ($route->start_lat && $route->start_lng) {
            $coordinates[] = [(float) $route->start_lng, (float) $route->start_lat];
        }

        foreach ($families as $family) {
            $coordinates[] = [(float) $family->longitude, (float) $family->latitude];
        }

        if ($route->start_lat && $route->start_lng) {
            $coordinates[] = [(float) $route->start_lng, (float) $route->start_lat];
        }

        return $coordinates;
    }

    private function fallbackPolyline(DeliveryRoute $route, Collection $families): array
    {
        $polyline = [];
        if ($route->start_lat && $route->start_lng) {
            $polyline[] = [(float) $route->start_lat, (float) $route->start_lng];
        }

        foreach ($families as $family) {
            $polyline[] = [(float) $family->latitude, (float) $family->longitude];
        }

        if ($route->start_lat && $route->start_lng) {
            $polyline[] = [(float) $route->start_lat, (float) $route->start_lng];
        }

        return $polyline;
    }
}
