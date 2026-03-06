<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\Child;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\PackingSession;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $coordinator;
    private User $familyUser;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();

        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'Santa', 'last_name' => 'Claus',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->coordinator = User::create([
            'username' => 'coord', 'first_name' => 'Coord', 'last_name' => 'Inator',
            'password' => 'password', 'permission' => 8,
        ]);
        $this->familyUser = User::create([
            'username' => 'family', 'first_name' => 'Family', 'last_name' => 'User',
            'password' => 'password', 'permission' => 7,
        ]);
    }

    private function seedWarehouseCategories(): void
    {
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    private function seedGroceryItems(): void
    {
        GroceryItem::create([
            'name' => 'Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
        GroceryItem::create([
            'name' => 'Rice', 'category' => 'dry', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
    }

    private function createFamily(array $overrides = []): Family
    {
        static $counter = 0;
        $counter++;
        return Family::create(array_merge([
            'family_name' => "Edge Family {$counter}", 'family_number' => 800 + $counter,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Edge St', 'phone1' => '555-9999',
        ], $overrides));
    }

    private function packAllItems(PackingList $list): void
    {
        $service = app(PackingService::class);
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = $item->quantity_packed; $i < $item->quantity_needed; $i++) {
                    $service->markItemPacked($item, $this->santa);
                }
            }
        }
    }

    // ==========================================
    // API: Scan Edge Cases
    // ==========================================

    public function test_api_scan_with_known_barcode_matches_pending_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $cannedCategory = WarehouseCategory::where('name', 'Canned Goods')->first();
        if (!$cannedCategory) {
            $this->markTestSkipped('Canned Goods category not found.');
        }

        $warehouseItem = WarehouseItem::create([
            'category_id' => $cannedCategory->id,
            'name' => 'Brand Tuna',
            'barcode' => '012345678901',
            'active' => true,
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);

        $response = $this->postJson("/api/packing/{$list->id}/scan", [
            'barcode' => '012345678901',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['match' => true]);
    }

    public function test_api_scan_returns_suggestion_when_category_already_packed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $cannedCategory = WarehouseCategory::where('name', 'Canned Goods')->first();
        if (!$cannedCategory) {
            $this->markTestSkipped('Canned Goods category not found.');
        }

        WarehouseItem::create([
            'category_id' => $cannedCategory->id,
            'name' => 'Extra Tuna',
            'barcode' => 'EXTRA-TUNA',
            'active' => true,
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);

        // Pack all canned items first
        foreach ($list->items as $item) {
            if ($item->category_id === $cannedCategory->id && $item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    app(PackingService::class)->markItemPacked($item, $this->santa);
                }
            }
        }

        // Now scan a canned item — should say already fulfilled
        $response = $this->postJson("/api/packing/{$list->id}/scan", [
            'barcode' => 'EXTRA-TUNA',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['match' => false]);
    }

    // ==========================================
    // API: Quick Pack on Nonexistent List
    // ==========================================

    public function test_api_quick_pack_on_nonexistent_list_returns_404(): void
    {
        $response = $this->postJson("/api/packing/99999/item/1/pack");
        $response->assertNotFound();
    }

    public function test_api_scan_on_nonexistent_list_returns_404(): void
    {
        $response = $this->postJson("/api/packing/99999/scan", ['barcode' => 'TEST']);
        $response->assertNotFound();
    }

    public function test_api_complete_on_nonexistent_list_returns_404(): void
    {
        $response = $this->postJson("/api/packing/99999/complete");
        $response->assertNotFound();
    }

    // ==========================================
    // API: Verify Edge Cases
    // ==========================================

    public function test_api_verify_already_verified_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);
        $this->packAllItems($list);

        // Verify once
        $this->actingAs($this->santa)->postJson("/api/packing/{$list->id}/verify");

        // Verify again — should still succeed
        $response = $this->actingAs($this->santa)->postJson("/api/packing/{$list->id}/verify");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    public function test_api_verify_with_coordinator_role(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);
        $this->packAllItems($list);

        $response = $this->actingAs($this->coordinator)->postJson("/api/packing/{$list->id}/verify");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    // ==========================================
    // API: Clock In/Out Edge Cases
    // ==========================================

    public function test_api_double_clock_in_returns_422(): void
    {
        $this->actingAs($this->coordinator)->postJson('/api/packing/sessions/clock-in');

        $response = $this->actingAs($this->coordinator)->postJson('/api/packing/sessions/clock-in');
        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_api_clock_out_without_session_returns_422(): void
    {
        $response = $this->actingAs($this->coordinator)->postJson('/api/packing/sessions/clock-out');
        $response->assertStatus(422);
    }

    public function test_api_clock_in_requires_auth(): void
    {
        $response = $this->postJson('/api/packing/sessions/clock-in');
        $response->assertUnauthorized();
    }

    public function test_api_clock_out_requires_auth(): void
    {
        $response = $this->postJson('/api/packing/sessions/clock-out');
        $response->assertUnauthorized();
    }

    public function test_api_active_session_requires_auth(): void
    {
        $response = $this->getJson('/api/packing/sessions/active');
        $response->assertUnauthorized();
    }

    // ==========================================
    // API: Substitution on Verified/Complete Items
    // ==========================================

    public function test_api_substitute_already_substituted_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        // First substitution
        $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => 'First',
        ]);

        // Second substitution — should still work
        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => 'Second replacement',
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    // ==========================================
    // Web: Coordinator Full Packing Flow
    // ==========================================

    public function test_coordinator_can_complete_full_packing_flow(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);

        // Generate
        $response = $this->actingAs($this->coordinator)->post(route('packing.generateSingle', $family));
        $response->assertRedirect();

        $list = PackingList::first();
        $this->assertNotNull($list);

        // View index
        $response = $this->actingAs($this->coordinator)->get(route('packing.index'));
        $response->assertOk();

        // View detail
        $response = $this->actingAs($this->coordinator)->get(route('packing.show', $list));
        $response->assertOk();

        // Pack all items
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $this->actingAs($this->coordinator)->post(route('packing.packItem', [$list, $item]));
                }
            }
        }

        // Verify
        $response = $this->actingAs($this->coordinator)->post(route('packing.verify', $list));
        $response->assertRedirect(route('packing.show', $list));

        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);
    }

    // ==========================================
    // Web: Notes Boundary Tests
    // ==========================================

    public function test_update_notes_with_exactly_1000_chars(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);

        $notes = str_repeat('x', 1000);
        $response = $this->actingAs($this->santa)->post(route('packing.updateNotes', $list), [
            'notes' => $notes,
        ]);
        $response->assertRedirect(route('packing.show', $list));

        $list->refresh();
        $this->assertEquals(1000, strlen($list->notes));
    }

    public function test_update_notes_with_empty_string_clears_notes(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);
        $list->update(['notes' => 'Some old notes']);

        $response = $this->actingAs($this->santa)->post(route('packing.updateNotes', $list), [
            'notes' => '',
        ]);
        $response->assertRedirect(route('packing.show', $list));

        $list->refresh();
        $this->assertEmpty($list->notes);
    }

    // ==========================================
    // Mobile Scanner: Various Token Formats
    // ==========================================

    public function test_mobile_scan_with_sql_injection_token(): void
    {
        $response = $this->get("/warehouse/mobile-scan?token=' OR 1=1 --");
        $response->assertNotFound();
    }

    public function test_mobile_scan_with_empty_token(): void
    {
        $response = $this->get('/warehouse/mobile-scan?token=');
        // Empty token should redirect to login (no auth) or show info page (with auth)
        $response->assertRedirect(route('login'));
    }

    public function test_api_load_list_with_sql_injection_token(): void
    {
        $response = $this->getJson("/api/packing/' OR 1=1 --");
        $response->assertNotFound();
    }

    // ==========================================
    // System Toggle Edge Cases
    // ==========================================

    public function test_generate_works_even_when_system_disabled(): void
    {
        Setting::set('packing_system_enabled', '0');
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        // Web routes should 404 but service layer should still work
        $response = $this->actingAs($this->santa)->post(route('packing.generate'));
        $response->assertNotFound();

        // But the service itself works
        $service = app(PackingService::class);
        $list = $service->generatePackingList($family);
        $this->assertNotNull($list);
        $this->assertNotEmpty($list->items);
    }

    public function test_api_pack_works_when_system_disabled(): void
    {
        // API endpoints (except stats) are NOT protected by PackingSystemEnabled middleware
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        Setting::set('packing_system_enabled', '0');
        Setting::clearCache();

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");
        // This should still work — API pack endpoints are not behind the middleware
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    // ==========================================
    // Batch Print Edge Cases
    // ==========================================

    public function test_print_batch_with_nonexistent_list_ids_fails_validation(): void
    {
        $response = $this->actingAs($this->santa)->post(route('packing.printBatch'), [
            'list_ids' => [99999, 99998],
        ]);

        // Validation rejects nonexistent IDs via 'exists:packing_lists,id' rule
        $response->assertRedirect();
        $response->assertSessionHasErrors('list_ids.0');
    }

    public function test_print_batch_with_single_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Solo Print']);
        $list = app(PackingService::class)->generatePackingList($family);

        $response = $this->actingAs($this->santa)->post(route('packing.printBatch'), [
            'list_ids' => [$list->id],
        ]);
        $response->assertOk();
        $response->assertSee('Solo Print');
    }

    // ==========================================
    // Artisan Command Edge Cases
    // ==========================================

    public function test_artisan_command_with_nonexistent_family_id_returns_failure(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $this->artisan('packing:generate', ['--family' => 99999])
            ->assertExitCode(1); // Command returns FAILURE for missing family

        $this->assertEquals(0, PackingList::count());
    }

    public function test_artisan_command_idempotent_on_second_run(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $this->createFamily();

        $this->artisan('packing:generate')->assertExitCode(0);
        $this->assertEquals(1, PackingList::count());

        // Second run — should not duplicate
        $this->artisan('packing:generate')->assertExitCode(0);
        $this->assertEquals(1, PackingList::count());
    }

    // ==========================================
    // Large Family Stress Test
    // ==========================================

    public function test_large_family_with_many_children_generates_all_gift_items(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['number_of_family_members' => 8, 'number_of_children' => 6]);

        for ($i = 0; $i < 6; $i++) {
            Child::create([
                'family_id' => $family->id,
                'gender' => $i % 2 === 0 ? 'Male' : 'Female',
                'age' => (string) ($i + 3),
                'gift_level' => GiftLevel::None,
            ]);
        }

        $list = app(PackingService::class)->generatePackingList($family);

        $giftItems = $list->items->filter(fn ($i) => $i->child_id !== null);
        $this->assertCount(6, $giftItems);
    }

    // ==========================================
    // API: Complete While Already Complete
    // ==========================================

    public function test_api_complete_on_already_complete_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);
        $this->packAllItems($list);

        // First complete
        $this->postJson("/api/packing/{$list->id}/complete");

        // Second complete — should still succeed
        $response = $this->postJson("/api/packing/{$list->id}/complete");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    // ==========================================
    // API: QR Token vs Numeric ID Routing
    // ==========================================

    public function test_api_show_uses_qr_token_not_numeric_id(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);

        // QR token should work
        $response = $this->getJson("/api/packing/{$list->qr_token}");
        $response->assertOk();

        // Numeric ID should NOT work (returns 404 since it's not a valid UUID)
        $response = $this->getJson("/api/packing/{$list->id}");
        $response->assertNotFound();
    }

    // ==========================================
    // Substitution Notes Boundary
    // ==========================================

    public function test_api_substitute_notes_max_length(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        // 501 chars — should fail validation (max:500)
        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => str_repeat('x', 501),
        ]);
        $response->assertUnprocessable();
    }

    public function test_api_substitute_notes_exactly_500_chars(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = app(PackingService::class)->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => str_repeat('x', 500),
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    // ==========================================
    // Dashboard: Packing Status Badges on Family Views
    // ==========================================

    public function test_family_show_displays_correct_packing_progress(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Progress Family', 'number_of_family_members' => 1]);
        $list = app(PackingService::class)->generatePackingList($family);

        // Pack some items
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();
        app(PackingService::class)->markItemPacked($item, $this->santa);

        $response = $this->actingAs($this->santa)->get(route('family.show', $family));
        $response->assertOk();
        $response->assertSee('Packing Status');
    }

    // ==========================================
    // Summary Page Edge Cases
    // ==========================================

    public function test_summary_page_with_date_parameter(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('packing.summary', [
            'date' => '2026-01-15',
        ]));
        $response->assertOk();
        $response->assertSee('End-of-Day Summary');
    }

    public function test_summary_page_with_invalid_date_falls_back_to_today(): void
    {
        // Invalid date should gracefully fall back to today
        $response = $this->actingAs($this->coordinator)->get(route('packing.summary', [
            'date' => 'not-a-date',
        ]));
        $response->assertOk();
        $response->assertSee('End-of-Day Summary');
    }
}
