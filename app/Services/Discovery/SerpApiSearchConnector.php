<?php

declare(strict_types=1);

namespace App\Services\Discovery;

use App\Contracts\DiscoveryConnector;
use App\Enums\LeadSourceType;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SerpAPI Google Search connector for search_query_pack lead sources.
 * Caches query results for 24h per query + provider.
 * Bing Search APIs were retired Aug 2025; SerpAPI is the default discovery provider.
 */
class SerpApiSearchConnector implements DiscoveryConnector
{
    private const API_URL = 'https://serpapi.com/search';

    private const CACHE_TTL_SECONDS = 86400; // 24h

    public function __construct(
        private readonly DomainNormaliser $domainNormaliser
    ) {}

    public function run(LeadSource $source): DiscoveryRunResult
    {
        if ($source->type !== LeadSourceType::SearchQueryPack) {
            return new DiscoveryRunResult('serpapi', [], [
                ['message' => 'Connector only supports search_query_pack type'],
            ], false);
        }

        $config = $source->config ?? [];
        $queries = $config['queries'] ?? [];
        $perQueryLimit = (int) ($config['per_query_limit'] ?? 10);
        $apiKeyEnv = $config['api_key_env'] ?? 'SERPAPI_API_KEY';
        $apiKey = config('services.serpapi.api_key') ?? env($apiKeyEnv);

        if (empty($queries) || ! is_array($queries)) {
            return new DiscoveryRunResult('serpapi', [], [
                ['message' => 'No queries configured'],
            ], false);
        }

        if (empty($apiKey)) {
            return new DiscoveryRunResult('serpapi', [], [
                ['message' => 'SerpAPI key not configured'],
            ], false);
        }

        $rateLimitPerMinute = (int) config('discovery.rate_limit_per_minute', 10);
        $allItems = [];
        $errors = [];
        $throttledCount = 0;

        foreach ($queries as $index => $query) {
            $query = is_string($query) ? trim($query) : '';
            if ($query === '') {
                continue;
            }

            $cacheKey = 'discovery:results:serpapi:'.md5($query);
            $cached = Cache::get($cacheKey);

            if ($cached instanceof DiscoveryRunResult) {
                foreach ($cached->items as $item) {
                    $item['query'] = $query;
                    $allItems[] = $item;
                }

                continue;
            }

            $result = $this->fetchQuery($query, $perQueryLimit, $apiKey);

            if ($result['rate_limit_hit']) {
                $throttledCount++;
            }
            foreach ($result['errors'] as $err) {
                $errors[] = array_merge($err, ['query' => $query]);
            }
            foreach ($result['items'] as $item) {
                $item['query'] = $query;
                $allItems[] = $item;
            }

            if (! empty($result['items'])) {
                $toCache = new DiscoveryRunResult(
                    'serpapi',
                    $result['items'],
                    [],
                    $result['rate_limit_hit']
                );
                Cache::put($cacheKey, $toCache, self::CACHE_TTL_SECONDS);
            }

            usleep((int) (60_000_000 / $rateLimitPerMinute));
        }

        return new DiscoveryRunResult(
            'serpapi',
            $allItems,
            $errors,
            $throttledCount > 0
        );
    }

    /**
     * @return array{items: array<int, array{url: string, title?: string, snippet?: string, position: int}>, errors: array<int, array{message: string}>, rate_limit_hit: bool}
     */
    private function fetchQuery(string $query, int $count, string $apiKey): array
    {
        $items = [];
        $errors = [];
        $rateLimitHit = false;

        try {
            $response = Http::get(self::API_URL, [
                'engine' => 'google',
                'q' => $query,
                'num' => min($count, 50),
                'api_key' => $apiKey,
                'gl' => 'uk',
                'hl' => 'en',
            ]);

            if ($response->status() === 429) {
                $rateLimitHit = true;
                $errors[] = ['message' => 'Rate limit hit for query: '.$query];

                return ['items' => [], 'errors' => $errors, 'rate_limit_hit' => $rateLimitHit];
            }

            if (! $response->successful()) {
                $errors[] = ['message' => 'SerpAPI error: '.$response->status().' '.$response->body()];

                return ['items' => [], 'errors' => $errors, 'rate_limit_hit' => false];
            }

            $body = $response->json();
            if (isset($body['error'])) {
                $errors[] = ['message' => $body['error']];
                if (stripos((string) ($body['error'] ?? ''), 'rate limit') !== false) {
                    $rateLimitHit = true;
                }

                return ['items' => [], 'errors' => $errors, 'rate_limit_hit' => $rateLimitHit];
            }

            $organicResults = $body['organic_results'] ?? [];
            foreach ($organicResults as $result) {
                $link = $result['link'] ?? '';
                if ($link === '') {
                    continue;
                }
                $items[] = [
                    'url' => $link,
                    'title' => $result['title'] ?? null,
                    'snippet' => $result['snippet'] ?? null,
                    'position' => (int) ($result['position'] ?? count($items) + 1),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('SerpAPI search request failed', ['query' => $query, 'error' => $e->getMessage()]);
            $errors[] = ['message' => $e->getMessage()];
        }

        return ['items' => $items, 'errors' => $errors, 'rate_limit_hit' => $rateLimitHit];
    }
}
