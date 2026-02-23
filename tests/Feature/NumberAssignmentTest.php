<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Family;
use App\Models\SchoolRange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

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

        // Create school ranges
        SchoolRange::create(['school_name' => 'Crossroads', 'range_start' => 1, 'range_end' => 99, 'sort_order' => 1]);
        SchoolRange::create(['school_name' => 'GFHS', 'range_start' => 100, 'range_end' => 199, 'sort_order' => 2]);
        SchoolRange::create(['school_name' => 'GFMS', 'range_start' => 200, 'range_end' => 299, 'sort_order' => 3]);
        SchoolRange::create(['school_name' => 'Monte Cristo', 'range_start' => 300, 'range_end' => 399, 'sort_order' => 4]);
        SchoolRange::create(['school_name' => 'Mountain Way', 'range_start' => 400, 'range_end' => 499, 'sort_order' => 5]);
        SchoolRange::create(['school_name' => 'Special Case', 'range_start' => 500, 'range_end' => 599, 'sort_order' => 6]);
    }

    private function createFamilyWithChild(string $school, int $age, ?int $familyNumber = null): Family
    {
        $family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => "Family-{$school}",
            'address' => '123 Test St',
            'phone1' => '360-555-0000',
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
            'family_number' => $familyNumber,
        ]);

        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => (string) $age,
            'school' => $school,
        ]);

        return $family;
    }

    public function test_number_assignment_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/number-assignment');
        $response->assertStatus(200);
    }

    public function test_manual_number_assignment(): void
    {
        $family = $this->createFamilyWithChild('Crossroads', 10);

        $response = $this->actingAs($this->santa)->post('/santa/number-assignment', [
            'family_id' => $family->id,
            'family_number' => 15,
        ]);

        $response->assertRedirect();
        $this->assertEquals(15, $family->fresh()->family_number);
    }

    public function test_duplicate_number_rejected(): void
    {
        $family1 = $this->createFamilyWithChild('Crossroads', 10, 15);
        $family2 = $this->createFamilyWithChild('Crossroads', 8);

        $response = $this->actingAs($this->santa)->post('/santa/number-assignment', [
            'family_id' => $family2->id,
            'family_number' => 15,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNull($family2->fresh()->family_number);
    }

    public function test_auto_assign_assigns_numbers(): void
    {
        $family1 = $this->createFamilyWithChild('Crossroads', 10);
        $family2 = $this->createFamilyWithChild('GFHS', 16);
        $family3 = $this->createFamilyWithChild('Mountain Way', 6);

        $response = $this->actingAs($this->santa)->post('/santa/number-assignment/auto-assign');

        $response->assertRedirect();

        $f1 = $family1->fresh();
        $f2 = $family2->fresh();
        $f3 = $family3->fresh();

        // Crossroads range: 1-99
        $this->assertNotNull($f1->family_number);
        $this->assertGreaterThanOrEqual(1, $f1->family_number);
        $this->assertLessThanOrEqual(99, $f1->family_number);

        // GFHS range: 100-199
        $this->assertNotNull($f2->family_number);
        $this->assertGreaterThanOrEqual(100, $f2->family_number);
        $this->assertLessThanOrEqual(199, $f2->family_number);

        // Mountain Way range: 400-499
        $this->assertNotNull($f3->family_number);
        $this->assertGreaterThanOrEqual(400, $f3->family_number);
        $this->assertLessThanOrEqual(499, $f3->family_number);
    }

    public function test_auto_assign_skips_families_without_children(): void
    {
        $family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'No Kids Family',
            'address' => '123 Test St',
            'phone1' => '360-555-0000',
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

        $response = $this->actingAs($this->santa)->post('/santa/number-assignment/auto-assign');

        $response->assertRedirect();
        $this->assertNull($family->fresh()->family_number);
    }

    public function test_next_available_number_skips_used(): void
    {
        // Take number 1
        $this->createFamilyWithChild('Crossroads', 10, 1);

        $range = SchoolRange::where('school_name', 'Crossroads')->first();
        $next = $range->nextAvailableNumber();

        $this->assertEquals(2, $next);
    }

    public function test_family_user_cannot_access_number_assignment(): void
    {
        $familyUser = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($familyUser)->get('/santa/number-assignment');
        $response->assertStatus(403);
    }
}
