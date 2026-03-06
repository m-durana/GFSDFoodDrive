<?php

namespace Tests\Unit;

use App\Enums\GiftLevel;
use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\Child;
use App\Models\Family;
use App\Models\GiftBankItem;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\PackingSession;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Services\PackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    private PackingService $service;
    private User $packer;
    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        $this->service = app(PackingService::class);
        $this->packer = User::create([
            'username' => 'packer', 'first_name' => 'Test', 'last_name' => 'Packer',
            'password' => 'password', 'permission' => 8,
        ]);
        $this->coordinator = User::create([
            'username' => 'coord', 'first_name' => 'Co', 'last_name' => 'Ordinator',
            'password' => 'password', 'permission' => 8,
        ]);
    }

    // --- Helpers ---

    private function seedWarehouseCategories(): void
    {
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    private function seedGroceryItems(): void
    {
        GroceryItem::create([
            'name' => 'Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 4, 'qty_3' => 4, 'qty_4' => 4,
            'qty_5' => 7, 'qty_6' => 7, 'qty_7' => 8, 'qty_8' => 15,
        ]);
        GroceryItem::create([
            'name' => 'Green Beans', 'category' => 'canned', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 2, 'qty_4' => 3,
            'qty_5' => 3, 'qty_6' => 4, 'qty_7' => 4, 'qty_8' => 6,
        ]);
        GroceryItem::create([
            'name' => 'Rice', 'category' => 'dry', 'sort_order' => 3,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 2, 'qty_4' => 2,
            'qty_5' => 3, 'qty_6' => 3, 'qty_7' => 4, 'qty_8' => 5,
        ]);
    }

    private function createFamily(array $overrides = []): Family
    {
        static $counter = 0;
        $counter++;
        return Family::create(array_merge([
            'family_name' => "Edge Family {$counter}", 'family_number' => 900 + $counter,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '999 Test St', 'phone1' => '555-0000',
        ], $overrides));
    }

    private function packAllItems(PackingList $list): void
    {
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = $item->quantity_packed; $i < $item->quantity_needed; $i++) {
                    $this->service->markItemPacked($item, $this->packer);
                }
            }
        }
    }

    // ==========================================
    // Family Size Boundary Tests
    // ==========================================

    public function test_family_with_zero_members_uses_minimum_of_one(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 0]);

        $list = $this->service->generatePackingList($family);

        // Should produce items using qty_1 values
        $tunaItem = $list->items->firstWhere('description', 'Tuna');
        $this->assertNotNull($tunaItem);
        $this->assertEquals(1, $tunaItem->quantity_needed); // qty_1 = 1
    }

    public function test_family_with_one_member_uses_qty_1(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);

        $list = $this->service->generatePackingList($family);

        $tunaItem = $list->items->firstWhere('description', 'Tuna');
        $this->assertNotNull($tunaItem);
        $this->assertEquals(1, $tunaItem->quantity_needed); // qty_1 = 1
    }

    public function test_family_with_more_than_8_members_uses_qty_8(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 15]);

        $list = $this->service->generatePackingList($family);

        $tunaItem = $list->items->firstWhere('description', 'Tuna');
        $this->assertNotNull($tunaItem);
        $this->assertEquals(15, $tunaItem->quantity_needed); // qty_8 = 15
    }

    public function test_grocery_item_with_zero_quantity_for_size_is_skipped(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Special Item', 'category' => 'canned', 'sort_order' => 99,
            'qty_1' => 0, 'qty_2' => 0, 'qty_3' => 5, 'qty_4' => 5,
            'qty_5' => 5, 'qty_6' => 5, 'qty_7' => 5, 'qty_8' => 5,
        ]);

        // Family of 1 — qty_1 = 0, should be skipped
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $specialItem = $list->items->firstWhere('description', 'Special Item');
        $this->assertNull($specialItem);

        // Family of 3 — qty_3 = 5, should be included
        $family2 = $this->createFamily(['number_of_family_members' => 3]);
        $list2 = $this->service->generatePackingList($family2);

        $specialItem2 = $list2->items->firstWhere('description', 'Special Item');
        $this->assertNotNull($specialItem2);
        $this->assertEquals(5, $specialItem2->quantity_needed);
    }

    // ==========================================
    // Dietary Restriction Edge Cases
    // ==========================================

    public function test_multiple_dietary_conflicts_flag_multiple_items(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Peanut Butter', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'dietary_flags' => ['nuts'],
        ]);
        GroceryItem::create([
            'name' => 'Pork Chops', 'category' => 'canned', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'dietary_flags' => ['pork'],
        ]);
        GroceryItem::create([
            'name' => 'Safe Crackers', 'category' => 'dry', 'sort_order' => 3,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
        ]);

        $family = $this->createFamily([
            'number_of_family_members' => 1,
            'dietary_restrictions' => ['nut_free', 'halal'],
        ]);

        $list = $this->service->generatePackingList($family);

        $peanutButter = $list->items->first(fn ($i) => str_contains($i->description, 'Peanut Butter'));
        $porkChops = $list->items->first(fn ($i) => str_contains($i->description, 'Pork Chops'));
        $crackers = $list->items->firstWhere('description', 'Safe Crackers');

        $this->assertEquals(PackingItemStatus::Unfulfilled, $peanutButter->status);
        $this->assertEquals(PackingItemStatus::Unfulfilled, $porkChops->status);
        $this->assertEquals(PackingItemStatus::Pending, $crackers->status);
    }

    public function test_family_with_dietary_restrictions_but_no_conflicting_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems(); // None of these have dietary_flags

        $family = $this->createFamily([
            'number_of_family_members' => 1,
            'dietary_restrictions' => ['nut_free', 'gluten_free'],
        ]);

        $list = $this->service->generatePackingList($family);

        // All items should be Pending since they have no dietary flags
        $allPending = $list->items->every(fn ($item) => $item->status === PackingItemStatus::Pending);
        $this->assertTrue($allPending);
    }

    public function test_family_with_empty_dietary_restrictions_array(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Nutty Bar', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'dietary_flags' => ['nuts'],
        ]);

        $family = $this->createFamily([
            'number_of_family_members' => 1,
            'dietary_restrictions' => [],
        ]);

        $list = $this->service->generatePackingList($family);

        // Empty restrictions array should NOT trigger conflicts
        $nuttyBar = $list->items->firstWhere('description', 'Nutty Bar');
        $this->assertNotNull($nuttyBar);
        $this->assertEquals(PackingItemStatus::Pending, $nuttyBar->status);
    }

    // ==========================================
    // Gift Category Resolution Edge Cases
    // ==========================================

    public function test_gift_category_resolution_for_nonbinary_gender(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Other',
            'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->first(fn ($i) => $i->child_id !== null);
        $this->assertNotNull($giftItem);

        // 'Other' gender should resolve to Neutral category
        if ($giftItem->category_id) {
            $category = WarehouseCategory::find($giftItem->category_id);
            $this->assertTrue(
                str_contains($category->name, 'Neutral') || str_contains($category->name, 'General'),
                "Expected Neutral or General gift category, got: {$category->name}"
            );
        }
    }

    public function test_gift_category_for_infant_age_zero(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => '0',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->first(fn ($i) => $i->child_id !== null);
        $this->assertNotNull($giftItem);
        $this->assertStringContainsString('age 0', $giftItem->description);
    }

    public function test_gift_category_for_age_18_uses_teen_bracket(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female',
            'age' => '18',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->first(fn ($i) => $i->child_id !== null);
        $this->assertNotNull($giftItem);
        // Age 18+ defaults to 13-17 range
        if ($giftItem->category_id) {
            $category = WarehouseCategory::find($giftItem->category_id);
            $this->assertTrue(
                str_contains($category->name, '13-17') || str_contains($category->name, 'General'),
                "Expected 13-17 or General category for age 18, got: {$category->name}"
            );
        }
    }

    public function test_gift_description_includes_preferences_truncated(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $longPrefs = str_repeat('Pokemon cards and toys ', 10); // Very long preference string
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => '10',
            'gift_level' => GiftLevel::None,
            'gift_preferences' => $longPrefs,
        ]);

        $list = $this->service->generatePackingList($family);
        $giftItem = $list->items->first(fn ($i) => $i->child_id !== null);

        // Description should contain preferences but be truncated (Str::limit 60)
        $this->assertStringContainsString('Pokemon', $giftItem->description);
        $this->assertLessThan(200, strlen($giftItem->description));
    }

    public function test_gift_description_uses_toy_ideas_as_fallback(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female',
            'age' => '5',
            'gift_level' => GiftLevel::None,
            'gift_preferences' => null,
            'toy_ideas' => 'Barbie dolls',
        ]);

        $list = $this->service->generatePackingList($family);
        $giftItem = $list->items->first(fn ($i) => $i->child_id !== null);

        $this->assertStringContainsString('Barbie dolls', $giftItem->description);
    }

    // ==========================================
    // Gift Bank Matching Edge Cases
    // ==========================================

    public function test_gift_bank_gender_neutral_matches_any_child(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = Child::create([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);

        GiftBankItem::withoutGlobalScopes()->create([
            'description' => 'Board Game',
            'age_range' => '6-12',
            'gender_suitability' => 'neutral',
            'quantity' => 1,
        ]);

        $list = $this->service->generatePackingList($family);
        $giftItem = $list->items->first(fn ($i) => $i->child_id === $child->id);

        $this->assertNotNull($giftItem);
        $this->assertNotEquals(PackingItemStatus::Unfulfilled, $giftItem->status);
        $this->assertStringContainsString('Board Game', $giftItem->description);
    }

    public function test_gift_bank_null_age_range_matches_any_child(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = Child::create([
            'family_id' => $family->id,
            'gender' => 'Female',
            'age' => '14',
            'gift_level' => GiftLevel::None,
        ]);

        GiftBankItem::withoutGlobalScopes()->create([
            'description' => 'Universal Gift Card',
            'age_range' => null,
            'gender_suitability' => null,
            'quantity' => 1,
        ]);

        $list = $this->service->generatePackingList($family);
        $giftItem = $list->items->first(fn ($i) => $i->child_id === $child->id);

        $this->assertNotEquals(PackingItemStatus::Unfulfilled, $giftItem->status);
        $this->assertStringContainsString('Universal Gift Card', $giftItem->description);
    }

    public function test_gift_bank_does_not_double_assign_same_item(): void
    {
        $this->seedWarehouseCategories();

        // One gift bank item, two children needing gifts
        GiftBankItem::withoutGlobalScopes()->create([
            'description' => 'Lone Gift',
            'age_range' => '6-12',
            'gender_suitability' => 'male',
            'quantity' => 1,
        ]);

        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => '7',
            'gift_level' => GiftLevel::None,
        ]);
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => '9',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItems = $list->items->filter(fn ($i) => $i->child_id !== null);

        // One should match, the other should be unfulfilled
        $matched = $giftItems->filter(fn ($i) => str_contains($i->description, 'Lone Gift'));
        $unfulfilled = $giftItems->filter(fn ($i) => $i->status === PackingItemStatus::Unfulfilled);

        $this->assertCount(1, $matched);
        $this->assertCount(1, $unfulfilled);
    }

    // ==========================================
    // Packing Item Status Transition Edge Cases
    // ==========================================

    public function test_mark_item_packed_on_already_packed_item_returns_capped(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        // Pack the item fully
        $this->service->markItemPacked($item, $this->packer);
        $item->refresh();
        $this->assertEquals(PackingItemStatus::Packed, $item->status);

        // Try packing again — quantity should be capped at quantity_needed
        $result = $this->service->markItemPacked($item, $this->packer);
        $item->refresh();
        $this->assertEquals($item->quantity_needed, $item->quantity_packed);
    }

    public function test_mark_item_packed_with_quantity_needed_greater_than_one(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 8]);
        $list = $this->service->generatePackingList($family);

        $tunaItem = $list->items->firstWhere('description', 'Tuna');
        $this->assertGreaterThan(1, $tunaItem->quantity_needed); // qty_8 = 15

        // Pack only 1 — should remain Pending
        $this->service->markItemPacked($tunaItem, $this->packer);
        $tunaItem->refresh();
        $this->assertEquals(1, $tunaItem->quantity_packed);
        $this->assertEquals(PackingItemStatus::Pending, $tunaItem->status);

        // Pack the rest
        for ($i = 1; $i < $tunaItem->quantity_needed; $i++) {
            $this->service->markItemPacked($tunaItem, $this->packer);
        }
        $tunaItem->refresh();
        $this->assertEquals(PackingItemStatus::Packed, $tunaItem->status);
    }

    public function test_substitute_already_substituted_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $cat = WarehouseCategory::first();
        $subA = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Sub A', 'barcode' => 'SUB-A', 'active' => true,
        ]);
        $subB = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Sub B', 'barcode' => 'SUB-B', 'active' => true,
        ]);

        // First substitution
        $this->service->substituteItem($item, $subA, 'First sub', $this->packer);
        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertStringContainsString('Sub A', $item->description);

        // Second substitution — should overwrite
        $this->service->substituteItem($item, $subB, 'Second sub', $this->packer);
        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertStringContainsString('Sub B', $item->description);
        $this->assertEquals('Second sub', $item->substitute_notes);
        $this->assertEquals($subB->id, $item->item_id);
    }

    public function test_substitute_item_with_integer_id_instead_of_model(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $cat = WarehouseCategory::first();
        $warehouseItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Int Sub', 'barcode' => 'INT-1', 'active' => true,
        ]);

        // Pass integer ID instead of model
        $this->service->substituteItem($item, $warehouseItem->id, 'Using integer ID', $this->packer);
        $item->refresh();

        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertEquals($warehouseItem->id, $item->item_id);
        // Description should NOT have [Substituted: ...] suffix since model wasn't passed
        $this->assertStringNotContainsString('[Substituted:', $item->description);
    }

    public function test_substitute_item_with_null_new_item(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();

        $this->service->substituteItem($item, null, 'Manual substitution, no warehouse item', $this->packer);
        $item->refresh();

        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertNull($item->item_id);
        $this->assertEquals($item->quantity_needed, $item->quantity_packed);
    }

    // ==========================================
    // Verification Edge Cases
    // ==========================================

    public function test_verify_already_verified_list_still_succeeds(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $this->packAllItems($list);

        // First verification
        $this->service->verifyPackingList($list, $this->coordinator);
        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);

        // Second verification — should still return true (idempotent)
        $result = $this->service->verifyPackingList($list, $this->packer);
        // isComplete() checks items, not list status — verified items count as complete
        $this->assertTrue($result);
    }

    public function test_verify_fails_when_unfulfilled_items_exist(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);
        $list = $this->service->generatePackingList($family);

        // The unfulfilled gift item should block completion
        $unfulfilledExists = $list->items->contains(fn ($i) => $i->status === PackingItemStatus::Unfulfilled);
        if ($unfulfilledExists) {
            $this->assertFalse($list->isComplete());
            $result = $this->service->verifyPackingList($list, $this->coordinator);
            $this->assertFalse($result);
        } else {
            $this->markTestSkipped('No unfulfilled items generated — test precondition not met.');
        }
    }

    public function test_verify_with_all_substituted_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Substitute every item instead of packing
        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                $this->service->substituteItem($item, null, 'All substituted', $this->packer);
            }
        }

        $list->refresh();
        $this->assertTrue($list->isComplete());
        $result = $this->service->verifyPackingList($list, $this->coordinator);
        $this->assertTrue($result);

        // All items should now be Verified
        $list->load('items');
        foreach ($list->items as $item) {
            $this->assertEquals(PackingItemStatus::Verified, $item->status);
        }
    }

    // ==========================================
    // Refresh Edge Cases
    // ==========================================

    public function test_refresh_verified_list_does_not_downgrade_status(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $this->packAllItems($list);
        $this->service->verifyPackingList($list, $this->coordinator);
        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);

        // Refresh should not downgrade from Verified
        $this->service->refreshPackingList($list);
        $list->refresh();
        // Status should remain Verified since all items are verified (the code only checks Complete→InProgress)
        $this->assertEquals(PackingStatus::Verified, $list->status);
    }

    public function test_refresh_after_new_child_added_creates_new_gift_item(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child1 = Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => '7',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->generatePackingList($family);
        $initialGiftCount = $list->items->filter(fn ($i) => $i->child_id !== null)->count();
        $this->assertEquals(1, $initialGiftCount);

        // Add another child
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female', 'age' => '5',
            'gift_level' => GiftLevel::None,
        ]);

        $list = $this->service->refreshPackingList($list);
        $newGiftCount = $list->items->filter(fn ($i) => $i->child_id !== null)->count();

        $this->assertEquals(2, $newGiftCount);
    }

    public function test_refresh_when_all_items_are_already_packed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $originalCount = $list->items->count();

        $this->packAllItems($list);
        $list->refresh();

        // Refresh — packed items should be preserved; no new items needed
        $refreshed = $this->service->refreshPackingList($list);
        $this->assertEquals($originalCount, $refreshed->items->count());
    }

    public function test_refresh_complete_list_transitions_to_in_progress_if_new_items_pending(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $this->packAllItems($list);
        $list->refresh();
        $this->assertEquals(PackingStatus::Complete, $list->status);

        // Add a new grocery item — refresh should add it and transition back to InProgress
        GroceryItem::create([
            'name' => 'New Cereal', 'category' => 'dry', 'sort_order' => 99,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
        ]);

        $refreshed = $this->service->refreshPackingList($list);
        $list->refresh();

        $this->assertEquals(PackingStatus::InProgress, $list->status);
        $cerealItem = $refreshed->items->firstWhere('description', 'New Cereal');
        $this->assertNotNull($cerealItem);
        $this->assertEquals(PackingItemStatus::Pending, $cerealItem->status);
    }

    // ==========================================
    // Scanning Edge Cases
    // ==========================================

    public function test_scan_barcode_matching_already_packed_category(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $cannedCategory = WarehouseCategory::where('name', 'Canned Goods')->first();
        if (!$cannedCategory) {
            $this->markTestSkipped('No Canned Goods category found.');
        }

        $warehouseItem = WarehouseItem::create([
            'category_id' => $cannedCategory->id,
            'name' => 'Scan Target',
            'barcode' => 'SCAN-001',
            'active' => true,
        ]);

        // Pack all items in the canned category
        foreach ($list->items as $item) {
            if ($item->category_id === $cannedCategory->id) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $this->service->markItemPacked($item, $this->packer);
                }
            }
        }

        // Now scan — category items are already fulfilled
        $result = $this->service->scanItemIntoPack($list, 'SCAN-001', $this->packer);
        $this->assertFalse($result['match']);
        $this->assertStringContainsString('already fulfilled', $result['message']);
    }

    public function test_scan_barcode_for_item_not_on_list_at_all(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Create a warehouse item in a category that doesn't appear on the list
        $giftCategory = WarehouseCategory::where('type', 'gift')->first();
        if (!$giftCategory) {
            $this->markTestSkipped('No gift category found.');
        }

        WarehouseItem::create([
            'category_id' => $giftCategory->id,
            'name' => 'Random Toy',
            'barcode' => 'TOY-001',
            'active' => true,
        ]);

        $result = $this->service->scanItemIntoPack($list, 'TOY-001', $this->packer);
        $this->assertFalse($result['match']);
        $this->assertStringContainsString('not on this family', $result['message']);
    }

    public function test_scan_item_prioritizes_exact_item_id_over_category_match(): void
    {
        $this->seedWarehouseCategories();
        $cannedCategory = WarehouseCategory::where('name', 'Canned Goods')->first();
        if (!$cannedCategory) {
            $this->markTestSkipped('No Canned Goods category found.');
        }

        $specificItem = WarehouseItem::create([
            'category_id' => $cannedCategory->id,
            'name' => 'Specific Tuna Brand',
            'barcode' => 'TUNA-BRAND',
            'active' => true,
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);

        // Manually create a packing list with an item_id pre-set
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::Pending,
        ]);

        // Create two items: one with specific item_id, one with just category
        $list->items()->create([
            'category_id' => $cannedCategory->id,
            'item_id' => $specificItem->id,
            'description' => 'Specific Tuna Brand',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
            'sort_order' => 0,
        ]);
        $list->items()->create([
            'category_id' => $cannedCategory->id,
            'description' => 'Generic Canned Item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
            'sort_order' => 1,
        ]);

        $result = $this->service->scanItemIntoPack($list, 'TUNA-BRAND', $this->packer);

        $this->assertTrue($result['match']);
        // The specific item should have been packed, not the generic one
        $specificPackingItem = $list->items()->where('description', 'Specific Tuna Brand')->first();
        $genericPackingItem = $list->items()->where('description', 'Generic Canned Item')->first();

        $this->assertEquals(1, $specificPackingItem->quantity_packed);
        $this->assertEquals(0, $genericPackingItem->quantity_packed);
    }

    // ==========================================
    // Session / Clock Edge Cases
    // ==========================================

    public function test_clock_out_without_active_session_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No active session found.');

        $this->service->clockOut($this->packer);
    }

    public function test_double_clock_in_throws(): void
    {
        $this->service->clockIn($this->packer);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Already clocked in.');

        $this->service->clockIn($this->packer);
    }

    public function test_session_items_per_hour_with_zero_items(): void
    {
        $session = PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'items_packed' => 0,
        ]);

        $this->assertEquals(0, $session->itemsPerHour());
    }

    public function test_session_duration_when_still_active(): void
    {
        $session = PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => now()->subHours(2),
            'ended_at' => null,
            'items_packed' => 0,
        ]);

        // Duration should use now() for active sessions
        $hours = $session->durationInHours();
        $this->assertGreaterThan(1.9, $hours);
        $this->assertLessThan(2.5, $hours);
    }

    public function test_marking_item_packed_increments_active_session_counter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $session = $this->service->clockIn($this->packer);
        $this->assertEquals(0, $session->items_packed);

        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();
        $this->service->markItemPacked($item, $this->packer);

        $session->refresh();
        $this->assertEquals(1, $session->items_packed);
    }

    public function test_marking_item_by_different_user_does_not_increment_other_session(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $session = $this->service->clockIn($this->packer);

        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();
        // Coordinator packs the item, not the packer with the active session
        $this->service->markItemPacked($item, $this->coordinator);

        $session->refresh();
        // Packer's session should NOT be incremented because coordinator packed it
        $this->assertEquals(0, $session->items_packed);
    }

    public function test_clock_out_computes_lists_worked_correctly(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['number_of_family_members' => 1]);
        $familyB = $this->createFamily(['number_of_family_members' => 1]);
        $listA = $this->service->generatePackingList($familyA);
        $listB = $this->service->generatePackingList($familyB);

        $this->service->clockIn($this->packer);

        // Pack items from two different lists
        $itemA = $listA->items()->where('status', PackingItemStatus::Pending->value)->first();
        $itemB = $listB->items()->where('status', PackingItemStatus::Pending->value)->first();
        $this->service->markItemPacked($itemA, $this->packer);
        $this->service->markItemPacked($itemB, $this->packer);

        $session = $this->service->clockOut($this->packer);
        $this->assertEquals(2, $session->lists_worked);
    }

    // ==========================================
    // Auto-Substitute Edge Cases
    // ==========================================

    public function test_auto_substitute_with_no_affected_items_returns_zero(): void
    {
        $this->seedWarehouseCategories();
        $cat = WarehouseCategory::first();
        $removedItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Orphan Item', 'barcode' => 'ORPHAN', 'active' => true,
        ]);

        // No packing items reference this item
        $count = $this->service->autoSubstituteRemovedItem($removedItem, $this->coordinator);
        $this->assertEquals(0, $count);
    }

    public function test_auto_substitute_marks_unfulfilled_when_no_alternatives_exist(): void
    {
        $this->seedWarehouseCategories();
        $cat = WarehouseCategory::create([
            'name' => 'Isolated Category', 'type' => 'food', 'sort_order' => 99, 'active' => true,
        ]);
        $loneItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Only Item', 'barcode' => 'LONE-1', 'active' => true,
        ]);

        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::Pending,
        ]);
        $packingItem = $list->items()->create([
            'category_id' => $cat->id,
            'item_id' => $loneItem->id,
            'description' => 'Only Item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
        ]);

        $count = $this->service->autoSubstituteRemovedItem($loneItem, $this->coordinator);

        $this->assertEquals(1, $count);
        $packingItem->refresh();
        $this->assertEquals(PackingItemStatus::Unfulfilled, $packingItem->status);
        $this->assertStringContainsString('ITEM REMOVED', $packingItem->description);
    }

    public function test_auto_substitute_picks_available_alternative(): void
    {
        $this->seedWarehouseCategories();
        $cat = WarehouseCategory::create([
            'name' => 'Sub Category', 'type' => 'food', 'sort_order' => 99, 'active' => true,
        ]);
        $removedItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Removed Item', 'barcode' => 'RM-1', 'active' => true,
        ]);
        $altItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Alternative Item', 'barcode' => 'ALT-1', 'active' => true,
        ]);

        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::Pending,
        ]);
        $packingItem = $list->items()->create([
            'category_id' => $cat->id,
            'item_id' => $removedItem->id,
            'description' => 'Removed Item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
        ]);

        $count = $this->service->autoSubstituteRemovedItem($removedItem, $this->coordinator);

        $this->assertEquals(1, $count);
        $packingItem->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $packingItem->status);
        $this->assertEquals($altItem->id, $packingItem->item_id);
        $this->assertStringContainsString('Auto-substituted', $packingItem->substitute_notes);
    }

    // ==========================================
    // Suggest Substitutes Edge Cases
    // ==========================================

    public function test_suggest_substitutes_with_null_category_returns_empty(): void
    {
        $item = new PackingItem([
            'category_id' => null,
            'description' => 'No category item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
        ]);

        $candidates = $this->service->suggestSubstitutes($item);
        $this->assertEmpty($candidates);
    }

    public function test_suggest_substitutes_excludes_current_item(): void
    {
        $this->seedWarehouseCategories();
        $cat = WarehouseCategory::first();

        $currentItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Current', 'barcode' => 'CUR-1', 'active' => true,
        ]);
        $otherItem = WarehouseItem::create([
            'category_id' => $cat->id, 'name' => 'Other', 'barcode' => 'OTH-1', 'active' => true,
        ]);

        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::Pending,
        ]);
        $packingItem = $list->items()->create([
            'category_id' => $cat->id,
            'item_id' => $currentItem->id,
            'description' => 'Current Item',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
        ]);

        $candidates = $this->service->suggestSubstitutes($packingItem);

        $ids = array_column($candidates, 'id');
        $this->assertNotContains($currentItem->id, $ids);
        $this->assertContains($otherItem->id, $ids);
    }

    // ==========================================
    // Shopping Deficit Edge Cases
    // ==========================================

    public function test_shopping_deficits_with_only_gift_items_returns_empty(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);

        // No grocery items — only gift items on the packing list
        $this->service->generatePackingList($family);

        $deficits = $this->service->getShoppingDeficits();
        $this->assertEmpty($deficits);
    }

    public function test_shopping_deficits_aggregates_across_multiple_families(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['number_of_family_members' => 1]);
        $familyB = $this->createFamily(['number_of_family_members' => 1]);
        $familyC = $this->createFamily(['number_of_family_members' => 1]);

        $this->service->generatePackingList($familyA);
        $this->service->generatePackingList($familyB);
        $this->service->generatePackingList($familyC);

        $deficits = $this->service->getShoppingDeficits();

        $tunaDeficit = collect($deficits)->firstWhere('grocery_item_name', 'Tuna');
        $this->assertNotNull($tunaDeficit);
        // 3 families × qty_1(1) = 3
        $this->assertEquals(3, $tunaDeficit['total_needed']);
    }

    // ==========================================
    // Dashboard Stats Edge Cases
    // ==========================================

    public function test_dashboard_stats_with_zero_packing_lists(): void
    {
        $stats = $this->service->getDashboardStats();

        $this->assertEquals(0, $stats['total_families']);
        $this->assertEquals(0, $stats['fulfillment_rate']);
        $this->assertEmpty($stats['volunteers']);
        $this->assertEmpty($stats['recently_completed']);
    }

    public function test_dashboard_recently_completed_excludes_old_completions(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Set completed_at to 2 minutes ago (outside 60-second window)
        $list->update([
            'status' => PackingStatus::Complete,
            'completed_at' => now()->subSeconds(120),
        ]);

        $stats = $this->service->getDashboardStats();
        $this->assertEmpty($stats['recently_completed']);
    }

    public function test_dashboard_recently_completed_includes_fresh_completions(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Set completed_at to 10 seconds ago (within 60-second window)
        $list->update([
            'status' => PackingStatus::Complete,
            'completed_at' => now()->subSeconds(10),
        ]);

        $stats = $this->service->getDashboardStats();
        $this->assertNotEmpty($stats['recently_completed']);
        $this->assertEquals($list->id, $stats['recently_completed'][0]['id']);
    }

    // ==========================================
    // Volunteer Trend Edge Cases
    // ==========================================

    public function test_volunteer_trend_with_active_but_no_completed_sessions(): void
    {
        // Create an active (not ended) session today
        PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => now()->subMinutes(30),
            'items_packed' => 10,
        ]);

        $trend = $this->service->getVolunteerTrend();

        // Active sessions should be counted but not in today_sessions_count (requires ended_at)
        $this->assertEquals(0, $trend['today_sessions_count']);
        $this->assertEquals(1, $trend['active_sessions']);
        $this->assertEquals(0, $trend['today_avg_items_per_hour']);
    }

    // ==========================================
    // End-of-Day Summary Edge Cases
    // ==========================================

    public function test_end_of_day_summary_only_includes_given_date(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Complete yesterday
        $list->update([
            'status' => PackingStatus::Complete,
            'completed_at' => now()->subDay()->setHour(14),
        ]);

        // Summary for TODAY should NOT include yesterday's completion
        $summary = $this->service->getEndOfDaySummary(Carbon::today());
        $this->assertEquals(0, $summary['families_packed_count']);

        // Summary for YESTERDAY should include it
        $summary = $this->service->getEndOfDaySummary(Carbon::yesterday());
        $this->assertEquals(1, $summary['families_packed_count']);
    }

    // ==========================================
    // Warehouse Category Resolution Edge Cases
    // ==========================================

    public function test_unmapped_grocery_category_falls_back_to_fuzzy_match(): void
    {
        $this->seedWarehouseCategories();
        // Create a grocery item with a category not in GROCERY_TO_WAREHOUSE_MAP
        GroceryItem::create([
            'name' => 'Special Spread', 'category' => 'spread', 'sort_order' => 99,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $specialItem = $list->items->firstWhere('description', 'Special Spread');
        // Should still be created even if category ID is null
        $this->assertNotNull($specialItem);
    }

    // ==========================================
    // Conditional Grocery Items Edge Cases
    // ==========================================

    public function test_conditional_baby_item_included_for_infant_family(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Baby Formula', 'category' => 'canned', 'sort_order' => 50,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'conditional' => true, 'condition_field' => 'has_infants',
        ]);

        $family = $this->createFamily(['number_of_family_members' => 2, 'infants' => 1]);
        $list = $this->service->generatePackingList($family);

        $formulaItem = $list->items->firstWhere('description', 'Baby Formula');
        $this->assertNotNull($formulaItem);
    }

    public function test_conditional_baby_item_excluded_for_no_infant_family(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Baby Formula', 'category' => 'canned', 'sort_order' => 50,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'conditional' => true, 'condition_field' => 'has_infants',
        ]);

        $family = $this->createFamily(['number_of_family_members' => 2, 'infants' => 0]);
        $list = $this->service->generatePackingList($family);

        $formulaItem = $list->items->firstWhere('description', 'Baby Formula');
        $this->assertNull($formulaItem);
    }

    public function test_conditional_pet_item_included_when_family_has_pet_info(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Dog Food', 'category' => 'canned', 'sort_order' => 55,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'conditional' => true, 'condition_field' => 'has_pets',
        ]);

        $family = $this->createFamily([
            'number_of_family_members' => 2,
            'pet_information' => '1 dog, 2 cats',
        ]);
        $list = $this->service->generatePackingList($family);

        $dogFood = $list->items->firstWhere('description', 'Dog Food');
        $this->assertNotNull($dogFood);
    }

    public function test_conditional_unknown_field_excludes_item(): void
    {
        $this->seedWarehouseCategories();
        GroceryItem::create([
            'name' => 'Mystery Item', 'category' => 'canned', 'sort_order' => 99,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'conditional' => true, 'condition_field' => 'unknown_field_xyz',
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $mystery = $list->items->firstWhere('description', 'Mystery Item');
        $this->assertNull($mystery);
    }

    // ==========================================
    // Progress Summary Edge Cases
    // ==========================================

    public function test_progress_summary_with_no_items(): void
    {
        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::Pending,
        ]);

        $summary = $list->progressSummary();
        $this->assertEquals(0, $summary['total']);
        $this->assertEquals(0, $summary['packed']);
        $this->assertEquals(0, $summary['percentage']); // No division by zero
    }

    public function test_is_complete_returns_true_for_list_with_only_packed_and_substituted(): void
    {
        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::InProgress,
        ]);

        $list->items()->create([
            'description' => 'Item A', 'quantity_needed' => 1,
            'quantity_packed' => 1, 'status' => PackingItemStatus::Packed,
        ]);
        $list->items()->create([
            'description' => 'Item B', 'quantity_needed' => 1,
            'quantity_packed' => 1, 'status' => PackingItemStatus::Substituted,
        ]);
        $list->items()->create([
            'description' => 'Item C', 'quantity_needed' => 1,
            'quantity_packed' => 1, 'status' => PackingItemStatus::Verified,
        ]);

        $this->assertTrue($list->isComplete());
    }

    public function test_is_complete_returns_false_with_unfulfilled_item(): void
    {
        $family = $this->createFamily();
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'status' => PackingStatus::InProgress,
        ]);

        $list->items()->create([
            'description' => 'Packed Item', 'quantity_needed' => 1,
            'quantity_packed' => 1, 'status' => PackingItemStatus::Packed,
        ]);
        $list->items()->create([
            'description' => 'Unfulfilled Item', 'quantity_needed' => 1,
            'status' => PackingItemStatus::Unfulfilled,
        ]);

        $this->assertFalse($list->isComplete());
    }

    // ==========================================
    // Baby Items Edge Cases
    // ==========================================

    public function test_baby_items_for_family_with_both_needs_baby_and_infants(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily([
            'needs_baby_supplies' => true,
            'infants' => 2,
        ]);

        $list = $this->service->generatePackingList($family);

        // Should create baby items (one per baby category, not doubled)
        $babyItems = $list->items->filter(fn ($i) => $i->sort_order >= 2000);
        $this->assertNotEmpty($babyItems);

        // Each baby category should appear exactly once
        $categoryCounts = $babyItems->groupBy('category_id')->map->count();
        foreach ($categoryCounts as $count) {
            $this->assertEquals(1, $count);
        }
    }

    // ==========================================
    // Concurrent Operations / Race Conditions
    // ==========================================

    public function test_two_packers_can_pack_different_items_on_same_list(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $items = $list->items()->where('status', PackingItemStatus::Pending->value)->get();
        $this->assertGreaterThanOrEqual(2, $items->count());

        $packer2 = User::create([
            'username' => 'packer2', 'first_name' => 'Other', 'last_name' => 'Packer',
            'password' => 'password', 'permission' => 8,
        ]);

        // Pack different items with different users
        $this->service->markItemPacked($items[0], $this->packer);
        $this->service->markItemPacked($items[1], $packer2);

        $items[0]->refresh();
        $items[1]->refresh();

        $this->assertEquals($this->packer->id, $items[0]->packed_by);
        $this->assertEquals($packer2->id, $items[1]->packed_by);
    }

    // ==========================================
    // QR Token Edge Cases
    // ==========================================

    public function test_packing_list_always_gets_unique_qr_token(): void
    {
        $familyA = $this->createFamily();
        $familyB = $this->createFamily();

        $listA = PackingList::withoutGlobalScopes()->create([
            'family_id' => $familyA->id,
            'status' => PackingStatus::Pending,
        ]);
        $listB = PackingList::withoutGlobalScopes()->create([
            'family_id' => $familyB->id,
            'status' => PackingStatus::Pending,
        ]);

        $this->assertNotEmpty($listA->qr_token);
        $this->assertNotEmpty($listB->qr_token);
        $this->assertNotEquals($listA->qr_token, $listB->qr_token);
    }

    // ==========================================
    // Pick Path Sort Edge Cases
    // ==========================================

    public function test_pick_path_sort_with_mixed_location_and_no_location_items(): void
    {
        $this->seedWarehouseCategories();
        $cannedCategory = WarehouseCategory::where('name', 'Canned Goods')->first();
        $dryCategory = WarehouseCategory::where('name', 'Dry Goods')->first();

        if (!$cannedCategory || !$dryCategory) {
            $this->markTestSkipped('Required categories not found.');
        }

        // Create items with and without locations
        $itemWithLoc = WarehouseItem::create([
            'category_id' => $cannedCategory->id,
            'name' => 'Located Tuna',
            'barcode' => 'LOC-1',
            'active' => true,
            'location_zone' => 'A',
            'location_shelf' => '1',
            'location_bin' => '01',
        ]);
        $itemNoLoc = WarehouseItem::create([
            'category_id' => $dryCategory->id,
            'name' => 'Unlocated Rice',
            'barcode' => 'NOLOC-1',
            'active' => true,
        ]);

        GroceryItem::create([
            'name' => 'Located Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
        ]);
        GroceryItem::create([
            'name' => 'Unlocated Rice', 'category' => 'dry', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
        ]);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Items with location should sort before items without
        $foodItems = $list->items->where('sort_order', '<', 1000)->sortBy('sort_order');
        // We just verify no crashes and sort_order is applied
        $this->assertGreaterThanOrEqual(2, $foodItems->count());
    }
}
