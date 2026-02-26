<?php

namespace App\Notifications;

use App\Models\Family;
use App\Services\SmsService;

class GiftsAdopted
{
    public static function send(Family $family): bool
    {
        $phone = $family->phone1;
        if (empty($phone)) return false;

        $adoptedCount = $family->children()
            ->whereNotNull('adopter_name')
            ->count();
        $totalCount = $family->children()->count();

        $name = $family->family_name ?? 'Family';
        $message = "Great news, {$name}! Gifts are being collected for {$adoptedCount} of your {$totalCount} children. "
            . "We'll let you know when delivery is scheduled.";

        if ($family->status_token) {
            $statusUrl = route('family.status', $family->status_token);
            $message .= "\n\nStatus: {$statusUrl}";
        }

        return SmsService::send($phone, $message);
    }
}
