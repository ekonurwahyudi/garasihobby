<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f4f6f8;
            color: #101828;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 24px;
            background: #fff;
            border-bottom: 1px solid #e4e7ec;
        }
        .btn {
            border: 0;
            border-radius: 6px;
            padding: 9px 14px;
            color: #fff;
            background: #1570ef;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-light { color: #344054; background: #eef2f6; }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 18mm;
            background: #fff;
            box-shadow: 0 10px 30px rgba(16, 24, 40, .12);
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 2px solid #101828;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .brand {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .brand img {
            width: 74px;
            height: 74px;
            object-fit: contain;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .brand-subtitle {
            color: #475467;
            font-size: 12px;
            margin-top: 3px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }
        .meta, .info-table, .items-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            padding: 3px 0 3px 14px;
            white-space: nowrap;
        }
        .section {
            margin-top: 16px;
        }
        .section-title {
            margin-bottom: 8px;
            font-weight: 800;
            color: #1570ef;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .panel {
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            padding: 12px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 120px;
            color: #667085;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #d0d5dd;
            padding: 8px;
            vertical-align: top;
        }
        .items-table th {
            background: #f2f4f7;
            text-align: left;
            font-size: 12px;
        }
        .text-end { text-align: right; }
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }
        .totals-table {
            width: 330px;
        }
        .totals-table td {
            padding: 7px 0;
            border-bottom: 1px solid #eaecf0;
        }
        .totals-table td:last-child {
            text-align: right;
            font-weight: 700;
        }
        .grand td {
            border-bottom: 0;
            padding-top: 12px;
            font-size: 17px;
            font-weight: 800;
            color: #1570ef;
        }
        .payment {
            margin-top: 20px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            padding: 12px;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 34px;
            text-align: center;
        }
        .signature-line {
            padding-top: 56px;
            border-bottom: 1px solid #101828;
            margin: 0 22px 6px;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 10mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
@php
    $checklistTotal = $order->items->sum('price');
    $materialTotal = $order->materials->sum('subtotal');
    $vehicleSizeMap = [
        'small' => 'S - City Car / Hatchback / Sedan Kecil',
        'medium' => 'M - MPV / SUV Medium / Pickup Ringan',
        'large' => 'L - SUV Besar / Double Cabin / Ladder Frame',
    ];
@endphp

<div class="toolbar">
    <a href="{{ route('orders.show', $order) }}" class="btn btn-light">Kembali</a>
    <button type="button" class="btn" onclick="window.print()">Download PDF</button>
</div>

<main class="sheet">
    <div class="header">
        <div class="brand">
            <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby">
            <div>
                <div class="brand-title">GARASI HOBBY</div>
                <div class="brand-subtitle">Bengkel Mobil</div>
                <div class="brand-subtitle">Telp: 0812-6221-0708</div>
                <div class="brand-subtitle">Instagram: garasi_hobby.id</div>
            </div>
        </div>
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <table class="meta">
                <tr><td>No Faktur</td><td>: {{ $order->order_number }}</td></tr>
                <tr><td>Tanggal</td><td>: {{ $order->order_date->format('d/m/Y') }}</td></tr>
                <tr><td>Status</td><td>: Selesai</td></tr>
            </table>
        </div>
    </div>

    <section class="section info-grid">
        <div class="panel">
            <div class="section-title">Pelanggan</div>
            <table class="info-table">
                <tr><td>Nama</td><td>{{ $order->customer->name ?? '-' }}</td></tr>
                <tr><td>Telp</td><td>{{ $order->customer->phone ?? '-' }}</td></tr>
                <tr><td>Email</td><td>{{ $order->customer->email ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="panel">
            <div class="section-title">Kendaraan</div>
            <table class="info-table">
                <tr><td>Plat</td><td>{{ $order->vehicle->plate_number ?? '-' }}</td></tr>
                <tr><td>Mobil</td><td>{{ $order->vehicle->brand ?? '' }} {{ $order->vehicle->model ?? '' }} {{ $order->vehicle->year ?? '' }}</td></tr>
                <tr><td>Ukuran</td><td>{{ $vehicleSizeMap[$order->vehicle->vehicle_size ?? ''] ?? '-' }}</td></tr>
            </table>
        </div>
    </section>

    <section class="section info-grid">
        <div class="panel">
            <div class="section-title">Mekanik</div>
            <table class="info-table">
                <tr><td>Kepala</td><td>{{ $order->head_mechanic ?? '-' }}</td></tr>
                <tr><td>Mekanik</td><td>{{ $order->mechanic ?? '-' }}</td></tr>
                <tr><td>No Mekanik</td><td>{{ $order->mechanic_number ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="panel">
            <div class="section-title">Kilometer</div>
            <table class="info-table">
                <tr><td>Jarak Tempuh</td><td>{{ $order->mileage ? number_format((int) $order->mileage, 0, ',', '.') : '-' }}</td></tr>
                <tr><td>KM Service</td><td>{{ $order->km_service ? number_format((int) $order->km_service, 0, ',', '.') : '-' }}</td></tr>
                <tr><td>KM Kembali</td><td>{{ $order->km_return ? number_format((int) $order->km_return, 0, ',', '.') : '-' }}</td></tr>
            </table>
        </div>
    </section>

    @if($order->items->count())
    <section class="section">
        <div class="section-title">Jasa / Item Pengecekan</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 32px;">No</th>
                    <th style="width: 170px;">Kategori</th>
                    <th>Item</th>
                    <th style="width: 130px;" class="text-end">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->checklistItem?->category?->name ?? '-' }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endif

    @if($order->materials->count())
    <section class="section">
        <div class="section-title">Material</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 130px;" class="text-end">Harga</th>
                    <th style="width: 130px;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->materials as $mat)
                <tr>
                    <td>{{ $mat->name }}</td>
                    <td>{{ $mat->qty }}</td>
                    <td class="text-end">Rp {{ number_format($mat->price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($mat->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endif

    <div class="totals-wrap">
        <table class="totals-table">
            <tr><td>Subtotal Checklist</td><td>Rp {{ number_format($checklistTotal, 0, ',', '.') }}</td></tr>
            <tr><td>Subtotal Material</td><td>Rp {{ number_format($materialTotal, 0, ',', '.') }}</td></tr>
            <tr><td>Jasa Lainnya</td><td>Rp {{ number_format($order->other_service_price ?? 0, 0, ',', '.') }}</td></tr>
            <tr><td>Subtotal Semua</td><td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon</td><td>Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
            <tr class="grand"><td>Total</td><td>Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="payment">
        <div class="section-title">Metode Pembayaran</div>
        <table class="info-table">
            <tr><td>Atas Nama</td><td>Muhammad Azhari</td></tr>
            <tr><td>Nama Bank</td><td>Seabank</td></tr>
            <tr><td>No Rek</td><td>901932884080</td></tr>
        </table>
    </div>

    <div class="signatures">
        <div><div>Pelanggan</div><div class="signature-line"></div><div>{{ $order->customer->name ?? '' }}</div></div>
        <div><div>Mekanik</div><div class="signature-line"></div><div>{{ $order->mechanic ?? '' }}</div></div>
        <div><div>CS / Kasir</div><div class="signature-line"></div><div>{{ $order->creator->name ?? '' }}</div></div>
    </div>
</main>
</body>
</html>
