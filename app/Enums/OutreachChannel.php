<?php

declare(strict_types=1);

namespace App\Enums;

enum OutreachChannel: string
{
    case LinkedIn = 'linkedin';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::LinkedIn => 'LinkedIn',
            self::Email => 'Email',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LinkedIn => 'linkedin',
            self::Email => 'mail',
        };
    }
}
