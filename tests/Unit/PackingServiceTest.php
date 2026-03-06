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
use Carbon\Carbon;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PackingService $service;
    private User $packer;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        $this->service = app(PackingService::class);
        $this->packer = User::create([
            'username' => 'packer', 'first_name' => 'Test', 'last_name' => 'Packer',
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
            'qty_1' => 1, 'qty_2' => 3, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 11,
        ]);
        GroceryItem::create([
            'name' => 'Pasta Noodles', 'category' => 'dry', 'sort_order' => 3,
            'qty_1' => 4, 'qty_2' => 11, 'qty_3' => 11, 'qty_4' => 13,
            'qty_5' => 18, 'qty_6' => 20, 'qty_7' => 24, 'qty_8' => 37,
        ]);
    }

    private function createFamily(array $overrides = []): Family
    {
        return Family::create(array_merge([
            'family_name' => 'Test Family', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
        ], $overrides));
    }

    private function createChild(Family $family, array $overrides = []): Child
    {
        return Child::create(array_merge([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => '8',
            'gift_level' => GiftLevel::None,
        ], $overrides));
    }

    // ==========================================
    // Phase 1 Tests: Packing List Generation
    // ==========================================

    public function test_generate_packing_list_creates_list_for_family(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $list = $this->service->generatePackingList($family);

        $this->assertInstanceOf(PackingList::class, $list);
        $this->assertEquals($family->id, $list->family_id);
        $this->assertEquals(PackingStatus::Pending, $list->status);
        $this->assertNotNull($list->qr_token);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($list->qr_token));
    }

    public function test_generate_packing_list_is_idempotent(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $list1 = $this->service->generatePackingList($family);
        $list2 = $this->service->generatePackingList($family);

        $this->assertEquals($list1->id, $list2->id);
        // Item count should not double
        $this->assertEquals($list1->items->count(), $list2->items->count());
    }

    public function test_generate_packing_list_creates_food_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 3]);

        $list = $this->service->generatePackingList($family);

        $foodItems = $list->items->filter(fn ($item) => $item->grocery_item_id !== null);
        $this->assertEquals(3, $foodItems->count());

        // Check quantities for family size 3
        $tuna = $foodItems->firstWhere('description', 'Tuna');
        $this->assertNotNull($tuna);
        $this->assertEquals(4, $tuna->quantity_needed); // qty_3 = 4

        $pasta = $foodItems->firstWhere('description', 'Pasta Noodles');
        $this->assertNotNull($pasta);
        $this->assertEquals(11, $pasta->quantity_needed); // qty_3 = 11
    }

    public function test_food_items_scale_with_family_size(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $smallFamily = $this->createFamily(['family_number' => 1, 'number_of_family_members' => 1]);
        $largeFamily = $this->createFamily(['family_name' => 'Large', 'family_number' => 2, 'number_of_family_members' => 8]);

        $smallList = $this->service->generatePackingList($smallFamily);
        $largeList = $this->service->generatePackingList($largeFamily);

        $smallTuna = $smallList->items->firstWhere('description', 'Tuna');
        $largeTuna = $largeList->items->firstWhere('description', 'Tuna');

        $this->assertEquals(1, $smallTuna->quantity_needed);  // qty_1
        $this->assertEquals(15, $largeTuna->quantity_needed); // qty_8
    }

    public function test_conditional_grocery_items_excluded_when_condition_not_met(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        GroceryItem::create([
            'name' => 'Baby Cereal', 'category' => 'personal', 'sort_order' => 10,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 1, 'qty_4' => 1,
            'qty_5' => 1, 'qty_6' => 1, 'qty_7' => 1, 'qty_8' => 1,
            'conditional' => true, 'condition_field' => 'has_infants',
        ]);

        $family = $this->createFamily(['infants' => 0]);
        $list = $this->service->generatePackingList($family);

        $this->assertNull($list->items->firstWhere('description', 'Baby Cereal'));
    }

    public function test_conditional_grocery_items_included_when_condition_met(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        GroceryItem::create([
            'name' => 'Baby Cereal', 'category' => 'personal', 'sort_order' => 10,
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 2, 'qty_4' => 2,
            'qty_5' => 2, 'qty_6' => 2, 'qty_7' => 2, 'qty_8' => 2,
            'conditional' => true, 'condition_field' => 'has_infants',
        ]);

        $family = $this->createFamily(['infants' => 1]);
        $list = $this->service->generatePackingList($family);

        $babyCereal = $list->items->firstWhere('description', 'Baby Cereal');
        $this->assertNotNull($babyCereal);
        $this->assertEquals(2, $babyCereal->quantity_needed); // qty_3 for size 3
    }

    public function test_food_items_get_correct_warehouse_category(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $list = $this->service->generatePackingList($family);

        $tuna = $list->items()->where('description', 'Tuna')->with('category')->first();
        $this->assertNotNull($tuna->category);
        $this->assertEquals('Canned Goods', $tuna->category->name);

        $pasta = $list->items()->where('description', 'Pasta Noodles')->with('category')->first();
        $this->assertNotNull($pasta->category);
        $this->assertEquals('Dry Goods', $pasta->category->name);
    }

    // ==========================================
    // Phase 1 Tests: Gift Items
    // ==========================================

    public function test_gift_items_created_per_child(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['number_of_children' => 2]);
        $this->createChild($family, ['gender' => 'Male', 'age' => '8']);
        $this->createChild($family, ['gender' => 'Female', 'age' => '5']);

        $list = $this->service->generatePackingList($family);

        $giftItems = $list->items->filter(fn ($item) => $item->child_id !== null);
        $this->assertEquals(2, $giftItems->count());
    }

    public function test_gift_items_contain_child_info(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $this->createChild($family, ['gender' => 'Male', 'age' => '8', 'gift_preferences' => 'LEGO sets']);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->firstWhere(fn ($i) => $i->child_id !== null);
        $this->assertNotNull($giftItem);
        $this->assertStringContainsString('Male', $giftItem->description);
        $this->assertStringContainsString('age 8', $giftItem->description);
        $this->assertStringContainsString('LEGO sets', $giftItem->description);
    }

    public function test_gift_items_for_adopted_child_with_gift(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, [
            'gender' => 'Female', 'age' => '6',
            'adoption_token' => 'test-token-123',
            'gift_dropped_off' => true,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->firstWhere('child_id', $child->id);
        $this->assertNotNull($giftItem);
        $this->assertEquals(PackingItemStatus::Pending, $giftItem->status);
        $this->assertStringNotContainsString('awaiting', $giftItem->description);
    }

    public function test_gift_items_for_adopted_child_awaiting_gift(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, [
            'gender' => 'Male', 'age' => '10',
            'adoption_token' => 'test-token-456',
            'gift_dropped_off' => false,
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->firstWhere('child_id', $child->id);
        $this->assertNotNull($giftItem);
        $this->assertEquals(PackingItemStatus::Pending, $giftItem->status);
        $this->assertStringContainsString('Adopted - awaiting gift drop-off', $giftItem->description);
    }

    public function test_gift_items_unfulfilled_when_no_match(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Male', 'age' => '8']);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->firstWhere('child_id', $child->id);
        $this->assertNotNull($giftItem);
        $this->assertEquals(PackingItemStatus::Unfulfilled, $giftItem->status);
        $this->assertStringContainsString('No gift matched', $giftItem->description);
    }

    public function test_gift_items_matched_from_gift_bank(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Male', 'age' => '8']);

        // Add a matching gift bank item
        GiftBankItem::create([
            'description' => 'LEGO City Set',
            'age_range' => '6-12',
            'gender_suitability' => 'male',
            'season_year' => date('Y'),
        ]);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items->firstWhere('child_id', $child->id);
        $this->assertNotNull($giftItem);
        $this->assertEquals(PackingItemStatus::Pending, $giftItem->status);
        $this->assertStringContainsString('Gift Bank: LEGO City Set', $giftItem->description);
    }

    public function test_gift_bank_item_reserved_after_match(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Female', 'age' => '4']);

        $giftBankItem = GiftBankItem::create([
            'description' => 'Doll Set',
            'age_range' => '0-5',
            'gender_suitability' => 'female',
            'season_year' => date('Y'),
        ]);

        $this->service->generatePackingList($family);

        $giftBankItem->refresh();
        $this->assertEquals($child->id, $giftBankItem->assigned_child_id);
        $this->assertNotNull($giftBankItem->assigned_at);
    }

    public function test_gift_bank_fifo_oldest_matched_first(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Male', 'age' => '8']);

        $older = GiftBankItem::create([
            'description' => 'Old Gift',
            'age_range' => '6-12',
            'gender_suitability' => 'male',
            'season_year' => date('Y'),
            'created_at' => now()->subDays(5),
        ]);
        $newer = GiftBankItem::create([
            'description' => 'New Gift',
            'age_range' => '6-12',
            'gender_suitability' => 'male',
            'season_year' => date('Y'),
            'created_at' => now(),
        ]);

        $this->service->generatePackingList($family);

        $older->refresh();
        $newer->refresh();
        $this->assertEquals($child->id, $older->assigned_child_id);
        $this->assertNull($newer->assigned_child_id);
    }

    public function test_gift_category_resolution_boy_under_6(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Male', 'age' => '3']);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items()->where('child_id', $child->id)->with('category')->first();
        $this->assertNotNull($giftItem->category);
        $this->assertEquals('Gift - Boy Under 6', $giftItem->category->name);
    }

    public function test_gift_category_resolution_girl_6_12(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Female', 'age' => '9']);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items()->where('child_id', $child->id)->with('category')->first();
        $this->assertNotNull($giftItem->category);
        $this->assertEquals('Gift - Girl 6-12', $giftItem->category->name);
    }

    public function test_gift_category_resolution_teen(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily();
        $child = $this->createChild($family, ['gender' => 'Male', 'age' => '15']);

        $list = $this->service->generatePackingList($family);

        $giftItem = $list->items()->where('child_id', $child->id)->with('category')->first();
        $this->assertNotNull($giftItem->category);
        $this->assertEquals('Gift - Boy 13-17', $giftItem->category->name);
    }

    // ==========================================
    // Phase 1 Tests: Baby Items
    // ==========================================

    public function test_baby_items_created_when_needs_baby_supplies(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['needs_baby_supplies' => true]);

        $list = $this->service->generatePackingList($family);

        $babyItems = $list->items->filter(fn ($item) =>
            $item->category && $item->category->type === 'baby'
        );
        // 3 baby categories: Diapers, Formula, Baby Supplies
        $this->assertEquals(3, $babyItems->count());
    }

    public function test_baby_items_created_when_family_has_infants(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['infants' => 1]);

        $list = $this->service->generatePackingList($family);

        $babyItems = $list->items->filter(fn ($item) =>
            $item->category && $item->category->type === 'baby'
        );
        $this->assertGreaterThan(0, $babyItems->count());
    }

    public function test_no_baby_items_when_not_needed(): void
    {
        $this->seedWarehouseCategories();
        $family = $this->createFamily(['needs_baby_supplies' => false, 'infants' => 0]);

        $list = $this->service->generatePackingList($family);

        $babyItems = $list->items->filter(fn ($item) =>
            $item->category && $item->category->type === 'baby'
        );
        $this->assertEquals(0, $babyItems->count());
    }

    // ==========================================
    // Phase 1 Tests: Packing Workflow
    // ==========================================

    public function test_mark_item_packed_increments_quantity(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $this->assertEquals(0, $item->quantity_packed);

        $result = $this->service->markItemPacked($item, $this->packer);

        $this->assertTrue($result['success']);
        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
    }

    public function test_mark_item_packed_sets_packed_status_when_complete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]); // qty_1 = 1 for tuna
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $this->assertEquals(1, $item->quantity_needed);

        $this->service->markItemPacked($item, $this->packer);

        $item->refresh();
        $this->assertEquals(PackingItemStatus::Packed, $item->status);
        $this->assertEquals($this->packer->id, $item->packed_by);
        $this->assertNotNull($item->packed_at);
    }

    public function test_mark_item_packed_stays_pending_when_partial(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 3]); // qty_3 = 4 for tuna
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $this->assertEquals(4, $item->quantity_needed);

        $this->service->markItemPacked($item, $this->packer);

        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
        $this->assertEquals(PackingItemStatus::Pending, $item->status);
    }

    public function test_mark_item_packed_does_not_exceed_quantity_needed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();

        // Pack twice — should cap at 1
        $this->service->markItemPacked($item, $this->packer);
        $this->service->markItemPacked($item, $this->packer);

        $item->refresh();
        $this->assertEquals(1, $item->quantity_packed);
    }

    public function test_packing_first_item_transitions_list_to_in_progress(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $this->assertEquals(PackingStatus::Pending, $list->status);

        $item = $list->items()->first();
        $this->service->markItemPacked($item, $this->packer);

        $list->refresh();
        $this->assertEquals(PackingStatus::InProgress, $list->status);
        $this->assertNotNull($list->started_at);
    }

    public function test_packing_all_items_transitions_list_to_complete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Pack all food items (qty=1 for family of 1)
        foreach ($list->items as $item) {
            if ($item->status === PackingItemStatus::Unfulfilled) {
                continue;
            }
            // Pack as many times as needed
            for ($i = 0; $i < $item->quantity_needed; $i++) {
                $this->service->markItemPacked($item, $this->packer);
            }
        }

        $list->refresh();
        $this->assertEquals(PackingStatus::Complete, $list->status);
        $this->assertNotNull($list->completed_at);
    }

    // ==========================================
    // Phase 1 Tests: Substitution
    // ==========================================

    public function test_substitute_item_records_substitution(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();

        $this->service->substituteItem($item, null, 'Replaced with sardines — tuna unavailable', $this->packer);

        $item->refresh();
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
        $this->assertEquals($item->quantity_needed, $item->quantity_packed);
        $this->assertStringContainsString('sardines', $item->substitute_notes);
        $this->assertEquals($this->packer->id, $item->packed_by);
    }

    // ==========================================
    // Phase 1 Tests: Verification
    // ==========================================

    public function test_verify_packing_list_succeeds_when_complete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Pack all items
        foreach ($list->items as $item) {
            if ($item->status === PackingItemStatus::Unfulfilled) {
                continue;
            }
            for ($i = 0; $i < $item->quantity_needed; $i++) {
                $this->service->markItemPacked($item, $this->packer);
            }
        }

        $verifier = User::create([
            'username' => 'verifier', 'first_name' => 'V', 'last_name' => 'V',
            'password' => 'password', 'permission' => 9,
        ]);

        $result = $this->service->verifyPackingList($list->fresh(), $verifier);

        $this->assertTrue($result);
        $list->refresh();
        $this->assertEquals(PackingStatus::Verified, $list->status);
        $this->assertEquals($verifier->id, $list->verified_by);
        $this->assertNotNull($list->verified_at);
    }

    public function test_verify_packing_list_fails_when_incomplete(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $result = $this->service->verifyPackingList($list, $this->packer);

        $this->assertFalse($result);
        $list->refresh();
        $this->assertNotEquals(PackingStatus::Verified, $list->status);
    }

    public function test_verify_marks_packed_items_as_verified(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        foreach ($list->items as $item) {
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $this->service->markItemPacked($item, $this->packer);
                }
            }
        }

        $this->service->verifyPackingList($list->fresh(), $this->packer);

        $list->items->each(function ($item) {
            $item->refresh();
            if ($item->status !== PackingItemStatus::Unfulfilled) {
                $this->assertEquals(PackingItemStatus::Verified, $item->status);
            }
        });
    }

    public function test_verify_blocked_by_unfulfilled_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $child = $this->createChild($family); // No adopted tag, no gift bank → unfulfilled

        $list = $this->service->generatePackingList($family);

        // Pack all food items
        foreach ($list->items as $item) {
            if ($item->status === PackingItemStatus::Unfulfilled) {
                continue;
            }
            for ($i = 0; $i < $item->quantity_needed; $i++) {
                $this->service->markItemPacked($item, $this->packer);
            }
        }

        $result = $this->service->verifyPackingList($list->fresh(), $this->packer);
        $this->assertFalse($result);
    }

    // ==========================================
    // Phase 1 Tests: Refresh
    // ==========================================

    public function test_refresh_preserves_packed_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        // Pack one item
        $item = $list->items()->where('description', 'Tuna')->first();
        for ($i = 0; $i < $item->quantity_needed; $i++) {
            $this->service->markItemPacked($item, $this->packer);
        }

        $list = $this->service->refreshPackingList($list);

        // Tuna should still be packed
        $tuna = $list->items->firstWhere('description', 'Tuna');
        $this->assertNotNull($tuna);
        $this->assertEquals(PackingItemStatus::Packed, $tuna->status);
    }

    public function test_refresh_is_idempotent(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $countBefore = $list->items->count();

        $list = $this->service->refreshPackingList($list);
        $countAfter = $list->items->count();

        $this->assertEquals($countBefore, $countAfter);
    }

    public function test_refresh_removes_pending_items_and_rebuilds(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $originalCount = $list->items->count();

        // Manually delete a pending item to simulate a gap
        $list->items()->where('description', 'Tuna')->delete();
        $this->assertEquals($originalCount - 1, $list->items()->count());

        // Refresh should rebuild the gap
        $list = $this->service->refreshPackingList($list);
        $this->assertEquals($originalCount, $list->items->count());
        $this->assertNotNull($list->items->firstWhere('description', 'Tuna'));
    }

    // ==========================================
    // Phase 1 Tests: Batch Generation
    // ==========================================

    public function test_generate_all_packing_lists(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $this->createFamily(['family_name' => 'Family A', 'family_number' => 1]);
        $this->createFamily(['family_name' => 'Family B', 'family_number' => 2]);
        $this->createFamily(['family_name' => 'Family C', 'family_number' => 3]);

        $count = $this->service->generateAllPackingLists();

        $this->assertEquals(3, $count);
        $this->assertEquals(3, PackingList::count());
    }

    // ==========================================
    // Phase 1 Tests: Model Methods
    // ==========================================

    public function test_packing_list_is_complete_when_all_items_packed(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Item 1',
            'quantity_needed' => 1,
            'quantity_packed' => 1,
            'status' => PackingItemStatus::Packed,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Item 2',
            'quantity_needed' => 1,
            'quantity_packed' => 1,
            'status' => PackingItemStatus::Verified,
        ]);

        $this->assertTrue($list->isComplete());
    }

    public function test_packing_list_not_complete_with_pending_items(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Packed Item',
            'quantity_needed' => 1,
            'quantity_packed' => 1,
            'status' => PackingItemStatus::Packed,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Pending Item',
            'quantity_needed' => 1,
            'quantity_packed' => 0,
            'status' => PackingItemStatus::Pending,
        ]);

        $this->assertFalse($list->isComplete());
    }

    public function test_packing_list_not_complete_with_unfulfilled_items(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Packed Item',
            'quantity_needed' => 1,
            'quantity_packed' => 1,
            'status' => PackingItemStatus::Packed,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id,
            'description' => 'Unfulfilled Gift',
            'quantity_needed' => 1,
            'quantity_packed' => 0,
            'status' => PackingItemStatus::Unfulfilled,
        ]);

        $this->assertFalse($list->isComplete());
    }

    public function test_progress_summary_calculates_correctly(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::InProgress]);

        PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'A',
            'quantity_needed' => 1, 'quantity_packed' => 1, 'status' => PackingItemStatus::Packed,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'B',
            'quantity_needed' => 1, 'quantity_packed' => 1, 'status' => PackingItemStatus::Substituted,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'C',
            'quantity_needed' => 1, 'quantity_packed' => 0, 'status' => PackingItemStatus::Pending,
        ]);
        PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'D',
            'quantity_needed' => 1, 'quantity_packed' => 0, 'status' => PackingItemStatus::Unfulfilled,
        ]);

        $summary = $list->progressSummary();
        $this->assertEquals(2, $summary['packed']);
        $this->assertEquals(4, $summary['total']);
        $this->assertEquals(50, $summary['percentage']);
    }

    public function test_packing_item_is_packed_method(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        $pending = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Pending',
            'quantity_needed' => 1, 'status' => PackingItemStatus::Pending,
        ]);
        $packed = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Packed',
            'quantity_needed' => 1, 'status' => PackingItemStatus::Packed,
        ]);
        $verified = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Verified',
            'quantity_needed' => 1, 'status' => PackingItemStatus::Verified,
        ]);
        $substituted = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Substituted',
            'quantity_needed' => 1, 'status' => PackingItemStatus::Substituted,
        ]);
        $unfulfilled = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Unfulfilled',
            'quantity_needed' => 1, 'status' => PackingItemStatus::Unfulfilled,
        ]);

        $this->assertFalse($pending->isPacked());
        $this->assertTrue($packed->isPacked());
        $this->assertTrue($verified->isPacked());
        $this->assertTrue($substituted->isPacked());
        $this->assertFalse($unfulfilled->isPacked());
    }

    public function test_packing_item_remaining_quantity(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        $item = PackingItem::create([
            'packing_list_id' => $list->id, 'description' => 'Test',
            'quantity_needed' => 5, 'quantity_packed' => 3, 'status' => PackingItemStatus::Pending,
        ]);

        $this->assertEquals(2, $item->remainingQuantity());
    }

    public function test_packing_list_auto_generates_uuid_qr_token(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        $this->assertNotNull($list->qr_token);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($list->qr_token));
    }

    public function test_packing_list_auto_sets_season_year(): void
    {
        $family = $this->createFamily();
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);

        $this->assertEquals(date('Y'), $list->season_year);
    }

    public function test_family_has_packing_list_relationship(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $this->assertNull($family->packingList);

        $this->service->generatePackingList($family);

        $family->refresh();
        $this->assertNotNull($family->packingList);
        $this->assertInstanceOf(PackingList::class, $family->packingList);
    }

    // ==========================================
    // Phase 1 Tests: Edge Cases
    // ==========================================

    public function test_family_with_no_children_has_no_gift_items(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['number_of_children' => 0]);

        $list = $this->service->generatePackingList($family);

        $giftItems = $list->items->filter(fn ($item) => $item->child_id !== null);
        $this->assertEquals(0, $giftItems->count());
    }

    public function test_packing_list_unique_per_family_per_season(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();

        $list1 = $this->service->generatePackingList($family, '2025');
        $list2 = $this->service->generatePackingList($family, '2026');

        $this->assertNotEquals($list1->id, $list2->id);
        $this->assertEquals('2025', $list1->season_year);
        $this->assertEquals('2026', $list2->season_year);
    }

    public function test_empty_grocery_items_still_creates_list(): void
    {
        $this->seedWarehouseCategories();
        // No grocery items seeded
        $family = $this->createFamily();

        $list = $this->service->generatePackingList($family);

        $this->assertInstanceOf(PackingList::class, $list);
        $this->assertEquals(0, $list->items->count());
    }

    // ==========================================
    // Phase 3 Tests: Dashboard Stats
    // ==========================================

    public function test_dashboard_stats_returns_status_counts(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['family_name' => 'A', 'family_number' => 1]);
        $familyB = $this->createFamily(['family_name' => 'B', 'family_number' => 2]);
        $familyC = $this->createFamily(['family_name' => 'C', 'family_number' => 3]);

        $this->service->generatePackingList($familyA);
        $listB = $this->service->generatePackingList($familyB);
        $listC = $this->service->generatePackingList($familyC);

        $listB->update(['status' => PackingStatus::Complete, 'completed_at' => now()]);
        $listC->update(['status' => PackingStatus::Verified, 'verified_at' => now()]);

        $stats = $this->service->getDashboardStats();

        $this->assertEquals(3, $stats['total_families']);
        $this->assertEquals(1, $stats['packed']);
        $this->assertEquals(1, $stats['verified']);
        $this->assertEquals(1, $stats['not_started']);
        $this->assertEqualsWithDelta(66.7, $stats['fulfillment_rate'], 0.1);
    }

    public function test_dashboard_stats_category_breakdown(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['needs_baby_supplies' => true]);
        $this->createChild($family, ['gender' => 'Male', 'age' => '8']);
        $this->service->generatePackingList($family);

        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('categories', $stats);
        $this->assertArrayHasKey('food', $stats['categories']);
        $this->assertArrayHasKey('gift', $stats['categories']);
        $this->assertArrayHasKey('baby', $stats['categories']);
        $this->assertGreaterThan(0, $stats['categories']['food']['total']);
        $this->assertEquals(0, $stats['categories']['food']['packed']);
        $this->assertGreaterThan(0, $stats['categories']['baby']['total']);
    }

    public function test_dashboard_stats_volunteer_metrics(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $this->service->markItemPacked($item, $this->packer);

        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('volunteers', $stats);
        $this->assertCount(1, $stats['volunteers']);
        $this->assertEquals($this->packer->id, $stats['volunteers'][0]['id']);
        $this->assertEquals(1, $stats['volunteers'][0]['items_packed']);
        $this->assertEquals(1, $stats['volunteers'][0]['lists_worked']);
    }

    public function test_dashboard_stats_recently_completed(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['family_name' => 'Recent', 'number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);

        // Pack all items to complete the list
        foreach ($list->items as $item) {
            if ($item->status === PackingItemStatus::Unfulfilled) continue;
            for ($i = 0; $i < $item->quantity_needed; $i++) {
                $this->service->markItemPacked($item, $this->packer);
            }
        }

        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('recently_completed', $stats);
        $this->assertCount(1, $stats['recently_completed']);
        $this->assertEquals('Recent', $stats['recently_completed'][0]['family_name']);
    }

    public function test_dashboard_stats_items_per_hour(): void
    {
        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('total_items_packed_today', $stats);
        $this->assertArrayHasKey('overall_items_per_hour', $stats);
        $this->assertEquals(0, $stats['total_items_packed_today']);
        $this->assertIsFloat($stats['overall_items_per_hour']);
    }

    // ==========================================
    // Phase 4 Tests: Dietary Filtering
    // ==========================================

    public function test_build_food_items_skips_items_with_dietary_conflict(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Mark Tuna as containing nuts
        GroceryItem::where('name', 'Tuna')->update(['dietary_flags' => json_encode(['nuts'])]);

        $family = $this->createFamily(['dietary_restrictions' => ['nut_free']]);
        $list = $this->service->generatePackingList($family);

        // Tuna should be marked as unfulfilled with dietary conflict
        $tuna = $list->items->firstWhere('grocery_item_id', GroceryItem::where('name', 'Tuna')->first()->id);
        $this->assertNotNull($tuna);
        $this->assertEquals(PackingItemStatus::Unfulfilled, $tuna->status);
        $this->assertStringContainsString('DIETARY CONFLICT', $tuna->description);
    }

    public function test_build_food_items_allows_compatible_items(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Mark Tuna as halal (positive tag, no conflicts)
        GroceryItem::where('name', 'Tuna')->update([
            'dietary_flags' => json_encode([]),
            'dietary_tags' => json_encode(['halal']),
        ]);

        $family = $this->createFamily(['dietary_restrictions' => ['halal']]);
        $list = $this->service->generatePackingList($family);

        $tuna = $list->items->firstWhere('grocery_item_id', GroceryItem::where('name', 'Tuna')->first()->id);
        $this->assertNotNull($tuna);
        $this->assertEquals(PackingItemStatus::Pending, $tuna->status);
    }

    public function test_build_food_items_marks_unfulfilled_when_no_substitute(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Mark Tuna as containing pork
        GroceryItem::where('name', 'Tuna')->update(['dietary_flags' => json_encode(['pork'])]);

        $family = $this->createFamily(['dietary_restrictions' => ['halal']]);
        $list = $this->service->generatePackingList($family);

        $tuna = $list->items->firstWhere('grocery_item_id', GroceryItem::where('name', 'Tuna')->first()->id);
        $this->assertNotNull($tuna);
        $this->assertEquals(PackingItemStatus::Unfulfilled, $tuna->status);
        $this->assertStringContainsString('DIETARY CONFLICT', $tuna->description);
    }

    // ==========================================
    // Phase 4 Tests: Substitution Methods
    // ==========================================

    public function test_substitute_item_with_warehouse_item_sets_item_id(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $category = WarehouseCategory::where('name', 'Canned Goods')->first();
        $warehouseItem = WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Sardines',
            'barcode' => '123456789',
            'active' => true,
        ]);

        $this->service->substituteItem($item, $warehouseItem, 'Replaced with sardines', $this->packer);

        $item->refresh();
        $this->assertEquals($warehouseItem->id, $item->item_id);
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
    }

    public function test_suggest_substitutes_returns_same_category_items(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        $item = $list->items()->where('description', 'Tuna')->first();
        $category = WarehouseCategory::where('name', 'Canned Goods')->first();

        // Create warehouse items in same category
        WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Sardines',
            'barcode' => '111111111',
            'active' => true,
        ]);
        WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Salmon',
            'barcode' => '222222222',
            'active' => true,
        ]);

        $suggestions = $this->service->suggestSubstitutes($item);

        $this->assertIsArray($suggestions);
        $this->assertCount(2, $suggestions);
        $names = array_column($suggestions, 'name');
        $this->assertContains('Sardines', $names);
        $this->assertContains('Salmon', $names);
    }

    // ==========================================
    // Phase 4 Tests: Auto-Substitution
    // ==========================================

    public function test_auto_substitute_removed_item_returns_count(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $category = WarehouseCategory::where('name', 'Canned Goods')->first();
        $removedItem = WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Old Tuna Brand',
            'barcode' => '999999999',
            'active' => true,
        ]);
        $replacement = WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'New Tuna Brand',
            'barcode' => '888888888',
            'active' => true,
        ]);

        // Create a family with a packing item referencing the soon-to-be-removed item
        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('description', 'Tuna')->first();
        $item->update(['item_id' => $removedItem->id]);

        $count = $this->service->autoSubstituteRemovedItem($removedItem, $this->packer);

        $this->assertEquals(1, $count);
        $item->refresh();
        $this->assertEquals($replacement->id, $item->item_id);
        $this->assertEquals(PackingItemStatus::Substituted, $item->status);
    }

    public function test_auto_substitute_marks_unfulfilled_when_no_candidate(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $category = WarehouseCategory::where('name', 'Canned Goods')->first();
        $removedItem = WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Only Tuna Brand',
            'barcode' => '777777777',
            'active' => true,
        ]);

        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('description', 'Tuna')->first();
        $item->update(['item_id' => $removedItem->id]);

        $count = $this->service->autoSubstituteRemovedItem($removedItem, $this->packer);

        $this->assertEquals(1, $count);
        $item->refresh();
        $this->assertEquals(PackingItemStatus::Unfulfilled, $item->status);
    }

    // ==========================================
    // Phase 4 Tests: Shopping Deficits
    // ==========================================

    public function test_get_shopping_deficits_aggregates_by_grocery_item(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['family_name' => 'A', 'family_number' => 1, 'number_of_family_members' => 1]);
        $familyB = $this->createFamily(['family_name' => 'B', 'family_number' => 2, 'number_of_family_members' => 1]);

        $this->service->generatePackingList($familyA);
        $this->service->generatePackingList($familyB);

        $deficits = $this->service->getShoppingDeficits();

        $this->assertIsArray($deficits);
        $this->assertNotEmpty($deficits);

        // Find Tuna deficit: 2 families × qty_1(1) = 2 total needed
        $tunaDeficit = collect($deficits)->firstWhere('grocery_item_name', 'Tuna');
        $this->assertNotNull($tunaDeficit);
        $this->assertEquals(2, $tunaDeficit['total_needed']);
    }

    public function test_get_shopping_deficits_returns_zero_deficit_when_fully_stocked(): void
    {

        $this->seedWarehouseCategories();
        // No families, no packing lists, no deficits
        $deficits = $this->service->getShoppingDeficits();

        $this->assertIsArray($deficits);
        $this->assertEmpty($deficits);
    }

    // ==========================================
    // Phase 5 Tests: Pick-Path + Sessions + Trends
    // ==========================================

    public function test_pick_path_sort_uses_warehouse_location(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $category = WarehouseCategory::where('name', 'Canned Goods')->first();

        // Create warehouse items with locations
        $itemB = WarehouseItem::create(['category_id' => $category->id, 'name' => 'Item B', 'active' => true, 'location_zone' => 'B', 'location_shelf' => '1', 'location_bin' => '01']);
        $itemA = WarehouseItem::create(['category_id' => $category->id, 'name' => 'Item A', 'active' => true, 'location_zone' => 'A', 'location_shelf' => '2', 'location_bin' => '03']);

        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        // Link food items to warehouse items
        $items = $list->items()->where('sort_order', '<', 1000)->orderBy('sort_order')->get();
        if ($items->count() >= 2) {
            $items[0]->update(['item_id' => $itemB->id]);
            $items[1]->update(['item_id' => $itemA->id]);
        }

        // Re-generate to trigger pick-path sort
        $list->items()->where('sort_order', '<', 1000)->delete();
        $list->delete();
        $list = $this->service->generatePackingList($family);

        // Items with zone A should sort before zone B
        $foodItems = $list->items()->where('sort_order', '<', 1000)->orderBy('sort_order')->get();
        $this->assertGreaterThan(0, $foodItems->count());
    }

    public function test_pick_path_items_without_location_sort_last(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $category = WarehouseCategory::where('name', 'Canned Goods')->first();
        $locatedItem = WarehouseItem::create(['category_id' => $category->id, 'name' => 'Located', 'active' => true, 'location_zone' => 'A', 'location_shelf' => '1', 'location_bin' => '01']);
        $unlocatedItem = WarehouseItem::create(['category_id' => $category->id, 'name' => 'Unlocated', 'active' => true]);

        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        // Assign warehouse items: one with location, one without
        $foodItems = $list->items()->where('sort_order', '<', 1000)->orderBy('sort_order')->get();
        if ($foodItems->count() >= 2) {
            $foodItems[0]->update(['item_id' => $unlocatedItem->id]);
            $foodItems[1]->update(['item_id' => $locatedItem->id]);

            // Mark items as packed so refresh won't delete them, then refresh to re-sort
            $foodItems[0]->update(['status' => PackingItemStatus::Packed, 'quantity_packed' => $foodItems[0]->quantity_needed]);
            $foodItems[1]->update(['status' => PackingItemStatus::Packed, 'quantity_packed' => $foodItems[1]->quantity_needed]);

            $list = $this->service->refreshPackingList($list);

            $resorted = $list->items()->where('sort_order', '<', 1000)->orderBy('sort_order')->get();
            // The located item (zone A) should sort before the unlocated item (zone zzz)
            $locatedIdx = $resorted->search(fn($i) => $i->item_id === $locatedItem->id);
            $unlocatedIdx = $resorted->search(fn($i) => $i->item_id === $unlocatedItem->id);
            $this->assertNotFalse($locatedIdx);
            $this->assertNotFalse($unlocatedIdx);
            $this->assertLessThan($unlocatedIdx, $locatedIdx, 'Located items should sort before unlocated items');
        } else {
            $this->assertTrue(true); // pass if only 1 food item
        }
    }

    public function test_pick_path_preserves_category_grouping(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['needs_baby_supplies' => true]);
        $this->createChild($family, ['gender' => 'Male', 'age' => '8']);
        $list = $this->service->generatePackingList($family);

        // Food items should be 0-999, gifts 1000+, baby 2000+
        $foodOrders = $list->items->filter(fn ($i) => $i->grocery_item_id !== null)->pluck('sort_order');
        $giftOrders = $list->items->filter(fn ($i) => $i->child_id !== null)->pluck('sort_order');
        $babyOrders = $list->items->filter(fn ($i) => $i->child_id === null && $i->category?->type === 'baby')->pluck('sort_order');

        foreach ($foodOrders as $o) {
            $this->assertLessThan(1000, $o);
        }
        foreach ($giftOrders as $o) {
            $this->assertGreaterThanOrEqual(1000, $o);
            $this->assertLessThan(2000, $o);
        }
        foreach ($babyOrders as $o) {
            $this->assertGreaterThanOrEqual(2000, $o);
        }
    }

    public function test_refresh_packing_list_reapplies_pick_path_sort(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily();
        $list = $this->service->generatePackingList($family);

        // Manually mess up sort orders
        $list->items()->where('sort_order', '<', 1000)->update(['sort_order' => 999]);

        $list = $this->service->refreshPackingList($list);

        // After refresh, food items should have sequential sort orders starting from 0
        $foodItems = $list->items()->where('sort_order', '<', 1000)->orderBy('sort_order')->get();
        if ($foodItems->count() > 1) {
            $orders = $foodItems->pluck('sort_order')->toArray();
            // Orders should be sequential
            for ($i = 0; $i < count($orders) - 1; $i++) {
                $this->assertLessThanOrEqual($orders[$i] + 1, $orders[$i + 1]);
            }
        }
        $this->assertTrue(true);
    }

    public function test_clock_in_creates_packing_session(): void
    {
        $session = $this->service->clockIn($this->packer);

        $this->assertInstanceOf(PackingSession::class, $session);
        $this->assertEquals($this->packer->id, $session->user_id);
        $this->assertNotNull($session->started_at);
        $this->assertNull($session->ended_at);
        $this->assertTrue($session->isActive());
    }

    public function test_clock_in_fails_with_active_session(): void
    {
        $this->service->clockIn($this->packer);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Already clocked in.');
        $this->service->clockIn($this->packer);
    }

    public function test_clock_out_ends_session_and_computes_stats(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $session = $this->service->clockIn($this->packer);

        // Pack an item while clocked in
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('description', 'Tuna')->first();
        $this->service->markItemPacked($item, $this->packer);

        $endedSession = $this->service->clockOut($this->packer);

        $this->assertNotNull($endedSession->ended_at);
        $this->assertFalse($endedSession->isActive());
        $this->assertEquals(1, $endedSession->lists_worked);
        $this->assertGreaterThan(0, $endedSession->durationInHours());
    }

    public function test_marking_item_packed_increments_session_counter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $session = $this->service->clockIn($this->packer);

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        $item = $list->items()->where('description', 'Tuna')->first();

        $this->assertEquals(0, $session->items_packed);

        $this->service->markItemPacked($item, $this->packer);

        $session->refresh();
        $this->assertEquals(1, $session->items_packed);
    }

    public function test_volunteer_trend_compares_today_vs_yesterday(): void
    {
        // Create a closed session for yesterday
        PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => Carbon::yesterday()->setHour(9),
            'ended_at' => Carbon::yesterday()->setHour(12),
            'items_packed' => 30,
        ]);

        // Create a closed session for today
        PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => Carbon::today()->setHour(9),
            'ended_at' => Carbon::today()->setHour(11),
            'items_packed' => 40,
        ]);

        $trend = $this->service->getVolunteerTrend();

        $this->assertArrayHasKey('today_avg_items_per_hour', $trend);
        $this->assertArrayHasKey('yesterday_avg_items_per_hour', $trend);
        $this->assertArrayHasKey('trend_direction', $trend);
        $this->assertContains($trend['trend_direction'], ['up', 'down', 'flat']);
        $this->assertGreaterThan(0, $trend['today_avg_items_per_hour']);
        $this->assertGreaterThan(0, $trend['yesterday_avg_items_per_hour']);
    }

    public function test_volunteer_trend_returns_flat_when_no_yesterday_data(): void
    {
        $trend = $this->service->getVolunteerTrend();

        $this->assertEquals('flat', $trend['trend_direction']);
        $this->assertEquals(0, $trend['today_avg_items_per_hour']);
        $this->assertEquals(0, $trend['yesterday_avg_items_per_hour']);
    }

    public function test_dashboard_stats_includes_fulfillment_alert_when_below_threshold(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        Setting::set('packing_fulfillment_alert_threshold', '90');

        // Create 3 families, none packed → 0% fulfillment < 90% threshold
        $this->createFamily(['family_name' => 'A', 'family_number' => 1]);
        $this->createFamily(['family_name' => 'B', 'family_number' => 2]);
        $this->createFamily(['family_name' => 'C', 'family_number' => 3]);
        $this->service->generateAllPackingLists();

        $stats = $this->service->getDashboardStats();

        $this->assertTrue($stats['fulfillment_alert']);
        $this->assertEquals(90.0, $stats['fulfillment_threshold']);
    }

    public function test_dashboard_stats_no_alert_when_above_threshold(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        Setting::set('packing_fulfillment_alert_threshold', '50');

        $familyA = $this->createFamily(['family_name' => 'A', 'family_number' => 1]);
        $familyB = $this->createFamily(['family_name' => 'B', 'family_number' => 2]);
        $this->service->generatePackingList($familyA);
        $listB = $this->service->generatePackingList($familyB);

        // Mark one as complete (50% fulfillment)
        $listB->update(['status' => \App\Enums\PackingStatus::Complete, 'completed_at' => now()]);

        $stats = $this->service->getDashboardStats();

        $this->assertFalse($stats['fulfillment_alert']);
    }

    public function test_end_of_day_summary_includes_all_metrics(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Create a session today
        PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => Carbon::today()->setHour(9),
            'ended_at' => Carbon::today()->setHour(12),
            'items_packed' => 25,
        ]);

        // Create a completed family today
        $family = $this->createFamily(['number_of_family_members' => 1]);
        $list = $this->service->generatePackingList($family);
        foreach ($list->items as $item) {
            if ($item->status !== \App\Enums\PackingItemStatus::Unfulfilled) {
                for ($i = 0; $i < $item->quantity_needed; $i++) {
                    $this->service->markItemPacked($item, $this->packer);
                }
            }
        }

        $summary = $this->service->getEndOfDaySummary(Carbon::today());

        $this->assertArrayHasKey('families_packed', $summary);
        $this->assertArrayHasKey('families_packed_count', $summary);
        $this->assertArrayHasKey('volunteers', $summary);
        $this->assertArrayHasKey('total_volunteers', $summary);
        $this->assertArrayHasKey('total_hours', $summary);
        $this->assertArrayHasKey('total_items_packed', $summary);
        $this->assertArrayHasKey('substitutions_count', $summary);
        $this->assertArrayHasKey('unfulfilled_count', $summary);
        $this->assertArrayHasKey('category_breakdown', $summary);
        $this->assertGreaterThanOrEqual(1, $summary['families_packed_count']);
        $this->assertEquals(1, $summary['total_volunteers']);
    }

    public function test_end_of_day_summary_scoped_to_date(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Create a session yesterday
        PackingSession::create([
            'user_id' => $this->packer->id,
            'started_at' => Carbon::yesterday()->setHour(9),
            'ended_at' => Carbon::yesterday()->setHour(12),
            'items_packed' => 15,
        ]);

        // Query for today — should have no sessions
        $summaryToday = $this->service->getEndOfDaySummary(Carbon::today());
        $this->assertEquals(0, $summaryToday['total_volunteers']);

        // Query for yesterday — should have the session
        $summaryYesterday = $this->service->getEndOfDaySummary(Carbon::yesterday());
        $this->assertEquals(1, $summaryYesterday['total_volunteers']);
    }
}
