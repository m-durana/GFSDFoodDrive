<?php

namespace App\Notifications;

use App\Models\Family;
use App\Services\SmsService;

class DeliveryComplete
{
    public static function send(Family $family): bool
    {
        $phone = $family->phone1;
        if (empty($phone)) return false;

        $name = $family->family_name ?? 'Family';
        $message = "Hi {$name}! Your gifts from the GFSD Food Drive have been delivered. "
            . "Happy holidays from the Granite Falls community!";

        return SmsService::send($phone, $message);
    }
}
