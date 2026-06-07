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
            background: #eef2f7;
            color: #0f172a;
            font-family: "Inter", Arial, Helvetica, sans-serif;
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
            border-radius: 10px;
            padding: 9px 14px;
            color: #fff;
            background: #1b84ff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn svg {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
        }
        .btn-light { color: #344054; background: #eef2f6; }
        .btn-whatsapp { background: #16a34a; }
        .btn-whatsapp:hover { background: #15803d; }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 18mm;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .14);
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .header::after {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            right: -56px;
            top: -56px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .brand {
            display: flex;
            gap: 14px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .brand img {
            width: 74px;
            height: 74px;
            object-fit: contain;
            background: #fff;
            border-radius: 16px;
            padding: 8px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .brand-subtitle {
            color: #cbd5e1;
            font-size: 12px;
            margin-top: 3px;
        }
        .invoice-title {
            text-align: right;
            position: relative;
            z-index: 1;
        }
        .invoice-title h1 {
            margin: 0 0 8px;
            font-size: 32px;
            letter-spacing: 1px;
        }
        .status-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 6px 12px;
            margin-top: 8px;
            background: rgba(16, 185, 129, .16);
            color: #bbf7d0;
            font-weight: 800;
            font-size: 11px;
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
            color: #1b84ff;
            letter-spacing: .2px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .panel {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            background: #f8fafc;
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
            border-bottom: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }
        .items-table th {
            background: #f1f5f9;
            text-align: left;
            font-size: 12px;
            color: #334155;
        }
        .items-table {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }
        .text-end { text-align: right; }
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }
        .totals-table {
            width: 330px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            background: #f8fafc;
        }
        .totals-table td {
            padding: 9px 12px;
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
            color: #1b84ff;
            background: #eff6ff;
        }
        .payment {
            margin-top: 20px;
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 14px;
            background: linear-gradient(135deg, #eff6ff, #ffffff);
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
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
@php
    $checklistTotal = $order->items->sum('price');
    $materialTotal = $order->materials->sum('subtotal');
    $promoDescription = $order->promo_package_description ?: $order->promoPackage?->description;
    $hasPromoPackage = $order->promo_package_name || (float) ($order->promo_package_price ?? 0) > 0;
    $customerPhone = preg_replace('/\D+/', '', $order->customer->phone ?? '');
    if (str_starts_with($customerPhone, '0')) {
        $customerPhone = '62' . substr($customerPhone, 1);
    } elseif ($customerPhone && str_starts_with($customerPhone, '8')) {
        $customerPhone = '62' . $customerPhone;
    }
    $invoiceUrl = $invoiceShareUrl ?? route('orders.invoice', $order);
    $whatsappMessage = trim("Halo " . ($order->customer->name ?? 'Pelanggan') . ",\n\nBerikut invoice order " . $order->order_number . " dari Garasi Hobby.\nTotal invoice: Rp " . number_format($order->total, 0, ',', '.') . "\nKendaraan: " . trim(($order->vehicle->brand ?? '') . ' ' . ($order->vehicle->model ?? '') . ' ' . ($order->vehicle->year ?? '')) . "\nPlat: " . ($order->vehicle->plate_number ?? '-') . "\n\nLink invoice/PDF:\n" . $invoiceUrl . "\n\nTerima kasih.");
    $vehicleSizeMap = [
        'small' => 'S - City Car / Hatchback / Sedan Kecil',
        'medium' => 'M - MPV / SUV Medium / Pickup Ringan',
        'large' => 'L - SUV Besar / Double Cabin / Ladder Frame',
    ];
@endphp

<div class="toolbar">
    @empty($isPublicInvoice)
    <a href="{{ route('orders.show', $order) }}" class="btn btn-light">Kembali</a>
    @endempty
    <button type="button" class="btn btn-whatsapp" onclick="sendInvoiceWhatsapp()">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7.8 19.2 4 20l.8-3.7A8.1 8.1 0 1 1 7.8 19.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M8.8 8.7c.2-.5.4-.6.8-.6h.5c.2 0 .4.1.5.4l.8 1.8c.1.3.1.5-.1.7l-.5.6c.7 1.2 1.6 2.1 2.9 2.8l.7-.6c.2-.2.4-.2.7-.1l1.7.8c.3.1.4.3.4.6v.4c0 .5-.2.8-.7 1a4 4 0 0 1-2 .3c-2.9-.4-6.1-3.3-6.8-6.1a3.4 3.4 0 0 1 .1-2Z" fill="currentColor"/>
        </svg>
        Kirim WhatsApp
    </button>
    <button type="button" class="btn" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-4a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v4a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 14h10v6H7v-6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M17.5 11.5h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
        Print / PDF
    </button>
</div>

<main class="sheet">
    <div class="header">
        <div class="brand">
            <img src="{{ asset('assets/media/favicon.png') }}" alt="Garasi Hobby">
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
            </table>
            <div class="status-pill">SELESAI</div>
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

    @if($hasPromoPackage)
    <section class="section">
        <div class="section-title">Paket Promo</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 180px;">Nama Paket</th>
                    <th>Deskripsi</th>
                    <th style="width: 130px;" class="text-end">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $order->promo_package_name ?? '-' }}</td>
                    <td>{{ $promoDescription ?: '-' }}</td>
                    <td class="text-end">Rp {{ number_format($order->promo_package_price ?? 0, 0, ',', '.') }}</td>
                </tr>
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
            <tr><td>Paket Promo{{ $order->promo_package_name ? ' - ' . $order->promo_package_name : '' }}</td><td>Rp {{ number_format($order->promo_package_price ?? 0, 0, ',', '.') }}</td></tr>
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
<script>
    function sendInvoiceWhatsapp() {
        var phone = @js($customerPhone);
        var message = @js($whatsappMessage);

        if (!phone) {
            alert('Nomor WhatsApp pelanggan belum tersedia.');
            return;
        }

        window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(message), '_blank');
    }
</script>
</body>
</html>
