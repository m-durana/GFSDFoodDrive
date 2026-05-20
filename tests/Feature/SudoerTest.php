<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sudoer flag: gives a non-Santa user Santa-equivalent access while toggled on,
 * with every mutating request audit-logged via LogSudoActivity middleware.
 */
class SudoerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username, int $perm, bool $sudoer = false): User
    {
        return User::create([
            'username' => $username,
            'first_name' => 'F', 'last_name' => 'L',
            'password' => 'password', 'permission' => $perm,
            'is_sudoer' => $sudoer,
        ]);
    }

    public function test_sudoer_is_treated_as_santa_for_access(): void
    {
        $coord = $this->makeUser('sudo_coord', 8, true);

        $this->assertTrue($coord->isSanta(), 'sudoer must report as Santa for access checks');
        $this->assertFalse($coord->isOriginalSanta(), 'sudoer is NOT the original Santa tier');
        $this->assertTrue($coord->isSudoer());
    }

    public function test_real_santa_does_not_report_as_sudoer(): void
    {
        $santa = $this->makeUser('real_santa', 9, false);
        $this->assertTrue($santa->isOriginalSanta());
        $this->assertFalse($santa->isSudoer());
    }

    public function test_sudoer_can_reach_a_santa_only_route(): void
    {
        $coord = $this->makeUser('sudo_settings', 8, true);

        // santa.users is gated permission:santa.
        $this->actingAs($coord)
            ->get(route('santa.users'))
            ->assertOk();
    }

    public function test_non_sudoer_coordinator_is_blocked_from_santa_only_route(): void
    {
        $coord = $this->makeUser('no_sudo', 8, false);

        $this->actingAs($coord)
            ->get(route('santa.users'))
            ->assertForbidden();
    }

    public function test_mutating_request_by_sudoer_is_audit_logged(): void
    {
        $coord = $this->makeUser('audited_sudo', 8, true);

        // Pick a simple Santa-only POST route. updateSettings is one.
        $this->actingAs($coord)
            ->post(route('santa.updateSettings'), [
                'footer_text' => 'sudo test',
                'season_year' => date('Y'),
            ])->assertRedirect();

        $log = AuditLog::where('auditable_type', 'sudo_action')
            ->where('actor_id', $coord->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'expected a sudo_action audit log entry');
        $this->assertSame('sudo_post', $log->action);
        $this->assertSame('santa.updateSettings', $log->after['route'] ?? null);
    }

    public function test_get_request_by_sudoer_is_not_logged(): void
    {
        $coord = $this->makeUser('readonly_sudo', 8, true);

        $this->actingAs($coord)->get(route('santa.users'))->assertOk();

        $count = AuditLog::whereIn('auditable_type', ['sudo_action', 'mutation'])
            ->where('actor_id', $coord->id)
            ->count();

        $this->assertSame(0, $count, 'GET requests should not be audit-logged');
    }

    public function test_role_level_sudo_grants_santa_access(): void
    {
        \App\Models\User::clearSudoerRolesCache();
        \App\Models\Setting::set('sudoer_roles', json_encode(['system_coordinator']));
        \App\Models\User::clearSudoerRolesCache();

        $sc = $this->makeUser('rolesudo_sc', 8, false);
        if (method_exists($sc, 'assignRole')) {
            $sc->assignRole('system_coordinator');
        }
        $sc->refresh();

        $this->assertTrue($sc->isSudoer(), 'role-listed user should be sudoer');
        $this->assertTrue($sc->isSanta(), 'role-listed user should pass isSanta');

        $this->actingAs($sc)
            ->get(route('santa.users'))
            ->assertOk();
    }

    public function test_role_level_sudo_logs_mutations_as_sudo_action(): void
    {
        \App\Models\User::clearSudoerRolesCache();
        \App\Models\Setting::set('sudoer_roles', json_encode(['system_coordinator']));
        \App\Models\User::clearSudoerRolesCache();

        $sc = $this->makeUser('rolesudo_audit', 8, false);
        if (method_exists($sc, 'assignRole')) {
            $sc->assignRole('system_coordinator');
        }

        $this->actingAs($sc)
            ->post(route('santa.updateSettings'), [
                'footer_text' => 'role-sudo test',
                'season_year' => date('Y'),
            ])->assertRedirect();

        $log = AuditLog::where('auditable_type', 'sudo_action')
            ->where('actor_id', $sc->id)
            ->latest('id')->first();

        $this->assertNotNull($log, 'role-level sudo mutation should be logged as sudo_action');
        $this->assertTrue($log->after['is_sudoer'] ?? false);
    }

    public function test_untoggling_sudoer_role_revokes_access(): void
    {
        \App\Models\User::clearSudoerRolesCache();
        \App\Models\Setting::set('sudoer_roles', json_encode([])); // empty list
        \App\Models\User::clearSudoerRolesCache();

        $sc = $this->makeUser('rolesudo_revoked', 8, false);
        if (method_exists($sc, 'assignRole')) {
            $sc->assignRole('system_coordinator');
        }

        $this->actingAs($sc)
            ->get(route('santa.users'))
            ->assertForbidden();
    }

    public function test_system_engineer_position_is_same_identity_as_system_coordinator_role(): void
    {
        $u = User::create([
            'username' => 'sysengineer',
            'first_name' => 'Sys', 'last_name' => 'Eng',
            'password' => 'password', 'permission' => 8,
            'position' => 'System Engineer',
        ]);
        // No Spatie role assigned — yet isSystemCoordinator() must still be true
        // because "System Engineer" position = system_coordinator role identity.
        $this->assertTrue($u->isSystemCoordinator());
        $this->assertTrue($u->canSeePii());
    }

    public function test_mutating_request_by_normal_santa_is_logged_as_mutation(): void
    {
        $santa = $this->makeUser('plain_santa', 9, false);

        $this->actingAs($santa)
            ->post(route('santa.updateSettings'), [
                'footer_text' => 'normal santa test',
                'season_year' => date('Y'),
            ])->assertRedirect();

        $log = AuditLog::where('auditable_type', 'mutation')
            ->where('actor_id', $santa->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'normal Santa mutations are also audit-logged');
        $this->assertSame('post', $log->action);
        $this->assertFalse($log->after['is_sudoer'] ?? null);
    }
}
