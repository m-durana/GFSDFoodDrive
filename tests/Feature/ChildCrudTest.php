<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Advisor',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->family = Family::create([
            'user_id' => $this->user->id,
            'family_name' => 'Test Family',
            'address' => '123 Main St, Granite Falls, WA',
            'phone1' => '360-555-1234',
            'number_of_adults' => 2,
            'number_of_children' => 0,
            'number_of_family_members' => 2,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 0,
            'tweens' => 0,
            'teenagers' => 0,
        ]);
    }

    public function test_child_can_be_added_to_family(): void
    {
        $response = $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'gender' => 'Male',
            'age' => '8',
            'school' => 'Crossroads',
            'clothes_size' => 'M (8-10)',
            'clothing_styles' => 'Casual, comfortable clothes',
            'clothing_options' => 'Shirts, pants, socks',
            'gift_preferences' => 'Loves hands-on activities',
            'toy_ideas' => 'LEGO sets, anything Minecraft',
            'all_sizes' => 'Shirt: M, Pants: 8, Shoes: 3Y',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('children', [
            'family_id' => $this->family->id,
            'gender' => 'Male',
            'age' => '8',
            'school' => 'Crossroads',
        ]);
    }

    public function test_child_requires_gender(): void
    {
        $response = $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'age' => '8',
            'school' => 'Crossroads',
        ]);

        $response->assertSessionHasErrors('gender');
    }

    public function test_child_requires_valid_gender(): void
    {
        $response = $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'gender' => 'Other',
            'age' => '8',
        ]);

        $response->assertSessionHasErrors('gender');
    }

    public function test_child_requires_age(): void
    {
        $response = $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'gender' => 'Male',
        ]);

        $response->assertSessionHasErrors('age');
    }

    public function test_child_can_be_updated(): void
    {
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male',
            'age' => '8',
            'school' => 'Crossroads',
        ]);

        $response = $this->actingAs($this->user)->put(
            "/family/{$this->family->id}/children/{$child->id}",
            [
                'gender' => 'Male',
                'age' => '9',
                'school' => 'Crossroads',
                'gift_level' => 2,
                'adopter_name' => 'Boeing Employees Group',
                'adopter_contact_info' => '425-555-1234',
                'gifts_received' => 'Received coat, 2 shirts',
                'where_is_tag' => 'at store',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'age' => '9',
            'gift_level' => 2,
            'adopter_name' => 'Boeing Employees Group',
        ]);
    }

    public function test_child_gift_level_validation(): void
    {
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male',
            'age' => '8',
        ]);

        $response = $this->actingAs($this->user)->put(
            "/family/{$this->family->id}/children/{$child->id}",
            [
                'gender' => 'Male',
                'age' => '8',
                'gift_level' => 5,
            ]
        );

        $response->assertSessionHasErrors('gift_level');
    }

    public function test_child_can_be_deleted(): void
    {
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female',
            'age' => '10',
            'school' => 'GFMS',
        ]);

        $response = $this->actingAs($this->user)->delete(
            "/family/{$this->family->id}/children/{$child->id}"
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('children', ['id' => $child->id]);
    }

    public function test_multiple_children_can_be_added(): void
    {
        $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'gender' => 'Male',
            'age' => '5',
            'school' => 'Mountain Way',
        ]);

        $this->actingAs($this->user)->post("/family/{$this->family->id}/children", [
            'gender' => 'Female',
            'age' => '10',
            'school' => 'Crossroads',
        ]);

        $this->assertEquals(2, $this->family->children()->count());
    }
}
