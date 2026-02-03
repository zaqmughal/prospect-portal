<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RobotsParser
{
    private const CACHE_TTL_HOURS = 24;

    /**
     * Check if the given path is allowed by robots.txt.
     */
    public function isAllowed(string $domain, string $path, string $userAgent = '*'): bool
    {
        $robotsTxt = $this->getRobotsTxt($domain);

        if ($robotsTxt === null) {
            // No robots.txt means everything is allowed
            return true;
        }

        return $this->parseRules($robotsTxt, $path, $userAgent);
    }

    /**
     * Get robots.txt content from cache or fetch it.
     */
    private function getRobotsTxt(string $domain): ?string
    {
        $cacheKey = "robots_txt:{$domain}";

        return Cache::remember(
            $cacheKey,
            now()->addHours(self::CACHE_TTL_HOURS),
            function () use ($domain): ?string {
                try {
                    $httpOptions = [];
                    if (! config('services.crawler.verify_ssl', true)) {
                        $httpOptions['verify'] = false;
                    }
                    if (config('services.crawler.force_ipv4', false)) {
                        $httpOptions['curl'] = [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4];
                    }
                    $http = Http::timeout(10)
                        ->withUserAgent(config('app.scrape_user_agent', 'HareAndTortoiseBot/1.0'));
                    if ($httpOptions !== []) {
                        $http = $http->withOptions($httpOptions);
                    }
                    $response = $http->get("https://{$domain}/robots.txt");

                    if ($response->successful()) {
                        return $response->body();
                    }

                    // Try HTTP fallback
                    $response = $http->get("http://{$domain}/robots.txt");

                    if ($response->successful()) {
                        return $response->body();
                    }
                } catch (\Exception) {
                    // Robots.txt not available
                }

                return null;
            }
        );
    }

    /**
     * Parse robots.txt rules and check if path is allowed.
     */
    private function parseRules(string $robotsTxt, string $path, string $userAgent): bool
    {
        $lines = explode("\n", $robotsTxt);
        $currentUserAgent = null;
        $rules = [
            '*' => ['allow' => [], 'disallow' => []],
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Parse User-agent
            if (preg_match('/^User-agent:\s*(.+)$/i', $line, $matches)) {
                $currentUserAgent = trim($matches[1]);
                if (! isset($rules[$currentUserAgent])) {
                    $rules[$currentUserAgent] = ['allow' => [], 'disallow' => []];
                }

                continue;
            }

            if ($currentUserAgent === null) {
                continue;
            }

            // Parse Disallow
            if (preg_match('/^Disallow:\s*(.*)$/i', $line, $matches)) {
                $pattern = trim($matches[1]);
                if ($pattern !== '') {
                    $rules[$currentUserAgent]['disallow'][] = $pattern;
                }

                continue;
            }

            // Parse Allow
            if (preg_match('/^Allow:\s*(.*)$/i', $line, $matches)) {
                $pattern = trim($matches[1]);
                if ($pattern !== '') {
                    $rules[$currentUserAgent]['allow'][] = $pattern;
                }
            }
        }

        // Get rules for specific user agent or fall back to *
        $applicableRules = $rules[$userAgent] ?? $rules['*'] ?? ['allow' => [], 'disallow' => []];

        // Check Allow first (more specific)
        foreach ($applicableRules['allow'] as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }

        // Check Disallow
        foreach ($applicableRules['disallow'] as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if path matches a robots.txt pattern.
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        // Empty pattern matches nothing
        if ($pattern === '') {
            return false;
        }

        // Exact match
        if ($pattern === $path) {
            return true;
        }

        // Pattern with wildcard
        if (str_contains($pattern, '*')) {
            $regex = '/^'.str_replace(
                ['*', '\\$'],
                ['.*', '$'],
                preg_quote($pattern, '/')
            ).'/';

            return (bool) preg_match($regex, $path);
        }

        // Prefix match
        return str_starts_with($path, $pattern);
    }
}
