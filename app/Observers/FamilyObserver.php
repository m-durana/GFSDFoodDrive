<?php

namespace App\Observers;

use App\Enums\DeliveryStatus;
use App\Models\Family;
use App\Notifications\DeliveryComplete;
use App\Notifications\DeliveryOnTheWay;
use App\Notifications\DeliveryWindowSet;
use App\Notifications\FamilyRegistered;
use App\Services\SmsService;

class FamilyObserver
{
    public function created(Family $family): void
    {
        if (! SmsService::isAvailable()) return;
        if (empty($family->phone1)) return;

        FamilyRegistered::send($family);
    }

    public function updated(Family $family): void
    {
        if (! SmsService::isAvailable()) return;

        if ($family->wasChanged('delivery_status')) {
            $newStatus = $family->delivery_status;
            if ($newStatus === DeliveryStatus::InTransit)  DeliveryOnTheWay::send($family);
            if ($newStatus === DeliveryStatus::Delivered)  DeliveryComplete::send($family);
        }

        // REL-05: notify the family once when their delivery date/window is first set.
        if ($family->wasChanged('delivery_date') && $family->delivery_date && ! $family->getOriginal('delivery_date')) {
            DeliveryWindowSet::send($family);
        }
    }
}
