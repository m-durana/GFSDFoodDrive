<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Enums\PackingItemStatus;
use App\Models\Child;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\PackingSession;
use App\Models\Setting;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the 2026-05-20 correction pass:
 *   - PackingService guards (Unfulfilled rejection, fully-packed short-circuit)
 *   - AdoptionController::markDelivered gift_level monotonic (G-06/W-08 unification)
 *   - StoreUserRequest / UpdateUserRequest position whitelist via Rule::in()
 *   - User helpers: isNinja(), isSystemCoordinator(), canSeePii()
 *   - SH-02 dietary restriction filter in GroceryItem::calculateForFamily
 */
class CorrectionPassRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();

        $this->santa = User::create([
            'username' => 'santa_correction',
            'first_name' => 'Santa',
            'last_name' => 'Test',
            'password' => 'pw',
            'permission' => 9,
        ]);
        if (method_exists($this->santa, 'assignRole')) {
            $this->santa->assignRole('santa');
        }
    }

    // ── PackingService guards ────────────────────────────────────────────

    public function test_packing_service_refuses_to_pack_unfulfilled_item(): void
    {
        $family = Family::create([
            'family_number' => 101, 'family_name' => 'Foo',
            'address' => '1 Way', 'number_of_family_members' => 1,
            'phone1' => '555-0001',
            'user_id' => $this->santa->id,
        ]);
        $list = PackingList::create(['family_id' => $family->id]);
        $item = PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Test item',
            'category' => 'canned',
            'quantity_needed' => 2,
            'quantity_packed' => 0,
            'status' => PackingItemStatus::Unfulfilled,
        ]);

        $service = app(PackingService::class);
        $result = $service->markItemPacked($item, $this->santa);

        $this->assertFalse($result['success']);
        $this->assertSame('Cannot pack an unfulfilled item.', $result['message']);
        $this->assertSame(0, $item->fresh()->quantity_packed);
    }

    public function test_packing_service_short_circuits_already_fully_packed(): void
    {
        $family = Family::create([
            'family_number' => 102, 'family_name' => 'Bar',
            'address' => '2 Way', 'number_of_family_members' => 1,
            'phone1' => '555-0001',
            'user_id' => $this->santa->id,
        ]);
        $list = PackingList::create(['family_id' => $family->id]);
        $item = PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Test item',
            'category' => 'canned',
            'quantity_needed' => 2,
            'quantity_packed' => 2,
            'status' => PackingItemStatus::Packed,
        ]);
        $session = PackingSession::create([
            'user_id' => $this->santa->id,
            'started_at' => now(),
            'items_packed' => 5,
        ]);

        $service = app(PackingService::class);
        $result = $service->markItemPacked($item, $this->santa);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['warning'] ?? false);
        $this->assertSame(2, $item->fresh()->quantity_packed);
        // PackingSession.items_packed must NOT have incremented from the re-click.
        $this->assertSame(5, $session->fresh()->items_packed);
    }

    // ── G-06 / W-08 gift_level monotonic ─────────────────────────────────

    public function test_adopter_markDelivered_does_not_downgrade_full_gift_level(): void
    {
        $family = Family::create([
            'family_number' => 103, 'family_name' => 'Baz',
            'address' => '3 Way', 'number_of_family_members' => 1,
            'phone1' => '555-0001',
            'user_id' => $this->santa->id,
        ]);
        $token = bin2hex(random_bytes(16));
        $child = Child::create([
            'family_id' => $family->id,
            'first_name' => 'Kid',
            'last_name' => 'Three',
            'gender' => 'F',
            'age' => 7,
            'adopter_name' => 'A',
            'adopter_email' => 'a@a.test',
            'adoption_token' => $token,
            'adopted_at' => now(),
            'gift_level' => GiftLevel::Full,
        ]);

        $this->post(route('adopt.markDelivered', $token))->assertRedirect();

        $child->refresh();
        $this->assertSame(GiftLevel::Full, $child->gift_level);
        $this->assertTrue((bool) $child->gift_dropped_off);
    }

    public function test_adopter_markDelivered_promotes_partial_to_moderate(): void
    {
        $family = Family::create([
            'family_number' => 104, 'family_name' => 'Qux',
            'address' => '4 Way', 'number_of_family_members' => 1,
            'phone1' => '555-0001',
            'user_id' => $this->santa->id,
        ]);
        $token = bin2hex(random_bytes(16));
        $child = Child::create([
            'family_id' => $family->id,
            'first_name' => 'Kid',
            'last_name' => 'Four',
            'gender' => 'M',
            'age' => 5,
            'adopter_name' => 'A',
            'adopter_email' => 'a@a.test',
            'adoption_token' => $token,
            'adopted_at' => now(),
            'gift_level' => GiftLevel::Partial,
        ]);

        $this->post(route('adopt.markDelivered', $token))->assertRedirect();

        $this->assertSame(GiftLevel::Moderate, $child->fresh()->gift_level);
    }

    // ── Position whitelist via Rule::in() ────────────────────────────────

    public function test_store_user_rejects_position_outside_settings_list(): void
    {
        Setting::set('coordinator_positions', 'Activities Coordinator,System Engineer');

        $payload = [
            'username' => 'newcoord', 'first_name' => 'X', 'last_name' => 'Y',
            'password' => 'password12', 'role' => 'coordinator',
            'school_source' => null, 'position' => 'King of Pranks',
        ];

        $this->actingAs($this->santa)
            ->post(route('santa.storeUser'), $payload)
            ->assertSessionHasErrors('position');

        $this->assertDatabaseMissing('users', ['username' => 'newcoord']);
    }

    public function test_store_user_accepts_whitelisted_position(): void
    {
        Setting::set('coordinator_positions', 'Activities Coordinator,System Engineer');

        $this->actingAs($this->santa)
            ->post(route('santa.storeUser'), [
                'username' => 'acoord', 'first_name' => 'A', 'last_name' => 'C',
                'password' => 'password12', 'role' => 'coordinator',
                'school_source' => null, 'position' => 'Activities Coordinator',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'acoord',
            'position' => 'Activities Coordinator',
        ]);
    }

    // ── User helpers ────────────────────────────────────────────────────

    public function test_canSeePii_matrix(): void
    {
        $santa = $this->santa;
        $coord = User::create(['username' => 'c1', 'first_name' => 'C', 'last_name' => 'O', 'password' => 'pw', 'permission' => 8]);
        $advisor = User::create(['username' => 'a1', 'first_name' => 'A', 'last_name' => 'D', 'password' => 'pw', 'permission' => 7]);
        $ninja = User::create(['username' => 'n1', 'first_name' => 'N', 'last_name' => 'I', 'password' => 'pw', 'permission' => 7]);
        $sysCoord = User::create(['username' => 'sc1', 'first_name' => 'S', 'last_name' => 'C', 'password' => 'pw', 'permission' => 8]);

        if (method_exists($coord, 'assignRole')) {
            $coord->assignRole('coordinator');
            $advisor->assignRole('family');
            $ninja->assignRole('ninja');
            $sysCoord->assignRole('system_coordinator');
        }

        $this->assertTrue($santa->canSeePii());
        $this->assertTrue($sysCoord->canSeePii());
        $this->assertFalse($coord->canSeePii());
        $this->assertFalse($advisor->canSeePii());
        $this->assertFalse($ninja->canSeePii());
    }

    public function test_isNinja_and_isSystemCoordinator_helpers(): void
    {
        $ninja = User::create(['username' => 'n2', 'first_name' => 'N', 'last_name' => 'J', 'password' => 'pw', 'permission' => 7]);
        $sc = User::create(['username' => 'sc2', 'first_name' => 'S', 'last_name' => 'C', 'password' => 'pw', 'permission' => 8]);

        if (method_exists($ninja, 'assignRole')) {
            $ninja->assignRole('ninja');
            $sc->assignRole('system_coordinator');
        }

        $this->assertTrue($ninja->isNinja());
        $this->assertFalse($ninja->isSystemCoordinator());
        $this->assertTrue($sc->isSystemCoordinator());
        $this->assertTrue($sc->isCoordinator()); // SC counts as coordinator too
        $this->assertFalse($sc->isNinja());
    }

    // ── SH-02 dietary filter ────────────────────────────────────────────

    public function test_calculateForFamily_skips_items_incompatible_with_dietary_restrictions(): void
    {
        $halal = Family::create([
            'family_number' => 201, 'family_name' => 'Halal',
            'address' => '9 Way', 'number_of_family_members' => 4,
            'phone1' => '555-0009',
            'user_id' => $this->santa->id,
            'dietary_restrictions' => ['halal'],
        ]);

        GroceryItem::create([
            'name' => 'Bacon',
            'category' => 'canned',
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'sort_order' => 1,
            'dietary_flags' => ['pork'],
        ]);
        GroceryItem::create([
            'name' => 'Rice',
            'category' => 'dry',
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'sort_order' => 2,
        ]);

        $list = GroceryItem::calculateForFamily($halal);

        $this->assertArrayHasKey('Rice', $list);
        $this->assertArrayNotHasKey('Bacon', $list, 'SH-02: halal family must not receive pork-flagged items.');
    }
}
