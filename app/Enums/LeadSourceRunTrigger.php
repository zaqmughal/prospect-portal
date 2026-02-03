<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSourceRunTrigger: string
{
    case Manual = 'manual';
    case Scheduled = 'scheduled';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Scheduled => 'Scheduled',
        };
    }
}
