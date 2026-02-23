<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\DismissedDuplicate;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_dup',
            'first_name' => 'Santa',
            'last_name' => 'Dup',
            'password' => 'password123',
            'permission' => 9,
        ]);
    }

    public function test_duplicates_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/duplicates');
        $response->assertStatus(200);
    }

    public function test_similar_names_detected_as_duplicates(): void
    {
        Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Johnson',
            'address' => '100 Main St, Granite Falls',
            'phone1' => '360-555-1111',
            'number_of_family_members' => 3,
        ]);

        Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Johnson',
            'address' => '200 Oak Ave, Granite Falls',
            'phone1' => '360-555-2222',
            'number_of_family_members' => 4,
        ]);

        $response = $this->actingAs($this->santa)->get('/santa/duplicates');
        $response->assertStatus(200);
        $response->assertSee('Johnson');
    }

    public function test_same_address_detected_as_duplicates(): void
    {
        Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Smith',
            'address' => '100 Main St, Granite Falls, WA',
            'phone1' => '360-555-1111',
            'number_of_family_members' => 3,
        ]);

        Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Jones',
            'address' => '100 Main St, Granite Falls, WA',
            'phone1' => '360-555-2222',
            'number_of_family_members' => 2,
        ]);

        $response = $this->actingAs($this->santa)->get('/santa/duplicates');
        $response->assertStatus(200);
        $response->assertSee('Smith');
        $response->assertSee('Jones');
    }

    public function test_dismiss_duplicate_removes_pair(): void
    {
        $a = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Wilson',
            'address' => '100 Main St',
            'phone1' => '360-555-1111',
            'number_of_family_members' => 3,
        ]);

        $b = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Wilson',
            'address' => '200 Oak Ave',
            'phone1' => '360-555-2222',
            'number_of_family_members' => 4,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/duplicates/dismiss', [
            'family_a_id' => $a->id,
            'family_b_id' => $b->id,
        ]);

        $response->assertRedirect(route('santa.duplicates'));
        $this->assertTrue(DismissedDuplicate::isDismissed($a->id, $b->id));
    }

    public function test_merge_families_transfers_children(): void
    {
        $keep = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Anderson',
            'family_number' => 10,
            'address' => '100 Main St',
            'phone1' => '360-555-1111',
            'number_of_family_members' => 3,
        ]);

        $merge = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Anderson',
            'address' => '100 Main St',
            'phone1' => '360-555-1111',
            'number_of_family_members' => 2,
        ]);

        Child::create([
            'family_id' => $keep->id,
            'gender' => 'Male',
            'age' => '8',
            'gift_level' => 0,
        ]);

        Child::create([
            'family_id' => $merge->id,
            'gender' => 'Female',
            'age' => '5',
            'gift_level' => 0,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/duplicates/merge', [
            'keep_id' => $keep->id,
            'merge_id' => $merge->id,
        ]);

        $response->assertRedirect(route('santa.duplicates'));

        // Verify children were moved
        $this->assertEquals(2, $keep->fresh()->children()->count());
        // Verify merged family was deleted
        $this->assertNull(Family::find($merge->id));
    }

    public function test_family_user_cannot_access_duplicates(): void
    {
        $familyUser = User::create([
            'username' => 'family_dup',
            'first_name' => 'Family',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->actingAs($familyUser)->get('/santa/duplicates')->assertStatus(403);
    }
}
