<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\Family;
use App\Models\PackingList;
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
                'label' => 'Boxes Being Packed',
                'complete' => $family->packingList && in_array($family->packingList->status->value, ['complete', 'verified']),
                'description' => $family->packingList
                    ? ($family->packingList->status->value === 'verified'
                        ? 'Your box has been packed and verified!'
                        : ($family->packingList->status->value === 'complete'
                            ? 'Your box is packed and awaiting verification.'
                            : 'Your box is being prepared.'))
                    : 'Box packing has not started yet.',
                'visible' => Setting::get('packing_system_enabled', '1') === '1',
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
                'complete' => $family->delivery_status === DeliveryStatus::Delivered,
                'description' => $family->delivery_status === DeliveryStatus::Delivered
                    ? 'Your delivery has been completed!'
                    : 'Waiting for delivery.',
            ],
        ];

        // Filter out hidden steps
        $steps = array_values(array_filter($steps, fn ($s) => $s['visible'] ?? true));

        // Find the current step (last completed or first incomplete)
        $currentStepIndex = 0;
        foreach ($steps as $i => $step) {
            if ($step['complete']) {
                $currentStepIndex = $i;
            }
        }

        // Packing progress (only if packing system is enabled)
        $packingProgress = null;
        if (Setting::get('packing_system_enabled', '1') === '1') {
            $packingList = $family->packingList;
            if ($packingList) {
                $packingProgress = $packingList->progressSummary();
                $packingProgress['status'] = $packingList->status;
            }
        }

        return view('family.status', [
            'family' => $family,
            'steps' => $steps,
            'currentStepIndex' => $currentStepIndex,
            'childrenWithGifts' => $childrenWithGifts,
            'totalChildren' => $totalChildren,
            'packingProgress' => $packingProgress,
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
