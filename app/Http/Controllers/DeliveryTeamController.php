<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryTeamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'driver_user_id' => ['nullable', 'exists:users,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DeliveryTeam::create($request->only('name', 'color', 'driver_user_id', 'driver_name', 'notes'));

        return redirect()->route('delivery.index', ['tab' => 'teams'])
            ->with('success', "Team '{$request->name}' created.");
    }

    public function update(Request $request, DeliveryTeam $team): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'driver_user_id' => ['nullable', 'exists:users,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $team->update($request->only('name', 'color', 'driver_user_id', 'driver_name', 'notes'));

        return redirect()->route('delivery.index', ['tab' => 'teams'])
            ->with('success', "Team '{$team->name}' updated.");
    }

    public function destroy(DeliveryTeam $team): RedirectResponse
    {
        // Unassign families from this team
        $team->families()->update(['delivery_team_id' => null]);

        $name = $team->name;
        $team->delete();

        return redirect()->route('delivery.index', ['tab' => 'teams'])
            ->with('success', "Team '{$name}' deleted.");
    }
}
