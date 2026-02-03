<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSourceStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Enabled => 'Enabled',
            self::Disabled => 'Disabled',
        };
    }
}
