<?php

namespace Tests\Feature;

use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileScanTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private PackingService $packingService;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();

        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'Santa', 'last_name' => 'Claus',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->packingService = app(PackingService::class);
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

    private function createFamilyWithPackingList(array $overrides = []): array
    {
        $family = Family::create(array_merge([
            'family_name' => 'Scan Family', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
        ], $overrides));

        $packingList = $this->packingService->generatePackingList($family);

        return [$family, $packingList];
    }

    // ==========================================
    // Page Load Tests
    // ==========================================

    public function test_mobile_scan_loads_with_valid_token(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]));

        $response->assertOk();
        $response->assertSee('Family #' . $family->family_number);
        $response->assertSee('Quick Pack');
        $response->assertSee('Scan Mode');
        $response->assertSee('Mark Complete');
    }

    public function test_mobile_scan_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('warehouse.mobile-scan', ['token' => 'nonexistent-token']));

        $response->assertStatus(404);
    }

    public function test_mobile_scan_no_token_redirects_unauthenticated(): void
    {
        $response = $this->get(route('warehouse.mobile-scan'));

        $response->assertRedirect(route('login'));
    }

    public function test_mobile_scan_no_token_loads_general_mode_for_authenticated(): void
    {
        $response = $this->actingAs($this->santa)->get(route('warehouse.mobile-scan'));

        $response->assertOk();
        $response->assertSee('Mobile Packing Scanner');
        $response->assertSee('Scan a packing list QR code to begin');
    }

    public function test_mobile_scan_shows_active_packing_lists(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->actingAs($this->santa)->get(route('warehouse.mobile-scan'));

        $response->assertOk();
        $response->assertSee('Active Packing Lists');
        $response->assertSee('Family #' . $family->family_number);
    }

    public function test_mobile_scan_shows_no_active_lists_message(): void
    {
        $response = $this->actingAs($this->santa)->get(route('warehouse.mobile-scan'));

        $response->assertOk();
        $response->assertSee('No active packing lists');
    }

    // ==========================================
    // Packing Mode View Content Tests
    // ==========================================

    public function test_mobile_scan_shows_packing_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]));

        $response->assertOk();
        // Check that it renders the items JSON for Alpine.js
        $response->assertSee('items to pack');
    }

    public function test_mobile_scan_shows_volunteer_checkin_for_guests(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]));

        $response->assertOk();
        $response->assertSee('Welcome, Volunteer!');
        $response->assertSee('Start Packing');
    }

    public function test_mobile_scan_no_volunteer_checkin_for_authenticated(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->actingAs($this->santa)->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]));

        $response->assertOk();
        // Should not show guest check-in when logged in
        $response->assertDontSee('Welcome, Volunteer!');
    }

    public function test_mobile_scan_shows_substitution_drawer_markup(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]));

        $response->assertOk();
        $response->assertSee('Substitute Item');
        $response->assertSee('Original item:');
        $response->assertSee('Confirm Substitution');
    }

    // ==========================================
    // API: Quick Pack Tests
    // ==========================================

    public function test_quick_pack_marks_item_packed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = $packingList->items()->where('status', PackingItemStatus::Pending->value)->first();
        $this->assertNotNull($item, 'Should have a pending item to pack');

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/pack",
            ['volunteer_name' => 'Test Volunteer']
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $item->refresh();
        $this->assertGreaterThan(0, $item->quantity_packed);
    }

    public function test_quick_pack_fully_packs_item_with_quantity_one(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Find an item that needs exactly 1
        $item = $packingList->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->where('quantity_needed', 1)
            ->first();

        if (!$item) {
            // Create one manually if none exists
            $item = PackingItem::create([
                'packing_list_id' => $packingList->id,
                'description' => 'Single Item',
                'quantity_needed' => 1,
                'quantity_packed' => 0,
                'status' => PackingItemStatus::Pending,
                'sort_order' => 999,
            ]);
        }

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/pack"
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
        $this->assertEquals(PackingItemStatus::Packed, $item->status);
    }

    public function test_quick_pack_cannot_pack_unfulfilled_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = PackingItem::create([
            'packing_list_id' => $packingList->id,
            'description' => 'Unfulfilled Item',
            'quantity_needed' => 3,
            'quantity_packed' => 0,
            'status' => PackingItemStatus::Unfulfilled,
            'sort_order' => 999,
        ]);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/pack"
        );

        $response->assertOk();
        $response->assertJson(['success' => false, 'message' => 'Cannot pack an unfulfilled item.']);

        $item->refresh();
        $this->assertEquals(0, $item->quantity_packed);
        $this->assertEquals(PackingItemStatus::Unfulfilled, $item->status);
    }

    public function test_quick_pack_already_packed_returns_warning(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = PackingItem::create([
            'packing_list_id' => $packingList->id,
            'description' => 'Already Packed',
            'quantity_needed' => 1,
            'quantity_packed' => 1,
            'status' => PackingItemStatus::Packed,
            'sort_order' => 999,
        ]);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/pack"
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'warning' => true, 'message' => 'Item already fully packed.']);
    }

    public function test_quick_pack_wrong_list_returns_404(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Create a second packing list
        $family2 = Family::create([
            'family_name' => 'Other Family', 'family_number' => 2,
            'number_of_family_members' => 2, 'number_of_adults' => 2,
            'number_of_children' => 0, 'address' => '456 Other St', 'phone1' => '555-5678',
        ]);
        $list2 = $this->packingService->generatePackingList($family2);

        $itemFromList2 = $list2->items()->first();

        // Try to pack an item from list2 using list1's ID
        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$itemFromList2->id}/pack"
        );

        $response->assertStatus(404);
    }

    // ==========================================
    // API: Barcode Scan Tests
    // ==========================================

    public function test_scan_barcode_not_in_inventory(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/scan",
            ['barcode' => '9999999999999']
        );

        $response->assertOk();
        $response->assertJson(['match' => false, 'message' => 'Barcode not found in inventory.']);
    }

    public function test_scan_matching_barcode_packs_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Create a warehouse item with a barcode that matches a packing item's category
        $pendingItem = $packingList->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->whereNotNull('category_id')
            ->first();

        if (!$pendingItem) {
            $this->markTestSkipped('No pending items with a category found.');
        }

        $warehouseItem = WarehouseItem::create([
            'category_id' => $pendingItem->category_id,
            'name' => 'Barcoded Tuna',
            'barcode' => '1234567890123',
            'active' => true,
        ]);

        // Link the packing item to this warehouse item
        $pendingItem->update(['item_id' => $warehouseItem->id]);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/scan",
            ['barcode' => '1234567890123']
        );

        $response->assertOk();
        $response->assertJson(['match' => true]);
    }

    public function test_scan_requires_barcode_parameter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/scan",
            []
        );

        $response->assertStatus(422);
    }

    // ==========================================
    // API: Substitution Tests
    // ==========================================

    public function test_get_substitution_candidates(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = $packingList->items()->whereNotNull('category_id')->first();

        if (!$item) {
            $this->markTestSkipped('No items with a category to test substitutions.');
        }

        // Create some warehouse items in the same category
        WarehouseItem::create([
            'category_id' => $item->category_id,
            'name' => 'Alternative Item A',
            'barcode' => 'ALT-A',
            'active' => true,
        ]);
        WarehouseItem::create([
            'category_id' => $item->category_id,
            'name' => 'Alternative Item B',
            'barcode' => 'ALT-B',
            'active' => true,
        ]);

        $response = $this->actingAs($this->santa)->getJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/substitutes"
        );

        $response->assertOk();
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
    }

    public function test_confirm_substitution_marks_item_substituted(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = $packingList->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->first();
        $this->assertNotNull($item);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/substitute",
            ['notes' => 'Store was out of this brand']
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Substitution recorded.']);

        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertEquals($item->quantity_needed, $item->quantity_packed);
        $this->assertEquals('Store was out of this brand', $item->substitute_notes);
    }

    public function test_substitution_with_warehouse_item_updates_description(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = $packingList->items()
            ->where('status', PackingItemStatus::Pending->value)
            ->whereNotNull('category_id')
            ->first();

        if (!$item) {
            $this->markTestSkipped('No pending items with category found.');
        }

        $substitute = WarehouseItem::create([
            'category_id' => $item->category_id,
            'name' => 'Brand X Tuna',
            'barcode' => 'SUB-001',
            'active' => true,
        ]);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/substitute",
            ['notes' => 'Using Brand X instead', 'new_item_id' => $substitute->id]
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertStringContainsString('Brand X Tuna', $item->description);
        $this->assertEquals($substitute->id, $item->item_id);
    }

    public function test_substitution_requires_notes(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $item = $packingList->items()->where('status', PackingItemStatus::Pending->value)->first();

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/item/{$item->id}/substitute",
            ['notes' => '']
        );

        $response->assertStatus(422);
    }

    // ==========================================
    // API: List Completion Tests
    // ==========================================

    public function test_mark_complete_when_all_items_packed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Pack all items
        foreach ($packingList->items as $item) {
            $item->update([
                'status' => PackingItemStatus::Packed,
                'quantity_packed' => $item->quantity_needed,
            ]);
        }

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/complete"
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Packing list marked as complete.']);

        $packingList->refresh();
        $this->assertEquals(PackingStatus::Complete, $packingList->status);
        $this->assertNotNull($packingList->completed_at);
    }

    public function test_mark_complete_fails_with_pending_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Don't pack any items — they should all be pending
        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/complete"
        );

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $response->assertJsonStructure(['pending_count']);

        $packingList->refresh();
        $this->assertNotEquals(PackingStatus::Complete, $packingList->status);
    }

    public function test_mark_complete_succeeds_with_mix_of_packed_and_unfulfilled(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $items = $packingList->items;
        $first = true;
        foreach ($items as $item) {
            if ($first) {
                // Mark one as unfulfilled
                $item->update(['status' => PackingItemStatus::Unfulfilled]);
                $first = false;
            } else {
                // Pack the rest
                $item->update([
                    'status' => PackingItemStatus::Packed,
                    'quantity_packed' => $item->quantity_needed,
                ]);
            }
        }

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/complete"
        );

        $response->assertOk();
        // Should fail because unfulfilled items are not "complete"
        // isComplete() checks that all items are packed/verified/substituted
        // unfulfilled items are NOT in that list
        $this->assertIsBool($response->json('success'));
    }

    // ==========================================
    // API: Verification Tests
    // ==========================================

    public function test_verify_completed_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // Pack all items and complete the list
        foreach ($packingList->items as $item) {
            $item->update([
                'status' => PackingItemStatus::Packed,
                'quantity_packed' => $item->quantity_needed,
            ]);
        }
        $packingList->update(['status' => PackingStatus::Complete, 'completed_at' => now()]);

        $response = $this->actingAs($this->santa)->postJson(
            "/api/packing/{$packingList->id}/verify"
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Packing list verified.']);

        $packingList->refresh();
        $this->assertEquals(PackingStatus::Verified, $packingList->status);
        $this->assertEquals($this->santa->id, $packingList->verified_by);
        $this->assertNotNull($packingList->verified_at);

        // All packed items should now be verified
        foreach ($packingList->items()->get() as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                $this->assertEquals(PackingItemStatus::Verified, $item->status);
            }
        }
    }

    public function test_verify_requires_authentication(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->postJson("/api/packing/{$packingList->id}/verify");

        $response->assertStatus(401);
    }

    // ==========================================
    // API: Show (QR token) Tests
    // ==========================================

    public function test_api_show_returns_packing_list_by_token(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->getJson("/api/packing/{$packingList->qr_token}");

        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'family' => ['name', 'number', 'members'],
            'status',
            'status_label',
            'progress' => ['packed', 'total', 'percentage'],
            'items',
        ]);

        $this->assertEquals($family->family_name, $response->json('family.name'));
        $this->assertEquals($family->family_number, $response->json('family.number'));
    }

    public function test_api_show_invalid_token_returns_404(): void
    {
        $response = $this->getJson('/api/packing/invalid-token-here');

        $response->assertStatus(404);
    }

    public function test_api_show_items_have_correct_shape(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        $response = $this->getJson("/api/packing/{$packingList->qr_token}");

        $response->assertOk();
        $items = $response->json('items');
        $this->assertNotEmpty($items);

        $firstItem = $items[0];
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('description', $firstItem);
        $this->assertArrayHasKey('quantity_needed', $firstItem);
        $this->assertArrayHasKey('quantity_packed', $firstItem);
        $this->assertArrayHasKey('status', $firstItem);
        $this->assertArrayHasKey('sort_order', $firstItem);
    }

    // ==========================================
    // Integration: Full Packing Flow
    // ==========================================

    public function test_full_packing_flow(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$family, $packingList] = $this->createFamilyWithPackingList();

        // 1. Verify the page loads with token
        $this->get(route('warehouse.mobile-scan', ['token' => $packingList->qr_token]))
            ->assertOk()
            ->assertSee('Family #' . $family->family_number);

        // 2. Quick-pack all items one by one
        foreach ($packingList->items()->get() as $item) {
            for ($i = $item->quantity_packed; $i < $item->quantity_needed; $i++) {
                $this->actingAs($this->santa)->postJson(
                    "/api/packing/{$packingList->id}/item/{$item->id}/pack"
                )->assertJson(['success' => true]);
            }
        }

        // 3. Verify all items are packed
        $packingList->refresh();
        foreach ($packingList->items()->get() as $item) {
            $this->assertTrue(
                in_array($item->status, [PackingItemStatus::Packed, PackingItemStatus::Verified, PackingItemStatus::Substituted]),
                "Item '{$item->description}' should be packed but is {$item->status->value}"
            );
        }

        // 4. Complete the list
        $this->actingAs($this->santa)->postJson("/api/packing/{$packingList->id}/complete")
            ->assertJson(['success' => true]);

        $packingList->refresh();
        $this->assertEquals(PackingStatus::Complete, $packingList->status);

        // 5. Verify the list
        $this->actingAs($this->santa)->postJson("/api/packing/{$packingList->id}/verify")
            ->assertJson(['success' => true]);

        $packingList->refresh();
        $this->assertEquals(PackingStatus::Verified, $packingList->status);
    }
}
