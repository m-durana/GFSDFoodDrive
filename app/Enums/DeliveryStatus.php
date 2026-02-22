<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case PickedUp = 'picked_up';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::PickedUp => 'Picked Up',
        };
    }
}
