---
inclusion: always
---

# Skema Database — Garasi Hobby (PostgreSQL)

Semua tabel pakai `id BIGSERIAL PRIMARY KEY`, `created_at`, `updated_at` (Laravel default). `deleted_at` ditambahkan di tabel yang butuh soft delete (ditandai eksplisit di bawah).

## 1. Auth & Akses

### `users`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| name | varchar(255) | NOT NULL |
| jabatan | varchar(100) | NOT NULL |
| phone | varchar(20) | NOT NULL, validasi regex |
| email | varchar(255) | NOT NULL, UNIQUE |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | NOT NULL, bcrypt |
| status | varchar(10) | NOT NULL, default `aktif`, enum [`aktif`,`block`] |
| remember_token | varchar(100) | nullable |
| created_at, updated_at | timestamp | |

Soft delete: ya (`deleted_at`).

### Tabel Spatie Permission (auto-generate dari `php artisan vendor:publish`)
- `roles` (id, name, guard_name)
- `permissions` (id, name, guard_name)
- `model_has_roles` (role_id, model_type, model_id)
- `model_has_permissions` (permission_id, model_type, model_id)
- `role_has_permissions` (permission_id, role_id)

Daftar role default (di-seed):
- `Superadmin`
- `Customer Service`
- `Mekanik`
- `QC`

Permission name pattern: `module.action`. Contoh: `orders.create`, `orders.view`, `materials.approve`, `users.manage`, `roles.manage`, `dashboard.view`.

### `password_reset_tokens`, `sessions`
Default Laravel.

## 2. Master Data

### `checklist_categories`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| code | varchar(20) | UNIQUE, NOT NULL |
| name | varchar(100) | NOT NULL |
| created_at, updated_at | timestamp | |

Seed awal: `Braken System`, `As Roda`, `Stabilizer`, `Suspension`, `Steering`, `Bushing`, `Engine Mounting`, `Ban`, `Indikator Speedometer`.

### `checklist_items`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| checklist_category_id | bigint | FK → checklist_categories |
| name | varchar(150) | NOT NULL |
| price | numeric(14,2) | NOT NULL, default 0 |
| is_active | boolean | default true |
| created_at, updated_at | timestamp | |

Soft delete: ya.

### `material_categories`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| code | varchar(20) | UNIQUE, NOT NULL |
| name | varchar(100) | NOT NULL |
| created_at, updated_at | timestamp | |

Seed awal: `Oli & Fluida`, `Filter`, `Mesin`, `Kaki-kaki`, `Sistem Rem`, `Kelistrikan`, `Chemical`, `Fast Moving Part`.

### `materials`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_category_id | bigint | FK → material_categories |
| sku | varchar(50) | nullable, UNIQUE |
| name | varchar(200) | NOT NULL |
| price | numeric(14,2) | harga jual default |
| cost_price | numeric(14,2) | harga beli terakhir, nullable |
| min_stock | int | default 0, untuk threshold notifikasi |
| binrow | varchar(50) | lokasi rak, nullable |
| is_active | boolean | default true |
| created_at, updated_at | timestamp | |

Soft delete: ya.

### `material_stocks`
View atau tabel terpisah berisi qty live. Pilihan: tabel terpisah biar audit-able.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_id | bigint | FK → materials, UNIQUE |
| qty | int | NOT NULL, default 0 |
| updated_at | timestamp | |

Status (`Aman`, `Hampir Habis`, `Habis`) dihitung di accessor model, bukan disimpan:
- `qty == 0` → Habis
- `qty <= material.min_stock` → Hampir Habis
- selain itu → Aman

### `material_stock_movements`
Audit log keluar-masuk stok.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_id | bigint | FK → materials |
| type | varchar(20) | enum [`in`, `out`, `adjustment`] |
| qty | int | NOT NULL |
| reference_type | varchar(100) | morph (e.g. `App\Models\MaterialPurchase`, `App\Models\Order`) |
| reference_id | bigint | morph id |
| note | text | nullable |
| user_id | bigint | FK → users (siapa yang trigger) |
| created_at, updated_at | timestamp | |

### `promo_packages`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| name | varchar(150) | NOT NULL |
| description | text | nullable |
| price | numeric(14,2) | NOT NULL — harga paket fix |
| is_active | boolean | default true |
| valid_from | date | nullable |
| valid_until | date | nullable |
| created_at, updated_at | timestamp | |

Soft delete: ya.

### `promo_package_items`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| promo_package_id | bigint | FK → promo_packages, ON DELETE CASCADE |
| checklist_item_id | bigint | FK → checklist_items |
| created_at, updated_at | timestamp | |

UNIQUE(`promo_package_id`, `checklist_item_id`).

## 3. Pelanggan & Kendaraan

### `customers`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| name | varchar(150) | NOT NULL |
| phone | varchar(20) | NOT NULL |
| email | varchar(255) | nullable |
| created_at, updated_at | timestamp | |

Soft delete: ya.

### `vehicles`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| customer_id | bigint | FK → customers |
| plate_number | varchar(20) | NOT NULL, UNIQUE |
| brand | varchar(100) | nullable |
| model | varchar(100) | nullable, nama "Jenis Mobil" di UI |
| year | smallint | nullable |
| created_at, updated_at | timestamp | |

UNIQUE(`plate_number`). Saat input order, lookup berdasarkan plate_number → auto-fill customer.

## 4. Order Servis

### `orders`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| order_number | varchar(30) | UNIQUE, format `ORD/YYYYMM/0001` |
| order_date | date | NOT NULL |
| customer_id | bigint | FK → customers |
| vehicle_id | bigint | FK → vehicles |
| promo_package_id | bigint | FK → promo_packages, nullable |
| complaint | text | nullable, "Keluhan / Catatan" |
| subtotal | numeric(14,2) | total sebelum diskon |
| discount | numeric(14,2) | default 0 |
| total | numeric(14,2) | NOT NULL |
| status | varchar(20) | enum [`open`,`belum_bayar`,`sudah_bayar`,`selesai`,`batal`], default `open` |
| created_by | bigint | FK → users (CS yang input) |
| qc_approved_at | timestamp | nullable, terisi saat QC approve seluruh item |
| qc_approved_by | bigint | FK → users, nullable |
| paid_at | timestamp | nullable |
| created_at, updated_at | timestamp | |

Soft delete: ya.

Catatan: status `belum_bayar` baru tersetting setelah `qc_approved_at` terisi. Aturan ini di-enforce di service layer.

### `order_items`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| order_id | bigint | FK → orders, ON DELETE CASCADE |
| checklist_item_id | bigint | FK → checklist_items, nullable (kalau pakai promo bisa derive) |
| name | varchar(200) | snapshot nama item (jaga-jaga kalau master diubah) |
| condition_initial | text | "Kondisi Awal", nullable |
| next_action | text | "Tindakan Selanjutnya", nullable |
| qc_status | varchar(20) | enum [`pending`,`approved`,`rejected`], default `pending` |
| qc_note | text | nullable |
| qc_by | bigint | FK → users, nullable |
| qc_at | timestamp | nullable |
| qty | int | default 1 |
| price | numeric(14,2) | NOT NULL — harga setelah edit |
| subtotal | numeric(14,2) | qty * price |
| created_at, updated_at | timestamp | |

### `order_evidences`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| order_id | bigint | FK → orders, ON DELETE CASCADE |
| disk | varchar(20) | default `r2` |
| path | varchar(500) | path di R2 |
| original_name | varchar(255) | |
| mime_type | varchar(100) | |
| size | bigint | bytes |
| type | varchar(20) | enum [`work`, `payment`, `other`], "work" = foto saat kerjakan, "payment" = bukti bayar |
| uploaded_by | bigint | FK → users |
| created_at, updated_at | timestamp | |

### `order_payments`
Catat tiap pembayaran (mendukung pembayaran sebagian / split).

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| order_id | bigint | FK → orders |
| amount | numeric(14,2) | NOT NULL |
| method | varchar(30) | enum [`tunai`,`transfer`,`debit`,`kredit`,`qris`,`lain`] |
| paid_at | timestamp | NOT NULL |
| received_by | bigint | FK → users |
| note | text | nullable |
| created_at, updated_at | timestamp | |

### `invoices`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| order_id | bigint | FK → orders, UNIQUE |
| invoice_number | varchar(30) | UNIQUE, format `INV/YYYYMM/0001` |
| issued_at | timestamp | NOT NULL |
| pdf_path | varchar(500) | path di R2 |
| created_at, updated_at | timestamp | |

## 5. Pembelian Material (dengan Approval)

### `material_purchases`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| purchase_number | varchar(30) | UNIQUE, format `PO/YYYYMM/0001` |
| purchase_date | date | NOT NULL |
| note | text | nullable, catatan global |
| total | numeric(14,2) | NOT NULL |
| status | varchar(30) | enum [`draft`,`menunggu_approval`,`disetujui`,`ditolak`], default `draft` |
| submitted_at | timestamp | nullable |
| submitted_by | bigint | FK → users (pengaju) |
| approved_at | timestamp | nullable |
| approved_by | bigint | FK → users, nullable |
| rejected_at | timestamp | nullable |
| rejected_by | bigint | FK → users, nullable |
| rejection_reason | text | nullable, WAJIB ketika status `ditolak` (validasi di FormRequest) |
| created_at, updated_at | timestamp | |

Soft delete: ya.

### `material_purchase_items`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_purchase_id | bigint | FK → material_purchases, ON DELETE CASCADE |
| material_id | bigint | FK → materials |
| qty | int | NOT NULL |
| unit_price | numeric(14,2) | NOT NULL |
| subtotal | numeric(14,2) | qty * unit_price |
| created_at, updated_at | timestamp | |

### `material_purchase_evidences`
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_purchase_id | bigint | FK → material_purchases, ON DELETE CASCADE |
| disk | varchar(20) | default `r2` |
| path | varchar(500) | |
| original_name | varchar(255) | |
| mime_type | varchar(100) | |
| size | bigint | |
| uploaded_by | bigint | FK → users |
| created_at, updated_at | timestamp | |

### `material_purchase_approvals`
Audit log alur approval (siapa pernah submit/approve/reject).

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| material_purchase_id | bigint | FK → material_purchases |
| action | varchar(20) | enum [`submitted`,`approved`,`rejected`,`revised`] |
| actor_id | bigint | FK → users |
| note | text | nullable |
| created_at | timestamp | |

## 6. Notifikasi

### `notifications` (Laravel default — `php artisan notifications:table`)
| Kolom | Tipe | Catatan |
|---|---|---|
| id | uuid | PK |
| type | varchar(255) | class notifikasi |
| notifiable_type | varchar(255) | morph |
| notifiable_id | bigint | morph id (user) |
| data | jsonb | payload |
| read_at | timestamp | nullable |
| created_at, updated_at | timestamp | |

Index: `(notifiable_type, notifiable_id, read_at)`.

## 7. Diagram Relasi (ringkas)
```
users ─┬─< orders.created_by
       ├─< orders.qc_approved_by
       ├─< material_purchases.submitted_by / approved_by / rejected_by
       └─< material_stock_movements.user_id

customers 1 ──< vehicles 1 ──< orders 1 ──< order_items
                                 │
                                 ├──< order_evidences
                                 ├──< order_payments
                                 └─── invoices (1:1)

orders >── promo_packages (nullable)
promo_packages 1 ──< promo_package_items >── checklist_items
checklist_categories 1 ──< checklist_items

material_categories 1 ──< materials 1 ── material_stocks (1:1)
                                       └──< material_stock_movements

material_purchases 1 ──< material_purchase_items >── materials
                    ├──< material_purchase_evidences
                    └──< material_purchase_approvals
```

## 8. Aturan Index Penting (Performa)
- `orders.order_date` (B-tree) — dashboard query "hari ini"
- `orders.status` — filter daftar
- `orders.customer_id`, `orders.vehicle_id`
- `vehicles.plate_number` — UNIQUE + index lookup
- `materials.material_category_id`
- `material_stock_movements.material_id, created_at` (composite)
- `material_purchases.status, submitted_at`
- `notifications (notifiable_type, notifiable_id, read_at)`

## 9. Aturan Trigger Stok (lewat Service, BUKAN trigger DB)
- Saat `MaterialPurchase` di-approve → service `StockService::increase($materialId, $qty, $reference)`.
- Saat `Order` di-finalize (status `selesai` atau saat eviden bukti pemasangan diupload, tergantung kebijakan) → service `StockService::decrease($materialId, $qty, $reference)` untuk material yang di-link ke checklist (kalau ada relasi `checklist_items.material_id` ditambahkan kemudian).
- Setiap perubahan stok WAJIB tulis ke `material_stock_movements`.

## 10. Migration Order (urutan dibuat)
1. `0001_create_users_table` (sudah ada dari Laravel)
2. `0002_create_password_reset_tokens_table`
3. `0003_create_sessions_table`
4. `0004_create_permission_tables` (Spatie)
5. `0005_create_checklist_categories_table`
6. `0006_create_checklist_items_table`
7. `0007_create_material_categories_table`
8. `0008_create_materials_table`
9. `0009_create_material_stocks_table`
10. `0010_create_material_stock_movements_table`
11. `0011_create_promo_packages_table`
12. `0012_create_promo_package_items_table`
13. `0013_create_customers_table`
14. `0014_create_vehicles_table`
15. `0015_create_orders_table`
16. `0016_create_order_items_table`
17. `0017_create_order_evidences_table`
18. `0018_create_order_payments_table`
19. `0019_create_invoices_table`
20. `0020_create_material_purchases_table`
21. `0021_create_material_purchase_items_table`
22. `0022_create_material_purchase_evidences_table`
23. `0023_create_material_purchase_approvals_table`
24. `0024_create_notifications_table`
25. `0025_add_jabatan_phone_status_to_users_table` (kalau pakai default users dulu)
