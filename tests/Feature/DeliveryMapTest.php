<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryMapTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_map',
            'first_name' => 'Santa',
            'last_name' => 'Map',
            'password' => 'password123',
            'permission' => 9,
        ]);
    }

    public function test_map_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/delivery-day/map');
        $response->assertStatus(200);
        $response->assertSee('Live Delivery Map');
    }

    public function test_map_data_returns_json(): void
    {
        Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Map Family',
            'family_number' => 1,
            'address' => '100 Main St',
            'phone1' => '360-555-0001',
            'latitude' => 48.0849,
            'longitude' => -121.9683,
            'number_of_family_members' => 3,
        ]);

        $response = $this->actingAs($this->santa)->get('/delivery-day/map-data');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'families' => [['id', 'number', 'name', 'lat', 'lng', 'status']],
            'volunteers',
        ]);
    }

    public function test_update_location_stores_coordinates(): void
    {
        $response = $this->actingAs($this->santa)->postJson('/delivery-day/location', [
            'latitude' => 48.0849,
            'longitude' => -121.9683,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $this->santa->refresh();
        $this->assertEquals(48.0849, (float) $this->santa->last_lat);
        $this->assertEquals(-121.9683, (float) $this->santa->last_lng);
        $this->assertNotNull($this->santa->last_location_at);
    }

    public function test_track_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/delivery-day/track');
        $response->assertStatus(200);
        $response->assertSee('Location Sharing');
    }

    public function test_map_data_includes_recent_volunteer_locations(): void
    {
        $this->santa->update([
            'last_lat' => 48.0849,
            'last_lng' => -121.9683,
            'last_location_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->get('/delivery-day/map-data');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data['volunteers']);
        $this->assertEquals('Santa Map', $data['volunteers'][0]['name']);
    }

    public function test_map_data_excludes_stale_volunteer_locations(): void
    {
        $this->santa->update([
            'last_lat' => 48.0849,
            'last_lng' => -121.9683,
            'last_location_at' => now()->subMinutes(15),
        ]);

        $response = $this->actingAs($this->santa)->get('/delivery-day/map-data');
        $data = $response->json();
        $this->assertCount(0, $data['volunteers']);
    }

    public function test_family_user_cannot_access_map(): void
    {
        $familyUser = User::create([
            'username' => 'family_map',
            'first_name' => 'Family',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->actingAs($familyUser)->get('/delivery-day/map')->assertStatus(403);
    }
}
