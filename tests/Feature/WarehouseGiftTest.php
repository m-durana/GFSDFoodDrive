<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseGiftTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'warehouse_santa',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Warehouse Test Family',
            'family_number' => 100,
            'address' => '100 Test Ave',
            'phone1' => '360-555-1000',
            'number_of_adults' => 2,
            'number_of_children' => 1,
            'number_of_family_members' => 3,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 1,
            'tweens' => 0,
            'teenagers' => 0,
        ]);

        $this->child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male',
            'age' => '8',
            'gift_level' => GiftLevel::None,
        ]);

        // Seed required warehouse categories
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    public function test_confirm_gift_dropoff_creates_per_item_transactions(): void
    {
        $service = app(WarehouseService::class);

        $items = [
            ['name' => 'LEGO set', 'barcode' => '123456789'],
            ['name' => 'Winter jacket', 'barcode' => null],
            ['name' => 'Board game', 'barcode' => '987654321'],
        ];

        $service->confirmGiftDropoff($this->child, $this->santa, null, $items);

        // Should create one transaction per item
        $transactions = WarehouseTransaction::where('child_id', $this->child->id)->get();
        $this->assertCount(3, $transactions);

        // Each transaction should have the item name in notes
        $notes = $transactions->pluck('notes')->sort()->values()->toArray();
        $this->assertEquals(['Board game', 'LEGO set', 'Winter jacket'], $notes);

        // Child should be marked as dropped off
        $this->child->refresh();
        $this->assertTrue($this->child->gift_dropped_off);
        $this->assertGreaterThanOrEqual(GiftLevel::Moderate->value, $this->child->gift_level->value);
    }

    public function test_confirm_gift_dropoff_without_items_creates_single_transaction(): void
    {
        $service = app(WarehouseService::class);
        $service->confirmGiftDropoff($this->child, $this->santa, 'Misc gifts');

        $transactions = WarehouseTransaction::where('child_id', $this->child->id)->get();
        $this->assertCount(1, $transactions);
        $this->assertEquals('Misc gifts', $transactions->first()->notes);
    }

    public function test_computed_gifts_received_from_transactions(): void
    {
        $category = WarehouseCategory::where('name', 'Gift - Boy 6-12')->first();

        WarehouseTransaction::create([
            'category_id' => $category->id,
            'family_id' => $this->family->id,
            'child_id' => $this->child->id,
            'transaction_type' => 'in',
            'quantity' => 1,
            'source' => 'Gift Drop-off',
            'notes' => 'LEGO set',
            'scanned_by' => $this->santa->id,
        ]);

        WarehouseTransaction::create([
            'category_id' => $category->id,
            'family_id' => $this->family->id,
            'child_id' => $this->child->id,
            'transaction_type' => 'in',
            'quantity' => 1,
            'source' => 'Gift Drop-off',
            'notes' => 'Board game',
            'scanned_by' => $this->santa->id,
        ]);

        $computed = $this->child->getComputedGiftsReceived();
        $this->assertStringContainsString('LEGO set', $computed);
        $this->assertStringContainsString('Board game', $computed);
    }

    public function test_computed_gifts_received_falls_back_to_field(): void
    {
        $this->child->update(['gifts_received' => 'Manual entry']);
        $computed = $this->child->getComputedGiftsReceived();
        $this->assertEquals('Manual entry', $computed);
    }

    public function test_child_gifts_detail_route_loads(): void
    {
        $response = $this->actingAs($this->santa)->get("/warehouse/child/{$this->child->id}/gifts");
        $response->assertStatus(200);
        $response->assertSee('Gift History');
    }

    public function test_gift_dropoff_endpoint_accepts_items_array(): void
    {
        $response = $this->actingAs($this->santa)->postJson(
            "/warehouse/gift-dropoff/{$this->child->id}",
            [
                'gifts_received' => 'Test items',
                'items' => [
                    ['name' => 'Toy car', 'barcode' => null],
                    ['name' => 'Puzzle', 'barcode' => '111222333'],
                ],
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $transactions = WarehouseTransaction::where('child_id', $this->child->id)->get();
        $this->assertCount(2, $transactions);
    }

    public function test_gift_dropoff_sets_gift_dropped_off(): void
    {
        $this->actingAs($this->santa)->postJson(
            "/warehouse/gift-dropoff/{$this->child->id}",
            ['gifts_received' => 'Some gift']
        );

        $this->child->refresh();
        $this->assertTrue($this->child->gift_dropped_off);
    }
}
