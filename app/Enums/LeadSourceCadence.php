<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSourceCadence: string
{
    case Manual = 'manual';
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
        };
    }
}
