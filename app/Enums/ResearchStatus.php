<?php

declare(strict_types=1);

namespace App\Enums;

enum ResearchStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case BlockedByBudget = 'blocked_by_budget';
    case BlockedByLimit = 'blocked_by_limit';
    case QueuedBlocked = 'queued_blocked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Queued => 'Queued',
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::BlockedByBudget => 'Blocked (Budget)',
            self::BlockedByLimit => 'Blocked (Limit)',
            self::QueuedBlocked => 'Queued (Blocked)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Queued => 'blue',
            self::Running => 'yellow',
            self::Completed => 'green',
            self::Failed => 'red',
            self::BlockedByBudget, self::BlockedByLimit, self::QueuedBlocked => 'orange',
        };
    }

    public function isBlocked(): bool
    {
        return in_array($this, [self::BlockedByBudget, self::BlockedByLimit, self::QueuedBlocked], true);
    }
}
