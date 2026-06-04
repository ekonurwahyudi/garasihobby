@extends('layouts.app')

@section('title', 'Input Order')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('orders.index') }}" class="text-muted text-hover-primary">Order Management</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Input Order</li>
@endsection

@section('content')
{{-- Print header (only visible saat print) --}}
<div id="printInfoBlock">
    {{-- Header Invoice --}}
    <div class="print-invoice-header">
        <div class="print-invoice-title">
            <div class="print-doc-label">INVOICE</div>
            <table class="print-meta-table">
                <tr><td>TikTok</td><td>: @hobby_garage.id</td></tr>
                <tr><td>Telp</td><td>: 0812-6221-0708</td></tr>
                <tr><td>Instagram</td><td>: garasi_hobby.id</td></tr>
                <tr><td>Email</td><td>: garasihobi.id@gmail.com</td></tr>
            </table>
            <table class="print-meta-table print-document-table">
                <tr><td>No. Faktur</td><td>: <span id="print_invoice_no"></span></td></tr>
                <tr><td>Tanggal</td><td>: <span id="print_date"></span></td></tr>
            </table>
        </div>
        <div class="print-brand-block">
            <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby" class="print-brand-logo" />
            <div class="print-brand-subtitle">BENGKEL MOBIL</div>
            <div class="print-brand-name">GARASI HOBBY</div>
        </div>
    </div>

    {{-- Info Pelanggan & Mekanik (background abu) --}}
    <div class="print-customer-panel">
        <table class="print-info-table">
            <tr>
                <td><strong>No. Pelanggan</strong></td>
                <td>: <span id="print_plate"></span></td>
                <td><strong>Jarak Tempuh</strong></td>
                <td>: <span id="print_mileage"></span></td>
                <td><strong>KM Service</strong></td>
                <td>: <span id="print_km_service"></span></td>
            </tr>
            <tr>
                <td><strong>Nama</strong></td>
                <td>: <span id="print_name"></span></td>
                <td><strong>Kepala Mekanik</strong></td>
                <td>: <span id="print_head_mechanic"></span></td>
                <td><strong>KM Kembali</strong></td>
                <td>: <span id="print_km_return"></span></td>
            </tr>
            <tr>
                <td><strong>Telp</strong></td>
                <td>: <span id="print_phone"></span></td>
                <td><strong>Mekanik</strong></td>
                <td>: <span id="print_mechanic"></span></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td><strong>No. Kendaraan</strong></td>
                <td>: <span id="print_vehicle"></span></td>
                <td><strong>No. Mekanik</strong></td>
                <td>: <span id="print_mechanic_no"></span></td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>
</div>

<form id="orderForm">
    {{-- Section Pelanggan --}}
    <div class="card card-flush mb-7">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold">Data Pelanggan & Kendaraan</h3>
        </div>
        <div class="card-body pt-0">
            <div class="row mb-5">
                <div class="col-md-4 fv-row">
                    <label class="required form-label fw-semibold">Tanggal Order</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                    <input type="text" name="order_date" id="order_date" class="form-control" placeholder="Pilih tanggal" value="{{ date('Y-m-d') }}" required />
                    </div>
                </div>
                <div class="col-md-8 fv-row">
                    <label class="required form-label fw-semibold">Plat Mobil <i class="text-danger"> (BL 2345 ABD)</i></label>
                    <select name="plate_search" id="plate_search" class="form-select" data-placeholder="Ketik plat mobil...">
                        <option></option>
                    </select>
                    <div class="form-text text-muted">Ketik plat mobil untuk mencari. Jika tidak ditemukan, pilih "Tambah Baru" untuk input pelanggan baru.</div>
                </div>
            </div>

            {{-- Info pelanggan existing (readonly) --}}
            <div id="customerInfo" class="d-none">
                <div class="separator separator-dashed my-5"></div>
                <div class="row mb-5">
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Nama Pemilik</label>
                        <input type="text" id="c_name" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Kendaraan</label>
                        <input type="text" id="c_vehicle" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">No HP</label>
                        <input type="text" id="c_phone" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="text" id="c_email" class="form-control" readonly />
                    </div>
                </div>
            </div>

            {{-- Notifikasi status pelanggan --}}
            <div id="notifNewCustomer" class="d-none">
                <div class="alert alert-info d-flex align-items-center mt-5 mb-0">
                    <i class="ki-duotone ki-information-5 fs-2 text-info me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div>Pelanggan baru — lengkapi data di bawah.</div>
                </div>
            </div>
            <div id="notifExistingCustomer" class="d-none">
                <div class="alert alert-success d-flex align-items-center justify-content-between mt-5 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-check-circle fs-2 text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                        <div>Pelanggan ditemukan di database.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light-primary" id="btnRiwayatOrder">
                        <i class="ki-duotone ki-time fs-4"><span class="path1"></span><span class="path2"></span></i> Riwayat Order
                    </button>
                </div>
            </div>

            {{-- Form pelanggan baru (langsung tampil semua field) --}}
            <div id="newCustomerForm">
                <div class="separator separator-dashed my-5"></div>
                <div class="row mb-5">
                    <div class="col-md-3 fv-row">
                        <label class="required form-label fw-semibold">Plat Mobil</label>
                        <input type="text" name="new_plate" id="new_plate" class="form-control fw-bold" />
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Merk</label>
                        <select name="new_brand" id="new_brand" class="form-select" data-control="select2" data-placeholder="Pilih Merk">
                            <option></option>
                            <option value="Toyota">Toyota</option>
                            <option value="Honda">Honda</option>
                            <option value="Daihatsu">Daihatsu</option>
                            <option value="Suzuki">Suzuki</option>
                            <option value="Mitsubishi">Mitsubishi</option>
                            <option value="Nissan">Nissan</option>
                            <option value="Hyundai">Hyundai</option>
                            <option value="KIA">KIA</option>
                            <option value="Mazda">Mazda</option>
                            <option value="Wuling">Wuling</option>
                            <option value="Isuzu">Isuzu</option>
                            <option value="BMW">BMW</option>
                            <option value="Mercedes-Benz">Mercedes-Benz</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Jenis Mobil</label>
                        <input type="text" name="new_model" id="new_model" class="form-control" placeholder="Avanza, Jazz, dll" />
                    </div>
                    <div class="col-md-3 fv-row">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" name="new_year" id="new_year" class="form-control" min="1900" max="{{ date('Y')+1 }}" />
                    </div>
                </div>
                <div class="fv-row mb-5">
                    <label class="required form-label fw-semibold">Ukuran Mobil</label>
                    <input type="hidden" name="vehicle_size" id="vehicle_size" value="small" />
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="vehicle-size-option border rounded p-3 d-flex flex-column align-items-center justify-content-center gap-2 h-100 cursor-pointer text-center" for="order_vehicle_size_small">
                                <input type="checkbox" class="form-check-input order-vehicle-size-check" id="order_vehicle_size_small" value="small" checked />
                                <i class="ki-duotone ki-car fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <span>
                                    <span class="fw-bold fs-5 d-block">S</span>
                                    <span class="text-muted fs-8">City Car / Hatchback / Sedan Kecil</span>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="vehicle-size-option border rounded p-3 d-flex flex-column align-items-center justify-content-center gap-2 h-100 cursor-pointer text-center" for="order_vehicle_size_medium">
                                <input type="checkbox" class="form-check-input order-vehicle-size-check" id="order_vehicle_size_medium" value="medium" />
                                <i class="ki-duotone ki-car fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <span>
                                    <span class="fw-bold fs-5 d-block">M</span>
                                    <span class="text-muted fs-8">MPV / SUV Medium / Pickup Ringan</span>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="vehicle-size-option border rounded p-3 d-flex flex-column align-items-center justify-content-center gap-2 h-100 cursor-pointer text-center" for="order_vehicle_size_large">
                                <input type="checkbox" class="form-check-input order-vehicle-size-check" id="order_vehicle_size_large" value="large" />
                                <i class="ki-duotone ki-car fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <span>
                                    <span class="fw-bold fs-5 d-block">L</span>
                                    <span class="text-muted fs-8">SUV Besar / Double Cabin / Ladder Frame</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-4 fv-row">
                        <label class="required form-label fw-semibold">Nama Pemilik</label>
                        <input type="text" name="new_name" id="new_name" class="form-control" />
                    </div>
                    <div class="col-md-4 fv-row">
                        <label class="required form-label fw-semibold">No HP</label>
                        <input type="text" name="new_phone" id="new_phone" class="form-control" />
                    </div>
                    <div class="col-md-4 fv-row">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="new_email" id="new_email" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="separator separator-dashed my-5"></div>
            <div class="row mb-5">
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">Jarak Tempuh</label>
                    <input type="text" name="mileage" id="mileage" class="form-control numeric-separator" placeholder="Contoh: 45.000" inputmode="numeric" autocomplete="off" />
                </div>
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">KM Service</label>
                    <input type="text" name="km_service" id="km_service" class="form-control numeric-separator" placeholder="KM saat masuk" inputmode="numeric" autocomplete="off" />
                </div>
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">KM Kembali</label>
                    <input type="text" name="km_return" id="km_return" class="form-control numeric-separator" placeholder="KM saat keluar" inputmode="numeric" autocomplete="off" />
                </div>
            </div>
            <div class="row mb-5">
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">Kepala Mekanik</label>
                    <select name="head_mechanic" id="head_mechanic" class="form-select" data-placeholder="Pilih Kepala Mekanik">
                        <option value=""></option>
                        @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->name }}" data-phone="{{ $mechanic->phone }}">{{ $mechanic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">Mekanik</label>
                    <select name="mechanic" id="mechanic" class="form-select" data-placeholder="Pilih Mekanik">
                        <option value=""></option>
                        @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->name }}" data-phone="{{ $mechanic->phone }}">{{ $mechanic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 fv-row">
                    <label class="form-label fw-semibold">No. Mekanik</label>
                    <input type="text" name="mechanic_number" id="mechanic_number" class="form-control" readonly />
                </div>
            </div>

            <div class="fv-row">
                <label class="form-label fw-semibold">Keluhan / Catatan</label>
                <textarea name="complaint" id="complaint" class="form-control" rows="3" placeholder="Keluhan pelanggan..."></textarea>
            </div>
        </div>
    </div>

    <input type="hidden" id="customer_id" name="customer_id" value="" />
    <input type="hidden" id="vehicle_id" name="vehicle_id" value="" />

    {{-- Section Item Checklist --}}
    <div class="card card-flush mb-7" id="itemChecklistCard">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold">Item Pengecekan</h3>
            <div class="card-toolbar">
                <span class="fw-bold fs-6 text-primary" id="checklistSubtotalDisplay">Subtotal: Rp 0</span>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table table-bordered gy-4 gs-4" id="checklistTable">
                <thead>
                    <tr class="fw-semibold fs-7 text-gray-800 bg-light">
                        <th class="w-300px ps-4">Nama Item</th>
                        <th class="w-150px text-center">
                            <i class="ki-duotone ki-car fs-4 text-primary me-1"><span class="path1"></span><span class="path2"></span></i> S
                        </th>
                        <th class="w-150px text-center">
                            <i class="ki-duotone ki-car fs-4 text-primary me-1"><span class="path1"></span><span class="path2"></span></i> M
                        </th>
                        <th class="w-150px text-center">
                            <i class="ki-duotone ki-car fs-4 text-primary me-1"><span class="path1"></span><span class="path2"></span></i> L
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checklistCategories as $cat)
                    @continue($cat->items->isEmpty())
                    <tr class="bg-primary">
                        <td class="text-white fw-bold fs-6 ps-4" colspan="4">
                            <i class="ki-duotone ki-car fs-3 text-white me-2"><span class="path1"></span><span class="path2"></span></i>
                            {{ strtoupper($cat->name) }}
                        </td>
                    </tr>
                    @foreach($cat->items as $item)
                    <tr>
                        <td class="ps-4">
                            <label class="form-check form-check-custom form-check-sm">
                                <input type="checkbox" class="form-check-input checklist-check" value="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price }}" data-price-small="{{ $item->price_small }}" data-price-medium="{{ $item->price_medium }}" data-price-large="{{ $item->price_large }}" />
                                <span class="form-check-label fw-semibold">{{ $item->name }}</span>
                            </label>
                            <input type="hidden" class="ci-price" data-id="{{ $item->id }}" value="{{ $item->price_small ?: $item->price }}" />
                        </td>
                        <td class="text-end checklist-price-cell" data-id="{{ $item->id }}" data-size="small">Rp {{ number_format($item->price_small ?: $item->price, 0, ',', '.') }}</td>
                        <td class="text-end checklist-price-cell" data-id="{{ $item->id }}" data-size="medium">Rp {{ number_format($item->price_medium ?: $item->price, 0, ',', '.') }}</td>
                        <td class="text-end checklist-price-cell" data-id="{{ $item->id }}" data-size="large">Rp {{ number_format($item->price_large ?: $item->price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
            <div class="row mt-5 justify-content-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Jasa Lainnya</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="other_service_price_display" class="form-control text-end" placeholder="0" inputmode="numeric" autocomplete="off" />
                    </div>
                    <input type="hidden" name="other_service_price" id="other_service_price" value="0" />
                </div>
            </div>
        </div>
    </div>

    {{-- Section Material --}}
    <div class="card card-flush mb-7" id="materialSection">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold">Material yang Digunakan</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" id="btnAddMaterial">
                    <i class="ki-duotone ki-plus fs-3"></i> Tambah Material
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table table-bordered gy-4 gs-4" id="materialTable">
                <thead>
                    <tr class="fw-semibold fs-7 text-gray-800 bg-light">
                        <th>Nama Material</th>
                        <th class="w-100px">Qty Onhand</th>
                        <th class="w-100px">Qty</th>
                        <th class="w-150px">Harga Satuan</th>
                        <th class="w-150px">Harga Total</th>
                        <th class="w-50px"></th>
                    </tr>
                </thead>
                <tbody id="materialBody">
                </tbody>
                <tfoot>
                    <tr class="fw-bold fs-6">
                        <td colspan="4" class="text-end">Subtotal Material:</td>
                        <td id="subtotalMaterial">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Section Total --}}
    <div class="card card-flush mb-7">
        <div class="card-body">
            <div class="row align-items-start g-5">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Pembayaran Masuk Ke</label>
                    <select id="bank_account_id" class="form-select bank-account-select" data-placeholder="Pilih Bank/Cash">
                        <option value="">-- Pilih Bank/Cash --</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}"
                                data-logo-url="{{ $account->logo_url }}"
                                data-logo-text="{{ $account->logo_text }}"
                                data-bank-name="{{ $account->bank_name }}"
                                data-bank-code="{{ $account->code }}"
                                data-bank-balance="Rp {{ number_format($account->balance, 0, ',', '.') }}">
                                {{ $account->code }} - {{ $account->bank_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Wajib saat status Selesai. Mutasi otomatis tercatat sebagai uang masuk.</div>
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-5">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-semibold text-end">Subtotal:</td>
                            <td class="fw-bold text-end" id="displaySubtotal">Rp 0</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-end">Diskon:</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="discount_display" class="form-control text-end" value="0" inputmode="numeric" autocomplete="off" />
                                </div>
                                <input type="hidden" name="discount" id="discount" value="0" />
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-4 text-end">Total:</td>
                            <td class="fw-bold fs-4 text-end text-primary" id="displayTotal">Rp 0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Section Eviden (2 kolom) --}}
    <div class="card card-flush mb-7" id="evidenCard">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold">Upload Eviden</h3>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-bold fs-6 text-gray-800">Eviden Checklist / Pekerjaan</label>
                    <input type="file" name="evidences_work[]" id="evidenceWorkInput" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple />
                    <div class="form-text">Foto hasil pengecekan / pekerjaan mekanik.</div>
                    <div id="evidenceWorkPreview" class="d-none mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-gray-600 fs-7">Preview</span>
                            <button type="button" class="btn btn-sm btn-light-danger btn-clear-evidence" data-target="work">Hapus Semua</button>
                        </div>
                        <div class="row g-2" id="evidenceWorkList"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold fs-6 text-gray-800">Eviden Pembayaran</label>
                    <input type="file" name="evidences_payment[]" id="evidencePaymentInput" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple />
                    <div class="form-text">Bukti pembayaran / transfer / struk.</div>
                    <div id="evidencePaymentPreview" class="d-none mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-gray-600 fs-7">Preview</span>
                            <button type="button" class="btn btn-sm btn-light-danger btn-clear-evidence" data-target="payment">Hapus Semua</button>
                        </div>
                        <div class="row g-2" id="evidencePaymentList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section Actions --}}
    <div class="card card-flush mb-7">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-end align-items-end gap-3">
                <div class="order-status-control">
                    <label class="form-label fw-semibold">Status Order</label>
                    <select id="order_status" class="form-select">
                        <option value="open" selected>Open</option>
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-light">Kembali</a>
                <button type="button" class="btn btn-secondary" id="btnDraftOrder">
                    <i class="ki-duotone ki-save-2 fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan Draft
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveOrder">
                    <i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan Order
                </button>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="evidencePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Preview Gambar</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body text-center">
                <img src="" id="evidencePreviewModalImage" class="img-fluid rounded" alt="Preview evidence" />
            </div>
        </div>
    </div>
</div>

{{-- Print Total Block (visible only saat print) --}}
<div id="printTotalBlock">
    <table class="print-total-table">
        <tr>
            <td><strong>Subtotal Jasa:</strong></td>
            <td id="print_subtotal_jasa">Rp 0</td>
        </tr>
        <tr>
            <td><strong>Subtotal Material:</strong></td>
            <td id="print_subtotal_material">Rp 0</td>
        </tr>
        <tr>
            <td><strong>Diskon:</strong></td>
            <td id="print_discount">Rp 0</td>
        </tr>
        <tr class="print-grand-total">
            <td><strong>TOTAL:</strong></td>
            <td id="print_total"><strong>Rp 0</strong></td>
        </tr>
    </table>

    {{-- Metode Pembayaran --}}
    <div class="print-payment-block">
        <div class="print-payment-title">Metode Pembayaran</div>
        <div class="print-payment-note">Silakan transfer ke rekening</div>
        <table class="print-payment-table">
            <tr><td><strong>Atas Nama</strong></td><td>: Muhammad Azhari</td></tr>
            <tr><td><strong>Nama Bank</strong></td><td>: Seabank</td></tr>
            <tr><td><strong>No Rek</strong></td><td>: 901932884080</td></tr>
        </table>
    </div>

    {{-- Tanda tangan --}}
    <table class="print-signature-table">
        <tr>
            <td>Pelanggan<br><br><br><br>(_________________)</td>
            <td>Mekanik<br><br><br><br>(_________________)</td>
            <td>CS / Kasir<br><br><br><br>(_________________)</td>
        </tr>
    </table>
</div>

{{-- Modal Pilih Material --}}
<div class="modal fade" id="materialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Pilih Material</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <input type="text" id="materialSearch" class="form-control mb-4" placeholder="Cari material..." />
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-row-bordered gy-3 gs-4">
                        <thead>
                            <tr class="fw-semibold fs-7 text-gray-800">
                                <th></th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $mat)
                            <tr class="material-row">
                                <td>
                                    <button type="button" class="btn btn-sm btn-light-primary btn-select-material"
                                        data-id="{{ $mat->id }}"
                                        data-name="{{ $mat->name }}"
                                        data-stock="{{ $mat->stock_qty }}"
                                        data-price="{{ $mat->price }}">Pilih</button>
                                </td>
                                <td>{{ $mat->name }}</td>
                                <td>{{ $mat->category->name ?? '-' }}</td>
                                <td>{{ $mat->stock_qty }}</td>
                                <td>Rp {{ number_format($mat->price, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Riwayat Order Pelanggan --}}
<div class="modal fade" id="riwayatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Riwayat Order Pelanggan</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <div id="riwayatLoading" class="text-center py-10">
                    <span class="spinner-border text-primary"></span>
                    <div class="mt-3 text-muted">Memuat riwayat...</div>
                </div>
                <div id="riwayatContent" class="d-none">
                    <table class="table table-striped table-row-bordered gy-3 gs-4">
                        <thead>
                            <tr class="fw-semibold fs-7 text-gray-800">
                                <th>No Order</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatBody">
                        </tbody>
                    </table>
                    <div id="riwayatEmpty" class="text-center text-muted py-10 d-none">
                        Belum ada riwayat order untuk pelanggan ini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bankOptionTemplate(option) {
    if (!option.id) return option.text;

    var el = option.element;
    var logoUrl = el.getAttribute('data-logo-url');
    var logoText = el.getAttribute('data-logo-text') || 'BNK';
    var bankName = el.getAttribute('data-bank-name') || option.text;
    var bankCode = el.getAttribute('data-bank-code') || '';
    var balance = el.getAttribute('data-bank-balance') || '';
    var logo = logoUrl
        ? '<img src="' + logoUrl + '" alt="' + bankName.replace(/"/g, '&quot;') + '">'
        : '<span>' + logoText + '</span>';

    return $(
        '<div class="bank-select-option">' +
            '<div class="bank-select-logo">' + logo + '</div>' +
            '<div class="bank-select-text">' +
                '<div class="name">' + bankCode + ' - ' + bankName + '</div>' +
                '<div class="meta">Saldo ' + balance + '</div>' +
            '</div>' +
        '</div>'
    );
}

var selectedCustomerId = null;
var selectedVehicleId = null;
var selectedVehicleSize = 'small';
var isNewCustomer = false;
var materialRowIndex = 0;
var isEditMode = false;
var orderSubmitUrl = @json(route('orders.store'));
var orderSubmitMethod = 'POST';
var initialOrder = null;
var vehicleSizeLabels = {
    small: 'S - City Car / Hatchback / Sedan Kecil',
    medium: 'M - MPV / SUV Medium / Pickup Ringan',
    large: 'L - SUV Besar / Double Cabin / Ladder Frame'
};

function digitsOnly(value) {
    return (value || '').toString().replace(/[^\d]/g, '');
}

function formatDigits(value) {
    var digits = digitsOnly(value);
    return digits ? parseInt(digits, 10).toLocaleString('id-ID') : '';
}

function setVehicleSize(value) {
    selectedVehicleSize = value || 'small';
    $('#vehicle_size').val(selectedVehicleSize);
    document.querySelectorAll('.order-vehicle-size-check').forEach(function(input) {
        var checked = input.value === selectedVehicleSize;
        input.checked = checked;
        input.closest('.vehicle-size-option').classList.toggle('border-primary', checked);
        input.closest('.vehicle-size-option').classList.toggle('bg-light-primary', checked);
    });
    refreshChecklistPrices();
}

function getChecklistPriceForSize(check) {
    var size = selectedVehicleSize || 'small';
    return parseFloat(check.data('price-' + size)) || parseFloat(check.data('price')) || 0;
}

function refreshChecklistPrices() {
    $('.checklist-check').each(function() {
        var check = $(this);
        var price = getChecklistPriceForSize(check);
        $('.ci-price[data-id="' + check.val() + '"]').val(price);
    });
    $('.checklist-price-cell').removeClass('bg-light-primary text-primary fw-bold');
    $('.checklist-price-cell[data-size="' + selectedVehicleSize + '"]').addClass('bg-light-primary text-primary fw-bold');
    recalculate();
}

// Select2 AJAX for plate search
$(document).ready(function() {
    // Init flatpickr for date
    $('#order_date').flatpickr({
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        defaultDate: @json(date('Y-m-d'))
    });

    $('#plate_search').select2({
        ajax: {
            url: '{{ route("orders.search-plate") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) { return { q: params.term }; },
            processResults: function(data, params) {
                var results = data.map(function(v) {
                    return { id: 'existing_' + v.vehicle_id, text: v.plate_number + ' — ' + v.customer_name + ' (' + (v.brand||'') + ' ' + (v.model||'') + ')', data: v };
                });
                // Tambahkan opsi "Tambah Baru" di akhir
                if (params.term && params.term.length >= 2) {
                    results.push({
                        id: 'new_' + params.term.toUpperCase(),
                        text: '➕ Tambah Baru: ' + params.term.toUpperCase(),
                        isNew: true,
                        plateText: params.term.toUpperCase()
                    });
                }
                return { results: results };
            }
        },
        minimumInputLength: 2,
        placeholder: 'Ketik plat mobil...',
        allowClear: true,
        templateResult: function(item) {
            if (item.isNew) {
                return $('<span class="fw-bold text-primary">' + item.text + '</span>');
            }
            return item.text;
        }
    });

    $('#plate_search').on('select2:select', function(e) {
        var selected = e.params.data;

        if (selected.isNew) {
            // Pelanggan baru — isi plat saja, user isi sisanya
            isNewCustomer = true;
            selectedCustomerId = null;
            selectedVehicleId = null;
            $('#customer_id').val('');
            $('#vehicle_id').val('');
            $('#new_plate').val(selected.plateText);
            $('#new_name').val('');
            $('#new_phone').val('');
            $('#new_email').val('');
            $('#new_model').val('');
            $('#new_year').val('');
            $('#new_brand').val(null).trigger('change');
            setVehicleSize('small');
            $('#customerInfo').addClass('d-none');
            $('#notifNewCustomer').removeClass('d-none');
            $('#notifExistingCustomer').addClass('d-none');
        } else {
            // Pelanggan existing — auto-fill semua field
            var d = selected.data;
            isNewCustomer = false;
            selectedCustomerId = d.customer_id;
            selectedVehicleId = d.vehicle_id;
            $('#customer_id').val(d.customer_id);
            $('#vehicle_id').val(d.vehicle_id);
            $('#new_plate').val(d.plate_number);
            $('#new_brand').val(d.brand).trigger('change');
            $('#new_model').val(d.model || '');
            $('#new_year').val(d.year || '');
            $('#new_name').val(d.customer_name);
            $('#new_phone').val(d.customer_phone);
            $('#new_email').val(d.customer_email || '');
            $('#c_name').val(d.customer_name || '-');
            $('#c_vehicle').val((d.brand || '') + ' ' + (d.model || ''));
            $('#c_phone').val(d.customer_phone || '-');
            $('#c_email').val(d.customer_email || '-');
            setVehicleSize(d.vehicle_size || 'small');
            $('#customerInfo').removeClass('d-none');
            $('#notifExistingCustomer').removeClass('d-none');
            $('#notifNewCustomer').addClass('d-none');
        }
    });

    $('#head_mechanic, #mechanic').select2({
        placeholder: 'Pilih mekanik...',
        allowClear: true,
        width: '100%'
    });

    $('.bank-account-select').select2({
        width: '100%',
        templateResult: bankOptionTemplate,
        templateSelection: bankOptionTemplate,
        escapeMarkup: function(markup) { return markup; }
    });

    $('#mechanic').on('change', function() {
        var phone = $(this).find(':selected').data('phone') || '';
        $('#mechanic_number').val(phone);
    });

    $('.numeric-separator').on('input', function() {
        this.value = formatDigits(this.value);
    });

    $('#plate_search').on('select2:clear', function() {
        $('#customerInfo').addClass('d-none');
        $('#notifNewCustomer').addClass('d-none');
        $('#notifExistingCustomer').addClass('d-none');
        selectedCustomerId = null;
        selectedVehicleId = null;
        isNewCustomer = false;
        setVehicleSize('small');
    });

    // Riwayat Order
    $('#btnRiwayatOrder').on('click', function() {
        if (!selectedCustomerId) return;
        $('#riwayatLoading').removeClass('d-none');
        $('#riwayatContent').addClass('d-none');
        new bootstrap.Modal(document.getElementById('riwayatModal')).show();

        fetch('/operasional/orders?customer_id=' + selectedCustomerId + '&format=json', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            $('#riwayatLoading').addClass('d-none');
            $('#riwayatContent').removeClass('d-none');
            var body = $('#riwayatBody');
            body.empty();

            if (data.length === 0) {
                $('#riwayatEmpty').removeClass('d-none');
            } else {
                $('#riwayatEmpty').addClass('d-none');
                data.forEach(function(order) {
                    var statusBadge = '';
                    switch(order.status) {
                        case 'draft': statusBadge = '<span class="badge badge-light">Draft</span>'; break;
                        case 'open': statusBadge = '<span class="badge badge-light-primary">Open</span>'; break;
                        case 'belum_bayar': statusBadge = '<span class="badge badge-light-warning">Belum Bayar</span>'; break;
                        case 'selesai': statusBadge = '<span class="badge badge-light-success">Selesai</span>'; break;
                    }
                    var showButton = '<a href="' + order.show_url + '" target="_blank" rel="noopener" class="btn btn-sm btn-light-primary">' +
                        '<i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Lihat Order</a>';
                    body.append('<tr><td class="fw-bold">' + order.order_number + '</td><td>' + order.order_date + '</td><td>Rp ' + formatNumber(parseFloat(order.total)) + '</td><td>' + statusBadge + '</td><td class="text-end">' + showButton + '</td></tr>');
                });
            }
        });
    });

    initEditOrder();
});

function initEditOrder() {
    if (!isEditMode || !initialOrder) return;

    isNewCustomer = false;
    selectedCustomerId = initialOrder.customer_id;
    selectedVehicleId = initialOrder.vehicle_id;
    $('#customer_id').val(initialOrder.customer_id || '');
    $('#vehicle_id').val(initialOrder.vehicle_id || '');
    $('#new_plate').val(initialOrder.plate_number || '');
    $('#new_brand').val(initialOrder.brand || '').trigger('change');
    $('#new_model').val(initialOrder.model || '');
    $('#new_year').val(initialOrder.year || '');
    $('#new_name').val(initialOrder.customer_name || '');
    $('#new_phone').val(initialOrder.customer_phone || '');
    $('#new_email').val(initialOrder.customer_email || '');
    $('#c_name').val(initialOrder.customer_name || '-');
    $('#c_vehicle').val((initialOrder.brand || '') + ' ' + (initialOrder.model || ''));
    $('#c_phone').val(initialOrder.customer_phone || '-');
    $('#c_email').val(initialOrder.customer_email || '-');
    $('#customerInfo').removeClass('d-none');
    $('#notifExistingCustomer').removeClass('d-none');
    $('#notifNewCustomer').addClass('d-none');

    $('#mileage').val(formatDigits(initialOrder.mileage || ''));
    $('#km_service').val(formatDigits(initialOrder.km_service || ''));
    $('#km_return').val(formatDigits(initialOrder.km_return || ''));
    $('#head_mechanic').val(initialOrder.head_mechanic || null).trigger('change');
    $('#mechanic').val(initialOrder.mechanic || null).trigger('change');
    $('#mechanic_number').val(initialOrder.mechanic_number || '');
    $('#complaint').val(initialOrder.complaint || '');
    $('#order_status').val(initialOrder.status === 'draft' ? 'open' : (initialOrder.status || 'open'));
    $('#discount').val(initialOrder.discount || 0);
    $('#discount_display').val(formatDigits(initialOrder.discount || 0));
    $('#other_service_price').val(initialOrder.other_service_price || 0);
    $('#other_service_price_display').val(formatDigits(initialOrder.other_service_price || 0));

    setVehicleSize(initialOrder.vehicle_size || 'small');

    (initialOrder.items || []).forEach(function(item) {
        var check = $('.checklist-check[value="' + item.checklist_item_id + '"]');
        check.prop('checked', true);
        $('.ci-price[data-id="' + item.checklist_item_id + '"]').val(item.price || 0);
    });

    (initialOrder.materials || []).forEach(function(material) {
        addMaterialLine(material);
    });

    recalculate();
}

// Material modal
$('#btnAddMaterial').on('click', function() {
    new bootstrap.Modal(document.getElementById('materialModal')).show();
});

$('#materialSearch').on('keyup', function() {
    var val = this.value.toLowerCase();
    document.querySelectorAll('.material-row').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});

$(document).on('click', '.btn-select-material', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var stock = $(this).data('stock');
    var price = parseFloat($(this).data('price'));

    // Check if already added
    if ($('#materialBody tr[data-material-id="' + id + '"]').length > 0) {
        Swal.fire('Info', 'Material sudah ditambahkan.', 'info');
        return;
    }

    addMaterialLine({ material_id: id, name: name, stock: stock, qty: 1, price: price });
    bootstrap.Modal.getInstance(document.getElementById('materialModal')).hide();
    recalculate();
});

function addMaterialLine(data) {
    data = data || {};
    materialRowIndex++;
    var id = data.material_id || data.id;
    var name = data.name || '-';
    var stock = data.stock || '-';
    var qty = data.qty || 1;
    var price = parseFloat(data.price || 0);
    var total = qty * price;
    var row = '<tr data-material-id="' + id + '">' +
        '<td>' + name + '<input type="hidden" name="materials_used[' + materialRowIndex + '][material_id]" value="' + id + '"><input type="hidden" name="materials_used[' + materialRowIndex + '][name]" value="' + name + '"></td>' +
        '<td><input type="text" class="form-control form-control-sm" value="' + stock + '" disabled /></td>' +
        '<td><input type="number" class="form-control form-control-sm mat-qty" name="materials_used[' + materialRowIndex + '][qty]" value="' + qty + '" min="1" /></td>' +
        '<td><div class="input-group input-group-sm"><span class="input-group-text">Rp</span><input type="text" class="form-control form-control-sm mat-price-display text-end" value="' + formatDigits(price) + '" inputmode="numeric" autocomplete="off" /></div><input type="hidden" class="mat-price" name="materials_used[' + materialRowIndex + '][price]" value="' + price + '" /></td>' +
        '<td class="mat-total fw-bold">Rp ' + formatNumber(total) + '</td>' +
        '<td><button type="button" class="btn btn-icon btn-sm btn-danger btn-remove-material"><i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button></td>' +
        '</tr>';

    $('#materialBody').append(row);
}

$(document).on('input', '.mat-qty', function() {
    var row = $(this).closest('tr');
    var qty = parseInt(row.find('.mat-qty').val()) || 0;
    var price = parseFloat(row.find('.mat-price').val()) || 0;
    var total = qty * price;
    row.find('.mat-total').text('Rp ' + formatNumber(total));
    recalculate();
});

$(document).on('input', '.mat-price-display', function() {
    var row = $(this).closest('tr');
    var digits = digitsOnly(this.value);
    this.value = formatDigits(digits);
    row.find('.mat-price').val(digits || 0);
    var qty = parseInt(row.find('.mat-qty').val()) || 0;
    var price = parseFloat(row.find('.mat-price').val()) || 0;
    row.find('.mat-total').text('Rp ' + formatNumber(qty * price));
    recalculate();
});

$(document).on('click', '.btn-remove-material', function() {
    $(this).closest('tr').remove();
    recalculate();
});

function recalculate() {
    // Material subtotal
    var materialSubtotal = 0;
    $('#materialBody tr').each(function() {
        var qty = parseInt($(this).find('.mat-qty').val()) || 0;
        var price = parseFloat($(this).find('.mat-price').val()) || 0;
        materialSubtotal += qty * price;
    });

    // Checklist subtotal (hanya yang dicentang)
    var checklistSubtotal = 0;
    $('.checklist-check:checked').each(function() {
        var id = $(this).val();
        var priceStr = $('.ci-price[data-id="' + id + '"]').val() || '0';
        var price = parseInt(priceStr.replace(/[^\d]/g, '')) || 0;
        checklistSubtotal += price;
    });

    var otherServicePrice = parseInt(($('#other_service_price').val() || '0').replace(/[^\d]/g, '')) || 0;
    var subtotal = materialSubtotal + checklistSubtotal + otherServicePrice;
    var discount = parseInt(($('#discount').val() || '0').replace(/[^\d]/g, '')) || 0;
    var total = subtotal - discount;

    $('#subtotalMaterial').text('Rp ' + formatNumber(materialSubtotal));
    $('#checklistSubtotalDisplay').text('Subtotal: Rp ' + formatNumber(checklistSubtotal));
    $('#displaySubtotal').text('Rp ' + formatNumber(subtotal));
    $('#displayTotal').text('Rp ' + formatNumber(total));
}

// Recalculate saat checkbox checklist dicentang/uncentang atau harga diubah
$(document).on('change', '.checklist-check', recalculate);
$(document).on('change', '.order-vehicle-size-check', function() {
    setVehicleSize(this.checked ? this.value : 'small');
    recalculate();
});

$('#other_service_price_display').on('input', function() {
    var val = this.value.replace(/[^\d]/g, '');
    $('#other_service_price').val(val || 0);
    this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    recalculate();
});
$('#discount_display').on('input', function() {
    var val = this.value.replace(/[^\d]/g, '');
    $('#discount').val(val || 0);
    this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    recalculate();
});

function formatNumber(n) {
    return n.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Submit
function submitOrder(status) {
    status = status || $('#order_status').val() || 'open';
    var formData = {
        order_date: $('#order_date').val(),
        customer_id: $('#customer_id').val(),
        vehicle_id: $('#vehicle_id').val(),
        complaint: $('#complaint').val(),
        discount: $('#discount').val() || 0,
        other_service_price: $('#other_service_price').val() || 0,
        status: status,
        items: [],
        materials_used: []
    };

    // Collect checked items
    $('.checklist-check:checked').each(function() {
        var id = $(this).val();
        formData.items.push({
            checklist_item_id: id,
            name: $(this).data('name'),
            price: parseInt(($('.ci-price[data-id="' + id + '"]').val() || '0').replace(/[^\d]/g, '')) || 0
        });
    });

    // Collect materials
    $('#materialBody tr').each(function() {
        var qty = parseInt($(this).find('.mat-qty').val()) || 0;
        var price = parseFloat($(this).find('.mat-price').val()) || 0;
        formData.materials_used.push({
            material_id: $(this).data('material-id'),
            name: $(this).find('input[name$="[name]"]').val(),
            qty: qty,
            price: price
        });
    });

    // Validate
    if (!formData.customer_id && !isNewCustomer) {
        Swal.fire('Error', 'Pilih pelanggan atau input pelanggan baru.', 'error');
        return;
    }

    // Build FormData for file upload support
    var fd = new FormData();
    fd.append('order_date', formData.order_date);
    fd.append('customer_id', formData.customer_id);
    fd.append('vehicle_id', formData.vehicle_id);
    fd.append('new_plate', $('#new_plate').val() || '');
    fd.append('new_brand', $('#new_brand').val() || '');
    fd.append('new_model', $('#new_model').val() || '');
    fd.append('new_year', $('#new_year').val() || '');
    fd.append('new_name', $('#new_name').val() || '');
    fd.append('new_phone', $('#new_phone').val() || '');
    fd.append('new_email', $('#new_email').val() || '');
    fd.append('vehicle_size', $('#vehicle_size').val() || 'small');
    fd.append('complaint', formData.complaint);
    fd.append('mileage', digitsOnly($('#mileage').val()) || '');
    fd.append('km_service', digitsOnly($('#km_service').val()) || '');
    fd.append('km_return', digitsOnly($('#km_return').val()) || '');
    fd.append('head_mechanic', $('#head_mechanic').val() || '');
    fd.append('mechanic', $('#mechanic').val() || '');
    fd.append('mechanic_number', $('#mechanic_number').val() || '');
    fd.append('discount', formData.discount);
    fd.append('other_service_price', formData.other_service_price);
    fd.append('status', formData.status);
    fd.append('bank_account_id', $('#bank_account_id').val() || '');

    formData.items.forEach(function(item, i) {
        Object.keys(item).forEach(function(key) { fd.append('items[' + i + '][' + key + ']', item[key]); });
    });
    formData.materials_used.forEach(function(mat, i) {
        Object.keys(mat).forEach(function(key) { fd.append('materials_used[' + i + '][' + key + ']', mat[key]); });
    });

    // Append eviden files
    evidenceFiles.work.forEach(function(file) { fd.append('evidences_work[]', file); });
    evidenceFiles.payment.forEach(function(file) { fd.append('evidences_payment[]', file); });

    fetch(orderSubmitUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': orderSubmitMethod,
        },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil', 'Order berhasil disimpan.', 'success').then(() => {
                window.location.href = data.redirect || '{{ route("orders.index") }}';
            });
        } else if (data.errors) {
            Swal.fire('Gagal', Object.values(data.errors).flat().join('<br>'), 'error');
        }
    })
    .catch(() => Swal.fire('Error', 'Terjadi kesalahan.', 'error'));
}

$('#btnDraftOrder').on('click', function() { submitOrder('draft'); });
$('#btnSaveOrder').on('click', function() { submitOrder(); });

// Eviden upload preview (2 input: work & payment)
var evidenceFiles = { work: [], payment: [] };

function initEvidenceInput(type) {
    var inputId = type === 'work' ? 'evidenceWorkInput' : 'evidencePaymentInput';
    var previewId = type === 'work' ? 'evidenceWorkPreview' : 'evidencePaymentPreview';
    var listId = type === 'work' ? 'evidenceWorkList' : 'evidencePaymentList';

    $('#' + inputId).on('change', function() {
        evidenceFiles[type] = Array.from(this.files || []);
        renderEvidenceList(type);
    });
}

function renderEvidenceList(type) {
    var listId = type === 'work' ? 'evidenceWorkList' : 'evidencePaymentList';
    var previewId = type === 'work' ? 'evidenceWorkPreview' : 'evidencePaymentPreview';
    var list = document.getElementById(listId);
    list.innerHTML = '';

    if (!evidenceFiles[type].length) {
        document.getElementById(previewId).classList.add('d-none');
        return;
    }

    evidenceFiles[type].forEach(function(file, index) {
        var url = URL.createObjectURL(file);
        var isImage = file.type.indexOf('image/') === 0;
        var thumb = isImage
            ? '<button type="button" class="btn p-0 border-0 evidence-preview-open" data-url="' + url + '" title="Perbesar gambar"><img src="' + url + '" class="rounded" style="width:60px;height:60px;object-fit:cover;" /></button>'
            : '<div class="d-flex align-items-center justify-content-center rounded bg-light" style="width:60px;height:60px;"><i class="ki-duotone ki-document fs-2x text-danger"></i></div>';

        list.innerHTML += '<div class="col-auto"><div class="d-flex align-items-center gap-2 border rounded p-2">' +
            thumb +
            '<div><div class="fw-semibold text-truncate" style="max-width:120px;">' + file.name + '</div>' +
            '<div class="text-muted fs-8">' + (file.size / 1024).toFixed(0) + ' KB</div></div>' +
            '<button type="button" class="btn btn-icon btn-sm btn-light-danger" onclick="removeEvidenceFile(\'' + type + '\',' + index + ')"><i class="ki-duotone ki-cross fs-4"><span class="path1"></span><span class="path2"></span></i></button>' +
            '</div></div>';
    });

    document.getElementById(previewId).classList.remove('d-none');
}

function removeEvidenceFile(type, index) {
    evidenceFiles[type].splice(index, 1);
    var inputId = type === 'work' ? 'evidenceWorkInput' : 'evidencePaymentInput';
    var dt = new DataTransfer();
    evidenceFiles[type].forEach(function(f) { dt.items.add(f); });
    document.getElementById(inputId).files = dt.files;
    renderEvidenceList(type);
}

$('.btn-clear-evidence').on('click', function() {
    var type = $(this).data('target');
    evidenceFiles[type] = [];
    var inputId = type === 'work' ? 'evidenceWorkInput' : 'evidencePaymentInput';
    document.getElementById(inputId).value = '';
    renderEvidenceList(type);
});

$(document).on('click', '.evidence-preview-open', function() {
    document.getElementById('evidencePreviewModalImage').src = this.dataset.url;
    new bootstrap.Modal(document.getElementById('evidencePreviewModal')).show();
});

initEvidenceInput('work');
initEvidenceInput('payment');
setVehicleSize('small');
</script>
@endpush

@push('styles')
<style>
    .bank-select-option{display:flex;align-items:center;gap:10px;min-width:0}
    .bank-select-logo{width:32px;height:32px;border:1px solid #dfe6f2;border-radius:9px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .bank-select-logo img{max-width:26px;max-height:18px;object-fit:contain}
    .bank-select-logo span{font-size:10px;font-weight:800;color:#1d4ed8}
    .bank-select-text{min-width:0;line-height:1.25}
    .bank-select-text .name{font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .bank-select-text .meta{font-size:11px;color:#7e8299;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

    @media print {
    @page { size: A4; margin: 10mm; }

    /* Hide semua UI chrome */
    #kt_app_sidebar, #kt_app_header, #kt_app_toolbar, #kt_app_footer,
    .app-sidebar-toggle, #kt_scrolltop,
    #notifNewCustomer, #notifExistingCustomer, #customerInfo,
    .form-text, .select2-container,
    .btn, .card-toolbar,
    #materialModal, #riwayatModal,
    #evidenCard, .separator { display: none !important; }

    /* Hide section pelanggan form (replaced by printInfoBlock) */
    #orderForm > .card:first-child { display: none !important; }
    /* Hide section total/actions card */
    #orderForm > .card:last-child { display: none !important; }

    /* Full width no margin */
    body, .app-main, .app-wrapper, .app-content, #kt_app_content_container,
    .app-page, #kt_app_root, .app-container {
        margin: 0 !important; padding: 0 !important; max-width: 100% !important; width: 100% !important;
    }
    body { font-family: Arial, sans-serif !important; font-size: 12px !important; color: #000 !important; background: #fff !important; }

    /* Show print blocks */
    #printInfoBlock { display: block !important; }
    #printTotalBlock { display: block !important; }

    .print-invoice-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 24px !important;
        border-bottom: 2px solid #111 !important;
        padding-bottom: 10px !important;
        margin-bottom: 10px !important;
    }
    .print-invoice-title { flex: 1 1 auto !important; }
    .print-doc-label {
        margin: 0 0 6px !important;
        font-size: 27px !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        letter-spacing: 0 !important;
    }
    .print-brand-block {
        flex: 0 0 190px !important;
        text-align: right !important;
    }
    .print-brand-logo {
        display: inline-block !important;
        width: 150px !important;
        height: auto !important;
        max-height: 95px !important;
        object-fit: contain !important;
        margin-bottom: 3px !important;
    }
    .print-brand-subtitle,
    .print-brand-name {
        font-size: 14px !important;
        font-weight: 900 !important;
        line-height: 1.15 !important;
    }
    .print-brand-name { font-size: 16px !important; }

    .print-customer-panel {
        background: #eeeeee !important;
        border: 1px solid #bdbdbd !important;
        border-radius: 0 !important;
        padding: 8px 10px !important;
        margin-bottom: 10px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Card invisible wrapper */
    .card { border: none !important; box-shadow: none !important; margin-bottom: 8px !important; page-break-inside: avoid; }
    .card-header { display: none !important; }
    .card-body { padding: 0 !important; }

    /* Tables clean with solid borders */
    table { width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; margin-bottom: 10px !important; }
    th, td { border: 1px solid #000 !important; padding: 3px 5px !important; vertical-align: middle !important; }
    th { background: #e8e8e8 !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    .print-meta-table,
    .print-info-table,
    .print-total-table,
    .print-payment-table,
    .print-signature-table {
        border: none !important;
        margin-bottom: 0 !important;
    }
    .print-meta-table td,
    .print-info-table td,
    .print-total-table td,
    .print-payment-table td,
    .print-signature-table td {
        border: none !important;
    }
    .print-meta-table {
        width: auto !important;
        font-size: 11px !important;
        margin-top: 4px !important;
    }
    .print-meta-table td {
        padding: 1px 0 !important;
        line-height: 1.35 !important;
    }
    .print-meta-table td:first-child { width: 72px !important; }
    .print-document-table {
        margin-top: 7px !important;
        font-weight: 700 !important;
    }
    .print-info-table {
        font-size: 11px !important;
        table-layout: fixed !important;
    }
    .print-info-table td {
        padding: 2px 4px !important;
        line-height: 1.35 !important;
    }
    .print-info-table td:nth-child(1) { width: 105px !important; }
    .print-info-table td:nth-child(2) { width: 22% !important; }
    .print-info-table td:nth-child(3) { width: 112px !important; }
    .print-info-table td:nth-child(5) { width: 84px !important; }

    /* Kolom harga kecilkan */
    #checklistTable th:last-child, #checklistTable td:last-child { width: 50px !important; white-space: nowrap !important; font-size: 8px !important; }
    /* Kolom nama item lebih lebar no wrap */
    #checklistTable th:first-child, #checklistTable td:first-child { width: 35% !important; white-space: nowrap !important; }
    /* Kategori header tetap kiri */
    .bg-primary td { text-align: left !important; }
    /* Hide placeholder dash di print */
    input[placeholder="-"]::placeholder { color: transparent !important; }
    input[placeholder="-"] { color: transparent !important; }

    /* Category header */
    .bg-primary, .bg-primary td { background: #1565c0 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Input renders as plain text */
    .form-control, .form-select, input, textarea, select {
        border: none !important; background: transparent !important; box-shadow: none !important;
        padding: 0 !important; margin: 0 !important; height: auto !important;
        font-size: 11px !important; display: inline !important; width: auto !important;
    }
    .input-group { display: inline !important; border: none !important; }
    .input-group-text { border: none !important; background: transparent !important; padding: 0 2px 0 0 !important; font-size: 11px !important; display: inline !important; }
    input[disabled], input[readonly] { opacity: 1 !important; -webkit-text-fill-color: #000 !important; color: #000 !important; }

    /* Checkbox */
    .form-check-input { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; width: 11px !important; height: 11px !important; }
    .form-check { display: inline-flex !important; align-items: center !important; gap: 3px !important; }

    /* Hide material delete column */
    #materialTable th:last-child, #materialTable td:last-child { display: none !important; }

    /* Print total block styling */
    .print-total-table {
        width: 48% !important;
        margin: 8px 0 0 auto !important;
        font-size: 12px !important;
    }
    .print-total-table td {
        padding: 3px 8px !important;
        text-align: right !important;
    }
    .print-total-table td:first-child { width: 60% !important; }
    .print-grand-total td {
        border-top: 2px solid #000 !important;
        font-size: 14px !important;
        padding-top: 5px !important;
    }
    .print-payment-block {
        margin-top: 16px !important;
        border-top: 2px solid #000 !important;
        padding-top: 10px !important;
    }
    .print-payment-title {
        font-size: 13px !important;
        font-weight: 700 !important;
        margin-bottom: 2px !important;
    }
    .print-payment-note {
        color: #555 !important;
        font-size: 11px !important;
        margin-bottom: 6px !important;
    }
    .print-payment-table {
        width: 45% !important;
        font-size: 12px !important;
    }
    .print-payment-table td {
        padding: 2px 0 !important;
    }
    .print-payment-table td:first-child { width: 90px !important; }
    .print-signature-table {
        width: 100% !important;
        font-size: 11px !important;
        margin-top: 24px !important;
    }
    .print-signature-table td {
        width: 33.333% !important;
        text-align: center !important;
        padding: 0 !important;
    }
}

#printInfoBlock { display: none; }
#printTotalBlock { display: none; }

.vehicle-size-option {
    transition: border-color .15s ease, background-color .15s ease;
    min-height: 118px;
}

.checklist-price-cell {
    white-space: nowrap;
    transition: background-color .15s ease, color .15s ease;
}

.order-status-control {
    width: 220px;
}

.evidence-preview-open {
    line-height: 0;
}

@media (max-width: 767.98px) {
    .order-status-control {
        width: 100%;
    }
}
</style>
@endpush
