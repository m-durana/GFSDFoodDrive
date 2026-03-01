<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
        };
    }
}
