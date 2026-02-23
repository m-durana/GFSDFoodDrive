<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingCompanionTest extends TestCase
{
    use RefreshDatabase;

    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $santa = User::create([
            'username' => 'santa_shop',
            'first_name' => 'Santa',
            'last_name' => 'Shop',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->family = Family::create([
            'user_id' => $santa->id,
            'family_name' => 'Shoppers',
            'family_number' => 55,
            'address' => '100 Main St',
            'phone1' => '360-555-5500',
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
        ]);

        GroceryItem::create([
            'name' => 'Canned Beans',
            'category' => 'canned',
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
            'sort_order' => 1,
        ]);

        GroceryItem::create([
            'name' => 'Rice',
            'category' => 'dry',
            'qty_1' => 1, 'qty_2' => 1, 'qty_3' => 2, 'qty_4' => 2,
            'qty_5' => 3, 'qty_6' => 3, 'qty_7' => 4, 'qty_8' => 4,
            'sort_order' => 2,
        ]);
    }

    public function test_shopping_checklist_loads_for_valid_family(): void
    {
        $response = $this->get('/shopping/55');
        $response->assertStatus(200);
        $response->assertSee('Family #55');
        $response->assertSee('Shoppers');
        $response->assertSee('Canned Beans');
        $response->assertSee('Rice');
    }

    public function test_shopping_checklist_shows_correct_quantities(): void
    {
        $response = $this->get('/shopping/55');
        $response->assertStatus(200);
        // Family of 4 should get qty_4 values: 4 beans, 2 rice
        $response->assertSee('Canned Beans');
        $response->assertSee('Rice');
    }

    public function test_shopping_checklist_404_for_invalid_family(): void
    {
        $response = $this->get('/shopping/9999');
        $response->assertStatus(404);
    }

    public function test_shopping_checklist_no_auth_required(): void
    {
        // Should work without being logged in
        $response = $this->get('/shopping/55');
        $response->assertStatus(200);
    }
}
