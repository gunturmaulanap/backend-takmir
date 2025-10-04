# Cara Menggunakan Laravel Built-in CORS

## 1. Publish CORS config (opsional)
```bash
php artisan vendor:publish --tag=cors
```

## 2. Update bootstrap/app.php
```php
->withMiddleware(function (Middleware $middleware) {
    // Ganti custom CORS dengan built-in
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

## 3. Konfigurasi di config/cors.php
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // atau specific domains
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

## Keuntungan Built-in:
✅ Maintained by Laravel team
✅ Auto-updates dengan framework
✅ Less custom code to maintain
✅ Standard implementation

## Keuntungan Custom (seperti sekarang):
✅ Full control over logic
✅ Custom error handling
✅ Specific to project needs
✅ Better understanding of CORS