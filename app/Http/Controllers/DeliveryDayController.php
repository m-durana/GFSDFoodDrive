<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDayController extends Controller
{
    public function index(Request $request): View
    {
        $query = Family::whereNotNull('family_number')
            ->with(['children', 'volunteer', 'deliveryLogs' => fn($q) => $q->latest()->limit(5)])
            ->with(['deliveryLogs.user']);

        // Filter by delivery team
        if ($request->filled('team')) {
            $query->where('delivery_team', $request->team);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'needs_delivery') {
                $query->where('delivery_preference', 'like', '%deliver%')
                    ->where(function ($q) {
                        $q->where('delivery_status', DeliveryStatus::Pending)
                            ->orWhereNull('delivery_status');
                    });
            } else {
                $query->where('delivery_status', $request->status);
            }
        }

        // Filter by delivery date
        if ($request->filled('date')) {
            $query->where('delivery_date', $request->date);
        }

        $families = $query->orderBy('delivery_team')->orderBy('family_number')->get();

        // Group by delivery team
        $grouped = $families->groupBy(fn($f) => $f->delivery_team ?? 'Unassigned');

        // Stats
        $allDeliveryFamilies = Family::whereNotNull('family_number');
        $stats = [
            'total' => (clone $allDeliveryFamilies)->count(),
            'needs_delivery' => (clone $allDeliveryFamilies)
                ->where('delivery_preference', 'like', '%deliver%')
                ->count(),
            'pending' => (clone $allDeliveryFamilies)
                ->where(function ($q) {
                    $q->where('delivery_status', DeliveryStatus::Pending)
                        ->orWhereNull('delivery_status');
                })
                ->count(),
            'in_transit' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::InTransit)->count(),
            'delivered' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::Delivered)->count(),
            'picked_up' => (clone $allDeliveryFamilies)->where('delivery_status', DeliveryStatus::PickedUp)->count(),
        ];

        // Delivery teams for filter
        $teams = Family::select('delivery_team')
            ->distinct()
            ->whereNotNull('delivery_team')
            ->where('delivery_team', '!=', '')
            ->orderBy('delivery_team')
            ->pluck('delivery_team');

        // Delivery dates for filter
        $dates = Family::select('delivery_date')
            ->distinct()
            ->whereNotNull('delivery_date')
            ->where('delivery_date', '!=', '')
            ->orderBy('delivery_date')
            ->pluck('delivery_date');

        return view('delivery-day.index', compact('grouped', 'stats', 'teams', 'dates'));
    }

    public function updateStatus(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'delivery_status' => ['required', 'string', 'in:pending,in_transit,delivered,picked_up'],
        ]);

        $family->update(['delivery_status' => $request->delivery_status]);

        // Create a delivery log entry
        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->delivery_status,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->back()
            ->with('success', "Status updated for '{$family->family_name}'.");
    }

    public function updateTeam(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'delivery_team' => ['nullable', 'string', 'max:255'],
        ]);

        $family->update(['delivery_team' => $request->delivery_team]);

        return redirect()->back()
            ->with('success', "Team assigned for '{$family->family_name}'.");
    }

    public function addLog(Request $request, Family $family): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:delivered,left_at_door,no_answer,attempted,picked_up,note'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DeliveryLog::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Auto-update family delivery_status for terminal statuses
        if (in_array($request->status, ['delivered', 'picked_up'])) {
            $family->update(['delivery_status' => $request->status]);
        } elseif ($request->status === 'attempted' || $request->status === 'left_at_door') {
            $family->update(['delivery_status' => 'in_transit']);
        }

        return redirect()->back()
            ->with('success', "Log added for '{$family->family_name}'.");
    }

    public function map(): View
    {
        return view('delivery-day.map');
    }

    public function mapData(): JsonResponse
    {
        // Family pins with delivery status
        $families = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'family_number', 'family_name', 'address', 'latitude', 'longitude', 'delivery_status', 'delivery_team')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'number' => $f->family_number,
                'name' => $f->family_name,
                'address' => $f->address,
                'lat' => (float) $f->latitude,
                'lng' => (float) $f->longitude,
                'status' => $f->delivery_status?->value ?? 'pending',
                'team' => $f->delivery_team,
            ]);

        // Volunteer locations (updated in last 10 minutes)
        $volunteers = User::whereNotNull('last_lat')
            ->whereNotNull('last_lng')
            ->where('last_location_at', '>=', now()->subMinutes(10))
            ->select('id', 'first_name', 'last_name', 'last_lat', 'last_lng', 'last_location_at')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->first_name . ' ' . $v->last_name,
                'lat' => (float) $v->last_lat,
                'lng' => (float) $v->last_lng,
                'updated' => $v->last_location_at->diffForHumans(),
            ]);

        return response()->json([
            'families' => $families,
            'volunteers' => $volunteers,
        ]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $request->user()->update([
            'last_lat' => $request->latitude,
            'last_lng' => $request->longitude,
            'last_location_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function track(): View
    {
        return view('delivery-day.track');
    }

    public function logs(Request $request): View
    {
        $query = DeliveryLog::with(['family', 'user'])->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        $logs = $query->paginate(50);

        $logDates = DeliveryLog::selectRaw('DATE(created_at) as log_date')
            ->distinct()
            ->orderByDesc('log_date')
            ->pluck('log_date');

        return view('delivery-day.logs', compact('logs', 'logDates'));
    }
}
