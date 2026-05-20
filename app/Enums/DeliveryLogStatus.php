<?php

namespace App\Enums;

/**
 * Discrete status values written to delivery_logs.status. Mirrors the
 * `in:` validator list in DeliveryDayController::addLog plus the
 * 'in_transit' / 'delivered' values written from DeliveryRouteController.
 */
enum DeliveryLogStatus: string
{
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case LeftAtDoor = 'left_at_door';
    case NoAnswer = 'no_answer';
    case Attempted = 'attempted';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::LeftAtDoor => 'Left at Door',
            self::NoAnswer => 'No Answer',
            self::Attempted => 'Attempted',
            self::Note => 'Note',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
