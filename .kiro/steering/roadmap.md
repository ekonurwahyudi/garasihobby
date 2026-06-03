---
inclusion: manual
---

# Roadmap Eksekusi — Garasi Hobby

Roadmap step-by-step untuk membangun aplikasi dari nol. Setiap step punya checklist yang harus selesai sebelum lanjut ke step berikut. Aktifkan steering ini dengan `#roadmap` saat memulai sesi.

---

## STEP 1 — Foundation: Laravel + DB + Auth + Spatie Role

**Tujuan**: Project Laravel jalan, bisa login dengan role-permission Spatie.

### 1.1 Install Laravel
```bash
composer create-project laravel/laravel app
```
Pindah ke `d:\garasihobby\app`. Set up `.env` sesuai `tech.md`.

### 1.2 Setup PostgreSQL
- Buat database `garasihobby` di PostgreSQL.
- Pastikan `pdo_pgsql` enabled di PHP.
- Test koneksi: `php artisan db:show`.

### 1.3 Install Package
```bash
composer require spatie/laravel-permission
composer require league/flysystem-aws-s3-v3 "^3.0"
composer require barryvdh/laravel-dompdf
```

### 1.4 Konfigurasi
- Publish Spatie config: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- Tambah disk `r2` di `config/filesystems.php` (lihat `tech.md`).
- Set `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`.

### 1.5 Migration & Seeder Auth
- Tambah migration `add_jabatan_phone_status_to_users_table`:
  - `jabatan` varchar(100), `phone` varchar(20), `status` varchar(10) default `aktif`, `deleted_at`.
- Migrate: `php artisan migrate`.
- Buat `RolePermissionSeeder`:
  - Role: `Superadmin`, `Customer Service`, `Mekanik`, `QC`.
  - Permission: `dashboard.view`, `users.manage`, `roles.manage`, `customers.manage`, `orders.create`, `orders.view`, `orders.edit`, `orders.qc`, `orders.payment`, `materials.manage`, `materials.purchase`, `materials.approve`, `checklist.manage`, `promo.manage`, `notifications.view`.
  - Assign semua permission ke `Superadmin`.
  - CS: `customers.manage`, `orders.create`, `orders.view`, `orders.edit`, `orders.payment`, `materials.purchase`, `notifications.view`, `dashboard.view`.
  - Mekanik: `orders.view`, `notifications.view`.
  - QC: `orders.view`, `orders.qc`, `notifications.view`.
- Buat `UserSeeder`: superadmin pertama (email `admin@garasihobby.com`, password env-driven).

### 1.6 Login
- Route `/login`, controller `Auth\LoginController`.
- View `auth/login.blade.php` belum perlu Metronic, plain dulu (akan dipakaikan Metronic di Step 2).
- Middleware: `auth`, `verified` (opsional), `role:Superadmin|Customer Service|...`.
- Logout, remember-me, session timeout.

### Checklist Step 1
- [ ] `php artisan migrate:fresh --seed` jalan tanpa error
- [ ] Login dengan akun seed berhasil
- [ ] `auth()->user()->hasRole('Superadmin')` true
- [ ] R2 disk bisa upload file dummy (`Storage::disk('r2')->put(...)`)

---

## STEP 2 — UI/UX dengan Template Metronic

**Tujuan**: Layout sidebar + header Metronic siap dipakai semua halaman, halaman login pakai theme Metronic.

### 2.1 Salin Asset
- Salin `metronic/assets/` → `app/public/assets/`.
- Pilih layout reference: `metronic/layouts/light-sidebar.html` (rekomendasi).
- Salin `metronic/authentication/layouts/` versi yang dipilih untuk login page.

### 2.2 Buat Layout Blade
- `resources/views/layouts/app.blade.php`:
  - Adaptasi dari `light-sidebar.html`.
  - Yield: `@yield('title')`, `@yield('content')`, `@stack('scripts')`, `@stack('styles')`.
  - Include partials: `layouts.partials.sidebar`, `layouts.partials.header`, `layouts.partials.footer`, `layouts.partials.notification-bell`.
- `resources/views/layouts/auth.blade.php`:
  - Adaptasi dari `metronic/authentication/layouts/auth-1.html` (atau yang dipilih).

### 2.3 Sidebar Menu
Sidebar harus dinamis berdasarkan permission. Contoh struktur menu:
- Dashboard (`dashboard.view`)
- Master Data (group, butuh salah satu permission `*.manage`)
  - User (`users.manage`)
  - Item Checklist (`checklist.manage`)
  - Kategori Checklist (`checklist.manage`)
  - Katalog Material (`materials.manage`)
  - Kategori Material (`materials.manage`)
  - Paket Promo (`promo.manage`)
- Operasional
  - Pelanggan (`customers.manage`)
  - Input Order (`orders.create`)
  - History Order (`orders.view`)
  - Pembelian Material (`materials.purchase`)
  - Approval Pembelian (`materials.approve`)
  - Persediaan Material (`materials.manage`)
- Notifikasi (`notifications.view`)
- Role Management (`roles.manage`)

Pakai helper `@can('permission')` di Blade.

### 2.4 Halaman Login Themed
- Refactor `auth/login.blade.php` pakai layout `layouts.auth`.
- Tambah validasi visual (error state Metronic).

### 2.5 Notification Bell Skeleton
- Partial `notification-bell.blade.php`: dropdown bell icon dengan badge counter.
- Endpoint AJAX `/notifications/unread-count` (return JSON).
- Render daftar notifikasi terakhir 10.

### Checklist Step 2
- [ ] Layout sidebar tampil rapi di desktop & mobile
- [ ] Login page pakai theme Metronic
- [ ] Sidebar menu hide/show sesuai permission user
- [ ] Bell icon tampil (data dummy boleh dulu)

---

## STEP 3 — Master Data & Role Management

**Tujuan**: Semua master data CRUD-able. Superadmin bisa kelola role dan permission.

### 3.1 Master Data CRUD
Buat per modul (urutan dependency):
1. Kategori Checklist (`checklist-categories`)
2. Item Checklist (`checklist-items`) — depends on kategori
3. Kategori Material (`material-categories`)
4. Katalog Material (`materials`) — depends on kategori
5. Paket Promo (`promo-packages`) — depends on item checklist
6. User (`users`) — depends on role

Pola tiap modul:
- `index` (DataTable, search, filter, paginate 10/25/50)
- `create` (Modal atau halaman tersendiri)
- `store` (FormRequest validation, Service)
- `edit` (Modal atau halaman)
- `update` (FormRequest)
- `destroy` (soft delete, konfirmasi modal)
- Bulk action minimal (delete selected).

### 3.2 User Management
- Form user dengan field: nama, jabatan, role (multi-select Spatie), no HP, email, password, status.
- Password hanya required di create. Edit kosongkan = tidak ubah.
- Foto profil opsional, upload ke R2.

### 3.3 Role Management
- List role: tabel dengan kolom nama, jumlah user, jumlah permission.
- Create/Edit role: input nama + checklist permission grouped per modul (`Master Data`, `Operasional`, `Notifikasi`, `Dashboard`, `Pengaturan`).
- Delete role: cek dulu apakah ada user pakai role tsb (block kalau ada).

### 3.4 Seed Master Awal
- Kategori Checklist (9 kategori dari spec).
- Kategori Material (8 kategori dari spec).

### Checklist Step 3
- [ ] Semua 6 master CRUD jalan + validasi
- [ ] Role bisa dibuat dan permission-nya ke-assign
- [ ] User baru dengan role custom bisa login dan menu sidebar-nya sesuai

---

## STEP 4 — Operasional & Notifikasi

**Tujuan**: Workflow inti (input order → QC → bayar → invoice) jalan. Notifikasi terkirim.

### 4.1 Data Pelanggan
- CRUD pelanggan + kendaraan.
- Endpoint search by plate number (untuk auto-fill di order form).

### 4.2 Input Order
- Form order multi-section:
  1. Section Pelanggan (cari plat → auto-fill, atau input baru).
  2. Section Paket Promo (optional, dropdown).
  3. Section Item Checklist (grouped accordion per kategori, checkbox per item, jika promo dipilih → auto-check item paket).
  4. Section Detail Item (kondisi awal, tindakan, harga edit-able).
  5. Section Eviden (multi-upload ke R2, preview thumbnail, hapus per file).
  6. Section Total (subtotal, diskon input, total hitung otomatis lewat JS).
  7. Section Status (default `open`).
- Generate `order_number` di service (format `ORD/YYYYMM/0001`, sequence per bulan).
- Trigger notif `NewOrderCreated` ke role CS (kecuali yang bikin) + Superadmin.

### 4.3 QC Approval
- Halaman QC: list order status `open` → klik → list item dengan tombol Approve/Reject + note.
- Saat semua item approved → set `qc_approved_at` di order, ubah status ke `belum_bayar`.
- Trigger notif `OrderUnpaid` ke CS.

### 4.4 Pembayaran & Invoice
- Halaman pembayaran: input amount, method, eviden bukti bayar.
- Saat total bayar ≥ total order → status `sudah_bayar`, set `paid_at`.
- Generate invoice PDF (DomPDF), simpan ke R2, link di order detail.
- Status `selesai` di-set saat invoice terbit.
- Trigger notif `OrderCompleted` ke Superadmin.

### 4.5 History Order
- Halaman riwayat dengan filter: tanggal, status, plat, customer.
- Export Excel (opsional).

### 4.6 Pembelian Material
- Form ajuan: tanggal, items (multi-row: material, qty, harga satuan), eviden, catatan.
- Submit: status `menunggu_approval`, trigger notif `MaterialPurchaseSubmitted` ke Superadmin.
- Halaman approval (Superadmin only):
  - Approve → status `disetujui`, `StockService::increase()` per item, log ke `material_stock_movements`, notif `MaterialPurchaseApproved` ke pengaju.
  - Reject → wajib `rejection_reason`, status `ditolak`, notif `MaterialPurchaseRejected` + alasan ke pengaju.
- Pengaju bisa edit submission yang ditolak → resubmit (status balik `menunggu_approval`, log ke `material_purchase_approvals`).

### 4.7 Persediaan Material
- Halaman list material + qty + status (Aman/Hampir Habis/Habis).
- Filter berdasarkan status.
- Halaman detail material: history movement (dari `material_stock_movements`).

### 4.8 Notifikasi
- Halaman daftar notifikasi (`/notifications`):
  - Tab `Belum Dibaca` / `Semua`.
  - Klik notif → mark as read + redirect ke action target.
  - Tombol "Tandai Semua Dibaca", "Hapus".
- Bell badge: counter unread realtime (poll setiap 30 detik atau pakai Echo kalau ada).
- Scheduled command `CheckLowStockCommand`:
  - Tiap jam scan materials.
  - Kirim `LowStockAlert` saat threshold terlewati (sekali per material per hari, simpan di cache).
  - Kirim `OutOfStockAlert` saat qty 0.

### Checklist Step 4
- [ ] Order baru bisa dibuat, QC bisa approve, pembayaran bisa diproses, invoice PDF generate
- [ ] Pembelian material approval flow (submit → approve → stok bertambah) jalan
- [ ] Pembelian material reject mewajibkan alasan, pengaju bisa resubmit
- [ ] Notifikasi muncul di bell icon dan halaman notifikasi
- [ ] Stok material status (Aman/Hampir Habis/Habis) akurat

---

## STEP 5 — Dashboard

**Tujuan**: Owner punya helicopter view untuk decision making.

### 5.1 KPI Cards
- Total Order Hari Ini
- Order Open
- Order Selesai (hari ini)
- Order Belum Bayar
- Revenue Harian (sum total order `sudah_bayar` hari ini)
- Revenue Bulanan
- Total Pelanggan
- Material Hampir Habis (count)
- Approval Pending (count `menunggu_approval`)

### 5.2 Grafik (ApexCharts)
- Grafik Revenue 30 hari terakhir (line chart)
- Grafik Order Bulanan 12 bulan (bar chart, stacked by status)
- Grafik Penggunaan Material top 10 (horizontal bar, dari `material_stock_movements` type `out`)

### 5.3 Widget List
- Approval Pending (5 terakhir + tombol langsung ke approval)
- Material Kritis (5 material dengan stok terendah)
- Order Hari Ini (5 terakhir)

### 5.4 Performa
- Cache query dashboard 5-10 menit (`Cache::remember`) karena banyak agregasi.
- Index DB sesuai `database.md` section 8.

### Checklist Step 5
- [ ] Semua KPI cards menampilkan angka yang benar
- [ ] 3 grafik tampil dengan data real
- [ ] Widget list bisa diklik dan navigate ke modul terkait
- [ ] Dashboard load < 2 detik dengan data sample 1000 order
