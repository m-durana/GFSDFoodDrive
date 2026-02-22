<?php

namespace App\Http\Controllers;

use App\Enums\GiftLevel;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Child;
use App\Models\Family;
use App\Models\SchoolRange;
use App\Models\Setting;
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

    public function numberAssignment(): View
    {
        $schoolRanges = SchoolRange::orderBy('sort_order')->get();

        // Get families without numbers, eager load children
        $unassignedFamilies = Family::whereNull('family_number')
            ->with('children')
            ->get();

        // Group families by oldest child's school
        $grouped = [];
        $noSchool = [];

        foreach ($unassignedFamilies as $family) {
            $oldestChild = $family->children->sortByDesc(function ($child) {
                return (int) $child->age;
            })->first();

            if ($oldestChild && $oldestChild->school) {
                $school = $oldestChild->school;
                $grouped[$school][] = $family;
            } else {
                $noSchool[] = $family;
            }
        }

        // Get next available number per school range
        $rangeInfo = [];
        foreach ($schoolRanges as $range) {
            $rangeInfo[$range->school_name] = [
                'range' => $range,
                'next' => $range->nextAvailableNumber(),
            ];
        }

        // Already assigned families count
        $assignedCount = Family::whereNotNull('family_number')->count();

        return view('santa.number-assignment', compact(
            'schoolRanges', 'grouped', 'noSchool', 'rangeInfo', 'assignedCount'
        ));
    }

    public function updateFamilyNumber(Request $request): RedirectResponse
    {
        $request->validate([
            'family_id' => ['required', 'exists:families,id'],
            'family_number' => ['required', 'integer', 'min:1'],
        ]);

        $family = Family::findOrFail($request->family_id);

        // Check uniqueness
        $existing = Family::where('family_number', $request->family_number)
            ->where('id', '!=', $family->id)
            ->first();

        if ($existing) {
            return redirect()->route('santa.numberAssignment')
                ->with('error', "Number {$request->family_number} is already assigned to {$existing->family_name}.");
        }

        $family->update(['family_number' => $request->family_number]);

        return redirect()->route('santa.numberAssignment')
            ->with('success', "Family '{$family->family_name}' assigned number {$request->family_number}.");
    }

    public function autoAssign(): RedirectResponse
    {
        $unassigned = Family::whereNull('family_number')->with('children')->get();
        $schoolRanges = SchoolRange::orderBy('sort_order')->get();
        $assigned = 0;
        $errors = [];

        foreach ($unassigned as $family) {
            $oldestChild = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();
            $school = $oldestChild?->school;

            if (!$school) {
                $errors[] = "{$family->family_name}: no children or no school set";
                continue;
            }

            // Find matching range
            $range = $schoolRanges->first(function ($r) use ($school) {
                return stripos($school, $r->school_name) !== false
                    || stripos($r->school_name, $school) !== false;
            });

            if (!$range) {
                // Fall back to Special Case range
                $range = $schoolRanges->firstWhere('school_name', 'Special Case');
            }

            if (!$range) {
                $errors[] = "{$family->family_name}: no matching school range for '{$school}'";
                continue;
            }

            $nextNumber = $range->nextAvailableNumber();
            if ($nextNumber === null) {
                $errors[] = "{$family->family_name}: range for '{$range->school_name}' is full";
                continue;
            }

            $family->update(['family_number' => $nextNumber]);
            $assigned++;
        }

        $message = "Auto-assigned {$assigned} families.";
        if (count($errors) > 0) {
            $message .= ' Skipped ' . count($errors) . ': ' . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= '...';
            }
        }

        return redirect()->route('santa.numberAssignment')->with('success', $message);
    }

    public function schoolRanges(): View
    {
        return view('santa.school-ranges', [
            'ranges' => SchoolRange::orderBy('sort_order')->get(),
        ]);
    }

    public function storeSchoolRange(Request $request): RedirectResponse
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'range_start' => ['required', 'integer', 'min:0'],
            'range_end' => ['required', 'integer', 'min:0', 'gt:range_start'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        SchoolRange::create([
            'school_name' => $request->school_name,
            'range_start' => $request->range_start,
            'range_end' => $request->range_end,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$request->school_name}' added.");
    }

    public function updateSchoolRange(Request $request, SchoolRange $schoolRange): RedirectResponse
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'range_start' => ['required', 'integer', 'min:0'],
            'range_end' => ['required', 'integer', 'min:0', 'gt:range_start'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $schoolRange->update($request->only('school_name', 'range_start', 'range_end', 'sort_order'));

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$schoolRange->school_name}' updated.");
    }

    public function destroySchoolRange(SchoolRange $schoolRange): RedirectResponse
    {
        $name = $schoolRange->school_name;
        $schoolRange->delete();

        return redirect()->route('santa.schoolRanges')
            ->with('success', "School range '{$name}' removed.");
    }

    public function settings(): View
    {
        return view('santa.settings', [
            'selfRegistration' => Setting::get('self_registration_enabled', '0') === '1',
            'seasonYear' => Setting::get('season_year', (string) date('Y')),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        Setting::set('self_registration_enabled', $request->boolean('self_registration_enabled') ? '1' : '0');
        Setting::set('season_year', $request->input('season_year', (string) date('Y')));

        return redirect()->route('santa.settings')
            ->with('success', 'Settings updated successfully.');
    }

    public function gifts(Request $request): View
    {
        $query = Child::with('family')->whereHas('family');

        // Filter by gift level
        if ($request->filled('level')) {
            $level = GiftLevel::tryFrom((int) $request->level);
            if ($level !== null) {
                $query->where('gift_level', $level->value);
            }
        }

        // Filter by mail merge status
        if ($request->filled('merged')) {
            $query->where('mail_merged', $request->merged === '1');
        }

        // Filter by adopter status
        if ($request->filled('adopted')) {
            if ($request->adopted === '1') {
                $query->whereNotNull('adopter_name')->where('adopter_name', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('adopter_name')->orWhere('adopter_name', '');
                });
            }
        }

        $children = $query->orderBy('family_id')->get();

        // Summary counts
        $allChildren = Child::whereHas('family');
        $counts = [
            'total' => (clone $allChildren)->count(),
            'no_gifts' => (clone $allChildren)->where('gift_level', GiftLevel::None->value)->count(),
            'partial' => (clone $allChildren)->whereIn('gift_level', [GiftLevel::Partial->value, GiftLevel::Moderate->value])->count(),
            'complete' => (clone $allChildren)->where('gift_level', GiftLevel::Full->value)->count(),
            'unmerged' => (clone $allChildren)->where('mail_merged', false)->count(),
        ];

        return view('santa.gifts', compact('children', 'counts'));
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

        // Assign Spatie role if package is installed
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($request->role);
        }

        return redirect()->route('santa.users')
            ->with('success', "User '{$user->username}' created successfully.")
            ->with('created_credentials', "Username: {$user->username}  |  Password: {$request->password}");
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

        // Sync Spatie role if package is installed
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([]);
            if ($request->role !== 'inactive') {
                $user->assignRole($request->role);
            }
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
