<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use Illuminate\Support\Facades\Http;

class PoliteCrawler
{
    private const MAX_HTML_SIZE = 5 * 1024 * 1024; // 5MB

    private const MAX_TEXT_SIZE = 100 * 1024; // 100KB

    private const DEFAULT_TIMEOUT = 30;

    public function __construct(
        private readonly RobotsParser $robotsParser,
        private readonly DomainThrottler $throttler,
    ) {}

    /**
     * Fetch a page respecting robots.txt and rate limits.
     */
    public function fetch(string $url): CrawlResult
    {
        $parsed = parse_url($url);
        if (! isset($parsed['host'])) {
            return CrawlResult::failure($url, 'Invalid URL');
        }

        $domain = $parsed['host'];
        $path = $parsed['path'] ?? '/';

        // Check robots.txt
        if (! $this->robotsParser->isAllowed($domain, $path)) {
            return CrawlResult::failure($url, 'Blocked by robots.txt');
        }

        // Apply rate limiting
        $this->throttler->throttle($domain);

        try {
            $options = ['stream' => true];
            if (! config('services.crawler.verify_ssl', true)) {
                $options['verify'] = false;
            }
            if (config('services.crawler.force_ipv4', false)) {
                $options['curl'] = [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4];
            }
            $response = Http::timeout(self::DEFAULT_TIMEOUT)
                ->withUserAgent($this->getUserAgent())
                ->withOptions($options)
                ->get($url);

            if (! $response->successful()) {
                return CrawlResult::failure($url, "HTTP {$response->status()}");
            }

            // Check content type
            $contentType = $response->header('Content-Type') ?? '';
            if (! str_contains(strtolower($contentType), 'text/html')) {
                return CrawlResult::failure($url, "Not HTML: {$contentType}");
            }

            $html = $response->body();

            // Check size limit
            if (strlen($html) > self::MAX_HTML_SIZE) {
                return CrawlResult::failure($url, 'Page too large');
            }

            // Extract text
            $text = $this->extractText($html);

            // Truncate text if needed
            if (strlen($text) > self::MAX_TEXT_SIZE) {
                $text = substr($text, 0, self::MAX_TEXT_SIZE);
            }

            $hash = hash('sha256', $html);

            return CrawlResult::success($url, $html, $text, $hash);
        } catch (\Exception $e) {
            return CrawlResult::failure($url, $e->getMessage());
        }
    }

    /**
     * Extract text content from HTML.
     */
    private function extractText(string $html): string
    {
        // Remove scripts and styles
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html) ?? $html;

        // Remove HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalise whitespace
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        // Remove leading/trailing whitespace per line
        $lines = array_map('trim', explode("\n", $text));
        $lines = array_filter($lines);

        return trim(implode("\n", $lines));
    }

    private function getUserAgent(): string
    {
        return config('app.scrape_user_agent', 'HareAndTortoiseBot/1.0 (+https://hareandtortoise.agency)');
    }
}
