<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REL-03: Coordinator per-section scoping.
 * Santa + System Coordinator implicitly pass. Regular coordinators must hold
 * a position that matches one of the allowed section slugs.
 */
class CoordinatorSectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username, int $perm, ?string $position, ?string $role = null): User
    {
        $u = User::create([
            'username' => $username,
            'first_name' => 'F', 'last_name' => 'L',
            'password' => 'password', 'permission' => $perm,
            'position' => $position,
        ]);
        if ($role && method_exists($u, 'assignRole')) {
            $u->assignRole($role);
        }
        return $u;
    }

    public function test_sections_uses_default_map(): void
    {
        $u = $this->makeUser('u1', 8, 'Giving Tree Coordinator', 'coordinator');
        $this->assertSame(['giving-tree'], $u->sections());

        $u2 = $this->makeUser('u2', 8, 'Food Manager', 'coordinator');
        $this->assertSame(['food', 'packing'], $u2->sections());

        $u3 = $this->makeUser('u3', 8, 'Marketing Director', 'coordinator');
        $this->assertSame(['media'], $u3->sections());

        $u4 = $this->makeUser('u4', 8, null, 'coordinator');
        $this->assertSame([], $u4->sections());

        $u5 = $this->makeUser('u5', 8, 'Some Made-Up Title', 'coordinator');
        $this->assertSame([], $u5->sections());
    }

    public function test_sections_honors_setting_override(): void
    {
        \App\Models\Setting::set('coordinator_section_map', json_encode([
            'Food Manager' => ['food'], // strip packing
            'Giving Tree Coordinator' => ['giving-tree', 'food'], // grant food too
        ]));

        $food = $this->makeUser('foverride', 8, 'Food Manager', 'coordinator');
        $gt = $this->makeUser('gtoverride', 8, 'Giving Tree Coordinator', 'coordinator');

        $this->assertSame(['food'], $food->sections());
        $this->assertSame(['giving-tree', 'food'], $gt->sections());
    }

    public function test_sections_drops_unknown_slugs(): void
    {
        \App\Models\Setting::set('coordinator_section_map', json_encode([
            'Food Manager' => ['food', 'made-up-section'],
        ]));
        $u = $this->makeUser('u', 8, 'Food Manager', 'coordinator');
        $this->assertSame(['food'], $u->sections());
    }

    public function test_santa_can_access_any_section(): void
    {
        $santa = $this->makeUser('santa', 9, null, 'santa');
        $this->actingAs($santa)
            ->get(route('warehouse.gift-bank'))
            ->assertOk();
    }

    public function test_system_coordinator_can_access_any_section(): void
    {
        $sc = $this->makeUser('sc', 8, 'System Engineer', 'system_coordinator');
        $this->actingAs($sc)
            ->get(route('warehouse.gift-bank'))
            ->assertOk();
    }

    public function test_giving_tree_coordinator_can_access_gift_bank(): void
    {
        $coord = $this->makeUser('gt', 8, 'Giving Tree Coordinator', 'coordinator');
        $this->actingAs($coord)
            ->get(route('warehouse.gift-bank'))
            ->assertOk();
    }

    public function test_food_coordinator_is_blocked_from_gift_bank(): void
    {
        $coord = $this->makeUser('food', 8, 'Food Manager', 'coordinator');
        $this->actingAs($coord)
            ->get(route('warehouse.gift-bank'))
            ->assertForbidden();
    }

    public function test_food_coordinator_can_access_food_intake(): void
    {
        \App\Models\Setting::clearCache();
        $coord = $this->makeUser('food2', 8, 'Food Manager', 'coordinator');
        // Section gate should allow Food → /warehouse/receive (200 or redirect, but NOT 403).
        $status = $this->actingAs($coord)->get(route('warehouse.receive'))->getStatusCode();
        $this->assertNotSame(403, $status, 'Food Manager must not be section-blocked from /warehouse/receive');
    }

    public function test_giving_tree_coordinator_blocked_from_food_kiosk(): void
    {
        $coord = $this->makeUser('gt2', 8, 'Giving Tree Coordinator', 'coordinator');
        $this->actingAs($coord)
            ->get(route('warehouse.kiosk'))
            ->assertForbidden();
    }

    public function test_food_coordinator_can_access_packing_via_default_map(): void
    {
        \App\Models\Setting::clearCache();
        \App\Models\Setting::forget('coordinator_section_map'); // ensure default map applies
        \App\Models\Setting::set('packing_system_enabled', '1');

        $coord = $this->makeUser('food3', 8, 'Food Manager', 'coordinator');
        $status = $this->actingAs($coord)->get(route('packing.index'))->getStatusCode();
        $this->assertNotSame(403, $status, 'Food Manager must reach packing per default map');
    }

    public function test_coordinator_without_position_is_blocked(): void
    {
        $coord = $this->makeUser('np', 8, null, 'coordinator');
        $this->actingAs($coord)
            ->get(route('warehouse.gift-bank'))
            ->assertForbidden();
    }
}
