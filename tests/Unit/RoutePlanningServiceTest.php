<?php

namespace Tests\Unit;

use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Services\RoutePlanningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutePlanningServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoutePlanningService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoutePlanningService();
    }

    private function createFamilyOnRoute(DeliveryRoute $route, int $number, float $lat, float $lng, int $order): Family
    {
        return Family::create([
            'family_name' => "Family {$number}", 'family_number' => $number,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-0' . str_pad($number, 3, '0', STR_PAD_LEFT),
            'address' => "{$number} Test St",
            'latitude' => $lat, 'longitude' => $lng,
            'delivery_route_id' => $route->id, 'route_order' => $order,
        ]);
    }

    public function test_polyline_for_route_returns_fallback_without_ors_key(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Fallback Test',
            'start_lat' => 47.85,
            'start_lng' => -121.97,
        ]);
        $this->createFamilyOnRoute($route, 1, 47.86, -121.98, 1);
        $this->createFamilyOnRoute($route, 2, 47.87, -121.99, 2);

        $polyline = $this->service->polylineForRoute($route);

        $this->assertIsArray($polyline);
        $this->assertNotEmpty($polyline);
        // Should include start point + families + end point = 4 points
        $this->assertCount(4, $polyline);
        // First point is the start location
        $this->assertEquals(47.85, $polyline[0][0]);
        $this->assertEquals(-121.97, $polyline[0][1]);
    }

    public function test_polyline_fallback_uses_lat_lng_order(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Order Test',
            'start_lat' => 47.85,
            'start_lng' => -121.97,
        ]);
        $this->createFamilyOnRoute($route, 1, 47.86, -121.98, 1);

        $polyline = $this->service->polylineForRoute($route);

        // Fallback should be [lat, lng] order (not [lng, lat] like ORS)
        $this->assertEquals(47.85, $polyline[0][0]); // lat
        $this->assertEquals(-121.97, $polyline[0][1]); // lng
    }

    public function test_optimize_route_returns_false_with_no_families(): void
    {
        $route = DeliveryRoute::create(['name' => 'Empty Route']);

        $result = $this->service->optimizeRoute($route);
        $this->assertFalse($result);
    }

    public function test_optimize_route_with_one_family_succeeds(): void
    {
        $route = DeliveryRoute::create(['name' => 'Single Family']);
        $this->createFamilyOnRoute($route, 1, 47.85, -121.97, 1);

        // Without ORS key, should still succeed for single family
        $result = $this->service->optimizeRoute($route);
        $this->assertTrue($result);
        $this->assertEquals(1, $route->fresh()->stop_count);
    }

    public function test_refresh_geometry_returns_false_without_ors_key(): void
    {
        $route = DeliveryRoute::create(['name' => 'No Key', 'start_lat' => 47.85, 'start_lng' => -121.97]);
        $this->createFamilyOnRoute($route, 1, 47.86, -121.98, 1);

        $result = $this->service->refreshRouteGeometry($route);

        $this->assertFalse($result);
        // But should still save fallback geometry
        $route->refresh();
        $this->assertNotNull($route->route_geometry);
        $this->assertNotNull($route->geometry_updated_at);
    }

    public function test_refresh_geometry_creates_fallback_for_empty_route(): void
    {
        $route = DeliveryRoute::create(['name' => 'Empty']);

        $result = $this->service->refreshRouteGeometry($route);

        $this->assertFalse($result);
        $route->refresh();
        $this->assertNotNull($route->geometry_updated_at);
    }

    public function test_optimize_route_sets_start_coordinates(): void
    {
        $route = DeliveryRoute::create(['name' => 'Coords Test']);
        $this->createFamilyOnRoute($route, 1, 47.85, -121.97, 1);

        $this->service->optimizeRoute($route, 48.0, -122.0);

        $route->refresh();
        $this->assertEquals(48.0, (float) $route->start_lat);
        $this->assertEquals(-122.0, (float) $route->start_lng);
    }

    public function test_optimize_route_defaults_start_to_first_family(): void
    {
        $route = DeliveryRoute::create(['name' => 'Default Start']);
        $this->createFamilyOnRoute($route, 1, 47.85, -121.97, 1);

        $this->service->optimizeRoute($route);

        $route->refresh();
        $this->assertEquals(47.85, (float) $route->start_lat);
        $this->assertEquals(-121.97, (float) $route->start_lng);
    }
}
