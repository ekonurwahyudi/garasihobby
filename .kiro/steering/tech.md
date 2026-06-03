---
inclusion: always
---

# Tech Stack — Garasi Hobby

## Backend
- **Laravel 12** (PHP 8.2+)
- **PostgreSQL 17+** sebagai database utama
- **Spatie Laravel-Permission** untuk role & permission management
- **Laravel Sanctum** (opsional, hanya jika butuh API token)
- **Laravel Notifications** (channel: database) untuk in-app notification
- **Spatie Laravel-MediaLibrary** atau Laravel Filesystem langsung untuk upload eviden
- **DomPDF** atau **Spatie Browsershot** untuk generate invoice & template order PDF
- **Laravel Excel (Maatwebsite)** jika butuh export laporan

## Frontend
- **Blade Templating** (server-rendered) — sesuai dengan template Metronic yang HTML statis
- **Metronic v8** sebagai base UI/UX (folder `d:\garasihobby\metronic`)
- **Bootstrap 5** (sudah bundled di Metronic)
- **Vite** untuk asset bundling
- **Alpine.js** atau **jQuery** untuk interaktivitas ringan (Metronic pakai jQuery)
- **Livewire** opsional untuk komponen reaktif (notifikasi bell, form approval)
- **Select2, Flatpickr, ApexCharts** — sudah ada di bundle Metronic, manfaatkan langsung

## Storage
- **Cloudflare R2** (S3-compatible) untuk eviden, foto, PDF
  - Endpoint: `https://29f7a833a94e23dfd222a11aab7d504a.r2.cloudflarestorage.com/garasihobby`
  - Public Dev URL: `https://pub-aa3f88e3f2c043e19bd8a8f7f294cc7a.r2.dev`
  - CDN: `cdn.garasihobby.com`
- Pakai driver `s3` di `config/filesystems.php` dengan endpoint custom R2.

## Tools & DevOps
- **Composer** untuk dependency PHP
- **NPM** untuk dependency JS
- **Git** + branch convention: `main`, `develop`, `feature/*`, `hotfix/*`
- **PHPStan** atau **Larastan** level 5+ untuk static analysis
- **Pest** atau **PHPUnit** untuk testing
- **Laravel Pint** untuk code formatting

## Konfigurasi `.env` Penting
```
APP_NAME="Garasi Hobby"
APP_LOCALE=id
APP_TIMEZONE="Asia/Jakarta"

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=garasihobby
DB_USERNAME=postgres
DB_PASSWORD=

FILESYSTEM_DISK=r2

# Cloudflare R2 (S3-compatible)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=garasihobby
AWS_ENDPOINT=https://29f7a833a94e23dfd222a11aab7d504a.r2.cloudflarestorage.com
AWS_URL=https://cdn.garasihobby.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

## Disk R2 di `config/filesystems.php`
```php
'r2' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'auto'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'url' => env('AWS_URL'),
    'use_path_style_endpoint' => true,
    'throw' => false,
    'visibility' => 'public',
],
```

## Package Composer Wajib
```bash
composer require spatie/laravel-permission
composer require league/flysystem-aws-s3-v3 "^3.0"
composer require barryvdh/laravel-dompdf
composer require spatie/laravel-medialibrary  # opsional
composer require maatwebsite/excel            # opsional, untuk export
```

## Konvensi Penamaan
- **Tabel**: snake_case plural (`orders`, `order_items`, `material_purchases`)
- **Model**: PascalCase singular (`Order`, `OrderItem`, `MaterialPurchase`)
- **Controller**: PascalCase + `Controller` (`OrderController`)
- **Route name**: dot notation (`orders.index`, `material-purchases.approve`)
- **View**: kebab-case (`resources/views/orders/create.blade.php`)
- **Permission name**: `module.action` (`orders.create`, `materials.approve`)
