<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use Illuminate\Support\Facades\Cache;

class DomainThrottler
{
    private const DEFAULT_DELAY_SECONDS = 2;

    private const CACHE_PREFIX = 'domain_throttle:';

    public function __construct(
        private readonly int $delaySeconds = self::DEFAULT_DELAY_SECONDS
    ) {}

    /**
     * Wait if necessary to respect rate limits for a domain.
     */
    public function throttle(string $domain): void
    {
        $cacheKey = self::CACHE_PREFIX.$domain;
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest !== null) {
            $elapsed = time() - (int) $lastRequest;
            $waitTime = $this->delaySeconds - $elapsed;

            if ($waitTime > 0) {
                sleep($waitTime);
            }
        }

        Cache::put($cacheKey, time(), $this->delaySeconds + 60);
    }

    /**
     * Check if we can make a request to the domain without waiting.
     */
    public function canRequest(string $domain): bool
    {
        $cacheKey = self::CACHE_PREFIX.$domain;
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest === null) {
            return true;
        }

        $elapsed = time() - (int) $lastRequest;

        return $elapsed >= $this->delaySeconds;
    }

    /**
     * Get remaining wait time in seconds for a domain.
     */
    public function getWaitTime(string $domain): int
    {
        $cacheKey = self::CACHE_PREFIX.$domain;
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest === null) {
            return 0;
        }

        $elapsed = time() - (int) $lastRequest;
        $waitTime = $this->delaySeconds - $elapsed;

        return max(0, $waitTime);
    }
}
