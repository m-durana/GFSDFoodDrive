<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdoptionTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->family = Family::create([
            'family_name' => 'Adopt Test', 'family_number' => 100,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '100 Main St',
        ]);
        $this->child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female', 'age' => 8,
            'gift_level' => GiftLevel::None,
        ]);

        // Clear static cache between tests (DB is rolled back but cache persists)
        Setting::clearCache();
    }

    // ── Public Portal ─────────────────────────────────────────────

    public function test_adoption_portal_disabled_by_default(): void
    {
        $response = $this->get(route('adopt.index'));
        $response->assertOk();
        $response->assertSee('not available right now');
    }

    public function test_adoption_portal_shows_children_when_enabled(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        $response = $this->get(route('adopt.index'));
        $response->assertOk();
    }

    public function test_adoption_portal_closed_after_deadline(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');
        Setting::set('adopt_a_tag_deadline', now()->subDay()->toDateString());

        $response = $this->get(route('adopt.index'));
        $response->assertOk();
        $response->assertSee('Has Closed');
    }

    public function test_child_detail_page_loads(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');
        // Ensure no deadline is blocking
        Setting::where('key', 'adopt_a_tag_deadline')->delete();

        // Create a fresh child that's definitely available
        $child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male', 'age' => 5,
            'gift_level' => GiftLevel::None,
        ]);

        $response = $this->get(route('adopt.show', $child));
        $response->assertOk();
    }

    public function test_child_detail_404_when_disabled(): void
    {
        $response = $this->get(route('adopt.show', $this->child));
        $response->assertNotFound();
    }

    public function test_child_can_be_claimed(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        $response = $this->post(route('adopt.claim', $this->child), [
            'adopter_name' => 'Jane Donor',
            'adopter_email' => 'jane@example.com',
            'adopter_phone' => '555-9999',
        ]);
        $response->assertRedirect();

        $this->child->refresh();
        $this->assertEquals('Jane Donor', $this->child->adopter_name);
        $this->assertEquals('jane@example.com', $this->child->adopter_email);
        $this->assertNotNull($this->child->adoption_token);
        $this->assertEquals(GiftLevel::Partial, $this->child->gift_level);
    }

    public function test_already_adopted_child_cannot_be_claimed(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        $this->child->update([
            'adoption_token' => 'already-taken',
            'adopter_name' => 'First Person',
            'adopter_email' => 'first@example.com',
        ]);

        $response = $this->post(route('adopt.claim', $this->child), [
            'adopter_name' => 'Second Person',
            'adopter_email' => 'second@example.com',
        ]);
        $response->assertRedirect(route('adopt.index'));
        $response->assertSessionHas('error');
    }

    public function test_claim_requires_name_and_email(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        $response = $this->post(route('adopt.claim', $this->child), []);
        $response->assertSessionHasErrors(['adopter_name', 'adopter_email']);
    }

    public function test_confirmation_page_loads_with_valid_token(): void
    {
        $this->child->update([
            'adoption_token' => 'test-token-abc',
            'adopter_name' => 'Jane',
            'adopter_email' => 'jane@example.com',
        ]);

        $response = $this->get(route('adopt.confirmation', 'test-token-abc'));
        $response->assertOk();
    }

    public function test_confirmation_page_404_with_invalid_token(): void
    {
        $response = $this->get(route('adopt.confirmation', 'bad-token'));
        $response->assertNotFound();
    }

    public function test_mark_delivered_sets_gift_dropped_off(): void
    {
        $this->child->update([
            'adoption_token' => 'deliver-token',
            'adopter_name' => 'Jane',
            'adopter_email' => 'jane@example.com',
        ]);

        $response = $this->post(route('adopt.markDelivered', 'deliver-token'));
        $response->assertRedirect();

        $this->child->refresh();
        $this->assertTrue((bool) $this->child->gift_dropped_off);
        $this->assertEquals(GiftLevel::Moderate, $this->child->gift_level);
    }

    // ── Portal filters ────────────────────────────────────────────

    public function test_adoption_portal_filters_by_gender(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Male', 'age' => 5,
            'gift_level' => GiftLevel::None,
        ]);

        $response = $this->get(route('adopt.index', ['gender' => 'Male']));
        $response->assertOk();
    }

    public function test_adoption_portal_filters_by_age(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');

        $response = $this->get(route('adopt.index', ['age_min' => 5, 'age_max' => 10]));
        $response->assertOk();
    }

    // ── Admin Dashboard ───────────────────────────────────────────

    public function test_admin_dashboard_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.adoptions'));
        $response->assertOk();
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        $this->child->update([
            'adoption_token' => 'admin-test',
            'adopter_name' => 'Jane',
            'adopter_email' => 'jane@example.com',
            'adopted_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.adoptions'));
        $response->assertOk();
    }

    public function test_admin_can_release_adoption(): void
    {
        $this->child->update([
            'adoption_token' => 'release-test',
            'adopter_name' => 'Jane',
            'adopter_email' => 'jane@example.com',
            'adopted_at' => now(),
            'gift_level' => GiftLevel::Partial,
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.releaseAdoption', $this->child));
        $response->assertRedirect();

        $this->child->refresh();
        $this->assertNull($this->child->adoption_token);
        $this->assertNull($this->child->adopter_name);
        $this->assertEquals(GiftLevel::None, $this->child->gift_level);
    }

    public function test_admin_can_complete_adoption(): void
    {
        $this->child->update([
            'adoption_token' => 'complete-test',
            'adopter_name' => 'Jane',
            'adopter_email' => 'jane@example.com',
            'adopted_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.completeAdoption', $this->child));
        $response->assertRedirect();

        $this->child->refresh();
        $this->assertEquals(GiftLevel::Full, $this->child->gift_level);
        $this->assertTrue((bool) $this->child->gift_dropped_off);
    }

    public function test_admin_dashboard_filters_by_status(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.adoptions', ['status' => 'available']));
        $response->assertOk();

        $response = $this->actingAs($this->santa)->get(route('santa.adoptions', ['status' => 'dropped_off']));
        $response->assertOk();
    }
}
