<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Enums\TransactionType;
use App\Models\Child;
use App\Models\Family;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarehouseExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;
    private User $familyUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinator = User::create([
            'username' => 'coord', 'first_name' => 'C', 'last_name' => 'O',
            'password' => 'password', 'permission' => 8, 'position' => 'System Engineer',
        ]);
        $this->familyUser = User::create([
            'username' => 'family', 'first_name' => 'F', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);
    }

    public function test_warehouse_index_loads(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('warehouse.index'));
        $response->assertOk();
    }

    public function test_receive_redirects_to_kiosk(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('warehouse.receive'));
        $response->assertRedirect(route('warehouse.kiosk'));
    }

    public function test_gifts_intake_page_loads(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('warehouse.gifts-intake'));
        $response->assertOk();
    }

    public function test_inventory_page_loads(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('warehouse.inventory'));
        $response->assertOk();
    }

    public function test_kiosk_page_loads(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('warehouse.kiosk'));
        $response->assertOk();
    }

    public function test_warehouse_transaction_can_be_created(): void
    {
        $category = WarehouseCategory::create(['name' => 'Food Box', 'type' => 'food', 'unit' => 'box']);

        $response = $this->actingAs($this->coordinator)->post(route('warehouse.store'), [
            'category_id' => $category->id,
            'quantity' => 2,
            'source' => 'Donation',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('warehouse_transactions', [
            'category_id' => $category->id,
            'quantity' => 2,
        ]);
    }

    public function test_barcode_lookup_returns_json(): void
    {
        $response = $this->actingAs($this->coordinator)->getJson(route('warehouse.barcode.lookup', 'NONEXISTENT'));
        $response->assertOk();
        $response->assertJsonFragment(['found' => false]);
    }

    public function test_child_gifts_detail_shows_transactions(): void
    {
        $family = Family::create([
            'family_name' => 'Detail Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);
        $child = Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => 7,
        ]);
        $category = WarehouseCategory::create(['name' => 'Gift - Boy 6-12', 'type' => 'gift', 'unit' => 'item']);
        WarehouseTransaction::create([
            'category_id' => $category->id,
            'family_id' => $family->id,
            'child_id' => $child->id,
            'transaction_type' => TransactionType::In,
            'quantity' => 1,
            'source' => 'Gift Drop-off',
            'notes' => 'Toy truck',
        ]);

        $response = $this->actingAs($this->coordinator)->get(route('warehouse.child.gifts', $child));
        $response->assertOk();
        $response->assertSee('Toy truck');
    }

    public function test_family_user_cannot_access_warehouse(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('warehouse.index'));
        $response->assertForbidden();
    }

    public function test_inventory_shows_category_totals(): void
    {
        $category = WarehouseCategory::create(['name' => 'Canned Food', 'type' => 'food', 'unit' => 'can']);
        $family = Family::create([
            'family_name' => 'Inv Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);
        WarehouseTransaction::create([
            'category_id' => $category->id,
            'family_id' => $family->id,
            'transaction_type' => TransactionType::In,
            'quantity' => 10,
            'source' => 'Donation',
        ]);

        $response = $this->actingAs($this->coordinator)->get(route('warehouse.inventory'));
        $response->assertOk();
        $response->assertSee('Canned Food');
    }

    public function test_remove_item_rolls_back_packing_changes_when_deactivation_fails(): void
    {
        $category = WarehouseCategory::create(['name' => 'Canned Goods', 'type' => 'food', 'unit' => 'can']);
        $item = WarehouseItem::create([
            'category_id' => $category->id,
            'name' => 'Only Beans',
            'active' => true,
        ]);
        $family = Family::create([
            'family_name' => 'Rollback Inv Test', 'family_number' => 2,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-5678', 'address' => '2 Main St',
        ]);
        $list = PackingList::create(['family_id' => $family->id, 'status' => PackingStatus::Pending]);
        $packingItem = PackingItem::create([
            'packing_list_id' => $list->id,
            'category_id' => $category->id,
            'item_id' => $item->id,
            'description' => 'Only Beans',
            'quantity_needed' => 1,
            'status' => PackingItemStatus::Pending,
        ]);

        DB::unprepared("
            CREATE TRIGGER fail_warehouse_item_deactivation
            BEFORE UPDATE ON warehouse_items
            WHEN NEW.active = 0
            BEGIN
                SELECT RAISE(ABORT, 'forced warehouse item failure');
            END;
        ");

        try {
            $response = $this->actingAs($this->coordinator)
                ->delete(route('warehouse.item.remove', $item));
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_warehouse_item_deactivation');
        }

        $response->assertServerError();
        $packingItem->refresh();
        $item->refresh();

        $this->assertTrue($item->active);
        $this->assertEquals(PackingItemStatus::Pending, $packingItem->status);
        $this->assertSame('Only Beans', $packingItem->description);
        $this->assertNull($packingItem->packed_by);
    }
}
