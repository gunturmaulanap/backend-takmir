# CORS Implementation Guide

## 📋 Ringkasan Implementasi CORS

CORS (Cross-Origin Resource Sharing) telah berhasil diimplementasikan untuk project Takmir API Laravel ini dengan konfigurasi lengkap.

## 🔧 Komponen yang Telah Diimplementasikan

### 1. Middleware CORS (`app/Http/Middleware/Cors.php`)

-   ✅ Handle preflight OPTIONS requests
-   ✅ Menambahkan CORS headers ke semua responses
-   ✅ Menggunakan konfigurasi dari file config
-   ✅ Support untuk semua HTTP methods (GET, POST, PUT, DELETE, PATCH, OPTIONS)

### 2. Konfigurasi CORS (`config/cors.php`)

-   ✅ Konfigurasi allowed origins (default: semua \*)
-   ✅ Konfigurasi allowed methods (default: semua)
-   ✅ Konfigurasi allowed headers
-   ✅ Settings untuk credentials dan max age

### 3. Registrasi Middleware (`bootstrap/app.php`)

-   ✅ Middleware terdaftar sebagai alias 'cors'
-   ✅ Middleware diterapkan secara global ke semua API routes
-   ✅ Middleware dijalankan sebelum middleware lainnya (prepend)

### 4. Route Configuration (`routes/api.php`)

-   ✅ Route OPTIONS wildcard untuk handle preflight requests
-   ✅ Middleware CORS otomatis diterapkan ke semua API routes

## 🚀 Cara Menggunakan

### Server sudah berjalan di:

```
http://127.0.0.1:8000
```

### Test CORS:

1. Buka file `cors-test.html` di browser
2. Check console browser untuk melihat hasil test
3. Atau gunakan curl/Postman untuk test manual

### Test Manual dengan curl:

```bash
# Test preflight OPTIONS request
curl -X OPTIONS http://127.0.0.1:8000/api/login \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type,Authorization" \
  -v

# Test actual API request
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:3000" \
  -d '{"email":"test@example.com","password":"password"}' \
  -v
```

## ⚙️ Kustomisasi CORS

### Mengubah Allowed Origins

Edit file `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://localhost:3001',
    'https://yourdomain.com'
],
```

### Mengubah Allowed Methods

```php
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
```

### Enable Credentials

```php
'supports_credentials' => true,
```

## 🔍 Headers yang Ditambahkan

CORS middleware menambahkan headers berikut ke setiap response:

-   `Access-Control-Allow-Origin`
-   `Access-Control-Allow-Methods`
-   `Access-Control-Allow-Headers`
-   `Access-Control-Allow-Credentials`
-   `Access-Control-Max-Age` (untuk preflight requests)

## 🛠️ Troubleshooting

### Problem: CORS masih tidak bekerja

**Solution:**

1. Clear cache: `php artisan config:clear`
2. Restart server
3. Check browser console untuk error messages

### Problem: Preflight requests gagal

**Solution:**

1. Pastikan route OPTIONS ada
2. Check allowed headers di config
3. Pastikan middleware CORS dijalankan pertama

### Problem: Credentials tidak dikirim

**Solution:**

1. Set `supports_credentials` ke `true` di config
2. Set allowed origins spesifik (tidak bisa '\*' kalau pakai credentials)
3. Tambahkan `credentials: 'include'` di fetch request

## ✨ Fitur Tambahan

### Environment-specific Configuration

Bisa buat konfigurasi berbeda untuk setiap environment:

```php
// .env
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://yourdomain.com

// config/cors.php
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
```

### Logging CORS Requests

Tambahkan logging di middleware untuk debugging:

```php
\Log::info('CORS Request', [
    'method' => $request->getMethod(),
    'origin' => $request->header('Origin'),
    'uri' => $request->getRequestUri()
]);
```

## 📝 Notes

-   Middleware CORS sudah diterapkan globally ke semua API routes
-   Konfigurasi default mengizinkan semua origins (\*) untuk development
-   Untuk production, sebaiknya specify allowed origins secara eksplisit
-   Package `fruitcake/php-cors` sudah terinstall dan siap digunakan jika diperlukan

## 🎯 Next Steps

1. **Production Security**: Ubah allowed origins dari '\*' ke domain spesifik
2. **Testing**: Test dengan frontend application sebenarnya
3. **Monitoring**: Add logging untuk monitoring CORS requests
4. **Documentation**: Update API documentation dengan CORS information
