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
use App\Models\Setting;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingTest extends TestCase
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
            'family_name' => "Test Family {$counter}", 'family_number' => $counter,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
        ], $overrides));
    }

    private function createPackingListWithItems(Family $family): PackingList
    {
        $service = app(PackingService::class);
        return $service->generatePackingList($family);
    }

    // ==========================================
    // Phase 1 Feature Tests: Authorization
    // ==========================================

    public function test_packing_index_requires_auth(): void
    {
        $response = $this->get(route('packing.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_packing_index_forbidden_for_family_role(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('packing.index'));
        $response->assertForbidden();
    }

    public function test_packing_index_accessible_by_coordinator(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('packing.index'));
        $response->assertOk();
    }

    public function test_packing_index_accessible_by_santa(): void
    {
        $response = $this->actingAs($this->santa)->get(route('packing.index'));
        $response->assertOk();
    }

    // ==========================================
    // Phase 1 Feature Tests: Index Page
    // ==========================================

    public function test_index_displays_packing_lists(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Johnson']);
        $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.index'));
        $response->assertOk();
        $response->assertSee('Johnson');
    }

    public function test_index_filters_by_status(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['family_name' => 'Pending Family']);
        $familyB = $this->createFamily(['family_name' => 'Complete Family']);

        $this->createPackingListWithItems($familyA);
        $listB = $this->createPackingListWithItems($familyB);
        $listB->update(['status' => PackingStatus::Complete]);

        $response = $this->actingAs($this->santa)->get(route('packing.index', ['status' => 'complete']));
        $response->assertOk();
        $response->assertSee('Complete Family');
    }

    public function test_index_search_by_family_name(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['family_name' => 'Zambroni']);
        $familyB = $this->createFamily(['family_name' => 'Xylophone']);
        $this->createPackingListWithItems($familyA);
        $this->createPackingListWithItems($familyB);

        $response = $this->actingAs($this->santa)->get(route('packing.index', ['search' => 'Zambro']));
        $response->assertOk();
        $response->assertSee('Zambroni');
    }

    public function test_index_shows_summary_counts(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily();
        $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.index'));
        $response->assertOk();
        // The view shows count badges for each status
        $response->assertSee('Pending');
    }

    // ==========================================
    // Phase 1 Feature Tests: Show Page
    // ==========================================

    public function test_show_displays_packing_list_detail(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Detail Family']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertSee('Detail Family');
        $response->assertSee('Tuna');
        $response->assertSee('Rice');
    }

    public function test_show_displays_gift_items_with_child_info(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female', 'age' => '7',
            'gift_level' => GiftLevel::None,
            'gift_preferences' => 'Art supplies',
        ]);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertSee('Female');
        $response->assertSee('Art supplies');
    }

    public function test_show_displays_baby_items(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['needs_baby_supplies' => true]);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertSee('Diapers');
    }

    // ==========================================
    // Phase 1 Feature Tests: Generate
    // ==========================================

    public function test_generate_creates_packing_lists_for_all_families(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $this->createFamily(['family_name' => 'Gen A']);
        $this->createFamily(['family_name' => 'Gen B']);

        $response = $this->actingAs($this->santa)->post(route('packing.generate'));
        $response->assertRedirect(route('packing.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(2, PackingList::count());
    }

    public function test_generate_single_creates_list_for_one_family(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $response = $this->actingAs($this->santa)->post(route('packing.generateSingle', $family));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, PackingList::count());
    }

    // ==========================================
    // Phase 1 Feature Tests: Print
    // ==========================================

    public function test_print_renders_printable_view(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Print Family']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.print', $list));
        $response->assertOk();
        $response->assertSee('Print Family');
        $response->assertSee('Packing List');
    }

    public function test_print_batch_renders_multiple_lists(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $familyA = $this->createFamily(['family_name' => 'Batch A']);
        $familyB = $this->createFamily(['family_name' => 'Batch B']);
        $listA = $this->createPackingListWithItems($familyA);
        $listB = $this->createPackingListWithItems($familyB);

        $response = $this->actingAs($this->santa)->post(route('packing.printBatch'), [
            'list_ids' => [$listA->id, $listB->id],
        ]);
        $response->assertOk();
        $response->assertSee('Batch A');
        $response->assertSee('Batch B');
    }

    public function test_print_batch_validates_list_ids(): void
    {
        $response = $this->actingAs($this->santa)->post(route('packing.printBatch'), []);
        $response->assertSessionHasErrors('list_ids');
    }

    // ==========================================
    // Phase 1 Feature Tests: Pack Item
    // ==========================================

    public function test_mark_item_packed_via_post(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->createPackingListWithItems($family);

        $item = $list->items()->where('description', 'Tuna')->first();

        $response = $this->actingAs($this->santa)->post(route('packing.packItem', [$list, $item]));
        $response->assertRedirect(route('packing.show', $list));
        $response->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
    }

    public function test_pack_item_rejects_mismatched_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $familyA = $this->createFamily();
        $familyB = $this->createFamily();
        $listA = $this->createPackingListWithItems($familyA);
        $listB = $this->createPackingListWithItems($familyB);

        $itemFromB = $listB->items()->first();

        $response = $this->actingAs($this->santa)->post(route('packing.packItem', [$listA, $itemFromB]));
        $response->assertNotFound();
    }

    // ==========================================
    // Phase 1 Feature Tests: Verify
    // ==========================================

    public function test_verify_packing_list_via_post(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->createPackingListWithItems($family);

        // Pack all items
        $service = app(PackingService::class);
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $service->markItemPacked($item, $this->santa);
                }
            }
        }

        $response = $this->actingAs($this->santa)->post(route('packing.verify', $list));
        $response->assertRedirect(route('packing.show', $list));
        $response->assertSessionHas('success');

        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);
    }

    public function test_verify_fails_when_list_incomplete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->post(route('packing.verify', $list));
        $response->assertRedirect(route('packing.show', $list));
        $response->assertSessionHas('error');

        $list->refresh();
        $this->assertNotEquals(PackingStatus::Verified, $list->status);
    }

    // ==========================================
    // Phase 1 Feature Tests: Refresh & Notes
    // ==========================================

    public function test_refresh_packing_list_via_post(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->post(route('packing.refresh', $list));
        $response->assertRedirect(route('packing.show', $list));
        $response->assertSessionHas('success');
    }

    public function test_update_notes_via_post(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->post(route('packing.updateNotes', $list), [
            'notes' => 'Family has nut allergy',
        ]);
        $response->assertRedirect(route('packing.show', $list));

        $list->refresh();
        $this->assertEquals('Family has nut allergy', $list->notes);
    }

    public function test_update_notes_validates_max_length(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->post(route('packing.updateNotes', $list), [
            'notes' => str_repeat('x', 1001),
        ]);
        $response->assertSessionHasErrors('notes');
    }

    // ==========================================
    // Phase 1 Feature Tests: Artisan Command
    // ==========================================

    public function test_artisan_packing_generate_command(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $this->createFamily(['family_name' => 'Artisan Family']);

        $this->artisan('packing:generate')
            ->assertExitCode(0);

        $this->assertEquals(1, PackingList::count());
    }

    public function test_artisan_packing_generate_for_single_family(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'Single']);
        $this->createFamily(['family_name' => 'Other']);

        $this->artisan('packing:generate', ['--family' => $family->id])
            ->assertExitCode(0);

        $this->assertEquals(1, PackingList::count());
    }

    // ==========================================
    // Phase 2 Tests: Mobile Scanner & API
    // ==========================================

    public function test_mobile_scan_page_loads_with_valid_qr_token(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->get("/warehouse/mobile-scan?token={$list->qr_token}");
        $response->assertOk();
        $response->assertSee('Quick Pack');
        $response->assertSee('Scan Mode');
    }

    public function test_mobile_scan_page_rejects_invalid_token(): void
    {
        $response = $this->get('/warehouse/mobile-scan?token=invalid-fake-token');
        $response->assertNotFound();
    }

    public function test_mobile_scan_without_token_redirects_to_login(): void
    {
        $response = $this->get('/warehouse/mobile-scan');
        $response->assertRedirect(route('login'));
    }

    public function test_mobile_scan_without_token_shows_info_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->santa)->get('/warehouse/mobile-scan');
        $response->assertOk();
        $response->assertSee('Mobile Packing Scanner');
    }

    public function test_api_load_packing_list_by_qr_token(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->getJson("/api/packing/{$list->qr_token}");
        $response->assertOk();
        $response->assertJsonStructure([
            'id', 'family', 'status', 'items' => [['id', 'description', 'quantity_needed', 'quantity_packed', 'status']],
        ]);
    }

    public function test_api_load_packing_list_invalid_token_returns_404(): void
    {
        $response = $this->getJson('/api/packing/nonexistent-token-123');
        $response->assertNotFound();
    }

    public function test_api_scan_unknown_barcode_returns_no_match(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->postJson("/api/packing/{$list->id}/scan", [
            'barcode' => 'UNKNOWN-99999',
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['match' => false]);
    }

    public function test_api_scan_requires_barcode_field(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->postJson("/api/packing/{$list->id}/scan", []);
        $response->assertUnprocessable();
    }

    public function test_api_quick_pack_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
    }

    public function test_api_quick_pack_respects_quantity_limit(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        // Pack once
        $this->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");
        // Pack again — should warn
        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");
        $response->assertOk();
        $response->assertJsonFragment(['warning' => true]);
    }

    public function test_api_quick_pack_rejects_unfulfilled_item(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id, 'gender' => 'Male', 'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);
        $list = $this->createPackingListWithItems($family);

        $unfulfilledItem = $list->items()->where('status', PackingItemStatus::Unfulfilled->value)->first();
        if (!$unfulfilledItem) {
            $this->markTestSkipped('No unfulfilled items generated');
        }

        $response = $this->postJson("/api/packing/{$list->id}/item/{$unfulfilledItem->id}/pack");
        $response->assertOk();
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_api_quick_pack_rejects_mismatched_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $familyA = $this->createFamily();
        $familyB = $this->createFamily();
        $listA = $this->createPackingListWithItems($familyA);
        $listB = $this->createPackingListWithItems($familyB);

        $itemFromB = $listB->items()->first();

        $response = $this->postJson("/api/packing/{$listA->id}/item/{$itemFromB->id}/pack");
        $response->assertNotFound();
    }

    public function test_api_substitute_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => 'Replaced with equivalent brand',
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
    }

    public function test_api_substitute_requires_notes(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->first();

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", []);
        $response->assertUnprocessable();
    }

    public function test_api_mark_list_complete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->createPackingListWithItems($family);

        // Pack all items first
        $service = app(PackingService::class);
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $service->markItemPacked($item, $this->santa);
                }
            }
        }

        $response = $this->postJson("/api/packing/{$list->id}/complete");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $list->refresh();
        $this->assertEquals(PackingStatus::Complete, $list->status);
    }

    public function test_api_mark_list_complete_fails_when_items_pending(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->postJson("/api/packing/{$list->id}/complete");
        $response->assertOk();
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_api_verify_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->createPackingListWithItems($family);

        $service = app(PackingService::class);
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $service->markItemPacked($item, $this->santa);
                }
            }
        }

        $response = $this->actingAs($this->santa)->postJson("/api/packing/{$list->id}/verify");
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);
    }

    public function test_api_verify_requires_auth(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->postJson("/api/packing/{$list->id}/verify");
        $response->assertUnauthorized();
    }

    public function test_api_packing_stats(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $this->createFamily();

        $response = $this->getJson('/api/packing/stats');
        $response->assertOk();
        $response->assertJsonStructure([
            'total_families', 'packed', 'verified', 'in_progress', 'not_started', 'fulfillment_rate',
        ]);
    }

    // ==========================================
    // Phase 3 Tests: Dashboard & Coordinator Visibility
    // ==========================================

    public function test_stats_api_returns_enhanced_fields(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $this->createPackingListWithItems($family);

        $response = $this->getJson('/api/packing/stats');
        $response->assertOk();
        $response->assertJsonStructure([
            'total_families', 'packed', 'verified', 'in_progress', 'not_started', 'fulfillment_rate',
            'categories' => ['food' => ['total', 'packed'], 'gift' => ['total', 'packed'], 'baby' => ['total', 'packed']],
            'volunteers',
            'total_items_packed_today',
            'overall_items_per_hour',
            'recently_completed',
        ]);
    }

    public function test_dashboard_loads_for_santa(): void
    {
        $response = $this->actingAs($this->santa)->get(route('packing.dashboard'));
        $response->assertOk();
        $response->assertSee('Packing Dashboard');
    }

    public function test_dashboard_loads_for_coordinator(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('packing.dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_denied_for_family_user(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('packing.dashboard'));
        $response->assertForbidden();
    }

    public function test_family_index_shows_packing_badge_for_santa(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'BadgeFamily']);
        $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('family.index'));
        $response->assertOk();
        $response->assertSee('Packing');
    }

    public function test_family_show_shows_packing_card(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'ShowPacking']);
        $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('family.show', $family));
        $response->assertOk();
        $response->assertSee('Packing Status');
    }

    public function test_family_index_hides_packing_for_family_user(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'HiddenPacking', 'user_id' => $this->familyUser->id]);
        $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->familyUser)->get(route('family.index'));
        $response->assertOk();
        // The packing column header should not appear for family users
        $response->assertDontSee('Packing</th>', false);
    }

    public function test_navigation_shows_packing_badge_when_complete_lists_exist(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $list->update(['status' => \App\Enums\PackingStatus::Complete]);

        $response = $this->actingAs($this->santa)->get(route('packing.index'));
        $response->assertOk();
        $response->assertSee('packing-qa-badge', false);
    }

    public function test_qr_token_auth_no_login_required_for_api(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        // API call without auth — should work with valid token
        $response = $this->getJson("/api/packing/{$list->qr_token}");
        $response->assertOk();
        $response->assertJsonFragment(['id' => $list->id]);
    }

    // ==========================================
    // Phase 4 Feature Tests: Substitution API
    // ==========================================

    public function test_api_get_substitutes_returns_candidates(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        // Create warehouse items in same category
        $category = $item->category;
        \App\Models\WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Alt Item A',
            'barcode' => '111111111',
            'active' => true,
        ]);

        $response = $this->getJson("/api/packing/{$list->id}/item/{$item->id}/substitutes");
        $response->assertOk();
        $response->assertJsonStructure([['id', 'name', 'barcode']]);
    }

    public function test_api_substitute_with_new_item_id_sets_item_id(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $warehouseItem = \App\Models\WarehouseItem::create([
            'category_id' => $item->category_id,
            'name' => 'Substitute Brand',
            'barcode' => '333333333',
            'active' => true,
        ]);

        $response = $this->postJson("/api/packing/{$list->id}/item/{$item->id}/substitute", [
            'notes' => 'Replaced with alternative',
            'new_item_id' => $warehouseItem->id,
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $item->refresh();
        $this->assertEquals($warehouseItem->id, $item->item_id);
    }

    public function test_stats_api_returns_unfulfilled_counts(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $this->createPackingListWithItems($family);

        $response = $this->getJson('/api/packing/stats');
        $response->assertOk();
        $response->assertJsonStructure(['unfulfilled_families', 'unfulfilled_items']);
    }

    public function test_index_unfulfilled_filter_returns_correct_lists(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['family_name' => 'Unfulfilled Family']);
        $list = $this->createPackingListWithItems($family);

        // Manually create an unfulfilled item
        $list->items()->create([
            'description' => 'Missing item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Unfulfilled,
        ]);

        $response = $this->actingAs($this->santa)->get(route('packing.index', ['status' => 'unfulfilled']));
        $response->assertOk();
        $response->assertSee('Unfulfilled Family');
    }

    // ==========================================
    // Phase 5 Tests: Sessions + Toggle + Location
    // ==========================================

    public function test_clock_in_api_endpoint(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->postJson('/api/packing/sessions/clock-in');

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
        $response->assertJsonStructure(['success', 'session']);

        $this->assertDatabaseHas('packing_sessions', [
            'user_id' => $this->coordinator->id,
            'ended_at' => null,
        ]);
    }

    public function test_clock_out_api_endpoint(): void
    {
        // Clock in first
        $this->actingAs($this->coordinator)
            ->postJson('/api/packing/sessions/clock-in');

        $response = $this->actingAs($this->coordinator)
            ->postJson('/api/packing/sessions/clock-out', ['notes' => 'Done for today']);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('packing_sessions', [
            'user_id' => $this->coordinator->id,
            'ended_at' => null,
        ]);
    }

    public function test_active_session_api_endpoint(): void
    {
        // No active session
        $response = $this->actingAs($this->coordinator)
            ->getJson('/api/packing/sessions/active');
        $response->assertOk();
        $response->assertJsonFragment(['active' => false]);

        // Clock in
        $this->actingAs($this->coordinator)
            ->postJson('/api/packing/sessions/clock-in');

        $response = $this->actingAs($this->coordinator)
            ->getJson('/api/packing/sessions/active');
        $response->assertOk();
        $response->assertJsonFragment(['active' => true]);
    }

    public function test_summary_page_accessible_by_coordinator(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('packing.summary'));
        $response->assertOk();
        $response->assertSee('End-of-Day Summary');
    }

    public function test_summary_page_denied_for_family_user(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('packing.summary'));
        $response->assertForbidden();
    }

    public function test_packing_routes_return_404_when_system_disabled(): void
    {
        Setting::set('packing_system_enabled', '0');

        $response = $this->actingAs($this->santa)->get(route('packing.index'));
        $response->assertNotFound();

        $response = $this->actingAs($this->santa)->get(route('packing.dashboard'));
        $response->assertNotFound();

        $response = $this->actingAs($this->santa)->get(route('packing.summary'));
        $response->assertNotFound();
    }

    public function test_packing_nav_hidden_when_system_disabled(): void
    {
        Setting::set('packing_system_enabled', '0');

        // Visit any authenticated page to check navigation
        $response = $this->actingAs($this->santa)->get(route('family.index'));
        $response->assertOk();
        $response->assertDontSee('Packing</a>', false);
    }

    public function test_packing_api_stats_returns_disabled_flag(): void
    {
        Setting::set('packing_system_enabled', '0');

        $response = $this->getJson('/api/packing/stats');
        $response->assertOk();
        $response->assertJsonFragment(['enabled' => false]);
        // Should not contain normal stats keys
        $response->assertJsonMissing(['total_families']);
    }

    public function test_warehouse_item_location_update(): void
    {
        $this->seedWarehouseCategories();
        $category = \App\Models\WarehouseCategory::first();

        $item = \App\Models\WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Test Item',
            'barcode' => '999999',
            'active' => true,
        ]);

        $response = $this->actingAs($this->coordinator)->put(
            route('warehouse.item.location', $item),
            [
                'location_zone' => 'A',
                'location_shelf' => '3',
                'location_bin' => '07',
            ]
        );

        $response->assertRedirect(route('warehouse.item.detail', $item));
        $response->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals('A', $item->location_zone);
        $this->assertEquals('3', $item->location_shelf);
        $this->assertEquals('07', $item->location_bin);
        $this->assertEquals('A-3-07', $item->locationLabel());
    }
}
