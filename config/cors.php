<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | CORS digunakan untuk:
    | 1. Memungkinkan frontend apps mengakses API dari domain berbeda
    | 2. Mengontrol keamanan - domain mana yang boleh akses API
    | 3. Melindungi dari unauthorized access
    |
    */

    // Paths yang akan menggunakan CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // HTTP methods yang diizinkan
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Origins yang diizinkan - SECURITY CRITICAL!
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') ?
        explode(',', env('CORS_ALLOWED_ORIGINS')) : (env('APP_ENV') === 'production' ? [] : ['*']),

    // Pattern untuk origins (regex)
    'allowed_origins_patterns' => [],

    // Headers yang diizinkan dalam request
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'Origin'
    ],

    // Headers yang akan di-expose ke client
    'exposed_headers' => [
        'Authorization'
    ],

    // Cache preflight request (seconds)
    'max_age' => 86400, // 24 hours

    // Support credentials (cookies, auth headers)
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];
