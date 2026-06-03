# Sistem Manajemen Bengkel

#Teknologi
- Laravel
- Spatie untuk management role
- postgres

#storage
- Cloudflare R2
- s3 Api: https://29f7a833a94e23dfd222a11aab7d504a.r2.cloudflarestorage.com/garasihobby
- Public Development URL: https://pub-aa3f88e3f2c043e19bd8a8f7f294cc7a.r2.dev
- cdn.garasihobby.com

WORKFLOW ORDER

1. Customer datang
2. CS input order
3. Pilih paket / checklist
4. Mekanik kerjakan
5. QC approval
6. Pembayaran
7. Upload eviden
8. Invoice
9. Selesai

## Master Data

### Management User
- Nama (required, string, max:255)
- Jabatan (required, string)
- Role (required)
- No HP (required, string, regex phone)
- Email (required, email, unique)
- Password (required pada create, optional pada edit, min:8)
- Status (required, select: Aktif/Block)

### Item Checklist
- Nama Item Checklist
- Kategori Checklist
- Harga Checklist

### Katalog Material
- Nama Material
- Kategori Material
- Harga Material

### Paket Building (Promo)
- Nama Promo
- Item Checklist (json dari Item Checklist)
- Harga Promo

### Kategori Material
- Nama Kategori
  - Oli & Fluida
  - Filter
  - Mesin
  - Kaki-kaki
  - Sistem Rem
  - Kelistrikan
  - Chemical
  - Fast Moving Part
- Kode Kategori

### Kategori Item Checklist
- Nama Kategori
  - Braken System
  - As Roda
  - Stabilizer
  - Suspension
  - Steering
  - Bushing
  - Engine Mounting
  - Ban
  - Indikator Speedometer
- Kode Kategori

## Login
- Login Email & Password
- Remember Me
- Logout
- Session Management
- Middleware Authentication
- Role Based Access

## Dashboard
- Total Order Hari Ini
- Order Open
- Order Selesai
- Order Belum Bayar
- Revenue Harian
- Revenue Bulanan
- Total Pelanggan
- Material Hampir Habis
- Approval Pending
- Grafik Revenue
- Grafik Order Bulanan
- Grafik Penggunaan Material

## Operasional

### Data Pelanggan
- Plat Mobil
- Nama Pemilik
- Jenis Mobil
- Tahun Mobil
- No. HP
- Email

### Input Order
- Tanggal Order
- Plat Mobil (pilih atau input baru)
- Nama Pemilik (otomatis jika sudah ada)
- Jenis Mobil
- Tahun Mobil
- No. HP
- Email
- Keluhan / Catatan
- Paket Building (Promo)
  - Jika Ya: item checklist otomatis terisi
  - Harga menggunakan Harga Promo
- Item Checklist (dikelompokkan per kategori)
  - Nama Item
  - Kondisi Awal
  - Tindakan Selanjutnya
  - QC Pemasangan
  - Harga (otomatis, bisa diedit)
- Jumlah
- Diskon
- Total Harga
- Eviden (jpg, jpeg, pdf, png) + preview + hapus
- Status (Open, Belum Bayar, Sudah Bayar)
- Print PDF Template

### History Order
- Riwayat seluruh order

### Pembelian Material
- Tanggal Pembelian
- Eviden (jpg, jpeg, pdf, png) + preview + hapus
- Nama Material
- Qty Material
- Harga Satuan
- Harga Total
- Catatan
- Status Pengajuan
  - Draft
  - Menunggu Approval
  - Disetujui
  - Ditolak

#### Alur Approval Pembelian Material
- CS/Operator mengajukan pembelian → status: Menunggu Approval
- Superadmin menerima notifikasi pengajuan baru
- Superadmin review detail pembelian
- Jika Disetujui
  - Status berubah menjadi Disetujui
  - Notifikasi dikirim ke pengaju: Pembelian disetujui
  - Stok material otomatis bertambah
- Jika Ditolak
  - Status berubah menjadi Ditolak
  - Wajib isi Alasan Penolakan (required, textarea)
  - Notifikasi dikirim ke pengaju: Pembelian ditolak + alasan
- Pengaju bisa revisi & ajukan ulang jika ditolak

#### Alasan Penolakan
- Input Alasan (required, textarea)
- Ditampilkan di detail pembelian
- Ditampilkan di notifikasi pengaju
- Tersimpan di riwayat approval

## Notifikasi

### Notifikasi Pembelian Material
- Pengajuan Baru (ke Superadmin)
  - Judul: Ada pengajuan pembelian baru
  - Isi: Nama pengaju, total harga, tanggal
  - Aksi: Tombol langsung ke halaman approval
- Disetujui (ke Pengaju)
  - Judul: Pembelian material disetujui
  - Isi: Detail pembelian yang disetujui
- Ditolak (ke Pengaju)
  - Judul: Pembelian material ditolak
  - Isi: Detail pembelian + alasan penolakan

### Notifikasi Stok Material
- Material Hampir Habis (ke Superadmin & CS)
  - Trigger: Qty <= Stok Minimum
  - Isi: Nama material + sisa stok
- Material Habis (ke Superadmin & CS)
  - Trigger: Qty = 0
  - Isi: Nama material

### Notifikasi Order
- Order Baru Masuk (ke CS)
- Order Belum Bayar (ke CS)
- Order Selesai (ke Superadmin)

### Pengaturan Notifikasi
- Channel: In-App (Bell Icon + Badge)
- Halaman Daftar Notifikasi
  - Tandai sudah dibaca
  - Tandai semua sudah dibaca
  - Hapus notifikasi
- Badge counter notifikasi belum dibaca

### Persediaan Material
- Nama Material
- Qty Material
- Stok Minimum
- Binrow
- Status Aman
- Status Hampir Habis
- Status Habis

## Role Management
- List Semua Roles
  - Superadmin
  - Customer Service
- Create Role Baru
- Edit Role
  - Checklist Permissions
  - Grouped by Module
- Assign Role ke User
- UI Tabel + Modal Form
  - Checkbox Permissions per Group
