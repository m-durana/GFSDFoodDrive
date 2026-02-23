<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_users_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/users');
        $response->assertStatus(200);
    }

    public function test_user_can_be_created(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/users', [
            'username' => 'new_volunteer',
            'first_name' => 'New',
            'last_name' => 'Volunteer',
            'password' => 'securepass1',
            'password_confirmation' => 'securepass1',
            'role' => 'family',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'new_volunteer',
            'permission' => 7,
        ]);
    }

    public function test_user_creation_requires_username(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/users', [
            'first_name' => 'No',
            'last_name' => 'Username',
            'password' => 'securepass1',
            'password_confirmation' => 'securepass1',
            'role' => 'family',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_duplicate_username_rejected(): void
    {
        User::create([
            'username' => 'taken',
            'first_name' => 'Already',
            'last_name' => 'Taken',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/users', [
            'username' => 'taken',
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'password' => 'securepass1',
            'password_confirmation' => 'securepass1',
            'role' => 'family',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::create([
            'username' => 'update_me',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/users/{$user->id}", [
            'first_name' => 'New',
            'last_name' => 'Name',
            'role' => 'coordinator',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New',
            'permission' => 8,
        ]);
    }

    public function test_user_password_can_be_reset(): void
    {
        $user = User::create([
            'username' => 'reset_me',
            'first_name' => 'Reset',
            'last_name' => 'Password',
            'password' => 'oldpassword1',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/users/{$user->id}/reset-password", [
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertRedirect();
    }

    public function test_password_reset_requires_confirmation(): void
    {
        $user = User::create([
            'username' => 'confirm_me',
            'first_name' => 'Confirm',
            'last_name' => 'Password',
            'password' => 'oldpassword1',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/users/{$user->id}/reset-password", [
            'password' => 'newpassword1',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_reset_minimum_length(): void
    {
        $user = User::create([
            'username' => 'short_pw',
            'first_name' => 'Short',
            'last_name' => 'Password',
            'password' => 'oldpassword1',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/users/{$user->id}/reset-password", [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_be_deactivated(): void
    {
        $user = User::create([
            'username' => 'deactivate_me',
            'first_name' => 'Deactivate',
            'last_name' => 'Me',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/users/{$user->id}", [
            'first_name' => 'Deactivate',
            'last_name' => 'Me',
            'role' => 'inactive',
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->permission);
    }
}
