<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
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
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/settings');
        $response->assertStatus(200);
    }

    public function test_self_registration_can_be_enabled(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/settings', [
            'self_registration_enabled' => true,
            'season_year' => '2026',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1', Setting::get('self_registration_enabled'));
    }

    public function test_self_registration_can_be_disabled(): void
    {
        Setting::set('self_registration_enabled', '1');

        $response = $this->actingAs($this->santa)->post('/santa/settings', [
            'season_year' => '2026',
        ]);

        $response->assertRedirect();
        $this->assertEquals('0', Setting::get('self_registration_enabled'));
    }

    public function test_season_year_can_be_set(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/settings', [
            'season_year' => '2027',
        ]);

        $response->assertRedirect();
        $this->assertEquals('2027', Setting::get('season_year'));
    }

    public function test_setting_model_get_returns_default(): void
    {
        $this->assertEquals('fallback', Setting::get('nonexistent_key', 'fallback'));
    }

    public function test_setting_model_set_creates_and_updates(): void
    {
        Setting::set('test_key', 'value1');
        $this->assertEquals('value1', Setting::get('test_key'));

        Setting::set('test_key', 'value2');
        $this->assertEquals('value2', Setting::get('test_key'));

        // Should only have one record
        $this->assertEquals(1, Setting::where('key', 'test_key')->count());
    }

    public function test_family_user_cannot_access_settings(): void
    {
        $familyUser = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->actingAs($familyUser)->get('/santa/settings')->assertStatus(403);
        $this->actingAs($familyUser)->post('/santa/settings', [])->assertStatus(403);
    }
}
