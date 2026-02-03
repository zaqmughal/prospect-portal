<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSourceType: string
{
    case SearchQueryPack = 'search_query_pack';
    case Registry = 'registry';
    case Directory = 'directory';
    case Rss = 'rss';

    public function label(): string
    {
        return match ($this) {
            self::SearchQueryPack => 'Search query pack',
            self::Registry => 'Registry',
            self::Directory => 'Directory',
            self::Rss => 'RSS',
        };
    }
}
