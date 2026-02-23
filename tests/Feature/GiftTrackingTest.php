<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Gift Test Family',
            'family_number' => 42,
            'address' => '123 Gift St',
            'phone1' => '360-555-4242',
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 2,
            'tweens' => 0,
            'teenagers' => 0,
        ]);
    }

    public function test_gifts_overview_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/gifts');
        $response->assertStatus(200);
    }

    public function test_gifts_page_shows_summary_counts(): void
    {
        Child::create(['family_id' => $this->family->id, 'gender' => 'Male', 'age' => '8', 'gift_level' => 0]);
        Child::create(['family_id' => $this->family->id, 'gender' => 'Female', 'age' => '10', 'gift_level' => 3]);

        $response = $this->actingAs($this->santa)->get('/santa/gifts');
        $response->assertStatus(200);
    }

    public function test_gifts_filter_by_level(): void
    {
        Child::create(['family_id' => $this->family->id, 'gender' => 'Male', 'age' => '8', 'gift_level' => 0]);
        Child::create(['family_id' => $this->family->id, 'gender' => 'Female', 'age' => '10', 'gift_level' => 3]);

        $response = $this->actingAs($this->santa)->get('/santa/gifts?level=0');
        $response->assertStatus(200);
    }

    public function test_gifts_filter_by_merged_status(): void
    {
        Child::create(['family_id' => $this->family->id, 'gender' => 'Male', 'age' => '8', 'mail_merged' => false]);
        Child::create(['family_id' => $this->family->id, 'gender' => 'Female', 'age' => '10', 'mail_merged' => true]);

        $response = $this->actingAs($this->santa)->get('/santa/gifts?merged=0');
        $response->assertStatus(200);
    }

    public function test_gifts_filter_by_adopted(): void
    {
        Child::create(['family_id' => $this->family->id, 'gender' => 'Male', 'age' => '8', 'adopter_name' => 'Boeing']);
        Child::create(['family_id' => $this->family->id, 'gender' => 'Female', 'age' => '10', 'adopter_name' => null]);

        $response = $this->actingAs($this->santa)->get('/santa/gifts?adopted=1');
        $response->assertStatus(200);
    }

    public function test_child_gift_level_can_be_updated(): void
    {
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male',
            'age' => '8',
            'gift_level' => 0,
        ]);

        $response = $this->actingAs($this->santa)->put(
            "/family/{$this->family->id}/children/{$child->id}",
            [
                'gender' => 'Male',
                'age' => '8',
                'gift_level' => 3,
                'gifts_received' => 'LEGO set, coat, pants',
            ]
        );

        $response->assertRedirect();
        $this->assertEquals(GiftLevel::Full, $child->fresh()->gift_level);
        $this->assertEquals('LEGO set, coat, pants', $child->fresh()->gifts_received);
    }

    public function test_adopter_info_can_be_set(): void
    {
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female',
            'age' => '10',
            'gift_level' => 0,
        ]);

        $response = $this->actingAs($this->santa)->put(
            "/family/{$this->family->id}/children/{$child->id}",
            [
                'gender' => 'Female',
                'age' => '10',
                'adopter_name' => 'Granite Falls Rotary',
                'adopter_contact_info' => '360-555-9999',
            ]
        );

        $response->assertRedirect();
        $this->assertEquals('Granite Falls Rotary', $child->fresh()->adopter_name);
    }
}
