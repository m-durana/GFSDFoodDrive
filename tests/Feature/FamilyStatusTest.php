<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FamilyStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->family = Family::create([
            'family_name' => 'Status Test', 'family_number' => 50,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '50 Main St',
            'status_token' => Str::random(32),
        ]);
    }

    public function test_status_page_loads_when_enabled(): void
    {
        Setting::set('family_status_enabled', '1');

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertOk();
        $response->assertSee('Status Test');
        $response->assertSee('Registered');
    }

    public function test_status_page_404_when_disabled(): void
    {
        Setting::set('family_status_enabled', '0');

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertNotFound();
    }

    public function test_status_page_404_with_invalid_token(): void
    {
        Setting::set('family_status_enabled', '1');

        $response = $this->get(route('family.status', 'invalid-token'));
        $response->assertNotFound();
    }

    public function test_status_shows_number_assigned_step(): void
    {
        Setting::set('family_status_enabled', '1');

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertOk();
        $response->assertSee('#50');
    }

    public function test_status_shows_gifts_being_collected(): void
    {
        Setting::set('family_status_enabled', '1');

        Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female', 'age' => 8,
            'gift_level' => GiftLevel::Moderate,
        ]);

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertOk();
        $response->assertSee('Gifts Being Collected');
    }

    public function test_status_shows_delivery_scheduled(): void
    {
        Setting::set('family_status_enabled', '1');

        $this->family->update(['delivery_date' => '2026-12-20']);

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertOk();
        $response->assertSee('Delivery Scheduled');
        $response->assertSee('2026-12-20');
    }

    public function test_status_shows_delivered(): void
    {
        Setting::set('family_status_enabled', '1');

        $this->family->update(['delivery_status' => DeliveryStatus::Delivered]);

        $response = $this->get(route('family.status', $this->family->status_token));
        $response->assertOk();
        $response->assertSee('Delivered');
        $response->assertSee('delivery has been completed');
    }

    public function test_regenerate_token(): void
    {
        $oldToken = $this->family->status_token;

        $response = $this->actingAs($this->santa)->post(
            route('family.regenerateStatus', $this->family)
        );
        $response->assertRedirect();

        $this->family->refresh();
        $this->assertNotEquals($oldToken, $this->family->status_token);
        $this->assertEquals(32, strlen($this->family->status_token));
    }

    public function test_family_user_cannot_regenerate_token(): void
    {
        $familyUser = User::create([
            'username' => 'family', 'first_name' => 'F', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);

        $response = $this->actingAs($familyUser)->post(
            route('family.regenerateStatus', $this->family)
        );
        $response->assertForbidden();
    }
}
