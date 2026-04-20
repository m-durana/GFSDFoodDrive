<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
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

    /**
     * Regression: A-10 SEC-INPUT P0 — Laravel's `image` rule accepts SVG, which can carry
     * embedded <script> and be served verbatim from /storage/avatars/*. The FormRequest now
     * pins allowed mimes to jpg/png/webp, so an SVG upload must be rejected.
     */
    public function test_svg_avatar_upload_is_rejected(): void
    {
        Storage::fake('public');

        $svg = <<<SVG
<?xml version="1.0"?>
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><script>alert(1)</script></svg>
SVG;
        $file = UploadedFile::fake()->createWithContent('evil.svg', $svg);

        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'avatar_action' => 'upload',
                'avatar' => $file,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('avatar');

        $this->assertNull($this->user->fresh()->avatar_path);
        $this->assertEmpty(Storage::disk('public')->files('avatars'));
    }

    /**
     * Regression: A-10 — no max image dimensions means a 2 MB 20000x20000 JPEG can DoS
     * consumers that fetch and decode. The FormRequest now caps dimensions at 2048x2048.
     */
    public function test_oversized_image_dimensions_are_rejected(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'avatar_action' => 'upload',
                'avatar' => UploadedFile::fake()->image('huge.jpg', 2100, 2100),
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('avatar');
        $this->assertNull($this->user->fresh()->avatar_path);
    }

    /**
     * Regression: A-10 — `avatar_seed` used to be read with no length cap or character
     * allow-list. A megabyte-long seed would bloat the users row. The FormRequest now
     * enforces alpha_dash|max:32.
     */
    public function test_avatar_seed_length_is_capped(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'avatar_action' => 'randomize',
                'avatar_seed' => str_repeat('a', 100),
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('avatar_seed');
        $this->assertNull($this->user->fresh()->avatar_path);
    }

    public function test_avatar_seed_rejects_non_alpha_dash_chars(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'avatar_action' => 'randomize',
                'avatar_seed' => '<script>alert(1)</script>',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('avatar_seed');
        $this->assertNull($this->user->fresh()->avatar_path);
    }

    /**
     * Regression: A-10 — `avatar_restricted` short-circuit used to silently discard the
     * `show_on_website` change. The controller now persists visibility before redirecting.
     */
    public function test_restricted_avatar_does_not_drop_visibility_change(): void
    {
        $this->user->update(['avatar_restricted' => true, 'show_on_website' => false]);

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'show_on_website' => true,
            'avatar_action' => 'randomize',
            'avatar_seed' => 'ignored',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');

        $fresh = $this->user->fresh();
        $this->assertTrue((bool) $fresh->show_on_website, 'visibility toggle must persist even when avatar is locked');
        $this->assertNull($fresh->avatar_path, 'avatar must not be mutated when locked');
    }

    public function test_restricted_avatar_blocks_upload(): void
    {
        Storage::fake('public');
        $this->user->update(['avatar_restricted' => true]);

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'avatar_action' => 'upload',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        $this->assertNull($this->user->fresh()->avatar_path);
        $this->assertEmpty(Storage::disk('public')->files('avatars'));
    }

    /**
     * Regression: A-10 — there was no throttle on PUT /profile, so an authenticated user
     * could hammer the endpoint to fill the public disk. Route now uses throttle:30,1.
     */
    public function test_profile_update_is_throttled(): void
    {
        RateLimiter::clear('throttle:30,1|' . $this->user->id);

        $this->actingAs($this->user);

        for ($i = 0; $i < 30; $i++) {
            $this->put(route('profile.update'), ['show_on_website' => (bool) ($i % 2)])
                ->assertRedirect(route('profile.edit'));
        }

        $response = $this->put(route('profile.update'), ['show_on_website' => true]);
        $response->assertStatus(429);
    }

    public function test_golden_path_save_changes_with_no_avatar_action_persists_visibility(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'show_on_website' => true,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');
        $this->assertTrue((bool) $this->user->fresh()->show_on_website);
    }
}
