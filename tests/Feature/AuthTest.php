<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/family');
        $response->assertRedirect('/login');
    }

    public function test_santa_user_can_login(): void
    {
        $user = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->post('/login', [
            'username' => 'santa_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_rejected(): void
    {
        User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->post('/login', [
            'username' => 'santa_test',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    public function test_family_user_cannot_access_santa_routes(): void
    {
        $user = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($user)->get('/santa');
        $response->assertStatus(403);
    }

    public function test_family_user_cannot_access_coordinator_routes(): void
    {
        $user = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($user)->get('/coordinator');
        $response->assertStatus(403);
    }

    public function test_coordinator_can_access_coordinator_routes(): void
    {
        $user = User::create([
            'username' => 'coord_test',
            'first_name' => 'Test',
            'last_name' => 'Coord',
            'password' => 'password123',
            'permission' => 8,
        ]);

        $response = $this->actingAs($user)->get('/coordinator');
        $response->assertStatus(200);
    }

    public function test_santa_can_access_all_routes(): void
    {
        $user = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->actingAs($user)->get('/santa')->assertStatus(200);
        $this->actingAs($user)->get('/coordinator')->assertStatus(200);
        $this->actingAs($user)->get('/family')->assertStatus(200);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'username' => 'test_user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->actingAs($user)->post('/logout');
        $this->assertGuest();
    }
}
