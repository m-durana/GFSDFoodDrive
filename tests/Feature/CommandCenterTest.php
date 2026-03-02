<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\DeliveryLog;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandCenterTest extends TestCase
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

    public function test_command_center_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.commandCenter'));
        $response->assertOk();
        $response->assertSee('Command Center');
    }

    public function test_command_center_accepts_mode_parameter(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.commandCenter', ['mode' => 'delivery']));
        $response->assertOk();
    }

    public function test_command_center_data_returns_json(): void
    {
        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $response->assertOk();
        $response->assertJsonStructure([
            'overview' => ['total_families', 'total_children', 'total_members'],
            'shopping' => ['ninjas', 'total_items', 'checked_items', 'pct'],
            'delivery' => ['needs_delivery', 'pending', 'in_transit', 'delivered', 'pct', 'routes'],
            'gifts' => ['total', 'level_0', 'level_1', 'level_2', 'level_3', 'adopted', 'pct_covered'],
            'recent_activity',
            'delivery_map' => ['routes', 'families', 'drivers'],
            'timestamp',
        ]);
    }

    public function test_command_center_data_counts_families(): void
    {
        Family::create([
            'family_name' => 'Test', 'family_number' => 1,
            'number_of_family_members' => 4, 'number_of_adults' => 2,
            'number_of_children' => 2, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $data = $response->json();

        $this->assertEquals(1, $data['overview']['total_families']);
        $this->assertEquals(4, $data['overview']['total_members']);
    }

    public function test_command_center_data_tracks_delivery_progress(): void
    {
        $family = Family::create([
            'family_name' => 'Delivered', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
            'delivery_preference' => 'Delivery',
            'delivery_status' => DeliveryStatus::Delivered,
        ]);

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $data = $response->json();

        $this->assertEquals(1, $data['delivery']['delivered']);
    }

    public function test_command_center_data_includes_recent_activity(): void
    {
        $family = Family::create([
            'family_name' => 'Activity Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => $this->santa->id,
            'status' => 'delivered',
            'notes' => 'Test activity',
        ]);

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $data = $response->json();

        $this->assertNotEmpty($data['recent_activity']);
        $this->assertEquals('Delivered', $data['recent_activity'][0]['status']);
    }

    public function test_command_center_data_includes_route_info(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Test Route',
            'start_lat' => 47.85,
            'start_lng' => -121.97,
            'stop_count' => 2,
        ]);

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $data = $response->json();

        $this->assertNotEmpty($data['delivery']['routes']);
        $this->assertEquals('Test Route', $data['delivery']['routes'][0]['name']);
    }

    public function test_command_center_data_tracks_gift_stats(): void
    {
        $family = Family::create([
            'family_name' => 'Gift Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);

        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female', 'age' => 8,
            'gift_level' => GiftLevel::Full,
        ]);

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));
        $data = $response->json();

        $this->assertEquals(1, $data['gifts']['total']);
        $this->assertEquals(1, $data['gifts']['level_3']);
        $this->assertEquals(100, $data['gifts']['pct_covered']);
    }

    public function test_family_user_cannot_access_command_center(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('santa.commandCenter'));
        $response->assertForbidden();
    }
}
