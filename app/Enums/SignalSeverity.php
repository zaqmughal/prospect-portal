<?php

declare(strict_types=1);

namespace App\Enums;

enum SignalSeverity: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::High => 'High',
            self::Medium => 'Medium',
            self::Low => 'Low',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::High => 'red',
            self::Medium => 'yellow',
            self::Low => 'blue',
        };
    }

    public function defaultScoreImpact(): int
    {
        return match ($this) {
            self::High => 12,
            self::Medium => 8,
            self::Low => 4,
        };
    }
}
