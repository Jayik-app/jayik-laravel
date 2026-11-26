<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    | Created in the Tajr dashboard (https://tajr.jayik.app/api-keys). The key
    | acts as the merchant account that owns it; keep it server-side only.
    | For marketplaces holding one key per vendor, use Jayik::withKey($key).
    */
    'api_key' => env('JAYIK_API_KEY'),

    'base_url' => env('JAYIK_BASE_URL', 'https://api.jayik.app/api/merchant'),

    // Seconds.
    'timeout' => (int) env('JAYIK_TIMEOUT', 30),
    'connect_timeout' => (int) env('JAYIK_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Rate limit handling
    |--------------------------------------------------------------------------
    | The API allows 120 requests/minute per account and answers 429 with a
    | Retry-After header. The client waits and retries up to `retries` times,
    | never sleeping longer than `max_wait` seconds per attempt. Set retries
    | to 0 to get a RateLimitException immediately.
    */
    'rate_limit' => [
        'retries' => (int) env('JAYIK_RATE_LIMIT_RETRIES', 2),
        'max_wait' => (int) env('JAYIK_RATE_LIMIT_MAX_WAIT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Incoming webhooks
    |--------------------------------------------------------------------------
    | `secret` is the `secret.value` returned by Jayik::webhook()->get()
    | (starts with whsec_). Requests are verified with HMAC-SHA256 over
    | "{X-Jayik-Timestamp}.{raw body}" and rejected when older than
    | `tolerance` seconds.
    */
    'webhook' => [
        'secret' => env('JAYIK_WEBHOOK_SECRET'),
        'tolerance' => (int) env('JAYIK_WEBHOOK_TOLERANCE', 300),

        'route' => [
            'enabled' => (bool) env('JAYIK_WEBHOOK_ROUTE_ENABLED', true),
            'path' => env('JAYIK_WEBHOOK_PATH', 'jayik/webhook'),
            'middleware' => ['api'],
        ],
    ],

];
