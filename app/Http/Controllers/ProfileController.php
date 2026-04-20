<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $user->show_on_website = $request->boolean('show_on_website');

        $action = $data['avatar_action'] ?? null;

        // Admin can restrict avatar changes. Persist the visibility toggle
        // before short-circuiting so the user's checkbox change is not silently dropped.
        if ($user->avatar_restricted && $action) {
            $user->save();

            return redirect()->route('profile.edit')
                ->with('error', 'Your avatar has been locked by an administrator.');
        }

        if ($action === 'upload' && $request->hasFile('avatar')) {
            if ($user->avatar_path && !str_starts_with($user->avatar_path, 'dicebear:')) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        } elseif ($action === 'randomize') {
            if ($user->avatar_path && !str_starts_with($user->avatar_path, 'dicebear:')) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $seed = $data['avatar_seed'] ?? bin2hex(random_bytes(4));
            $user->avatar_path = 'dicebear:' . $seed;
        } elseif ($action === 'remove') {
            if ($user->avatar_path && !str_starts_with($user->avatar_path, 'dicebear:')) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated.');
    }
}
