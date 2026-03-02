<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'username' => 'testuser', 'first_name' => 'Test', 'last_name' => 'User',
            'password' => 'password', 'permission' => 8,
        ]);
    }

    public function test_profile_edit_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee('testuser');
    }

    public function test_show_on_website_can_be_toggled(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'show_on_website' => true,
        ]);
        $response->assertRedirect(route('profile.edit'));
        $this->assertTrue((bool) $this->user->fresh()->show_on_website);
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'avatar_action' => 'upload',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);
        $response->assertRedirect(route('profile.edit'));

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_path);
        Storage::disk('public')->assertExists($this->user->avatar_path);
    }

    public function test_avatar_can_be_randomized(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'avatar_action' => 'randomize',
            'avatar_seed' => 'test-seed',
        ]);
        $response->assertRedirect(route('profile.edit'));

        $this->user->refresh();
        $this->assertEquals('dicebear:test-seed', $this->user->avatar_path);
    }

    public function test_avatar_can_be_removed(): void
    {
        $this->user->update(['avatar_path' => 'dicebear:old-seed']);

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'avatar_action' => 'remove',
        ]);
        $response->assertRedirect(route('profile.edit'));

        $this->assertNull($this->user->fresh()->avatar_path);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }
}
