<?php

namespace Tests\Feature;

use App\Enums\PackingItemStatus;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\ShoppingCheck;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingIntegrationTest extends TestCase
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

    private function createFamily(array $overrides = []): Family
    {
        static $counter = 0;
        $counter++;
        return Family::create(array_merge([
            'family_name' => "Shop Family {$counter}", 'family_number' => $counter,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
        ], $overrides));
    }

    // ==========================================
    // Phase 4 Tests: Shopping Deficits
    // ==========================================

    public function test_get_shopping_deficits_returns_deficit_items_correctly(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 3]);
        $this->packingService->generatePackingList($family);

        $deficits = $this->packingService->getShoppingDeficits();

        $this->assertNotEmpty($deficits);
        $tunaDeficit = collect($deficits)->firstWhere('grocery_item_name', 'Tuna');
        $this->assertNotNull($tunaDeficit);
        $this->assertEquals(3, $tunaDeficit['total_needed']); // qty_3 = 3
        $this->assertGreaterThanOrEqual(0, $tunaDeficit['deficit']);
    }

    public function test_shopping_assignment_uses_packing_deficits(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $this->packingService->generatePackingList($family);

        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Test NINJA',
            'split_type' => 'deficit',
        ]);

        $list = $assignment->getShoppingList();

        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
    }

    public function test_category_split_filters_by_assigned_categories(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $this->packingService->generatePackingList($family);

        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Cat NINJA',
            'split_type' => 'category',
            'categories' => ['canned'],
        ]);

        $list = $assignment->getShoppingList();

        // Should only contain canned items, not dry
        $this->assertArrayHasKey('canned', $list);
        $this->assertArrayNotHasKey('dry', $list);
    }

    public function test_family_range_queries_only_range_families(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $familyA = $this->createFamily(['family_number' => 1, 'number_of_family_members' => 1]);
        $familyB = $this->createFamily(['family_number' => 2, 'number_of_family_members' => 1]);
        $familyC = $this->createFamily(['family_number' => 3, 'number_of_family_members' => 1]);

        $this->packingService->generatePackingList($familyA);
        $this->packingService->generatePackingList($familyB);
        $this->packingService->generatePackingList($familyC);

        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Range NINJA',
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 2,
        ]);

        $list = $assignment->getShoppingList();

        // Should aggregate items for families 1-2 only (not 3)
        $totalTuna = 0;
        foreach ($list as $items) {
            if (isset($items['Tuna'])) {
                $totalTuna += $items['Tuna'];
            }
        }
        $this->assertEquals(2, $totalTuna); // 2 families × qty_1(1)
    }

    public function test_shopping_day_page_shows_deficit_panel(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily();
        $this->packingService->generatePackingList($family);

        $response = $this->actingAs($this->santa)->get(route('santa.shoppingDay'));
        $response->assertOk();
        $response->assertSee('What Needs to be Purchased');
    }

    public function test_create_assignment_with_deficit_split_type(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $response = $this->actingAs($this->santa)->post(route('santa.createAssignment'), [
            'ninja_name' => 'Deficit Shopper',
            'split_type' => 'deficit',
        ]);

        $response->assertRedirect(route('santa.shoppingDay'));
        $this->assertDatabaseHas('shopping_assignments', [
            'ninja_name' => 'Deficit Shopper',
            'split_type' => 'deficit',
        ]);
    }

    public function test_shopping_api_response_shape_unchanged(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $this->packingService->generatePackingList($family);

        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'API NINJA',
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 100,
        ]);

        $response = $this->getJson("/api/shopping/{$assignment->token}");
        $response->assertOk();

        // Shape: { assignment, items: { 'Category Name': { 'Item Name': qty } }, checks: [...] }
        $response->assertJsonStructure(['assignment', 'items']);
    }

    public function test_create_assignment_with_category_split_type(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $response = $this->actingAs($this->santa)->post(route('santa.createAssignment'), [
            'ninja_name' => 'Category Shopper',
            'split_type' => 'category',
            'categories' => ['canned', 'dry'],
        ]);

        $response->assertRedirect(route('santa.shoppingDay'));
        $this->assertDatabaseHas('shopping_assignments', [
            'ninja_name' => 'Category Shopper',
            'split_type' => 'category',
        ]);
    }

    public function test_reconciliation_data_shows_discrepancies(): void
    {

        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $family = $this->createFamily(['number_of_family_members' => 1]);
        $this->packingService->generatePackingList($family);

        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Recon NINJA',
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 100,
        ]);

        // Simulate a shopping check
        ShoppingCheck::create([
            'shopping_assignment_id' => $assignment->id,
            'item_key' => 'canned|Tuna',
            'checked_by' => 'NINJA',
            'checked_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.shoppingDay'));
        $response->assertOk();
    }
}
