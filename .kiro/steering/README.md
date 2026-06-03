---
inclusion: manual
---

# Steering Files — Garasi Hobby

Dokumen ini menjelaskan steering yang sudah dibuat dan kapan masing-masing aktif.

## Daftar Steering

| File | Inclusion | Tujuan |
|---|---|---|
| `product.md` | always | Konteks produk, aktor, workflow inti, aturan bisnis |
| `tech.md` | always | Tech stack, package, konfigurasi `.env` & R2, konvensi nama |
| `structure.md` | always | Struktur folder Laravel, penempatan kode, routing |
| `database.md` | always | Skema lengkap PostgreSQL, indeks, urutan migration |
| `coding-standards.md` | fileMatch `*.php` | Pola controller, service, model, FormRequest, migration |
| `metronic-ui.md` | fileMatch `*.blade.php` | Mapping halaman Metronic, komponen Blade reusable |
| `roadmap.md` | manual (`#roadmap`) | Roadmap 5-step build dari nol |

## Cara Pakai
- Steering `always` aktif otomatis di setiap percakapan.
- Steering `fileMatch` aktif saat file tipe tersebut dibuka/diedit.
- Steering `manual` panggil dengan `#roadmap` di chat saat butuh.

## Urutan Build (ringkas)
1. **Step 1** — Laravel + PostgreSQL + Login + Spatie Role
2. **Step 2** — Layout Metronic + Login Themed + Sidebar dinamis
3. **Step 3** — Master Data CRUD + Role Management
4. **Step 4** — Operasional (Order, QC, Bayar, Pembelian Material) + Notifikasi
5. **Step 5** — Dashboard (KPI, Grafik, Widget)

Detail tiap step ada di `roadmap.md`.

## Aturan Modifikasi
- Owner/Lead boleh edit `product.md` dan `database.md` saat ada perubahan kebutuhan.
- `metronic-ui.md` dan `coding-standards.md` adalah norma tim, ubah lewat diskusi.
- `roadmap.md` evolves seiring progress, mark checklist saat selesai.
