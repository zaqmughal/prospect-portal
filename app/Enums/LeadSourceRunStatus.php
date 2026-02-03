<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSourceRunStatus: string
{
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
    case Partial = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Partial => 'Partial',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Running => 'yellow',
            self::Success => 'green',
            self::Failed => 'red',
            self::Partial => 'orange',
        };
    }
}
