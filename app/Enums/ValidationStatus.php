<?php

declare(strict_types=1);

namespace App\Enums;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Valid => 'Valid',
            self::Invalid => 'Invalid',
            self::Error => 'Error',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Valid => 'green',
            self::Invalid => 'yellow',
            self::Error => 'red',
        };
    }
}
