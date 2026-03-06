<?php

namespace App\Enums;

enum PackingStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Complete = 'complete';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Complete => 'Complete',
            self::Verified => 'Verified',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProgress => 'yellow',
            self::Complete => 'blue',
            self::Verified => 'green',
        };
    }
}
