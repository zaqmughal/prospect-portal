<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use Spatie\LaravelData\Data;

class CrawlResult extends Data
{
    public function __construct(
        public readonly bool $success,
        public readonly string $url,
        public readonly ?string $html,
        public readonly ?string $text,
        public readonly ?string $error,
        public readonly int $byteSize,
        public readonly ?string $hash,
    ) {}

    public static function failure(string $url, string $error): self
    {
        return new self(
            success: false,
            url: $url,
            html: null,
            text: null,
            error: $error,
            byteSize: 0,
            hash: null,
        );
    }

    public static function success(string $url, string $html, string $text, string $hash): self
    {
        return new self(
            success: true,
            url: $url,
            html: $html,
            text: $text,
            error: null,
            byteSize: strlen($html),
            hash: $hash,
        );
    }
}
