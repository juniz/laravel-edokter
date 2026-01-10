<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.6;
            background: #ffffff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #3b82f6;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.8;
        }

        .invoice-info {
            text-align: right;
            flex: 1;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .invoice-number {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .invoice-date {
            font-size: 11px;
            color: #6b7280;
        }

        /* Status Badge */
        .status-section {
            margin-bottom: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-unpaid {
            background: #fef3c7;
            color: #92400e;
        }

        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Billing Info */
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 40px;
        }

        .billing-box {
            flex: 1;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }

        .billing-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .billing-content {
            font-size: 12px;
            color: #1f2937;
            line-height: 1.8;
        }

        .billing-content strong {
            color: #111827;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .table thead {
            background: #1e40af;
            color: #ffffff;
        }

        .table th {
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Totals */
        .totals-section {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 12px;
        }

        .total-row.subtotal,
        .total-row.discount,
        .total-row.tax {
            color: #6b7280;
        }

        .total-row.grand-total {
            border-top: 2px solid #3b82f6;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }

        .total-label {
            font-weight: 500;
        }

        .total-value {
            font-weight: 600;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .footer-note {
            margin-top: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        .payment-info {
            margin-top: 30px;
            padding: 20px;
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
        }

        .payment-info-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
        }

        .payment-info-content {
            font-size: 11px;
            color: #1f2937;
            line-height: 1.8;
        }

        /* Utility */
        .mt-2 { margin-top: 10px; }
        .mb-2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $companyName ?? 'Abahost' }}</div>
                <div class="company-details">
                    @if(isset($companyAddress))
                        {{ $companyAddress }}<br>
                    @endif
                    @if(isset($companyPhone))
                        Tel: {{ $companyPhone }}<br>
                    @endif
                    @if(isset($companyEmail))
                        Email: {{ $companyEmail }}<br>
                    @endif
                    @if(isset($companyWebsite))
                        {{ $companyWebsite }}
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">No: {{ $invoice->number }}</div>
                <div class="invoice-date">
                    Tanggal: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d F Y') }}<br>
                    Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_at)->format('d F Y') }}
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="status-section">
            @php
                $statusConfig = [
                    'paid' => ['class' => 'status-paid', 'label' => 'Lunas'],
                    'unpaid' => ['class' => 'status-unpaid', 'label' => 'Belum Dibayar'],
                    'overdue' => ['class' => 'status-overdue', 'label' => 'Jatuh Tempo'],
                ];
                $status = $statusConfig[$invoice->status] ?? ['class' => 'status-unpaid', 'label' => ucfirst($invoice->status)];
            @endphp
            <span class="status-badge {{ $status['class'] }}">{{ $status['label'] }}</span>
        </div>

        <!-- Billing Info -->
        <div class="billing-section">
            <div class="billing-box">
                <div class="billing-label">Tagihan Kepada</div>
                <div class="billing-content">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    {{ $invoice->customer->email }}<br>
                    @if($invoice->customer->phone)
                        {{ $invoice->customer->phone }}<br>
                    @endif
                    @if($invoice->customer->organization)
                        {{ $invoice->customer->organization }}<br>
                    @endif
                    @if($invoice->customer->street_1)
                        {{ $invoice->customer->street_1 }}<br>
                    @endif
                    @if($invoice->customer->city)
                        {{ $invoice->customer->city }}, {{ $invoice->customer->state ?? '' }}<br>
                    @endif
                    @if($invoice->customer->postal_code)
                        {{ $invoice->customer->postal_code }}<br>
                    @endif
                    @if($invoice->customer->country_code)
                        {{ $invoice->customer->country_code }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 45%;">Deskripsi</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 20%;">Harga Satuan</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $item->description }}
                                @if(isset($item->meta) && is_array($item->meta) && !empty($item->meta))
                                    <br><small style="color: #6b7280;">
                                        @if(isset($item->meta['product_name']))
                                            Produk: {{ $item->meta['product_name'] }}
                                        @endif
                                        @if(isset($item->meta['plan_code']))
                                            | Paket: {{ $item->meta['plan_code'] }}
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-right">{{ number_format($item->unit_price_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</td>
                            <td class="text-right">{{ number_format($item->total_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px; color: #6b7280;">
                                Tidak ada item
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row subtotal">
                <span class="total-label">Subtotal</span>
                <span class="total-value">{{ number_format($invoice->subtotal_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</span>
            </div>
            @if($invoice->discount_cents > 0)
                <div class="total-row discount">
                    <span class="total-label">Diskon</span>
                    <span class="total-value">-{{ number_format($invoice->discount_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</span>
                </div>
            @endif
            @if($invoice->tax_cents > 0)
                <div class="total-row tax">
                    <span class="total-label">Pajak</span>
                    <span class="total-value">{{ number_format($invoice->tax_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span class="total-label">Total Pembayaran</span>
                <span class="total-value">{{ number_format($invoice->total_cents / 100, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        @if($invoice->status === 'paid' && $invoice->payments->where('status', 'succeeded')->count() > 0)
            @php
                $successfulPayment = $invoice->payments->where('status', 'succeeded')->first();
            @endphp
            <div class="payment-info">
                <div class="payment-info-title">Informasi Pembayaran</div>
                <div class="payment-info-content">
                    Status: <strong>Lunas</strong><br>
                    @if($successfulPayment->paid_at)
                        Tanggal Pembayaran: {{ \Carbon\Carbon::parse($successfulPayment->paid_at)->format('d F Y H:i') }}<br>
                    @endif
                    Metode: <strong>{{ ucfirst(str_replace('_', ' ', $successfulPayment->provider ?? 'N/A')) }}</strong><br>
                    @if($successfulPayment->provider_ref)
                        Referensi: {{ $successfulPayment->provider_ref }}
                    @endif
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div class="footer-note">
                <strong>Catatan:</strong><br>
                {{ $invoice->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda menggunakan layanan kami.</p>
            <p style="margin-top: 10px;">Dokumen ini dibuat secara otomatis dan sah secara hukum.</p>
        </div>
    </div>
</body>
</html>
