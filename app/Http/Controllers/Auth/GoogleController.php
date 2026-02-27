<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function configureGoogle(): bool
    {
        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');

        if (!$clientId || !$clientSecret) {
            return false;
        }

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => route('auth.google.callback'),
        ]);

        return true;
    }

    /**
     * Redirect to Google for OAuth.
     */
    public function redirect(): RedirectResponse
    {
        if (!$this->configureGoogle()) {
            return redirect()->route('login')
                ->with('status', 'Google Sign-In is not configured. Please contact your administrator.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        if (!$this->configureGoogle()) {
            return redirect()->route('login')
                ->with('status', 'Google Sign-In is not configured.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('status', 'Google authentication failed. Please try again.');
        }

        // Find existing user by email
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (!$user->isActive()) {
                return redirect()->route('login')
                    ->with('status', 'Your account is inactive. Please contact your administrator.');
            }

            Auth::login($user, true);

            if ($user->isSanta()) {
                return redirect()->route('santa.index');
            }
            if ($user->isCoordinator()) {
                return redirect()->route('coordinator.index');
            }
            return redirect()->route('family.index');
        }

        // No account exists — check for existing pending request
        $existingRequest = AccessRequest::where('email', $googleUser->getEmail())
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->route('login')
                ->with('status', 'Your access request is pending approval. Please check back later or contact your administrator.');
        }

        // Check if they were previously denied
        $deniedRequest = AccessRequest::where('email', $googleUser->getEmail())
            ->where('status', 'denied')
            ->latest()
            ->first();

        if ($deniedRequest) {
            return redirect()->route('login')
                ->with('status', 'Your previous access request was denied' . ($deniedRequest->deny_reason ? ': ' . $deniedRequest->deny_reason : '. Please contact your administrator.'));
        }

        // Store Google user info in session and redirect to role selection
        $request->session()->put('google_oauth_user', [
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.request');
    }

    /**
     * Show role selection form for new Google OAuth users.
     */
    public function requestAccess(Request $request): View|RedirectResponse
    {
        $googleUser = $request->session()->get('google_oauth_user');

        if (!$googleUser) {
            return redirect()->route('login')
                ->with('status', 'Please sign in with Google first.');
        }

        $schools = \App\Models\SchoolRange::orderBy('sort_order')->pluck('school_name')->toArray();
        $positions = array_filter(array_map('trim', explode(',', Setting::get('coordinator_positions', 'System Engineer,Activities Coordinator,Giving Tree Coordinator,Food Manager,Business Operator,Video Producer,NINJA,Marketing Director'))));

        return view('auth.request-access', compact('googleUser', 'schools', 'positions'));
    }

    /**
     * Submit access request from Google OAuth user.
     */
    public function submitRequest(Request $request): RedirectResponse
    {
        $googleUser = $request->session()->get('google_oauth_user');

        if (!$googleUser) {
            return redirect()->route('login')
                ->with('status', 'Session expired. Please sign in with Google again.');
        }

        $request->validate([
            'requested_role' => 'required|in:family,coordinator',
            'school_source' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        AccessRequest::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'],
                'google_id' => $googleUser['google_id'],
                'avatar' => $googleUser['avatar'],
                'requested_role' => $request->requested_role,
                'school_source' => $request->school_source,
                'position' => $request->requested_role === 'coordinator' ? $request->position : null,
                'status' => 'pending',
                'deny_reason' => null,
                'reviewed_by' => null,
            ]
        );

        $request->session()->forget('google_oauth_user');

        return redirect()->route('login')
            ->with('success', 'Your access request has been submitted! An administrator will review it shortly.');
    }
}
