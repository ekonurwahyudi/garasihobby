---
inclusion: fileMatch
fileMatchPattern: '*.blade.php'
---

# Panduan UI/UX Metronic — Garasi Hobby

Saat membuat atau mengedit file Blade, ikuti pola Metronic berikut. Master template ada di `d:\garasihobby\metronic\` (read-only).

## Layout Mapping
| Halaman | Referensi Metronic |
|---|---|
| Layout utama (sidebar + header) | `metronic/layouts/light-sidebar.html` |
| Login | `metronic/authentication/layouts/auth-1.html` + `metronic/authentication/general/sign-in.html` |
| Dashboard | `metronic/dashboards/ecommerce.html` (KPI cards + charts) atau `dashboards/projects.html` |
| List/Tabel master | `metronic/apps/customers/listing.html` |
| Form create/edit | `metronic/apps/customers/details.html` atau `metronic/apps/ecommerce/customers/details.html` |
| Detail order | `metronic/apps/invoices/view/invoice-1.html` (untuk invoice) + `metronic/apps/projects/project.html` |
| Approval card | `metronic/apps/support-center/tickets/view.html` |
| Notifikasi list | `metronic/apps/inbox/listing.html` |
| Profile | `metronic/account/overview.html` |
| Role list | `metronic/apps/user-management/roles/list.html` |
| Role form | `metronic/apps/user-management/roles/view.html` |
| User list | `metronic/apps/user-management/users/list.html` |
| File manager (eviden) | `metronic/apps/file-manager/files.html` |

## Komponen yang Sudah Tersedia di Metronic
- **DataTable**: pakai `KTDatatable` JS atau library `datatables.net` yang sudah include.
- **Modal**: class `modal fade`, dipanggil pakai `data-bs-toggle="modal"`.
- **Form**: class `form-control form-control-solid`, label `form-label fw-semibold`.
- **Select2**: `<select class="form-select" data-control="select2">`.
- **Flatpickr**: `<input class="form-control" data-flatpickr>`.
- **ApexCharts**: container `<div id="kt_chart_xxx"></div>`, init di JS halaman.
- **Sweetalert2**: untuk konfirmasi delete (sudah bundled).
- **File Upload Preview**: pakai `metronic/account/settings.html` pattern atau Dropzone.
- **Toast**: `toastr` atau `Swal.fire({toast:true})`.
- **Badge**: `badge badge-light-success`, `badge-light-warning`, `badge-light-danger`.

## Pola Blade Component Reusable
Buat di `resources/views/components/`:

### `<x-kt-card>`
```blade
@props(['title' => null, 'toolbar' => null])
<div class="card">
    @if($title)
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
        @if($toolbar)<div class="card-toolbar">{{ $toolbar }}</div>@endif
    </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
```

### `<x-kt-status-badge>`
```blade
@props(['status'])
@php
$map = [
    'open' => ['light-primary', 'Open'],
    'belum_bayar' => ['light-warning', 'Belum Bayar'],
    'sudah_bayar' => ['light-success', 'Sudah Bayar'],
    'selesai' => ['light-info', 'Selesai'],
    'batal' => ['light-danger', 'Batal'],
    'draft' => ['light', 'Draft'],
    'menunggu_approval' => ['light-warning', 'Menunggu Approval'],
    'disetujui' => ['light-success', 'Disetujui'],
    'ditolak' => ['light-danger', 'Ditolak'],
    'aktif' => ['light-success', 'Aktif'],
    'block' => ['light-danger', 'Block'],
];
[$class, $label] = $map[$status] ?? ['light', ucfirst($status)];
@endphp
<span class="badge badge-{{ $class }}">{{ $label }}</span>
```

### `<x-kt-stock-status>`
```blade
@props(['qty', 'minStock'])
@if($qty == 0)
    <span class="badge badge-light-danger">Habis</span>
@elseif($qty <= $minStock)
    <span class="badge badge-light-warning">Hampir Habis</span>
@else
    <span class="badge badge-light-success">Aman</span>
@endif
```

## Pola Halaman Index (List + DataTable)
Struktur konsisten:
```blade
@extends('layouts.app')
@section('title', 'Daftar Pelanggan')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3">
        <div class="app-container container-xxl d-flex flex-stack">
            <div class="page-title">
                <h1 class="page-heading">Daftar Pelanggan</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
                    Tambah Pelanggan
                </a>
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">
            <x-kt-card>
                {{-- Filter row --}}
                <div class="row g-3 mb-5">...</div>
                {{-- Table --}}
                <table id="kt-table-customers" class="table align-middle table-row-dashed fs-6 gy-5">
                    ...
                </table>
            </x-kt-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    $('#kt-table-customers').DataTable({
        // konfigurasi
    });
});
</script>
@endpush
```

## Pola Form
- Wrap input di `<div class="fv-row mb-7">`.
- Label: `<label class="required form-label fw-semibold">Label</label>` (tambahkan `required` kalau wajib).
- Input: `<input class="form-control form-control-solid" />`.
- Helper text: `<div class="form-text">Catatan...</div>`.
- Error: gunakan `@error('field') <div class="text-danger small">{{ $message }}</div> @enderror`.

## Bahasa & Tone
- Tombol primer: "Simpan", "Tambah", "Update", "Hapus", "Batal", "Kembali".
- Konfirmasi delete: "Yakin ingin menghapus data ini? Aksi tidak bisa dibatalkan." (SweetAlert2).
- Empty state: "Belum ada data" + tombol tambah.
- Toast sukses: "Data berhasil disimpan."
- Toast error: "Terjadi kesalahan. Coba lagi."

## Responsive
Metronic responsive by default (Bootstrap 5). Hindari custom CSS yang melawan grid Bootstrap. Test di breakpoint:
- Mobile: < 768px (sidebar collapse jadi drawer)
- Tablet: 768-1199px
- Desktop: ≥ 1200px

## JavaScript Convention
- Halaman-specific JS di `resources/js/pages/{module}/{action}.js`.
- Load via `@push('scripts')` + `@vite('resources/js/pages/orders/create.js')`.
- Hindari inline script besar di Blade.
- Pakai `KTUtil`, `KTApp` dari Metronic kalau perlu helper.
