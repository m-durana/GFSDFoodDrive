<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Setting;
use App\Notifications\Adopter;
use App\Notifications\AdoptionConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdoptionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        abort_unless(Setting::get('adopt_a_tag_enabled', '0') === '1', 404);

        // Auto-close portal after deadline
        $deadline = Setting::get('adopt_a_tag_deadline');
        if ($deadline && now()->startOfDay()->gt(\Carbon\Carbon::parse($deadline))) {
            return view('adopt.closed', [
                'deadline' => \Carbon\Carbon::parse($deadline),
                'deliveryDates' => Setting::get('delivery_dates', ''),
            ]);
        }

        $query = Child::availableForAdoption()->with('family:id,family_number');

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('age_min')) {
            $query->where('age', '>=', (int) $request->age_min);
        }
        if ($request->filled('age_max')) {
            $query->where('age', '<=', (int) $request->age_max);
        }
        if ($request->filled('school')) {
            $query->where('school', $request->school);
        }

        $children = $query->get();

        $totalAvailable = Child::availableForAdoption()->count();
        $totalChildren = Child::whereHas('family', fn ($q) => $q->whereNotNull('family_number'))->count();
        $customMessage = Setting::get('adopt_a_tag_message', '');
        $adoptionDeadline = $deadline ? \Carbon\Carbon::parse($deadline) : null;
        $schools = Child::availableForAdoption()
            ->select('school')
            ->distinct()
            ->whereNotNull('school')
            ->where('school', '!=', '')
            ->orderBy('school')
            ->pluck('school');

        return view('adopt.index', compact('children', 'totalAvailable', 'totalChildren', 'customMessage', 'schools', 'adoptionDeadline'));
    }

    public function show(Child $child): View
    {
        abort_unless(Setting::get('adopt_a_tag_enabled', '0') === '1', 404);

        // Block after deadline
        $deadline = Setting::get('adopt_a_tag_deadline');
        if ($deadline && now()->startOfDay()->gt(\Carbon\Carbon::parse($deadline))) {
            abort(404);
        }

        abort_unless(!$child->isAdopted() && $child->family && $child->family->family_number, 404);

        $child->load('family:id,family_number');

        return view('adopt.show', compact('child'));
    }

    public function claim(Request $request, Child $child): RedirectResponse
    {
        abort_unless(Setting::get('adopt_a_tag_enabled', '0') === '1', 404);

        $request->validate([
            'adopter_name' => ['required', 'string', 'max:255'],
            'adopter_email' => ['nullable', 'email', 'max:255'],
            'adopter_phone' => ['nullable', 'string', 'max:255'],
        ]);

        // At least one contact method required
        if (!$request->filled('adopter_email') && !$request->filled('adopter_phone')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['contact' => 'Please provide at least an email or phone number.']);
        }

        // Race condition guard: re-check availability
        if ($child->adoption_token !== null || !$child->family || !$child->family->family_number) {
            return redirect()->route('adopt.index')
                ->with('error', 'Sorry, this tag has already been adopted by someone else.');
        }

        $deadline = Setting::get('adopt_a_tag_deadline');
        $token = Str::random(32);

        $child->update([
            'adopter_name' => $request->adopter_name,
            'adopter_email' => $request->adopter_email,
            'adopter_phone' => $request->adopter_phone,
            'adopted_at' => now(),
            'adoption_token' => $token,
            'gift_level' => GiftLevel::Partial,
            'adoption_deadline' => $deadline ?: now()->addDays(14)->toDateString(),
        ]);

        // Send confirmation notification if enabled
        if (Setting::get('notifications_enabled', '0') === '1') {
            $adopter = new Adopter(
                $request->adopter_email ?? '',
                $request->adopter_phone ?? '',
                $request->adopter_name,
            );
            $adopter->notify(new AdoptionConfirmation($child, $token));
        }

        return redirect()->route('adopt.confirmation', $token);
    }

    public function confirmation(string $token): View
    {
        $child = Child::where('adoption_token', $token)->with('family:id,family_number')->firstOrFail();

        return view('adopt.confirmation', compact('child'));
    }

    public function markDelivered(string $token): RedirectResponse
    {
        $child = Child::where('adoption_token', $token)->firstOrFail();

        $child->update([
            'gift_dropped_off' => true,
            'gift_level' => GiftLevel::Moderate,
        ]);

        return redirect()->route('adopt.confirmation', $token)
            ->with('success', 'Thank you for dropping off the gift!');
    }

    // --- Admin methods (Santa auth) ---

    public function adminDashboard(Request $request): View
    {
        $stats = [
            'available' => Child::availableForAdoption()->count(),
            'adopted' => Child::adopted()->where('gift_dropped_off', false)->count(),
            'dropped_off' => Child::adopted()->where('gift_dropped_off', true)->count(),
            'overdue' => Child::overdue()->count(),
        ];

        $query = Child::with('family:id,family_number,family_name')
            ->whereHas('family', fn ($q) => $q->whereNotNull('family_number'));

        $status = $request->get('status', 'adopted');

        switch ($status) {
            case 'adopted':
                $query->adopted()->where('gift_dropped_off', false);
                break;
            case 'dropped_off':
                $query->adopted()->where('gift_dropped_off', true);
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'available':
                $query->availableForAdoption();
                break;
            default: // 'all'
                $query->where(function ($q) {
                    $q->whereNotNull('adoption_token')
                      ->orWhere(function ($q2) {
                          $q2->where('gift_level', GiftLevel::None->value)
                             ->whereNull('adoption_token');
                      });
                });
                break;
        }

        $children = $query->orderBy('adopted_at', 'desc')->get();

        return view('santa.adoptions', compact('stats', 'children', 'status'));
    }

    public function release(Child $child): RedirectResponse
    {
        $child->update([
            'adopter_name' => null,
            'adopter_contact_info' => null,
            'adopter_email' => null,
            'adopter_phone' => null,
            'adopted_at' => null,
            'adoption_token' => null,
            'adoption_deadline' => null,
            'gift_dropped_off' => false,
            'adoption_reminder_sent' => false,
            'gift_level' => GiftLevel::None,
        ]);

        return redirect()->route('santa.adoptions')
            ->with('success', 'Tag released back to the available pool.');
    }

    public function complete(Child $child): RedirectResponse
    {
        $child->update([
            'gift_level' => GiftLevel::Full,
            'gift_dropped_off' => true,
        ]);

        return redirect()->route('santa.adoptions')
            ->with('success', 'Adoption marked as complete (gift level set to Full).');
    }
}
