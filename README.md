# Setup Project Laravel

## Langkah Instalasi

1. Copy file environment:

    ```bash
    cp .env.example .env
    ```

2. Install dependency composer:

    ```bash
    composer install
    ```

3. Generate application key:

    ```bash
    php artisan key:generate
    ```

4. Jalankan migrasi database:

    ```bash
    php artisan migrate
    ```

5. (Opsional) Untuk reset dan seed ulang database:

    ```bash
    php artisan migrate:fresh --seed
    ```

6. Jalankan server lokal:
    ```bash
    php artisan serve
    ```

Pastikan sudah mengatur koneksi database di file `.env` sebelum menjalankan migrasi.
