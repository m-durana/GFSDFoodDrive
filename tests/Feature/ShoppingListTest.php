<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
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
        GroceryItem::create([
            'name' => 'Baby Food', 'category' => 'personal', 'sort_order' => 4,
            'qty_1' => 2, 'qty_2' => 3, 'qty_3' => 4, 'qty_4' => 5,
            'qty_5' => 6, 'qty_6' => 7, 'qty_7' => 8, 'qty_8' => 9,
            'conditional' => true, 'condition_field' => 'has_infants',
        ]);
    }

    public function test_shopping_list_page_loads(): void
    {
        $this->seedGroceryItems();
        $response = $this->actingAs($this->santa)->get(route('santa.shoppingList'));
        $response->assertOk();
        $response->assertSee('Shopping Lists');
        $response->assertSee('4 grocery items configured');
    }

    public function test_shopping_list_generates_for_family(): void
    {
        $this->seedGroceryItems();
        $family = Family::create([
            'family_name' => 'Test Family', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Test St', 'phone1' => '555-1234',
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.shoppingList', ['family_id' => $family->id]));
        $response->assertOk();
        $response->assertSee('Test Family');
        $response->assertSee('Tuna');
        $response->assertSee('Pasta Noodles');
    }

    public function test_shopping_list_calculates_correct_quantities(): void
    {
        $this->seedGroceryItems();
        $family = Family::create([
            'family_name' => 'Size 3', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Test St', 'phone1' => '555-1234',
        ]);

        $list = GroceryItem::calculateForFamily($family);

        $this->assertEquals(4, $list['Tuna']['quantity']);
        $this->assertEquals(3, $list['Green Beans']['quantity']);
        $this->assertEquals(11, $list['Pasta Noodles']['quantity']);
        $this->assertArrayNotHasKey('Baby Food', $list); // conditional, no infants
    }

    public function test_conditional_item_included_when_condition_met(): void
    {
        $this->seedGroceryItems();
        $family = Family::create([
            'family_name' => 'Baby Family', 'family_number' => 2,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'infants' => 1,
            'address' => '456 Test St', 'phone1' => '555-5678',
        ]);

        $list = GroceryItem::calculateForFamily($family);
        $this->assertArrayHasKey('Baby Food', $list);
        $this->assertEquals(4, $list['Baby Food']['quantity']); // size 3 = qty_3 = 4
    }

    public function test_shopping_list_csv_export(): void
    {
        $this->seedGroceryItems();
        Family::create([
            'family_name' => 'CSV Family', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '789 Test St', 'phone1' => '555-9012',
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.shoppingList', ['format' => 'csv']));
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Family Number', $content);
        $this->assertStringContainsString('CSV Family', $content);
        $this->assertStringContainsString('TOTALS', $content);
    }

    public function test_manage_page_loads(): void
    {
        $this->seedGroceryItems();
        $response = $this->actingAs($this->santa)->get(route('santa.shoppingList', ['manage' => '1']));
        $response->assertOk();
        $response->assertSee('Manage Grocery Items');
        $response->assertSee('Tuna');
    }

    public function test_grocery_item_can_be_added(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.storeGroceryItem'), [
            'name' => 'New Item',
            'category' => 'canned',
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('grocery_items', ['name' => 'New Item', 'category' => 'canned', 'qty_3' => 3]);
    }

    public function test_grocery_item_can_be_updated(): void
    {
        $item = GroceryItem::create([
            'name' => 'Old Name', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 0, 'qty_2' => 0, 'qty_3' => 0, 'qty_4' => 0,
            'qty_5' => 0, 'qty_6' => 0, 'qty_7' => 0, 'qty_8' => 0,
        ]);

        $response = $this->actingAs($this->santa)->put(route('santa.updateGroceryItem', $item), [
            'name' => 'New Name', 'category' => 'dry',
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('grocery_items', ['name' => 'New Name', 'category' => 'dry', 'qty_5' => 5]);
    }

    public function test_grocery_item_can_be_deleted(): void
    {
        $item = GroceryItem::create([
            'name' => 'Delete Me', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 0, 'qty_2' => 0, 'qty_3' => 0, 'qty_4' => 0,
            'qty_5' => 0, 'qty_6' => 0, 'qty_7' => 0, 'qty_8' => 0,
        ]);

        $response = $this->actingAs($this->santa)->delete(route('santa.destroyGroceryItem', $item));
        $response->assertRedirect();
        $this->assertDatabaseMissing('grocery_items', ['id' => $item->id]);
    }

    public function test_export_formula_csv(): void
    {
        $this->seedGroceryItems();
        $response = $this->actingAs($this->santa)->get(route('santa.exportGroceryFormula'));
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Tuna', $content);
        $this->assertStringContainsString('Green Beans', $content);
    }

    public function test_family_range_filter(): void
    {
        $this->seedGroceryItems();
        Family::create([
            'family_name' => 'Zambroni', 'family_number' => 5,
            'number_of_family_members' => 2, 'number_of_adults' => 1,
            'number_of_children' => 1, 'address' => 'X', 'phone1' => '1',
        ]);
        Family::create([
            'family_name' => 'Xylophane', 'family_number' => 150,
            'number_of_family_members' => 4, 'number_of_adults' => 2,
            'number_of_children' => 2, 'address' => 'Y', 'phone1' => '2',
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.shoppingList', [
            'family_number_start' => 1,
            'family_number_end' => 99,
        ]));
        $response->assertOk();
        $response->assertSee('Zambroni');
        // Xylophane appears in the filter dropdown but NOT in the results
        // Check that only 1 family is in the results (Zambroni)
        $response->assertSee('1 family');
        $response->assertDontSee('2 families');
    }
}
