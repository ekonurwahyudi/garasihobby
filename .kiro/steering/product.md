---
inclusion: always
---

# Garasi Hobby — Sistem Manajemen Bengkel

## Ringkasan Produk
Aplikasi web internal bengkel untuk mengelola alur servis kendaraan dari kedatangan customer sampai invoice. Bukan aplikasi publik — pengguna adalah staff bengkel (Superadmin, Customer Service, Mekanik, QC, Kasir).

## Tujuan
- Mempercepat input order oleh CS dengan paket/checklist yang sudah terstandarisasi.
- Menjamin kontrol kualitas via QC approval sebelum kendaraan keluar.
- Menjaga akurasi stok material via alur pembelian dengan approval.
- Memberi visibilitas ke owner via dashboard revenue, order, dan stok.

## Workflow Inti (Wajib Diingat)
Urutan ini bersifat baku dan jadi landasan setiap fitur:

1. Customer datang
2. CS input order (pelanggan + paket/checklist)
3. Pilih paket / checklist
4. Mekanik kerjakan
5. QC approval per item checklist
6. Pembayaran
7. Upload eviden (foto/struk)
8. Generate invoice (PDF)
9. Order selesai

Setiap modul harus konsisten dengan urutan ini. Misalnya status order tidak boleh `Sudah Bayar` jika QC belum approve.

## Aktor & Peran
- **Superadmin** — full access, approval pembelian material, review revenue, kelola user/role.
- **Customer Service (CS)** — input pelanggan, input order, ajukan pembelian material, kelola pembayaran.
- **Mekanik** — lihat order assigned, update progress checklist.
- **QC** — approve/reject hasil pemasangan per item checklist.
- **Kasir** (opsional, bisa di-cover CS) — proses pembayaran, cetak invoice.

Permission digarap pakai Spatie Laravel-Permission, jangan hardcode role di controller.

## Modul Utama
- **Master Data**: User, Item Checklist, Kategori Item Checklist, Katalog Material, Kategori Material, Paket Building (Promo).
- **Operasional**: Data Pelanggan, Input Order, History Order, Pembelian Material (dengan approval), Persediaan Material.
- **Notifikasi**: In-app (bell icon + badge) untuk pembelian material, stok kritis, dan order.
- **Role Management**: list role, create/edit role, assign permission per modul.
- **Dashboard**: KPI harian, grafik revenue, grafik order, material kritis, approval pending.

## Aturan Bisnis Penting
- **Plat mobil** adalah identitas pelanggan. Saat input order, jika plat sudah ada, data pemilik auto-fill.
- **Paket Promo** mengoverride harga item checklist — gunakan `harga_promo`, bukan jumlahkan harga item.
- **Stok material** otomatis bertambah saat pembelian disetujui, otomatis berkurang saat dipakai di order.
- **Pembelian ditolak** wajib isi `alasan_penolakan` (textarea, required).
- **Notifikasi stok**: trigger saat `qty <= stok_minimum` (Hampir Habis) dan `qty = 0` (Habis).
- **Eviden** wajib untuk pembayaran dan pembelian material. Format: jpg, jpeg, png, pdf. Disimpan di Cloudflare R2.
- **Status Order**: `Open`, `Belum Bayar`, `Sudah Bayar`. Hanya boleh `Sudah Bayar` setelah QC approve seluruh item.
- **Status Pembelian**: `Draft`, `Menunggu Approval`, `Disetujui`, `Ditolak`. Pengaju bisa revisi & resubmit jika ditolak.

## Bahasa
Semua label UI, pesan validasi, dan notifikasi dalam **Bahasa Indonesia**. Nama kolom database & kode dalam Bahasa Inggris (snake_case) untuk konsistensi Laravel.
