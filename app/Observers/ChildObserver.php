<?php

namespace App\Observers;

use App\Models\Child;
use App\Notifications\GiftsAdopted;
use App\Services\SmsService;

class ChildObserver
{
    public function updated(Child $child): void
    {
        if (! SmsService::isAvailable()) return;

        // When a child gets an adopter for the first time, notify the family
        if ($child->wasChanged('adopter_name') && $child->adopter_name && ! $child->getOriginal('adopter_name')) {
            $family = $child->family;
            if ($family && $family->phone1) {
                // Only send once per family — check if this is the first adopted child
                $otherAdopted = $family->children()
                    ->where('id', '!=', $child->id)
                    ->whereNotNull('adopter_name')
                    ->exists();

                if (! $otherAdopted) {
                    // First adoption in this family — send SMS
                    GiftsAdopted::send($family);
                }
            }
        }
    }
}
