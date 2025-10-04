# 🌐 Penjelasan CORS: Frontend vs Backend

## 📍 **Perbedaan Port dan Fungsinya**

### **Port 8000 - Laravel API (Backend)**

```
http://localhost:8000
```

-   **Fungsi**: Server API yang menyediakan data
-   **Berisi**:
    -   Database logic
    -   Authentication
    -   Business logic
    -   JSON responses
-   **Contoh Endpoints**:
    ```
    GET  http://localhost:8000/api/
    POST http://localhost:8000/api/login
    GET  http://localhost:8000/api/users
    POST http://localhost:8000/api/categories
    ```

### **Port 3000 - Frontend Application**

```
http://localhost:3000
```

-   **Fungsi**: User Interface yang user lihat dan gunakan
-   **Berisi**:
    -   HTML, CSS, JavaScript
    -   Forms, buttons, displays
    -   User interactions
-   **Teknologi**: React, Vue, Angular, atau HTML biasa

## 🔄 **Bagaimana Keduanya Bekerja Sama**

```
[User Browser]  →  [Frontend:3000]  →  [API:8000]  →  [Database]
     👆              👆                 👆             👆
   User sees       UI/Forms          Data Logic      Data Storage
```

### **Flow Aplikasi Normal:**

1. **User** buka browser → `localhost:3000`
2. **Frontend** tampilkan form login
3. **User** input email/password → klik submit
4. **Frontend** kirim data ke `localhost:8000/api/login`
5. **API** cek database → return JWT token
6. **Frontend** terima token → redirect ke dashboard
7. **Frontend** pakai token untuk request data lain

## ⚠️ **Masalah Tanpa CORS**

Browser punya **Same-Origin Policy**:

```
❌ localhost:3000 TIDAK BOLEH akses localhost:8000
❌ https://myapp.com TIDAK BOLEH akses https://api.myapp.com
```

### **Error yang Muncul:**

```
Access to fetch at 'http://localhost:8000/api/login'
from origin 'http://localhost:3000' has been blocked by CORS policy
```

## ✅ **Solusi Dengan CORS**

CORS memberitahu browser: "Domain ini boleh akses API itu"

### **Konfigurasi CORS Anda:**

```php
// config/cors.php
'allowed_origins' => [
    'http://localhost:3000',  // Frontend development
    'https://myapp.com',      // Production frontend
],
```

## 🎯 **Kapan CORS Dibutuhkan?**

### **✅ BUTUH CORS:**

```
Frontend (localhost:3000) → API (localhost:8000)
Website (myapp.com) → API (api.myapp.com)
Mobile App → API Server
Different subdomains
```

### **❌ TIDAK BUTUH CORS:**

```
Same domain & port: myapp.com/page → myapp.com/api
Server-to-server: API → Database
Postman/curl requests (tidak via browser)
```

## 🛠️ **Testing Setup**

### **1. Jalankan Laravel API (Port 8000):**

```bash
cd /path/to/takmir_api_laravel-main
php artisan serve --port=8000
```

### **2. Buka Frontend Example (Port 3000):**

```bash
# Option 1: Simple HTTP server
python3 -m http.server 3000

# Option 2: Node.js serve
npx serve -p 3000

# Option 3: PHP built-in server
php -S localhost:3000
```

### **3. Test CORS:**

1. Buka `http://localhost:3000/frontend-example.html`
2. Klik "Test API Login"
3. Lihat apakah request berhasil atau error CORS

## 🔒 **Aspek Keamanan CORS**

### **Development (Permissive):**

```php
'allowed_origins' => ['*'],  // Allow semua domain
```

### **Production (Restrictive):**

```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://app.yourdomain.com',
],
```

### **Mengapa Perlu Restrictive di Production?**

-   Mencegah website lain "mencuri" API Anda
-   Melindungi dari unauthorized access
-   Kontrol siapa yang boleh pakai API

## 📱 **Real World Example**

### **Instagram-like App:**

```
Frontend: https://myinstagram.com (React)
API: https://api.myinstagram.com (Laravel)
Mobile: Instagram Mobile App
```

**CORS Config:**

```php
'allowed_origins' => [
    'https://myinstagram.com',           // Web app
    'https://admin.myinstagram.com',     // Admin panel
    // Mobile app tidak butuh CORS (native request)
],
```

## 🎯 **Kesimpulan**

**CORS itu seperti "Security Guard" untuk API:**

-   Guard: "Siapa yang mau akses API?"
-   Request: "Saya dari localhost:3000"
-   Guard: "OK, localhost:3000 ada di whitelist, silakan masuk"
-   Request: "Terima kasih!" → berhasil dapat data

**Tanpa CORS:**

-   Guard: "Maaf, localhost:3000 tidak ada di whitelist"
-   Request: "Tapi saya butuh data!"
-   Guard: "Tidak bisa, security policy"
-   Browser: "CORS Error!" ❌
