<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * REL-46c: Santa-editable Spatie roles.
 *
 * Santa can create / rename / delete custom roles, but built-in roles
 * (santa, system_coordinator, coordinator, family, ninja, self_service)
 * are protected from rename and delete.
 */
class SantaRolesControllerTest extends TestCase
{
    use RefreshDatabase;

    private function santa(): User
    {
        $u = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $u->assignRole('santa');
        return $u;
    }

    private function coordinator(): User
    {
        $u = User::create([
            'username' => 'coord', 'first_name' => 'C', 'last_name' => 'X',
            'password' => 'password', 'permission' => 8,
        ]);
        $u->assignRole('coordinator');
        return $u;
    }

    public function test_index_lists_built_in_roles_and_is_santa_only(): void
    {
        $this->actingAs($this->santa())
            ->get(route('santa.roles.index'))
            ->assertOk()
            ->assertSee('santa')
            ->assertSee('coordinator');
    }

    public function test_non_santa_cannot_reach_roles_index(): void
    {
        $this->actingAs($this->coordinator())
            ->get(route('santa.roles.index'))
            ->assertForbidden();
    }

    public function test_santa_can_create_custom_role(): void
    {
        $this->actingAs($this->santa())
            ->post(route('santa.roles.store'), ['name' => 'donor_liaison'])
            ->assertRedirect(route('santa.roles.index'));

        $this->assertTrue(Role::where('name', 'donor_liaison')->exists());
    }

    public function test_store_rejects_invalid_name(): void
    {
        $this->actingAs($this->santa())
            ->post(route('santa.roles.store'), ['name' => 'Bad Name!'])
            ->assertSessionHasErrors('name');

        $this->assertFalse(Role::where('name', 'Bad Name!')->exists());
    }

    public function test_store_rejects_duplicate_name(): void
    {
        $this->actingAs($this->santa())
            ->post(route('santa.roles.store'), ['name' => 'santa'])
            ->assertSessionHasErrors('name');
    }

    public function test_santa_cannot_rename_built_in_role(): void
    {
        $role = Role::where('name', 'coordinator')->first();
        $this->actingAs($this->santa())
            ->put(route('santa.roles.update', $role), ['name' => 'helper'])
            ->assertRedirect(route('santa.roles.index'))
            ->assertSessionHas('error');

        $this->assertSame('coordinator', $role->fresh()->name);
    }

    public function test_santa_can_rename_custom_role_and_sudoer_mirrors(): void
    {
        $role = Role::create(['name' => 'pilot', 'guard_name' => 'web']);
        \App\Models\Setting::set('sudoer_roles', json_encode(['pilot']));
        User::clearSudoerRolesCache();

        $this->actingAs($this->santa())
            ->put(route('santa.roles.update', $role), ['name' => 'aviator'])
            ->assertRedirect(route('santa.roles.index'));

        $this->assertSame('aviator', $role->fresh()->name);

        User::clearSudoerRolesCache();
        $this->assertContains('aviator', User::sudoerRolesList());
        $this->assertNotContains('pilot', User::sudoerRolesList());
    }

    public function test_santa_cannot_delete_built_in_role(): void
    {
        $role = Role::where('name', 'santa')->first();
        $this->actingAs($this->santa())
            ->delete(route('santa.roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertTrue(Role::where('name', 'santa')->exists());
    }

    public function test_santa_cannot_delete_role_with_users(): void
    {
        $role = Role::create(['name' => 'temp_role', 'guard_name' => 'web']);
        $u = User::create([
            'username' => 'tu', 'first_name' => 'T', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);
        $u->assignRole('temp_role');

        $this->actingAs($this->santa())
            ->delete(route('santa.roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertTrue(Role::where('name', 'temp_role')->exists());
    }

    public function test_santa_can_delete_empty_custom_role(): void
    {
        $role = Role::create(['name' => 'extinct', 'guard_name' => 'web']);

        $this->actingAs($this->santa())
            ->delete(route('santa.roles.destroy', $role))
            ->assertRedirect(route('santa.roles.index'));

        $this->assertFalse(Role::where('name', 'extinct')->exists());
    }
}
