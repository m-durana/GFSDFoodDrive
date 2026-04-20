<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression coverage for the P0 authorization holes in FamilyController
 * (BUGS.md bullets A-INTAKE-04 through A-INTAKE-10):
 *   - show/edit/update/storeChild/updateChild/destroyChild/toggleDone had no
 *     per-family ownership check; any advisor with the URL could read or
 *     mutate any family in the current season.
 *   - updateChild/destroyChild did not verify $child->family_id === $family->id,
 *     so PUT/DELETE /family/{a}/children/{b} mutated {b} regardless of {a}.
 *   - destroyChild silently broke an adopter's confirmation link.
 *
 * Also covers the golden path per workflow so we don't regress the happy case.
 */
class FamilyOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerAdvisor;
    private User $otherAdvisor;
    private User $santa;
    private Family $ownedFamily;
    private Family $foreignFamily;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerAdvisor = User::create([
            'username' => 'owner_advisor',
            'first_name' => 'Owner',
            'last_name' => 'Advisor',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->otherAdvisor = User::create([
            'username' => 'other_advisor',
            'first_name' => 'Other',
            'last_name' => 'Advisor',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->santa = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->ownedFamily = $this->makeFamily($this->ownerAdvisor, 'Owned Family');
        $this->foreignFamily = $this->makeFamily($this->otherAdvisor, 'Foreign Family');
    }

    private function makeFamily(User $owner, string $name): Family
    {
        return Family::create([
            'user_id' => $owner->id,
            'family_name' => $name,
            'address' => '123 Main St, Granite Falls, WA',
            'phone1' => '360-555-1234',
            'female_adults' => 1,
            'male_adults' => 1,
            'other_adults' => 0,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 0,
            'tweens' => 0,
            'teenagers' => 0,
            'number_of_adults' => 2,
            'number_of_children' => 0,
            'number_of_family_members' => 2,
        ]);
    }

    private function validFamilyPayload(): array
    {
        return [
            'family_name' => 'Mutated Name',
            'address' => '123 Main St, Granite Falls, WA',
            'phone1' => '360-555-1234',
            'preferred_language' => 'English',
            'female_adults' => 1,
            'male_adults' => 1,
            'other_adults' => 0,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 0,
            'tweens' => 0,
            'teenagers' => 0,
            'delivery_preference' => 'Pickup',
        ];
    }

    // --- A-INTAKE-04 show ---------------------------------------------------

    public function test_advisor_cannot_view_another_advisors_family(): void
    {
        $response = $this->actingAs($this->otherAdvisor)
            ->get("/family/{$this->ownedFamily->id}");

        $response->assertForbidden();
    }

    public function test_owner_advisor_can_view_own_family(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)
            ->get("/family/{$this->ownedFamily->id}");

        $response->assertOk();
        $response->assertSee('Owned Family');
    }

    public function test_santa_can_view_any_family(): void
    {
        $response = $this->actingAs($this->santa)
            ->get("/family/{$this->ownedFamily->id}");

        $response->assertOk();
    }

    // --- A-INTAKE-05 edit ---------------------------------------------------

    public function test_advisor_cannot_open_edit_for_another_advisors_family(): void
    {
        $response = $this->actingAs($this->otherAdvisor)
            ->get("/family/{$this->ownedFamily->id}/edit");

        $response->assertForbidden();
    }

    public function test_owner_advisor_can_open_edit_for_own_family(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)
            ->get("/family/{$this->ownedFamily->id}/edit");

        $response->assertOk();
    }

    // --- A-INTAKE-06 update -------------------------------------------------

    public function test_advisor_cannot_update_another_advisors_family(): void
    {
        $response = $this->actingAs($this->otherAdvisor)
            ->put("/family/{$this->ownedFamily->id}", $this->validFamilyPayload());

        $response->assertForbidden();
        $this->assertDatabaseHas('families', [
            'id' => $this->ownedFamily->id,
            'family_name' => 'Owned Family',
        ]);
    }

    public function test_owner_advisor_can_update_own_family(): void
    {
        $payload = $this->validFamilyPayload();
        $payload['family_name'] = 'Renamed';

        $response = $this->actingAs($this->ownerAdvisor)
            ->put("/family/{$this->ownedFamily->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('families', [
            'id' => $this->ownedFamily->id,
            'family_name' => 'Renamed',
        ]);
    }

    // --- A-INTAKE-07 storeChild --------------------------------------------

    public function test_advisor_cannot_add_child_to_another_advisors_family(): void
    {
        $response = $this->actingAs($this->otherAdvisor)
            ->post("/family/{$this->ownedFamily->id}/children", [
                'gender' => 'Male',
                'age' => '8',
                'school' => 'Crossroads',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('children', [
            'family_id' => $this->ownedFamily->id,
            'age' => '8',
        ]);
    }

    public function test_owner_advisor_can_add_child_to_own_family(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)
            ->post("/family/{$this->ownedFamily->id}/children", [
                'gender' => 'Male',
                'age' => '8',
                'school' => 'Crossroads',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('children', [
            'family_id' => $this->ownedFamily->id,
            'gender' => 'Male',
            'age' => '8',
        ]);
    }

    // --- A-INTAKE-08 updateChild -------------------------------------------

    public function test_cross_family_child_binding_is_rejected_on_update(): void
    {
        $foreignChild = Child::create([
            'family_id' => $this->foreignFamily->id,
            'gender' => 'Male',
            'age' => '8',
            'school' => 'Crossroads',
        ]);

        // Santa (who passes the ownership check) crafts a URL that pairs the
        // owned family with a child from a different family. The child
        // binding check must 404 regardless of role.
        $response = $this->actingAs($this->santa)->put(
            "/family/{$this->ownedFamily->id}/children/{$foreignChild->id}",
            [
                'gender' => 'Male',
                'age' => '99',
            ]
        );

        $response->assertNotFound();
        $this->assertDatabaseHas('children', [
            'id' => $foreignChild->id,
            'age' => '8',
        ]);
    }

    public function test_advisor_cannot_update_child_in_another_advisors_family(): void
    {
        $foreignChild = Child::create([
            'family_id' => $this->foreignFamily->id,
            'gender' => 'Female',
            'age' => '10',
        ]);

        $response = $this->actingAs($this->otherAdvisor)->put(
            "/family/{$this->ownedFamily->id}/children/{$foreignChild->id}",
            [
                'gender' => 'Female',
                'age' => '99',
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('children', [
            'id' => $foreignChild->id,
            'age' => '10',
        ]);
    }

    public function test_owner_advisor_can_update_own_child(): void
    {
        $child = Child::create([
            'family_id' => $this->ownedFamily->id,
            'gender' => 'Male',
            'age' => '8',
        ]);

        $response = $this->actingAs($this->ownerAdvisor)->put(
            "/family/{$this->ownedFamily->id}/children/{$child->id}",
            [
                'gender' => 'Male',
                'age' => '9',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('children', ['id' => $child->id, 'age' => '9']);
    }

    // --- A-INTAKE-09 destroyChild ------------------------------------------

    public function test_cross_family_child_binding_is_rejected_on_delete(): void
    {
        $foreignChild = Child::create([
            'family_id' => $this->foreignFamily->id,
            'gender' => 'Male',
            'age' => '8',
        ]);

        $response = $this->actingAs($this->santa)->delete(
            "/family/{$this->ownedFamily->id}/children/{$foreignChild->id}"
        );

        $response->assertNotFound();
        $this->assertDatabaseHas('children', ['id' => $foreignChild->id]);
    }

    public function test_advisor_cannot_delete_child_in_another_advisors_family(): void
    {
        $ownedChild = Child::create([
            'family_id' => $this->ownedFamily->id,
            'gender' => 'Female',
            'age' => '10',
        ]);

        $response = $this->actingAs($this->otherAdvisor)->delete(
            "/family/{$this->ownedFamily->id}/children/{$ownedChild->id}"
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('children', ['id' => $ownedChild->id]);
    }

    public function test_destroy_child_refuses_to_break_adopter_link(): void
    {
        $adoptedChild = Child::create([
            'family_id' => $this->ownedFamily->id,
            'gender' => 'Male',
            'age' => '8',
            'adoption_token' => Str::random(32),
            'adopter_name' => 'Boeing Employees Group',
        ]);

        $response = $this->actingAs($this->ownerAdvisor)->delete(
            "/family/{$this->ownedFamily->id}/children/{$adoptedChild->id}"
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('children', ['id' => $adoptedChild->id]);
    }

    public function test_owner_advisor_can_delete_unadopted_child(): void
    {
        $child = Child::create([
            'family_id' => $this->ownedFamily->id,
            'gender' => 'Female',
            'age' => '10',
        ]);

        $response = $this->actingAs($this->ownerAdvisor)->delete(
            "/family/{$this->ownedFamily->id}/children/{$child->id}"
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('children', ['id' => $child->id]);
    }

    // --- A-INTAKE-10 toggleDone --------------------------------------------

    public function test_advisor_cannot_toggle_done_on_another_advisors_family(): void
    {
        $response = $this->actingAs($this->otherAdvisor)
            ->post("/family/{$this->ownedFamily->id}/toggle-done");

        $response->assertForbidden();
        $this->assertFalse((bool) $this->ownedFamily->fresh()->family_done);
    }

    public function test_owner_advisor_can_toggle_done_on_own_family(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)
            ->post("/family/{$this->ownedFamily->id}/toggle-done");

        $response->assertRedirect();
        $this->assertTrue((bool) $this->ownedFamily->fresh()->family_done);
    }
}
