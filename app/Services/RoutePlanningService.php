<?php

namespace App\Services;

use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $payload = [
            'jobs' => $jobs,
            'vehicles' => [[
                'id' => $route->id,
                'profile' => 'driving-car',
                'start' => [$startLng, $startLat],
                'end' => [$startLng, $startLat],
            ]],
        ];

        Log::info('ORS Optimization request', [
            'route_id' => $route->id,
            'job_count' => count($jobs),
            'start' => [$startLng, $startLat],
        ]);

        $response = $this->client($orsKey)->post('https://api.openrouteservice.org/optimization', $payload);

        if (! $response->successful()) {
            Log::error('ORS Optimization failed', [
                'route_id' => $route->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
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

        Log::info('ORS Directions request', [
            'route_id' => $route->id,
            'coordinate_count' => count($coordinates),
            'coordinates' => array_slice($coordinates, 0, 3), // log first 3 for debugging
        ]);

        $response = $this->client($orsKey)->post('https://api.openrouteservice.org/v2/directions/driving-car/geojson', [
            'coordinates' => $coordinates,
            'instructions' => false,
        ]);

        if (! $response->successful()) {
            Log::error('ORS Directions failed', [
                'route_id' => $route->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $route->update([
                'route_geometry' => $this->fallbackPolyline($route, $families),
                'geometry_updated_at' => now(),
            ]);
            return false;
        }

        $data = $response->json();
        $feature = collect($data['features'] ?? [])->first();

        if (! $feature || empty($feature['geometry']['coordinates'] ?? [])) {
            Log::warning('ORS Directions returned empty geometry', [
                'route_id' => $route->id,
                'response_keys' => array_keys($data),
            ]);
            $route->update([
                'route_geometry' => $this->fallbackPolyline($route, $families),
                'geometry_updated_at' => now(),
            ]);
            return false;
        }

        $geometry = collect($feature['geometry']['coordinates'])
            ->map(fn($pair) => [(float) $pair[1], (float) $pair[0]])
            ->values()
            ->all();

        $summary = $feature['properties']['summary'] ?? [];

        Log::info('ORS Directions success', [
            'route_id' => $route->id,
            'geometry_points' => count($geometry),
            'distance_m' => $summary['distance'] ?? null,
            'duration_s' => $summary['duration'] ?? null,
        ]);

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
                'Content-type' => 'application/json',
                'Accept' => 'application/json, application/geo+json',
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
