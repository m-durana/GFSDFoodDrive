<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyController extends Controller
{
    public function index(Request $request): View
    {
        return view('family.index', [
            'families' => $request->user()->families,
        ]);
    }

    public function create(): View
    {
        return view('family.create');
    }

    public function store(Request $request)
    {
        // TODO: Phase 3 - Implement family creation with form validation
        abort(501, 'Family creation will be implemented in Phase 3.');
    }

    public function show(Family $family): View
    {
        $family->load('children');

        return view('family.show', compact('family'));
    }

    public function storeChild(Request $request, Family $family)
    {
        // TODO: Phase 3 - Implement child creation
        abort(501, 'Child creation will be implemented in Phase 3.');
    }
}
