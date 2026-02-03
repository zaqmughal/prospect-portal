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
 * Bing Web Search API v7 connector for search_query_pack lead sources.
 * Caches query results for 24h per query + provider.
 */
class BingSearchConnector implements DiscoveryConnector
{
    private const API_URL = 'https://api.bing.microsoft.com/v7.0/search';

    private const CACHE_TTL_SECONDS = 86400; // 24h

    public function __construct(
        private readonly DomainNormaliser $domainNormaliser
    ) {}

    public function run(LeadSource $source): DiscoveryRunResult
    {
        if ($source->type !== LeadSourceType::SearchQueryPack) {
            return new DiscoveryRunResult('bing', [], [
                ['message' => 'Connector only supports search_query_pack type'],
            ], false);
        }

        $config = $source->config ?? [];
        $queries = $config['queries'] ?? [];
        $perQueryLimit = (int) ($config['per_query_limit'] ?? 10);
        $apiKeyEnv = $config['api_key_env'] ?? 'BING_SEARCH_API_KEY';
        $apiKey = config('services.bing.api_key') ?? env($apiKeyEnv);

        if (empty($queries) || ! is_array($queries)) {
            return new DiscoveryRunResult('bing', [], [
                ['message' => 'No queries configured'],
            ], false);
        }

        if (empty($apiKey)) {
            return new DiscoveryRunResult('bing', [], [
                ['message' => 'Bing API key not configured'],
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

            $cacheKey = 'discovery:results:bing:'.md5($query);
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
                    $this->providerName(),
                    $result['items'],
                    [],
                    $result['rate_limit_hit']
                );
                Cache::put($cacheKey, $toCache, self::CACHE_TTL_SECONDS);
            }

            usleep((int) (60_000_000 / $rateLimitPerMinute));
        }

        return new DiscoveryRunResult(
            'bing',
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
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $apiKey,
            ])->get(self::API_URL, [
                'q' => $query,
                'count' => min($count, 50),
                'responseFilter' => 'Webpages',
                'mkt' => 'en-GB',
            ]);

            if ($response->status() === 429) {
                $rateLimitHit = true;
                $errors[] = ['message' => 'Rate limit hit for query: '.$query];

                return ['items' => [], 'errors' => $errors, 'rate_limit_hit' => $rateLimitHit];
            }

            if (! $response->successful()) {
                $errors[] = ['message' => 'Bing API error: '.$response->status().' '.$response->body()];

                return ['items' => [], 'errors' => $errors, 'rate_limit_hit' => false];
            }

            $body = $response->json();
            $webPages = $body['webPages']['value'] ?? [];

            foreach ($webPages as $position => $page) {
                $url = $page['url'] ?? '';
                if ($url === '') {
                    continue;
                }
                $items[] = [
                    'url' => $url,
                    'title' => $page['name'] ?? null,
                    'snippet' => $page['snippet'] ?? null,
                    'position' => $position + 1,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Bing search request failed', ['query' => $query, 'error' => $e->getMessage()]);
            $errors[] = ['message' => $e->getMessage()];
        }

        return ['items' => $items, 'errors' => $errors, 'rate_limit_hit' => $rateLimitHit];
    }
}
