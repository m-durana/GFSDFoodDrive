<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyRequest;
use App\Models\Family;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SelfServiceController extends Controller
{
    public function create(): View
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            abort(403, 'Family self-registration is currently disabled.');
        }

        return view('self-service.create');
    }

    public function store(StoreFamilyRequest $request): RedirectResponse
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            abort(403, 'Family self-registration is currently disabled.');
        }

        $data = $request->validated();

        // Compute totals
        $data['number_of_adults'] = ($data['female_adults'] ?? 0) + ($data['male_adults'] ?? 0);
        $data['number_of_children'] = ($data['infants'] ?? 0) + ($data['young_children'] ?? 0)
            + ($data['children_count'] ?? 0) + ($data['tweens'] ?? 0) + ($data['teenagers'] ?? 0);
        $data['number_of_family_members'] = $data['number_of_adults'] + $data['number_of_children'];

        $data['has_crhs_children'] = $request->boolean('has_crhs_children');
        $data['has_gfhs_children'] = $request->boolean('has_gfhs_children');
        $data['needs_baby_supplies'] = $request->boolean('needs_baby_supplies');

        // Self-service families have no user_id (submitted anonymously)
        $data['user_id'] = $request->user()?->id;

        Family::create($data);

        return redirect()->route('self-service.success');
    }

    public function success(): View
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            abort(403, 'Family self-registration is currently disabled.');
        }

        return view('self-service.success');
    }
}
