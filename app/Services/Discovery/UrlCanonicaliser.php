<?php

declare(strict_types=1);

namespace App\Services\Discovery;

/**
 * Decide if a discovered URL is "better" than the current account URL (https > http, root > deep path).
 */
class UrlCanonicaliser
{
    /**
     * Return true if $newUrl should replace $currentUrl for the same domain.
     * Prefer https over http, and root/short path over deep path.
     */
    public function isBetterThan(string $newUrl, string $currentUrl): bool
    {
        $new = $this->parse($newUrl);
        $current = $this->parse($currentUrl);

        if ($new['https'] && ! $current['https']) {
            return true;
        }
        if (! $new['https'] && $current['https']) {
            return false;
        }

        $newPathDepth = $this->pathDepth($new['path']);
        $currentPathDepth = $this->pathDepth($current['path']);

        return $newPathDepth < $currentPathDepth;
    }

    /**
     * @return array{https: bool, path: string}
     */
    private function parse(string $url): array
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        if ($path === '' || ! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }
        $scheme = strtolower($parsed['scheme'] ?? 'http');

        return [
            'https' => $scheme === 'https',
            'path' => $path,
        ];
    }

    private function pathDepth(string $path): int
    {
        $path = trim($path, '/');
        if ($path === '') {
            return 0;
        }

        return count(array_filter(explode('/', $path)));
    }
}
