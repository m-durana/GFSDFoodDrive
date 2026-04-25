<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for BUGS.md P0 Authorization hole (H-01/H-02):
 * Help topics have a `role` field (all|coordinator|santa). Previously decorative
 * — advisors could read Santa-only topics (settings, legacy-import, command-center).
 * HelpController now enforces the role on show() and filters the sidebar nav.
 */
class HelpRoleEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermission(int $permission, string $username): User
    {
        return User::create([
            'username' => $username,
            'first_name' => 'T',
            'last_name' => 'U',
            'password' => 'password123',
            'permission' => $permission,
        ]);
    }

    public function test_advisor_cannot_read_santa_only_topic_settings(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor');

        $this->actingAs($advisor)
            ->get('/help/settings')
            ->assertStatus(403);
    }

    public function test_advisor_cannot_read_santa_only_topic_legacy_import(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor2');

        $this->actingAs($advisor)
            ->get('/help/legacy-import')
            ->assertStatus(403);
    }

    public function test_advisor_cannot_read_santa_only_topic_command_center(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor3');

        $this->actingAs($advisor)
            ->get('/help/command-center')
            ->assertStatus(403);
    }

    public function test_coordinator_cannot_read_santa_only_topic(): void
    {
        $coord = $this->userWithPermission(8, 'help_coord');

        $this->actingAs($coord)
            ->get('/help/settings')
            ->assertStatus(403);
    }

    public function test_coordinator_can_read_coordinator_topic(): void
    {
        $coord = $this->userWithPermission(8, 'help_coord2');

        $this->actingAs($coord)
            ->get('/help/family-management')
            ->assertOk();
    }

    public function test_advisor_cannot_read_coordinator_topic(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor4');

        $this->actingAs($advisor)
            ->get('/help/family-management')
            ->assertStatus(403);
    }

    public function test_advisor_can_read_all_role_topic(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor5');

        $this->actingAs($advisor)
            ->get('/help/getting-started')
            ->assertOk();
    }

    public function test_santa_can_read_any_topic(): void
    {
        $santa = $this->userWithPermission(9, 'help_santa');

        $this->actingAs($santa)->get('/help/settings')->assertOk();
        $this->actingAs($santa)->get('/help/legacy-import')->assertOk();
        $this->actingAs($santa)->get('/help/command-center')->assertOk();
    }

    public function test_index_sidebar_hides_santa_only_topics_from_advisor(): void
    {
        $advisor = $this->userWithPermission(7, 'help_advisor6');

        $response = $this->actingAs($advisor)->get('/help');
        $response->assertOk();

        $response->assertDontSee('href="' . url('/help/settings') . '"', false);
        $response->assertDontSee('href="' . url('/help/legacy-import') . '"', false);
        $response->assertDontSee('href="' . url('/help/command-center') . '"', false);
        $response->assertSee('href="' . url('/help/getting-started') . '"', false);
    }
}
