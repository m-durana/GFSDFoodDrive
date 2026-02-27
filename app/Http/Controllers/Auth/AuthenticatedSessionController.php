<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login and redirect based on role.
     *
     * Mirrors legacy auth.php behavior:
     *   Permission 7 -> /family
     *   Permission 8 -> /coordinator
     *   Permission 9 -> /santa
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // When Google OAuth is configured, only Santa can use password login
        $googleConfigured = Setting::get('google_client_id') && Setting::get('google_client_secret');
        if ($googleConfigured && !$user->isSanta()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Password login is reserved for administrators. Please use "Sign in with Google" instead.',
            ]);
        }

        if ($user->isSanta()) {
            return redirect()->intended(route('santa.index'));
        }

        if ($user->isCoordinator()) {
            return redirect()->intended(route('coordinator.index'));
        }

        if ($user->isFamily()) {
            return redirect()->intended(route('family.index'));
        }

        // Inactive or unknown permission level
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'username' => 'Your account is inactive. Please contact an administrator.',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
