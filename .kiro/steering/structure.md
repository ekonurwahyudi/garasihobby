---
inclusion: always
---

# Struktur Proyek — Garasi Hobby

Proyek ini Laravel monolith dengan Blade + Metronic. Patuhi struktur folder berikut agar konsisten.

## Layout Workspace
```
d:\garasihobby\
├── .kiro\                       # Kiro steering & specs
│   └── steering\
├── garasihobby.md               # Spec produk (jangan diubah tanpa persetujuan owner)
├── metronic\                    # Template HTML Metronic (read-only, sebagai referensi)
└── app\                         # Root project Laravel (dibuat di Step 1)
    ├── app\
    ├── bootstrap\
    ├── config\
    ├── database\
    ├── public\
    ├── resources\
    ├── routes\
    └── ...
```

> Catatan: Saat install Laravel, install di subfolder `d:\garasihobby\app\` (atau langsung di root, sesuai preferensi). Folder `metronic\` jangan disentuh — itu master template.

## Struktur App Laravel

```
app\
├── Console\
│   └── Commands\
│       └── CheckLowStockCommand.php       # scheduled untuk notifikasi stok
├── Http\
│   ├── Controllers\
│   │   ├── Auth\                          # Login, Logout
│   │   ├── DashboardController.php
│   │   ├── Master\                        # Master data
│   │   │   ├── UserController.php
│   │   │   ├── ChecklistItemController.php
│   │   │   ├── ChecklistCategoryController.php
│   │   │   ├── MaterialController.php
│   │   │   ├── MaterialCategoryController.php
│   │   │   └── PromoPackageController.php
│   │   ├── Operasional\
│   │   │   ├── CustomerController.php
│   │   │   ├── OrderController.php
│   │   │   ├── OrderHistoryController.php
│   │   │   ├── MaterialPurchaseController.php
│   │   │   └── MaterialStockController.php
│   │   ├── Role\
│   │   │   ├── RoleController.php
│   │   │   └── PermissionController.php
│   │   ├── NotificationController.php
│   │   └── ProfileController.php
│   ├── Middleware\
│   ├── Requests\                          # Form Request validation per modul
│   │   ├── Master\
│   │   ├── Operasional\
│   │   └── Role\
│   └── Resources\                         # API resources (jika perlu)
├── Models\
│   ├── User.php
│   ├── Customer.php
│   ├── Vehicle.php                        # opsional, jika 1 customer banyak mobil
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderEvidence.php
│   ├── ChecklistItem.php
│   ├── ChecklistCategory.php
│   ├── Material.php
│   ├── MaterialCategory.php
│   ├── MaterialStock.php
│   ├── MaterialPurchase.php
│   ├── MaterialPurchaseItem.php
│   ├── MaterialPurchaseApproval.php
│   ├── PromoPackage.php
│   └── PromoPackageItem.php
├── Notifications\
│   ├── MaterialPurchaseSubmitted.php
│   ├── MaterialPurchaseApproved.php
│   ├── MaterialPurchaseRejected.php
│   ├── LowStockAlert.php
│   ├── OutOfStockAlert.php
│   ├── NewOrderCreated.php
│   ├── OrderUnpaid.php
│   └── OrderCompleted.php
├── Policies\
│   ├── OrderPolicy.php
│   ├── MaterialPurchasePolicy.php
│   └── ...
├── Providers\
└── Services\                              # business logic, dipakai dari controller
    ├── OrderService.php
    ├── MaterialPurchaseService.php
    ├── StockService.php
    ├── NotificationService.php
    └── R2UploadService.php

database\
├── migrations\
├── seeders\
│   ├── DatabaseSeeder.php
│   ├── RolePermissionSeeder.php           # buat role Superadmin, CS, Mekanik, QC
│   ├── UserSeeder.php                     # buat akun superadmin pertama
│   ├── ChecklistCategorySeeder.php
│   └── MaterialCategorySeeder.php
└── factories\

resources\
├── views\
│   ├── layouts\
│   │   ├── app.blade.php                  # layout utama Metronic (sidebar + header)
│   │   ├── auth.blade.php                 # layout halaman login
│   │   └── partials\
│   │       ├── sidebar.blade.php
│   │       ├── header.blade.php
│   │       ├── footer.blade.php
│   │       └── notification-bell.blade.php
│   ├── auth\
│   │   └── login.blade.php
│   ├── dashboard\
│   │   └── index.blade.php
│   ├── master\
│   │   ├── users\
│   │   ├── checklist-items\
│   │   ├── checklist-categories\
│   │   ├── materials\
│   │   ├── material-categories\
│   │   └── promo-packages\
│   ├── operasional\
│   │   ├── customers\
│   │   ├── orders\
│   │   ├── material-purchases\
│   │   └── material-stocks\
│   ├── roles\
│   ├── notifications\
│   └── components\                        # Blade components reusable
│       ├── kt-card.blade.php
│       ├── kt-table.blade.php
│       ├── kt-modal.blade.php
│       └── kt-form-input.blade.php
├── js\
│   ├── app.js
│   └── pages\                             # JS spesifik per halaman
└── sass\
    └── app.scss                           # @import metronic styles

routes\
├── web.php                                # semua route (auth required, by middleware)
├── auth.php                               # login/logout
└── console.php

public\
└── assets\                                # SALIN dari metronic\assets\ (CSS, JS, plugins)
```

## Aturan Penempatan Kode
- **Controller** tipis: hanya validasi (via FormRequest) → panggil Service → return view/redirect.
- **Service** berisi business logic (transaksi DB, kalkulasi total, update stok).
- **Model** hanya: relationships, casts, scopes, accessors. Tidak ada query bisnis kompleks.
- **FormRequest** untuk semua input user. Jangan validate di controller.
- **Notification** terpisah per event, jangan satu kelas multi-trigger.
- **View** strict pakai layout `layouts.app`. Tidak boleh inline `<html>` di view modul.

## Penempatan Asset Metronic
- Salin `metronic/assets/` ke `public/assets/` di Step 2.
- Salin `metronic/src/` (kalau ada source SCSS) ke `resources/sass/metronic/` jika butuh kustomisasi.
- Jangan modifikasi `metronic/` di workspace — itu master copy.

## Routing & Middleware
- Semua route admin di-group dengan `middleware('auth')`.
- Group permission pakai `middleware('can:permission-name')` per route atau `Route::middleware(['role:Superadmin'])`.
- Login route di file terpisah `routes/auth.php`, di-include dari `web.php`.
