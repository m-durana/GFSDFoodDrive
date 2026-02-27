<?php

namespace App\Enums;

enum TransactionType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Received',
            self::Out => 'Distributed',
            self::Adjustment => 'Adjustment',
            self::Return => 'Returned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::In => 'green',
            self::Out => 'red',
            self::Adjustment => 'blue',
            self::Return => 'yellow',
        };
    }
}
