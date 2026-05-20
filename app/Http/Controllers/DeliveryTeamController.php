<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryTeamRequest;
use App\Models\DeliveryTeam;
use Illuminate\Http\RedirectResponse;

class DeliveryTeamController extends Controller
{
    public function store(DeliveryTeamRequest $request): RedirectResponse
    {
        DeliveryTeam::create($request->only('name', 'color', 'driver_user_id', 'driver_name', 'notes'));

        return redirect()->route('delivery.index', ['tab' => 'teams'])
            ->with('success', "Team '{$request->name}' created.");
    }

    public function update(DeliveryTeamRequest $request, DeliveryTeam $team): RedirectResponse
    {
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
