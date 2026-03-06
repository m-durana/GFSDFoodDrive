<?php

namespace App\Enums;

enum PackingItemStatus: string
{
    case Pending = 'pending';
    case Packed = 'packed';
    case Verified = 'verified';
    case Substituted = 'substituted';
    case Unfulfilled = 'unfulfilled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Packed => 'Packed',
            self::Verified => 'Verified',
            self::Substituted => 'Substituted',
            self::Unfulfilled => 'Unfulfilled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Packed => 'blue',
            self::Verified => 'green',
            self::Substituted => 'yellow',
            self::Unfulfilled => 'red',
        };
    }
}
