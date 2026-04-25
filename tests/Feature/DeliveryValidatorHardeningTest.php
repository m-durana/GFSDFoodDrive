<?php

namespace Tests\Feature;

use App\Models\DeliveryRoute;
use App\Models\DeliveryTeam;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for BUGS.md P0 validator-tightening fixes:
 *   S-40/S-41: driver_user_id must resolve to a user with permission >= 8.
 *   S-46/S-93/S-94/S-101: exists:families,id / delivery_teams,id / delivery_routes,id
 *                         now enforce current-season scope, blocking stale-tab IDs
 *                         that would otherwise leak across seasons.
 */
class DeliveryValidatorHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private int $seasonYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seasonYear = (int) Setting::get('season_year', date('Y'));
        $this->santa = User::create([
            'username' => 'santa_v', 'first_name' => 'S', 'last_name' => 'V',
            'password' => 'password123', 'permission' => 9,
        ]);
    }

    private function familyFor(int $year, int $number = 1): Family
    {
        return Family::create([
            'family_name' => "Fam {$year}-{$number}",
            'family_number' => $number,
            'season_year' => $year,
            'number_of_family_members' => 2,
            'number_of_adults' => 1,
            'number_of_children' => 1,
            'address' => "{$number} Main St",
            'phone1' => '555-0100',
            'latitude' => 47.85,
            'longitude' => -121.97,
            'delivery_preference' => 'Delivery',
        ]);
    }

    private function teamFor(int $year, string $name = 'Red'): DeliveryTeam
    {
        return DeliveryTeam::create([
            'name' => "{$name}-{$year}",
            'color' => '#dc2626',
            'season_year' => $year,
        ]);
    }

    private function routeFor(int $year, string $name = 'Route'): DeliveryRoute
    {
        return DeliveryRoute::create([
            'name' => "{$name}-{$year}",
            'season_year' => $year,
        ]);
    }

    // ── S-40/S-41: driver_user_id must be permission >= 8 ──────────────────

    public function test_route_store_rejects_non_privileged_driver_user(): void
    {
        $household = User::create([
            'username' => 'fam_household', 'first_name' => 'H', 'last_name' => 'H',
            'password' => 'password123', 'permission' => 7, // Advisor/household
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.store'), [
            'name' => 'Escalation Attempt',
            'driver_user_id' => $household->id,
        ]);

        $response->assertSessionHasErrors('driver_user_id');
        $this->assertDatabaseMissing('delivery_routes', ['name' => 'Escalation Attempt']);
    }

    public function test_route_store_accepts_coordinator_as_driver(): void
    {
        $coord = User::create([
            'username' => 'coord_d', 'first_name' => 'C', 'last_name' => 'D',
            'password' => 'password123', 'permission' => 8,
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.store'), [
            'name' => 'Valid Route',
            'driver_user_id' => $coord->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_routes', ['name' => 'Valid Route', 'driver_user_id' => $coord->id]);
    }

    public function test_team_store_rejects_non_privileged_driver_user(): void
    {
        $household = User::create([
            'username' => 'fam_t', 'first_name' => 'H', 'last_name' => 'T',
            'password' => 'password123', 'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryTeams.store'), [
            'name' => 'Team X',
            'driver_user_id' => $household->id,
        ]);

        $response->assertSessionHasErrors('driver_user_id');
        $this->assertDatabaseMissing('delivery_teams', ['name' => 'Team X']);
    }

    public function test_quick_assign_rejects_non_privileged_driver_user(): void
    {
        $household = User::create([
            'username' => 'fam_q', 'first_name' => 'H', 'last_name' => 'Q',
            'password' => 'password123', 'permission' => 7,
        ]);
        $this->familyFor($this->seasonYear, 1);

        $response = $this->actingAs($this->santa)->postJson(route('delivery.quickAssign'), [
            'driver_name' => 'Somebody',
            'driver_user_id' => $household->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('driver_user_id');
    }

    // ── S-46: exists:delivery_routes,id scoped to current season ───────────

    public function test_optimize_rejects_cross_season_route_id(): void
    {
        $stale = $this->routeFor($this->seasonYear - 1, 'StaleRoute');

        $response = $this->actingAs($this->santa)->post(route('santa.deliveryRoutes.optimize'), [
            'route_ids' => [$stale->id],
            'start_lat' => 47.85,
            'start_lng' => -121.97,
        ]);

        $response->assertSessionHasErrors('route_ids.0');
    }

    // ── S-93/S-94: exists:delivery_teams,id scoped to current season ───────

    public function test_bulk_assign_team_rejects_cross_season_team(): void
    {
        $family = $this->familyFor($this->seasonYear, 2);
        $staleTeam = $this->teamFor($this->seasonYear - 1, 'Old');

        $response = $this->actingAs($this->santa)->postJson(route('delivery.bulkAssignTeam'), [
            'family_ids' => [$family->id],
            'delivery_team_id' => $staleTeam->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('delivery_team_id');
    }

    public function test_bulk_assign_team_rejects_cross_season_family(): void
    {
        $staleFamily = $this->familyFor($this->seasonYear - 1, 3);

        $response = $this->actingAs($this->santa)->postJson(route('delivery.bulkAssignTeam'), [
            'family_ids' => [$staleFamily->id],
            'delivery_team_id' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('family_ids.0');
    }

    public function test_bulk_assign_team_accepts_current_season_ids(): void
    {
        $family = $this->familyFor($this->seasonYear, 4);
        $team = $this->teamFor($this->seasonYear, 'Blue');

        $response = $this->actingAs($this->santa)->postJson(route('delivery.bulkAssignTeam'), [
            'family_ids' => [$family->id],
            'delivery_team_id' => $team->id,
        ]);

        $response->assertOk();
        $this->assertEquals($team->id, $family->fresh()->delivery_team_id);
    }

    // ── S-101: exists:families,id scoped in addFamiliesToRoute ─────────────

    public function test_add_families_to_route_rejects_cross_season_family(): void
    {
        $route = $this->routeFor($this->seasonYear, 'Live');
        $stale = $this->familyFor($this->seasonYear - 1, 5);

        $response = $this->actingAs($this->santa)->postJson(
            route('delivery.addFamiliesToRoute', ['deliveryRoute' => $route->id]),
            ['family_ids' => [$stale->id]]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('family_ids.0');
    }

    // ── updateFamilies on DeliveryRouteController ─────────────────────────

    public function test_route_update_families_rejects_cross_season_family(): void
    {
        $route = $this->routeFor($this->seasonYear, 'Update');
        $stale = $this->familyFor($this->seasonYear - 1, 6);

        $response = $this->actingAs($this->santa)->put(
            route('santa.deliveryRoutes.updateFamilies', ['deliveryRoute' => $route->id]),
            ['family_ids' => [$stale->id]]
        );

        $response->assertSessionHasErrors('family_ids.0');
    }

    // ── SantaController merge & dismiss duplicates ────────────────────────

    public function test_merge_families_rejects_cross_season_ids(): void
    {
        $keep = $this->familyFor($this->seasonYear, 7);
        $staleMerge = $this->familyFor($this->seasonYear - 1, 8);

        $response = $this->actingAs($this->santa)->post(route('santa.mergeFamilies'), [
            'keep_id' => $keep->id,
            'merge_id' => $staleMerge->id,
        ]);

        $response->assertSessionHasErrors('merge_id');
    }

    public function test_dismiss_duplicate_rejects_cross_season_id(): void
    {
        $current = $this->familyFor($this->seasonYear, 9);
        $stale = $this->familyFor($this->seasonYear - 1, 10);

        $response = $this->actingAs($this->santa)->post(route('santa.dismissDuplicate'), [
            'family_a_id' => $current->id,
            'family_b_id' => $stale->id,
        ]);

        $response->assertSessionHasErrors('family_b_id');
    }

    public function test_update_family_number_rejects_cross_season_family(): void
    {
        $stale = $this->familyFor($this->seasonYear - 1, 11);

        $response = $this->actingAs($this->santa)->post(route('santa.updateFamilyNumber'), [
            'family_id' => $stale->id,
            'family_number' => 42,
        ]);

        $response->assertSessionHasErrors('family_id');
    }
}
