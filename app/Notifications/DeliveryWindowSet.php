<?php

namespace App\Notifications;

use App\Models\Family;
use App\Services\SmsService;

/**
 * REL-05: notify a family when their delivery date/time window is
 * scheduled. Triggered by the FamilyObserver when `delivery_date` or
 * `delivery_time` changes from empty.
 */
class DeliveryWindowSet
{
    public static function send(Family $family): bool
    {
        $phone = $family->phone1;
        if (empty($phone)) return false;
        if (empty($family->delivery_date)) return false;

        $name = $family->family_name ?? 'Family';
        $when = $family->delivery_date . ($family->delivery_time ? ' ' . $family->delivery_time : '');
        $msg = "Hi {$name}! Your GFSD delivery is scheduled for {$when}. Please be home in that window.";

        if ($family->status_token) {
            $msg .= "\n\nTrack: " . route('family.status', $family->status_token);
        }

        SmsService::dispatch($phone, $msg);
        return true;
    }
}
