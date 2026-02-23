<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_button_hidden_when_not_configured(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertDontSee('Sign in with Google');
    }

    public function test_google_login_button_shown_when_configured(): void
    {
        Setting::set('google_client_id', 'test-client-id.apps.googleusercontent.com');
        Setting::set('google_client_secret', 'test-secret');

        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in with Google');
    }

    public function test_google_redirect_fails_gracefully_when_not_configured(): void
    {
        $response = $this->get('/auth/google');
        $response->assertRedirect('/login');
    }

    public function test_settings_page_shows_google_oauth_fields(): void
    {
        $santa = User::create([
            'username' => 'santa_oauth',
            'first_name' => 'Santa',
            'last_name' => 'OAuth',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->actingAs($santa)->get('/santa/settings');
        $response->assertStatus(200);
        $response->assertSee('Google Client ID');
        $response->assertSee('Google Client Secret');
    }

    public function test_settings_saves_google_oauth_config(): void
    {
        $santa = User::create([
            'username' => 'santa_oauth2',
            'first_name' => 'Santa',
            'last_name' => 'OAuth',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->actingAs($santa)->post('/santa/settings', [
            'season_year' => '2026',
            'google_client_id' => 'my-client-id',
            'google_client_secret' => 'my-client-secret',
        ]);

        $this->assertEquals('my-client-id', Setting::get('google_client_id'));
        $this->assertEquals('my-client-secret', Setting::get('google_client_secret'));
    }
}
