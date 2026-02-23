<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google for OAuth.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');

        if (!$clientId || !$clientSecret) {
            return redirect()->route('login')
                ->with('status', 'Google Sign-In is not configured. Please contact your administrator.');
        }

        // Configure Socialite dynamically from settings
        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => route('auth.google.callback'),
        ]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function callback(): RedirectResponse
    {
        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');

        if (!$clientId || !$clientSecret) {
            return redirect()->route('login')
                ->with('status', 'Google Sign-In is not configured.');
        }

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => route('auth.google.callback'),
        ]);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('status', 'Google authentication failed. Please try again.');
        }

        // Find user by email
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('status', 'No account found for ' . $googleUser->getEmail() . '. Ask your administrator to create an account first.');
        }

        if (!$user->isActive()) {
            return redirect()->route('login')
                ->with('status', 'Your account is inactive. Please contact your administrator.');
        }

        Auth::login($user, true);

        // Redirect based on role
        if ($user->isSanta()) {
            return redirect()->route('santa.index');
        }
        if ($user->isCoordinator()) {
            return redirect()->route('coordinator.index');
        }
        return redirect()->route('family.index');
    }
}
