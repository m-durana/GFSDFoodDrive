<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $familyUser;
    private User $santaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->familyUser = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Advisor',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->santaUser = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);
    }

    private function validFamilyData(): array
    {
        return [
            'family_name' => 'Test Family',
            'address' => '123 Main St, Granite Falls, WA',
            'phone1' => '360-555-1234',
            'phone2' => '',
            'email' => 'test@example.com',
            'preferred_language' => 'English',
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 1,
            'children_count' => 1,
            'tweens' => 0,
            'teenagers' => 0,
            'pet_information' => 'No pets',
            'delivery_preference' => 'Pickup',
            'delivery_date' => 'December 18',
            'delivery_time' => '9:00 AM - 11:00 AM',
            'need_for_help' => 'Lost job recently',
        ];
    }

    public function test_family_index_page_loads(): void
    {
        $response = $this->actingAs($this->familyUser)->get('/family');
        $response->assertStatus(200);
    }

    public function test_family_create_page_loads(): void
    {
        $response = $this->actingAs($this->familyUser)->get('/family/add');
        $response->assertStatus(200);
    }

    public function test_family_can_be_created(): void
    {
        $response = $this->actingAs($this->familyUser)->post('/family/add', $this->validFamilyData());

        $response->assertRedirect();
        $this->assertDatabaseHas('families', ['family_name' => 'Test Family']);

        $family = Family::where('family_name', 'Test Family')->first();
        $this->assertEquals(2, $family->number_of_adults);
        $this->assertEquals(2, $family->number_of_children);
        $this->assertEquals(4, $family->number_of_family_members);
        $this->assertEquals($this->familyUser->id, $family->user_id);
    }

    public function test_family_creation_requires_name(): void
    {
        $data = $this->validFamilyData();
        unset($data['family_name']);

        $response = $this->actingAs($this->familyUser)->post('/family/add', $data);
        $response->assertSessionHasErrors('family_name');
    }

    public function test_family_creation_requires_phone(): void
    {
        $data = $this->validFamilyData();
        unset($data['phone1']);

        $response = $this->actingAs($this->familyUser)->post('/family/add', $data);
        $response->assertSessionHasErrors('phone1');
    }

    public function test_family_show_page_loads(): void
    {
        $family = Family::create(array_merge($this->validFamilyData(), [
            'user_id' => $this->familyUser->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
        ]));

        $response = $this->actingAs($this->familyUser)->get("/family/{$family->id}");
        $response->assertStatus(200);
        $response->assertSee('Test Family');
    }

    public function test_family_edit_page_loads(): void
    {
        $family = Family::create(array_merge($this->validFamilyData(), [
            'user_id' => $this->familyUser->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
        ]));

        $response = $this->actingAs($this->familyUser)->get("/family/{$family->id}/edit");
        $response->assertStatus(200);
    }

    public function test_family_can_be_updated(): void
    {
        $family = Family::create(array_merge($this->validFamilyData(), [
            'user_id' => $this->familyUser->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
        ]));

        $updateData = $this->validFamilyData();
        $updateData['family_name'] = 'Updated Family';
        $updateData['preferred_language'] = 'Spanish';

        $response = $this->actingAs($this->familyUser)->put("/family/{$family->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('families', [
            'id' => $family->id,
            'family_name' => 'Updated Family',
            'preferred_language' => 'Spanish',
        ]);
    }

    public function test_toggle_done_marks_family_complete(): void
    {
        $family = Family::create(array_merge($this->validFamilyData(), [
            'user_id' => $this->familyUser->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
            'family_done' => false,
        ]));

        $response = $this->actingAs($this->familyUser)->post("/family/{$family->id}/toggle-done");

        $response->assertRedirect();
        $this->assertTrue($family->fresh()->family_done);
    }

    public function test_toggle_done_unmarks_family(): void
    {
        $family = Family::create(array_merge($this->validFamilyData(), [
            'user_id' => $this->familyUser->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
            'family_done' => true,
        ]));

        $response = $this->actingAs($this->familyUser)->post("/family/{$family->id}/toggle-done");

        $response->assertRedirect();
        $this->assertFalse($family->fresh()->family_done);
    }

    public function test_santa_can_access_family_routes(): void
    {
        $response = $this->actingAs($this->santaUser)->get('/family');
        $response->assertStatus(200);

        $response = $this->actingAs($this->santaUser)->get('/family/add');
        $response->assertStatus(200);
    }

    public function test_preferred_language_validation(): void
    {
        $data = $this->validFamilyData();
        $data['preferred_language'] = 'French';

        $response = $this->actingAs($this->familyUser)->post('/family/add', $data);
        $response->assertSessionHasErrors('preferred_language');
    }

    public function test_delivery_preference_validation(): void
    {
        $data = $this->validFamilyData();
        $data['delivery_preference'] = 'InvalidOption';

        $response = $this->actingAs($this->familyUser)->post('/family/add', $data);
        $response->assertSessionHasErrors('delivery_preference');
    }
}
