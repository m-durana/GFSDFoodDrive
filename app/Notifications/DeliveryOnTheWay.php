<?php

namespace App\Notifications;

use App\Models\Family;
use App\Services\SmsService;

class DeliveryOnTheWay
{
    public static function send(Family $family): bool
    {
        $phone = $family->phone1;
        if (empty($phone)) return false;

        $name = $family->family_name ?? 'Family';
        $message = "Hi {$name}! Your gifts from the GFSD Food Drive are on the way! "
            . "Please be home to receive them. If you can't be home, "
            . "we'll leave them at your door.";

        if ($family->status_token) {
            $statusUrl = route('family.status', $family->status_token);
            $message .= "\n\nTrack: {$statusUrl}";
        }

        return SmsService::send($phone, $message);
    }
}
