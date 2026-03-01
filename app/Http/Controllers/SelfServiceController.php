<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyRequest;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SelfServiceController extends Controller
{
    public function create(): View|Response
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            return $this->closedResponse();
        }

        return view('self-service.create');
    }

    public function store(StoreFamilyRequest $request): RedirectResponse|Response
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            return $this->closedResponse();
        }

        $data = $request->validated();

        // Compute totals
        $data['number_of_adults'] = ($data['female_adults'] ?? 0) + ($data['male_adults'] ?? 0) + ($data['other_adults'] ?? 0);
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

    public function success(): View|Response
    {
        if (Setting::get('self_registration_enabled', '0') !== '1') {
            return $this->closedResponse();
        }

        return view('self-service.success');
    }

    private function closedResponse(): Response
    {
        // Show advisors (permission 7 = family role with school_source) and coordinators
        $advisors = User::where(function ($q) {
                $q->where('permission', 7)->whereNotNull('school_source')->where('school_source', '!=', '');
            })
            ->orWhere(function ($q) {
                $q->where('permission', '>=', 8)->whereNotNull('position');
            })
            ->orderBy('name')
            ->get(['name', 'position', 'school_source']);

        return response(
            view('self-service.closed', compact('advisors')),
            200
        );
    }
}
