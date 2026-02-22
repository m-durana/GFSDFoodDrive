<?php

namespace App\Enums;

enum GiftLevel: int
{
    case None = 0;
    case Partial = 1;
    case Moderate = 2;
    case Full = 3;

    public function label(): string
    {
        return match ($this) {
            self::None => 'No Gifts',
            self::Partial => 'Partial',
            self::Moderate => 'Moderate',
            self::Full => 'Fully Gifted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::None => 'red',
            self::Partial => 'yellow',
            self::Moderate => 'yellow',
            self::Full => 'green',
        };
    }
}
