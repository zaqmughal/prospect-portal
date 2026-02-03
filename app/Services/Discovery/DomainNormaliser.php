<?php

declare(strict_types=1);

namespace App\Services\Discovery;

/**
 * Normalise URLs to registrable domain (eTLD+1). MVP: strip www + common prefixes.
 */
class DomainNormaliser
{
    private const COMMON_PREFIXES = ['www.', 'app.', 'blog.', 'careers.', 'jobs.'];

    /**
     * Normalise a URL or host to a canonical domain for dedupe and blocklist.
     * MVP: strip www and common subdomain prefixes; otherwise keep host as-is.
     * Prefer eTLD+1 library when available for gov.uk / ac.uk etc.
     */
    public function normalise(string $urlOrHost): string
    {
        $host = $this->extractHost($urlOrHost);
        $host = strtolower(trim($host));

        foreach (self::COMMON_PREFIXES as $prefix) {
            if (str_starts_with($host, $prefix)) {
                $host = substr($host, strlen($prefix));
                break;
            }
        }

        return explode('/', $host)[0];
    }

    private function extractHost(string $urlOrHost): string
    {
        if (str_contains($urlOrHost, '://') || str_starts_with($urlOrHost, '//')) {
            $parsed = parse_url(
                str_contains($urlOrHost, '://') ? $urlOrHost : 'https:'.$urlOrHost
            );

            return $parsed['host'] ?? $parsed['path'] ?? $urlOrHost;
        }

        return $urlOrHost;
    }
}
