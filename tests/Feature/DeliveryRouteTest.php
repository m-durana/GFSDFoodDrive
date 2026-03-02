<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryRouteTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $familyUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->familyUser = User::create([
            'username' => 'family', 'first_name' => 'F', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);
    }

    private function createFamilyWithCoords(int $number, float $lat, float $lng): Family
    {
        return Family::create([
            'family_name' => "Family {$number}", 'family_number' => $number,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => "{$number} Main St",
            'phone1' => '555-0' . str_pad($number, 3, '0', STR_PAD_LEFT),
            'latitude' => $lat, 'longitude' => $lng,
            'delivery_preference' => 'Delivery',
        ]);
    }

    // ── Route CRUD ────────────────────────────────────────────────

    public function test_route_can_be_created(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.store'), [
            'name' => 'North Route',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_routes', ['name' => 'North Route']);
    }

    public function test_route_can_be_created_with_families(): void
    {
        $f1 = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $f2 = $this->createFamilyWithCoords(2, 47.86, -121.98);

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.store'), [
            'name' => 'Test Route',
            'family_ids' => [$f1->id, $f2->id],
        ]);
        $response->assertRedirect();

        $route = DeliveryRoute::where('name', 'Test Route')->first();
        $this->assertNotNull($route);
        $this->assertEquals(2, $route->stop_count);
        $this->assertEquals($route->id, $f1->fresh()->delivery_route_id);
        $this->assertEquals(1, $f1->fresh()->route_order);
        $this->assertEquals(2, $f2->fresh()->route_order);
    }

    public function test_route_can_be_deleted(): void
    {
        $route = DeliveryRoute::create(['name' => 'Delete Me']);
        $f1 = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $f1->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->actingAs($this->santa)->delete(route('santa.deliveryRoutes.destroy', $route));
        $response->assertRedirect();
        $this->assertDatabaseMissing('delivery_routes', ['id' => $route->id]);
        $this->assertNull($f1->fresh()->delivery_route_id);
        $this->assertNull($f1->fresh()->route_order);
    }

    public function test_route_families_can_be_updated(): void
    {
        $route = DeliveryRoute::create(['name' => 'Update Route']);
        $f1 = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $f2 = $this->createFamilyWithCoords(2, 47.86, -121.98);
        $f3 = $this->createFamilyWithCoords(3, 47.87, -121.99);

        $f1->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->actingAs($this->santa)->put(
            route('santa.deliveryRoutes.updateFamilies', $route),
            ['family_ids' => [$f2->id, $f3->id]]
        );
        $response->assertRedirect();

        $this->assertNull($f1->fresh()->delivery_route_id);
        $this->assertEquals($route->id, $f2->fresh()->delivery_route_id);
        $this->assertEquals($route->id, $f3->fresh()->delivery_route_id);
        $this->assertEquals(2, $route->fresh()->stop_count);
    }

    public function test_route_index_redirects_to_delivery_index(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.deliveryRoutes.index'));
        $response->assertRedirect(route('delivery.index', ['tab' => 'routes']));
    }

    // ── Route auto-generates access token ─────────────────────────

    public function test_route_auto_generates_access_token(): void
    {
        $route = DeliveryRoute::create(['name' => 'Token Test']);
        $this->assertNotNull($route->access_token);
        $this->assertEquals(32, strlen($route->access_token));
    }

    // ── Driver View (public, token-secured) ───────────────────────

    public function test_driver_view_loads_with_valid_token(): void
    {
        $route = DeliveryRoute::create(['name' => 'Driver Route']);
        $f1 = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $f1->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->get(route('delivery.driverView', $route->access_token));
        $response->assertOk();
        $response->assertSee('Driver Route');
    }

    public function test_driver_view_404s_with_invalid_token(): void
    {
        $response = $this->get(route('delivery.driverView', 'nonexistent-token'));
        $response->assertNotFound();
    }

    public function test_complete_stop_marks_family_delivered(): void
    {
        $route = DeliveryRoute::create(['name' => 'Complete Test']);
        $family = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $family->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->postJson(
            route('delivery.completeStop', [$route->access_token, $family]),
        );
        $response->assertOk();
        $response->assertJson(['ok' => true, 'status' => 'delivered']);

        $this->assertEquals(DeliveryStatus::Delivered, $family->fresh()->delivery_status);
        $this->assertDatabaseHas('delivery_logs', [
            'family_id' => $family->id,
            'status' => 'delivered',
        ]);
    }

    public function test_complete_stop_rejects_family_from_other_route(): void
    {
        $route1 = DeliveryRoute::create(['name' => 'Route 1']);
        $route2 = DeliveryRoute::create(['name' => 'Route 2']);
        $family = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $family->update(['delivery_route_id' => $route2->id, 'route_order' => 1]);

        $response = $this->postJson(
            route('delivery.completeStop', [$route1->access_token, $family]),
        );
        $response->assertForbidden();
    }

    public function test_mark_heading_sets_in_transit(): void
    {
        $route = DeliveryRoute::create(['name' => 'Heading Test']);
        $family = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $family->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->postJson(
            route('delivery.markHeading', [$route->access_token, $family]),
        );
        $response->assertOk();
        $response->assertJson(['ok' => true, 'status' => 'in_transit']);
        $this->assertEquals(DeliveryStatus::InTransit, $family->fresh()->delivery_status);
    }

    public function test_mark_returning_sets_returning_at(): void
    {
        $route = DeliveryRoute::create(['name' => 'Return Test']);

        $response = $this->postJson(
            route('delivery.markReturning', $route->access_token),
        );
        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotNull($route->fresh()->returning_at);
    }

    public function test_update_driver_location_stores_coordinates(): void
    {
        $route = DeliveryRoute::create(['name' => 'Location Test']);

        $response = $this->postJson(
            route('delivery.updateDriverLocation', $route->access_token),
            ['latitude' => 47.85, 'longitude' => -121.97]
        );
        $response->assertOk();
        $route->refresh();
        $this->assertEquals(47.85, (float) $route->driver_lat);
        $this->assertEquals(-121.97, (float) $route->driver_lng);
    }

    public function test_update_driver_location_validates_coordinates(): void
    {
        $route = DeliveryRoute::create(['name' => 'Validate Test']);

        $response = $this->postJson(
            route('delivery.updateDriverLocation', $route->access_token),
            ['latitude' => 999, 'longitude' => -121.97]
        );
        $response->assertUnprocessable();
    }

    public function test_route_data_returns_json(): void
    {
        $route = DeliveryRoute::create(['name' => 'Data Test', 'start_lat' => 47.85, 'start_lng' => -121.97]);
        $family = $this->createFamilyWithCoords(1, 47.86, -121.98);
        $family->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->getJson(route('delivery.routeData', $route->access_token));
        $response->assertOk();
        $response->assertJsonStructure([
            'route' => ['name', 'distance', 'duration', 'stop_count', 'start_lat', 'start_lng', 'polyline'],
            'stops' => [['id', 'number', 'name', 'address', 'lat', 'lng', 'order', 'status', 'nav_url']],
        ]);
    }

    // ── Optimize requires ORS key ──────────────────────────────────

    public function test_optimize_fails_without_ors_key(): void
    {
        $route = DeliveryRoute::create(['name' => 'No Key']);
        $family = $this->createFamilyWithCoords(1, 47.85, -121.97);
        $family->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.optimize'), [
            'route_ids' => [$route->id],
            'start_lat' => 47.85,
            'start_lng' => -121.97,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ── Recalculate endpoint ──────────────────────────────────────

    public function test_recalculate_returns_json(): void
    {
        $route = DeliveryRoute::create(['name' => 'Recalc Test', 'start_lat' => 47.85, 'start_lng' => -121.97]);
        $family = $this->createFamilyWithCoords(1, 47.86, -121.98);
        $family->update(['delivery_route_id' => $route->id, 'route_order' => 1]);

        $response = $this->actingAs($this->santa)->postJson(
            route('santa.deliveryRoutes.recalculate', $route)
        );
        $response->assertOk();
        $response->assertJsonStructure(['ok', 'ors', 'message', 'distance', 'duration']);
    }

    // ── Permission checks ─────────────────────────────────────────

    public function test_family_user_cannot_manage_routes(): void
    {
        $response = $this->actingAs($this->familyUser)->post(route('santa.deliveryRoutes.store'), [
            'name' => 'Not Allowed',
        ]);
        $response->assertForbidden();
    }
}
