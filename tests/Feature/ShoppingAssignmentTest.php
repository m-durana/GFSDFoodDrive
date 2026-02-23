<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\ShoppingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'testsanta',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password',
            'permission' => 9,
        ]);

        $this->coordinator = User::create([
            'username' => 'testcoord',
            'first_name' => 'Test',
            'last_name' => 'Coordinator',
            'password' => 'password',
            'permission' => 8,
        ]);
    }

    public function test_santa_can_view_shopping_day_page(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/shopping-day');

        $response->assertStatus(200);
        $response->assertSee('Shopping Day');
    }

    public function test_santa_can_create_category_assignment(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'user_id' => $this->coordinator->id,
            'split_type' => 'category',
            'categories' => ['canned', 'dry'],
        ]);

        $response->assertRedirect('/santa/shopping-day');
        $this->assertDatabaseHas('shopping_assignments', [
            'user_id' => $this->coordinator->id,
            'split_type' => 'category',
        ]);
    }

    public function test_santa_can_create_family_range_assignment(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'user_id' => $this->coordinator->id,
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 50,
        ]);

        $response->assertRedirect('/santa/shopping-day');
        $this->assertDatabaseHas('shopping_assignments', [
            'user_id' => $this->coordinator->id,
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 50,
        ]);
    }

    public function test_santa_can_delete_assignment(): void
    {
        $assignment = ShoppingAssignment::create([
            'user_id' => $this->coordinator->id,
            'split_type' => 'category',
            'categories' => ['canned'],
        ]);

        $response = $this->actingAs($this->santa)->delete("/santa/shopping-day/assignments/{$assignment->id}");

        $response->assertRedirect('/santa/shopping-day');
        $this->assertDatabaseMissing('shopping_assignments', ['id' => $assignment->id]);
    }

    public function test_mobile_assignment_view_accessible_without_auth(): void
    {
        $assignment = ShoppingAssignment::create([
            'user_id' => $this->coordinator->id,
            'split_type' => 'category',
            'categories' => ['canned'],
        ]);

        $response = $this->get("/shopping/assignment/{$assignment->id}");

        $response->assertStatus(200);
        $response->assertSee($this->coordinator->first_name);
    }

    public function test_family_range_assignment_calculates_items(): void
    {
        // Create a family with a number
        $family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Test Family',
            'address' => '123 Test St',
            'phone1' => '555-1234',
            'family_number' => 5,
            'number_of_family_members' => 4,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 1,
            'children_count' => 1,
            'tweens' => 0,
            'teenagers' => 0,
        ]);

        // Create a grocery item
        GroceryItem::create([
            'name' => 'Canned Beans',
            'category' => 'canned',
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
            'sort_order' => 1,
        ]);

        $assignment = ShoppingAssignment::create([
            'user_id' => $this->coordinator->id,
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 10,
        ]);

        $list = $assignment->getShoppingList();
        $this->assertArrayHasKey('canned', $list);
        $this->assertEquals(4, $list['canned']['Canned Beans']);
    }
}
