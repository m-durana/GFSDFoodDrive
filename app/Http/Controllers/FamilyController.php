<?php

namespace App\Http\Controllers;

use App\Actions\AssignFamilyNumber;
use App\Http\Requests\StoreFamilyRequest;
use App\Models\Child;
use App\Models\Family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Santa and coordinators see all families; family advisors see only their own
        $families = ($user->isSanta() || $user->isCoordinator())
            ? Family::with('packingList')->orderBy('family_number')->get()
            : $user->families;

        return view('family.index', [
            'families' => $families,
        ]);
    }

    public function create(): View
    {
        return view('family.create');
    }

    public function store(StoreFamilyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Compute totals
        $data['user_id'] = $request->user()->id;
        $data['number_of_adults'] = ($data['female_adults'] ?? 0) + ($data['male_adults'] ?? 0) + ($data['other_adults'] ?? 0);
        $data['number_of_children'] = ($data['infants'] ?? 0) + ($data['young_children'] ?? 0)
            + ($data['children_count'] ?? 0) + ($data['tweens'] ?? 0) + ($data['teenagers'] ?? 0);
        $data['number_of_family_members'] = $data['number_of_adults'] + $data['number_of_children'];

        // Default checkboxes to false if not sent
        $data['has_crhs_children'] = $request->boolean('has_crhs_children');
        $data['has_gfhs_children'] = $request->boolean('has_gfhs_children');
        $data['needs_baby_supplies'] = $request->boolean('needs_baby_supplies');
        $data['is_severe_need'] = $request->boolean('is_severe_need');

        // Handle "Other" language
        if (($data['preferred_language'] ?? '') === 'Other' && $request->filled('preferred_language_other')) {
            $data['preferred_language'] = $request->input('preferred_language_other');
        }

        // Remove children array from family data (handled separately)
        $childrenData = $data['children'] ?? [];
        unset($data['children'], $data['preferred_language_other']);

        $family = Family::create($data);

        // Create children from wizard if provided
        foreach ($childrenData as $childData) {
            if (!empty($childData['gender']) || !empty($childData['age'])) {
                $family->children()->create($childData);
            }
        }

        // Auto-assign family number based on eldest child's school
        if (!$family->family_number) {
            $family->load('children');
            $assigner = new AssignFamilyNumber();
            $result = $assigner->assignNext($family);
            if ($result !== true) {
                return redirect()->route('family.show', $family)
                    ->with('warning', "Family created but number assignment failed: {$result}");
            }
        }

        return redirect()->route('family.show', $family)
            ->with('success', "Family '{$family->family_name}' created successfully (#{$family->family_number}).");
    }

    public function show(Family $family): View
    {
        $family->load(['children', 'packingList.volunteer']);

        return view('family.show', compact('family'));
    }

    public function edit(Family $family): View
    {
        return view('family.edit', compact('family'));
    }

    public function update(StoreFamilyRequest $request, Family $family): RedirectResponse
    {
        $data = $request->validated();

        // Compute totals
        $data['number_of_adults'] = ($data['female_adults'] ?? 0) + ($data['male_adults'] ?? 0) + ($data['other_adults'] ?? 0);
        $data['number_of_children'] = ($data['infants'] ?? 0) + ($data['young_children'] ?? 0)
            + ($data['children_count'] ?? 0) + ($data['tweens'] ?? 0) + ($data['teenagers'] ?? 0);
        $data['number_of_family_members'] = $data['number_of_adults'] + $data['number_of_children'];

        $data['has_crhs_children'] = $request->boolean('has_crhs_children');
        $data['has_gfhs_children'] = $request->boolean('has_gfhs_children');
        $data['needs_baby_supplies'] = $request->boolean('needs_baby_supplies');
        $data['dietary_restrictions'] = $request->input('dietary_restrictions', []);

        $family->update($data);

        return redirect()->route('family.show', $family)
            ->with('success', "Family '{$family->family_name}' updated successfully.");
    }

    public function storeChild(Request $request, Family $family): RedirectResponse
    {
        $validated = $request->validate([
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'age' => ['required', 'string', 'max:50'],
            'school' => ['nullable', 'string', 'max:255'],
            'clothes_size' => ['nullable', 'string', 'max:255'],
            'clothing_styles' => ['nullable', 'string', 'max:1000'],
            'clothing_options' => ['nullable', 'string', 'max:1000'],
            'gift_preferences' => ['nullable', 'string', 'max:1000'],
            'toy_ideas' => ['nullable', 'string', 'max:1000'],
            'all_sizes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldEldest = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();
        $family->children()->create($validated);
        $family->load('children');
        $newEldest = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();

        // Reassign family number if eldest child changed (different school)
        if ($newEldest && $oldEldest?->school !== $newEldest->school) {
            $assigner = new AssignFamilyNumber();
            $assigner->assignNext($family);
        }

        return redirect()->route('family.show', $family)
            ->with('success', 'Child added successfully.');
    }

    public function updateChild(Request $request, Family $family, Child $child): RedirectResponse
    {
        $validated = $request->validate([
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'age' => ['required', 'string', 'max:50'],
            'school' => ['nullable', 'string', 'max:255'],
            'clothes_size' => ['nullable', 'string', 'max:255'],
            'clothing_styles' => ['nullable', 'string', 'max:1000'],
            'clothing_options' => ['nullable', 'string', 'max:1000'],
            'gift_preferences' => ['nullable', 'string', 'max:1000'],
            'toy_ideas' => ['nullable', 'string', 'max:1000'],
            'all_sizes' => ['nullable', 'string', 'max:1000'],
            'gifts_received' => ['nullable', 'string', 'max:1000'],
            'gift_level' => ['nullable', 'integer', 'min:0', 'max:3'],
            'where_is_tag' => ['nullable', 'string', 'max:255'],
            'adopter_name' => ['nullable', 'string', 'max:255'],
            'adopter_email' => ['nullable', 'email', 'max:255'],
            'adopter_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $child->update($validated);

        return redirect()->route('family.show', $family)
            ->with('success', 'Child updated successfully.');
    }

    public function destroyChild(Family $family, Child $child): RedirectResponse
    {
        $child->delete();

        return redirect()->route('family.show', $family)
            ->with('success', 'Child removed.');
    }

    public function toggleDone(Family $family): RedirectResponse
    {
        $family->update(['family_done' => !$family->family_done]);

        $status = $family->family_done ? 'marked as complete' : 'marked as incomplete';

        return redirect()->route('family.show', $family)
            ->with('success', "Family {$status}.");
    }
}
