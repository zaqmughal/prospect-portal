<?php

declare(strict_types=1);

namespace App\Enums;

enum SignalType: string
{
    case Content = 'content';
    case Ux = 'ux';
    case Tech = 'tech';
    case Opportunity = 'opportunity';

    public function label(): string
    {
        return match ($this) {
            self::Content => 'Content',
            self::Ux => 'UX',
            self::Tech => 'Technology',
            self::Opportunity => 'Opportunity',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Content => 'Outdated content, copyright dates, stale blog posts',
            self::Ux => 'Navigation issues, unclear journeys, inconsistent design',
            self::Tech => 'Technology mentions, stack indicators, platform signals',
            self::Opportunity => 'Hiring signals, initiatives, aligned services',
        };
    }
}
