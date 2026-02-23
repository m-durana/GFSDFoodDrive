<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private function validFamilyData(): array
    {
        return [
            'family_name' => 'Self Service Family',
            'address' => '456 Public St, Granite Falls, WA',
            'phone1' => '360-555-5678',
            'female_adults' => 1,
            'male_adults' => 0,
            'infants' => 0,
            'young_children' => 1,
            'children_count' => 0,
            'tweens' => 0,
            'teenagers' => 0,
            'preferred_language' => 'English',
            'need_for_help' => 'Need help this season',
        ];
    }

    public function test_self_service_blocked_when_disabled(): void
    {
        Setting::set('self_registration_enabled', '0');

        $response = $this->get('/register-family');
        $response->assertStatus(403);
    }

    public function test_self_service_form_loads_when_enabled(): void
    {
        Setting::set('self_registration_enabled', '1');

        $response = $this->get('/register-family');
        $response->assertStatus(200);
    }

    public function test_self_service_submission_creates_family(): void
    {
        Setting::set('self_registration_enabled', '1');

        $response = $this->post('/register-family', $this->validFamilyData());

        $response->assertRedirect(route('self-service.success'));
        $this->assertDatabaseHas('families', ['family_name' => 'Self Service Family']);

        $family = Family::where('family_name', 'Self Service Family')->first();
        $this->assertNull($family->user_id); // anonymous submission
        $this->assertEquals(1, $family->number_of_adults);
        $this->assertEquals(1, $family->number_of_children);
    }

    public function test_self_service_submission_blocked_when_disabled(): void
    {
        Setting::set('self_registration_enabled', '0');

        $response = $this->post('/register-family', $this->validFamilyData());
        $response->assertStatus(403);
    }

    public function test_self_service_success_page_loads_when_enabled(): void
    {
        Setting::set('self_registration_enabled', '1');

        $response = $this->get('/register-family/success');
        $response->assertStatus(200);
    }

    public function test_self_service_success_page_blocked_when_disabled(): void
    {
        Setting::set('self_registration_enabled', '0');

        $response = $this->get('/register-family/success');
        $response->assertStatus(403);
    }

    public function test_self_service_validation_enforced(): void
    {
        Setting::set('self_registration_enabled', '1');

        $response = $this->post('/register-family', []);
        $response->assertSessionHasErrors(['family_name', 'address', 'phone1']);
    }

    public function test_authenticated_user_can_self_register(): void
    {
        Setting::set('self_registration_enabled', '1');

        $user = User::create([
            'username' => 'self_test',
            'first_name' => 'Self',
            'last_name' => 'Test',
            'password' => 'password123',
            'permission' => 6,
        ]);

        $response = $this->actingAs($user)->post('/register-family', $this->validFamilyData());

        $response->assertRedirect(route('self-service.success'));
        $family = Family::where('family_name', 'Self Service Family')->first();
        $this->assertEquals($user->id, $family->user_id);
    }
}
