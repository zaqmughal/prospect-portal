<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model_fast' => env('OPENAI_MODEL_FAST', 'gpt-4o-mini'),
        'model_quality' => env('OPENAI_MODEL_QUALITY', 'gpt-4o'),
        'daily_limit' => env('AI_DAILY_SPEND_LIMIT', 5.00),
    ],

    'research' => [
        'daily_limit' => env('RESEARCH_DAILY_LIMIT', 50),
    ],

    'crawler' => [
        'force_ipv4' => env('CRAWL_FORCE_IPV4', false),
        'verify_ssl' => env('CRAWL_VERIFY_SSL', true),
    ],

    'bing' => [
        'api_key' => env('BING_SEARCH_API_KEY'),
    ],

    'serpapi' => [
        'api_key' => env('SERPAPI_API_KEY'),
    ],

];
