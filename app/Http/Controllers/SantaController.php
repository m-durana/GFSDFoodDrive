<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SantaController extends Controller
{
    public function index(): View
    {
        return view('santa.index');
    }

    public function allFamilies(): View
    {
        return view('santa.families', [
            'families' => Family::with('user')->orderBy('family_number')->get(),
        ]);
    }

    public function numberAssignment()
    {
        // TODO: Phase 3 - Implement number assignment
        abort(501, 'Number assignment will be implemented in Phase 3.');
    }

    public function updateFamilyNumber(Request $request)
    {
        // TODO: Phase 3
        abort(501, 'Family number update will be implemented in Phase 3.');
    }

    public function users(): View
    {
        return view('santa.users', [
            'users' => User::orderBy('first_name')->get(),
        ]);
    }

    /**
     * Create a new user account (Santa-initiated registration).
     * Replaces legacy Santa/InsertNewUser.php
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $roleToPermission = [
            'family' => 7,
            'coordinator' => 8,
            'santa' => 9,
        ];

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => $request->password,
            'permission' => $roleToPermission[$request->role],
        ]);

        $user->assignRole($request->role);

        return redirect()->route('santa.users')
            ->with('success', "User '{$user->username}' created successfully.");
    }

    /**
     * Update an existing user account.
     * Replaces legacy Santa/UpdateUser.php
     */
    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $roleToPermission = [
            'family' => 7,
            'coordinator' => 8,
            'santa' => 9,
            'inactive' => 0,
        ];

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'permission' => $roleToPermission[$request->role],
        ];

        // Only update password if one was provided
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Sync Spatie role
        $user->syncRoles([]);
        if ($request->role !== 'inactive') {
            $user->assignRole($request->role);
        }

        return redirect()->route('santa.users')
            ->with('success', "User '{$user->username}' updated successfully.");
    }

    /**
     * Reset a user's password (admin-initiated).
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $request->password,
        ]);

        return redirect()->route('santa.users')
            ->with('success', "Password reset for '{$user->username}'.");
    }
}
