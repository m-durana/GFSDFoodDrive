<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for BUGS.md P0 Authorization hole (S-100/S-98):
 * Map-data and location-update endpoints used to require permission:santa,
 * locking out Coordinator field-leads from sharing/seeing location even though
 * C-06 shows them the map. Middleware now permits coordinator+santa on read+share,
 * while Santa-only mutation endpoints (quick-assign, bulk-assign, etc.) remain gated.
 */
class DeliveryMapRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(int $permission, string $username): User
    {
        return User::create([
            'username' => $username,
            'first_name' => 'T',
            'last_name' => 'U',
            'password' => 'password123',
            'permission' => $permission,
        ]);
    }

    public function test_coordinator_can_load_map_page(): void
    {
        $coord = $this->user(8, 'map_coord');

        $this->actingAs($coord)->get('/delivery-day/map')->assertOk();
    }

    public function test_coordinator_can_read_map_data(): void
    {
        $coord = $this->user(8, 'map_coord2');

        $this->actingAs($coord)
            ->get('/delivery-day/map-data')
            ->assertOk()
            ->assertJsonStructure([]);
    }

    public function test_coordinator_can_share_location(): void
    {
        $coord = $this->user(8, 'map_coord3');

        $this->actingAs($coord)
            ->post('/delivery-day/location', ['latitude' => 47.85, 'longitude' => -121.97])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    public function test_advisor_still_blocked_from_map(): void
    {
        $advisor = $this->user(7, 'map_advisor');

        $this->actingAs($advisor)->get('/delivery-day/map')->assertForbidden();
        $this->actingAs($advisor)->get('/delivery-day/map-data')->assertForbidden();
        $this->actingAs($advisor)
            ->post('/delivery-day/location', ['latitude' => 47.85, 'longitude' => -121.97])
            ->assertForbidden();
    }

    public function test_coordinator_still_blocked_from_santa_only_mutations(): void
    {
        $coord = $this->user(8, 'map_coord4');

        // bulk-assign-team remains santa-only
        $this->actingAs($coord)
            ->post('/delivery-day/bulk-assign-team', [])
            ->assertForbidden();
    }

    public function test_santa_can_still_access_map(): void
    {
        $santa = $this->user(9, 'map_santa');

        $this->actingAs($santa)->get('/delivery-day/map')->assertOk();
        $this->actingAs($santa)->get('/delivery-day/map-data')->assertOk();
    }
}
