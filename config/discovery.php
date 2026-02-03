<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discovery Configuration
    |--------------------------------------------------------------------------
    |
    | Lead source discovery: search query packs, rate limits, blocklist,
    | and approve-before-import gate. Default provider is SerpAPI (Bing Search APIs
    | retired Aug 2025). API keys (SERPAPI_API_KEY, BING_SEARCH_API_KEY) are in
    | .env only; never store in DB.
    |
    */

    'default_provider' => env('DISCOVERY_DEFAULT_PROVIDER', 'serpapi'),

    'rate_limit_per_minute' => (int) env('DISCOVERY_RATE_LIMIT_PER_MINUTE', 10),

    'approve_before_import' => (bool) env('DISCOVERY_APPROVE_BEFORE_IMPORT', false),

    /*
    |--------------------------------------------------------------------------
    | Blocklist (exact + suffix matching for MVP)
    |--------------------------------------------------------------------------
    |
    | Exact domain: facebook.com blocks facebook.com
    | Suffix: *.facebook.com blocks subdomain.facebook.com
    |
    */

    'blocklist_domains' => [
        // Social networks
        'facebook.com',
        '*.facebook.com',
        'twitter.com',
        '*.twitter.com',
        'linkedin.com',
        '*.linkedin.com',
        'instagram.com',
        '*.instagram.com',
        'youtube.com',
        '*.youtube.com',
        'tiktok.com',
        '*.tiktok.com',
        // Directories / aggregators
        'wikipedia.org',
        '*.wikipedia.org',
        'wikimedia.org',
        '*.wikimedia.org',
        'dmoz.org',
        '*.dmoz.org',
        // Gov / public sector (generic; add specific as needed)
        'gov.uk',
        '*.gov.uk',
        'gov.com',
        '*.gov.com',
        // Map / location
        'google.com',
        '*.google.com',
        'maps.google.com',
        'openstreetmap.org',
        '*.openstreetmap.org',
        // App stores / file hosts
        'apple.com',
        '*.apple.com',
        'play.google.com',
        'apps.apple.com',
        'github.com',
        '*.github.com',
        'bitbucket.org',
        '*.bitbucket.org',
        'dropbox.com',
        '*.dropbox.com',
        'drive.google.com',
        'docs.google.com',
        'sites.google.com',
        // PDF / file hosts
        'scribd.com',
        '*.scribd.com',
        'slideshare.net',
        '*.slideshare.net',
        'issuu.com',
        '*.issuu.com',
        // CDN / common non-company
        'cloudflare.com',
        '*.cloudflare.com',
        'amazonaws.com',
        '*.amazonaws.com',
        'azure.com',
        '*.azure.com',
    ],

];
