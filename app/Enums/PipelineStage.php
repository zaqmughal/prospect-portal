<?php

declare(strict_types=1);

namespace App\Enums;

enum PipelineStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case Meeting = 'meeting';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Replied => 'Replied',
            self::Meeting => 'Meeting',
            self::Proposal => 'Proposal',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Contacted => 'blue',
            self::Replied => 'indigo',
            self::Meeting => 'purple',
            self::Proposal => 'yellow',
            self::Won => 'green',
            self::Lost => 'red',
        };
    }
}
