<?php

namespace Tests\Unit;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryRoute;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryRouteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_distance_converts_meters_to_miles(): void
    {
        $route = DeliveryRoute::create(['name' => 'Distance Test', 'total_distance_meters' => 16093]);
        $this->assertEquals('10.0 mi', $route->formattedDistance());
    }

    public function test_formatted_distance_returns_dash_when_null(): void
    {
        $route = DeliveryRoute::create(['name' => 'No Distance']);
        $this->assertEquals('—', $route->formattedDistance());
    }

    public function test_formatted_duration_minutes(): void
    {
        $route = DeliveryRoute::create(['name' => 'Short', 'total_duration_seconds' => 1800]);
        $this->assertEquals('30 min', $route->formattedDuration());
    }

    public function test_formatted_duration_hours_and_minutes(): void
    {
        $route = DeliveryRoute::create(['name' => 'Long', 'total_duration_seconds' => 5400]);
        $this->assertEquals('1h 30m', $route->formattedDuration());
    }

    public function test_formatted_duration_returns_dash_when_null(): void
    {
        $route = DeliveryRoute::create(['name' => 'No Duration']);
        $this->assertEquals('—', $route->formattedDuration());
    }

    public function test_formatted_meta_combines_stops_distance_duration(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Meta Test',
            'stop_count' => 5,
            'total_distance_meters' => 8047,
            'total_duration_seconds' => 1200,
        ]);
        $this->assertEquals('5 stops · 5.0 mi · 20 min', $route->formattedMeta());
    }

    public function test_display_name_strips_seeded_prefix(): void
    {
        $route = DeliveryRoute::create(['name' => 'seeded North Route']);
        $this->assertEquals('North Route', $route->display_name);
    }

    public function test_display_name_keeps_normal_name(): void
    {
        $route = DeliveryRoute::create(['name' => 'North Route']);
        $this->assertEquals('North Route', $route->display_name);
    }

    // ── Route Status ──────────────────────────────────────────────

    public function test_route_status_pending_when_no_families(): void
    {
        $route = DeliveryRoute::create(['name' => 'Empty']);
        $this->assertEquals('pending', $route->route_status);
    }

    public function test_route_status_pending_with_all_pending_families(): void
    {
        $route = DeliveryRoute::create(['name' => 'Pending']);
        Family::create([
            'family_name' => 'Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234',
            'delivery_route_id' => $route->id, 'route_order' => 1,
            'delivery_status' => DeliveryStatus::Pending,
        ]);
        $route->load('families');
        $this->assertEquals('pending', $route->route_status);
    }

    public function test_route_status_in_transit(): void
    {
        $route = DeliveryRoute::create(['name' => 'Transit']);
        Family::create([
            'family_name' => 'Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234',
            'delivery_route_id' => $route->id, 'route_order' => 1,
            'delivery_status' => DeliveryStatus::InTransit,
        ]);
        $route->load('families');
        $this->assertEquals('in_transit', $route->route_status);
    }

    public function test_route_status_partially_delivered(): void
    {
        $route = DeliveryRoute::create(['name' => 'Partial']);
        Family::create([
            'family_name' => 'A', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234',
            'delivery_route_id' => $route->id, 'route_order' => 1,
            'delivery_status' => DeliveryStatus::Delivered,
        ]);
        Family::create([
            'family_name' => 'B', 'family_number' => 2,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-5678',
            'delivery_route_id' => $route->id, 'route_order' => 2,
            'delivery_status' => DeliveryStatus::Pending,
        ]);
        $route->load('families');
        $this->assertEquals('partially_delivered', $route->route_status);
    }

    public function test_route_status_returning(): void
    {
        $route = DeliveryRoute::create(['name' => 'Returning', 'returning_at' => now()]);
        Family::create([
            'family_name' => 'A', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234',
            'delivery_route_id' => $route->id, 'route_order' => 1,
            'delivery_status' => DeliveryStatus::Delivered,
        ]);
        $route->load('families');
        $this->assertEquals('returning', $route->route_status);
    }

    public function test_route_status_complete(): void
    {
        $route = DeliveryRoute::create(['name' => 'Complete', 'completed_at' => now()]);
        Family::create([
            'family_name' => 'A', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234',
            'delivery_route_id' => $route->id, 'route_order' => 1,
            'delivery_status' => DeliveryStatus::Delivered,
        ]);
        $route->load('families');
        $this->assertEquals('complete', $route->route_status);
    }

    // ── Geometry ──────────────────────────────────────────────────

    public function test_route_geometry_stored_as_array(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Geometry',
            'route_geometry' => [[47.85, -121.97], [47.86, -121.98]],
        ]);
        $route->refresh();
        $this->assertIsArray($route->route_geometry);
        $this->assertCount(2, $route->route_geometry);
    }
}
