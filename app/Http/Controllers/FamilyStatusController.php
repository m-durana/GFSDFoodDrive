<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\Family;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FamilyStatusController extends Controller
{
    public function show(string $token): View
    {
        if (Setting::get('family_status_enabled', '0') !== '1') {
            abort(404);
        }

        $family = Family::where('status_token', $token)->with('children')->firstOrFail();

        $totalChildren = $family->children->count();
        $childrenWithGifts = $family->children->filter(function ($child) {
            return ($child->gift_level && $child->gift_level->value >= 1) || $child->adoption_token !== null;
        })->count();

        $steps = [
            [
                'label' => 'Registered',
                'complete' => true,
                'description' => 'Your family has been registered with the food drive.',
            ],
            [
                'label' => 'Number Assigned',
                'complete' => $family->family_number !== null,
                'description' => $family->family_number !== null
                    ? "Your family number is #{$family->family_number}."
                    : 'Your family number will be assigned soon.',
            ],
            [
                'label' => 'Gifts Being Collected',
                'complete' => $childrenWithGifts > 0,
                'description' => $childrenWithGifts > 0
                    ? "Gifts are being collected for {$childrenWithGifts} of {$totalChildren} " . ($totalChildren === 1 ? 'child' : 'children') . '.'
                    : 'Gift collection has not started yet.',
            ],
            [
                'label' => 'Delivery Scheduled',
                'complete' => $family->delivery_date !== null,
                'description' => $family->delivery_date !== null
                    ? "Delivery is scheduled for {$family->delivery_date}" . ($family->delivery_time ? " ({$family->delivery_time})" : '') . '.'
                    : 'Delivery date has not been set yet.',
            ],
            [
                'label' => 'Out for Delivery',
                'complete' => $family->delivery_status === DeliveryStatus::InTransit,
                'description' => $family->delivery_status === DeliveryStatus::InTransit
                    ? 'Your delivery is on its way!'
                    : 'Your delivery has not left yet.',
            ],
            [
                'label' => 'Delivered',
                'complete' => in_array($family->delivery_status, [DeliveryStatus::Delivered, DeliveryStatus::PickedUp]),
                'description' => $family->delivery_status === DeliveryStatus::Delivered
                    ? 'Your delivery has been completed!'
                    : ($family->delivery_status === DeliveryStatus::PickedUp
                        ? 'Your items have been picked up!'
                        : 'Waiting for delivery.'),
            ],
        ];

        // Find the current step (last completed or first incomplete)
        $currentStepIndex = 0;
        foreach ($steps as $i => $step) {
            if ($step['complete']) {
                $currentStepIndex = $i;
            }
        }

        return view('family.status', [
            'family' => $family,
            'steps' => $steps,
            'currentStepIndex' => $currentStepIndex,
            'childrenWithGifts' => $childrenWithGifts,
            'totalChildren' => $totalChildren,
        ]);
    }

    public function regenerateToken(Family $family): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isCoordinator() && !$user->isSanta()) {
            abort(403);
        }

        $family->update(['status_token' => Str::random(32)]);

        return redirect()->back()->with('success', 'Status link has been regenerated.');
    }
}
