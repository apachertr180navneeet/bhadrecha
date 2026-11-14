<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BlackBuck FASTag API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for BlackBuck FASTag Transaction History API.
    | All credentials and endpoints are read from environment variables.
    |
    */

    'base_url' => env('BLACKBUCK_BASE_URL', 'https://api-fms.blackbuck.com'),

    'transaction_endpoint' => env('BLACKBUCK_TRANSACTION_ENDPOINT', '/fmstoll/api/lfo-transaction-details/transactions'),

    /*
    | Authorization Token for BlackBuck API:
    | Uses Header "Authorization: Token {BLACKBUCK_TOKEN}" exactly as specified.
    */
    'token' => env('BLACKBUCK_TOKEN'),

    /*
    | Configurable Phone Number for BlackBuck FASTag Account
    */
    'phone_number' => env('BLACKBUCK_PHONE_NUMBER', '9829894925'),

    /*
    | Request Timeout (seconds) & Retries
    */
    'timeout' => (int) env('BLACKBUCK_TIMEOUT', 60),

    'retry_count' => (int) env('BLACKBUCK_RETRY_COUNT', 3),

    'retry_delay_ms' => (int) env('BLACKBUCK_RETRY_DELAY', 1000),

    /*
    | Auto-sync defaults
    */
    'sync_days_lookback' => (int) env('BLACKBUCK_SYNC_LOOKBACK_DAYS', 3),
];
