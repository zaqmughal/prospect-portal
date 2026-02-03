<?php

declare(strict_types=1);

namespace App\Enums;

enum OutreachStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Sent = 'sent';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved',
            self::Sent => 'Sent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Approved => 'blue',
            self::Sent => 'green',
        };
    }
}
