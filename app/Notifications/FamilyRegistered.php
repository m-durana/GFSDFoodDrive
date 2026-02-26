<?php

namespace App\Notifications;

use App\Models\Family;
use App\Services\SmsService;

class FamilyRegistered
{
    public static function send(Family $family): bool
    {
        $phone = $family->phone1;
        if (empty($phone)) return false;

        $name = $family->family_name ?? 'Family';
        $message = "Hi {$name}! You're registered for the GFSD Food Drive. "
            . "We'll be in touch as the holidays approach. "
            . "Questions? Reply to this text or call the school office.";

        if ($family->status_token) {
            $statusUrl = route('family.status', $family->status_token);
            $message .= "\n\nTrack your status: {$statusUrl}";
        }

        return SmsService::send($phone, $message);
    }
}
