<?php

namespace App\Http\Controllers\Santa;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * REL-46c: Santa-editable Spatie roles.
 *
 * Built-in roles (santa, system_coordinator, coordinator, family, ninja, self_service)
 * are protected — they cannot be renamed or deleted because route middleware and
 * controller logic reference them by name. Santa CAN create new roles freely
 * and rename/delete those custom roles.
 *
 * Newly-created roles default to permission tier 7 (Advisor baseline) when
 * assigned to a user; section-level access is governed by the Santa-editable
 * coordinator_section_map (REL-44) plus the sudoer_roles list (REL-47).
 */
class RolesController extends Controller
{
    /** Names that must never be renamed or deleted. */
    public const PROTECTED_ROLES = [
        'santa',
        'system_coordinator',
        'coordinator',
        'family',
        'ninja',
        'self_service',
    ];

    public function index(): View
    {
        $roles = Role::orderBy('name')->withCount('users')->get();
        $sudoerRoles = User::sudoerRolesList();

        return view('santa.roles', [
            'roles'           => $roles,
            'protectedRoles'  => self::PROTECTED_ROLES,
            'sudoerRoles'     => $sudoerRoles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique(config('permission.table_names.roles', 'roles'), 'name'),
            ],
        ], [
            'name.regex' => 'Role names must be lowercase letters, digits, and underscores only.',
        ]);

        Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        return redirect()->route('santa.roles.index')
            ->with('success', "Role '{$data['name']}' created.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('santa.roles.index')
                ->with('error', "Role '{$role->name}' is built-in and cannot be renamed.");
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique(config('permission.table_names.roles', 'roles'), 'name')->ignore($role->id),
            ],
        ]);

        $old = $role->name;
        $role->update(['name' => $data['name']]);

        // Mirror the rename into sudoer_roles if it was tracked there.
        $sudoer = User::sudoerRolesList();
        if (in_array($old, $sudoer, true)) {
            $sudoer = array_values(array_unique(array_map(
                fn ($n) => $n === $old ? $data['name'] : $n,
                $sudoer
            )));
            \App\Models\Setting::set('sudoer_roles', json_encode($sudoer));
            User::clearSudoerRolesCache();
        }

        return redirect()->route('santa.roles.index')
            ->with('success', "Role '{$old}' renamed to '{$data['name']}'.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('santa.roles.index')
                ->with('error', "Role '{$role->name}' is built-in and cannot be deleted.");
        }

        if ($role->users()->exists()) {
            return redirect()->route('santa.roles.index')
                ->with('error', "Role '{$role->name}' still has users assigned. Reassign them first.");
        }

        $name = $role->name;

        // Drop from sudoer_roles if it was tracked there.
        $sudoer = User::sudoerRolesList();
        if (in_array($name, $sudoer, true)) {
            $sudoer = array_values(array_filter($sudoer, fn ($n) => $n !== $name));
            \App\Models\Setting::set('sudoer_roles', json_encode($sudoer));
            User::clearSudoerRolesCache();
        }

        $role->delete();

        return redirect()->route('santa.roles.index')
            ->with('success', "Role '{$name}' deleted.");
    }
}
