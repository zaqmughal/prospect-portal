<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscoveryCandidateStatus: string
{
    case New = 'new';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Blocked = 'blocked';
    case Duplicate = 'duplicate';
    case Unreachable = 'unreachable';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Blocked => 'Blocked',
            self::Duplicate => 'Duplicate',
            self::Unreachable => 'Unreachable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Blocked => 'orange',
            self::Duplicate => 'yellow',
            self::Unreachable => 'red',
        };
    }
}
