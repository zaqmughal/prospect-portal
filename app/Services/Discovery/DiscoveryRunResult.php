<?php

declare(strict_types=1);

namespace App\Services\Discovery;

/**
 * DTO returned by discovery connectors. Ingestion job consumes this.
 */
final class DiscoveryRunResult
{
    /**
     * @param  array<int, array{url: string, title?: string, snippet?: string, position: int, query?: string}>  $items
     * @param  array<int, array{query?: string, message: string}>  $errors
     */
    public function __construct(
        public readonly string $provider,
        public readonly array $items,
        public readonly array $errors = [],
        public readonly bool $rateLimitHit = false,
    ) {}

    /**
     * @return array<int, array{url: string, title?: string, snippet?: string, position: int, query?: string}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<int, array{query?: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
