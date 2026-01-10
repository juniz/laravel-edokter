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
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
            background: #ffffff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px 25px;
        }

        /* Header */
        .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3b82f6;
        }

        .header-row {
            display: table;
            width: 100%;
        }

        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .company-details {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.5;
        }

        .invoice-info {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .invoice-number {
            font-size: 11px;
            color: #374151;
            font-weight: 600;
        }

        .invoice-date {
            font-size: 9px;
            color: #6b7280;
            margin-top: 3px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 10px;
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
            margin-bottom: 12px;
        }

        .billing-box {
            background: #f9fafb;
            padding: 10px 12px;
            border-radius: 4px;
            border-left: 3px solid #3b82f6;
        }

        .billing-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .billing-content {
            font-size: 9px;
            color: #1f2937;
            line-height: 1.5;
        }

        .billing-content strong {
            color: #111827;
            font-size: 10px;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 10px;
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
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
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
            margin-top: 8px;
            margin-left: auto;
            width: 220px;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 4px 0;
            font-size: 9px;
        }

        .total-row .total-label,
        .total-row .total-value {
            display: table-cell;
        }

        .total-row .total-value {
            text-align: right;
        }

        .total-row.subtotal,
        .total-row.discount,
        .total-row.tax {
            color: #6b7280;
        }

        .total-row.grand-total {
            border-top: 1px solid #3b82f6;
            padding-top: 6px;
            margin-top: 4px;
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
        }

        .total-label {
            font-weight: 500;
        }

        .total-value {
            font-weight: 600;
        }

        /* Payment Info */
        .payment-info {
            margin-top: 12px;
            padding: 8px 10px;
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            border-radius: 4px;
        }

        .payment-info-title {
            font-size: 9px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
        }

        .payment-info-content {
            font-size: 8px;
            color: #1f2937;
            line-height: 1.5;
        }

        /* Notes */
        .footer-note {
            margin-top: 10px;
            padding: 8px 10px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 8px;
            color: #4b5563;
            line-height: 1.4;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-row">
                <div class="company-info">
                    <div class="company-name">{{ $companyName ?? 'Abahost' }}</div>
                    <div class="company-details">
                        @if(isset($companyAddress)){{ $companyAddress }}<br>@endif
                        @if(isset($companyPhone))Tel: {{ $companyPhone }} @endif
                        @if(isset($companyEmail))| {{ $companyEmail }}@endif
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $invoice->number }}</div>
                    <div class="invoice-date">
                        Tanggal: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d M Y') }} |
                        Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_at)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Billing in one row -->
        <div style="display: table; width: 100%; margin-bottom: 12px;">
            <div style="display: table-cell; width: 30%; vertical-align: top;">
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
            <div style="display: table-cell; width: 70%; vertical-align: top;">
                <div class="billing-box">
                    <div class="billing-label">Tagihan Kepada</div>
                    <div class="billing-content">
                        <strong>{{ $invoice->customer->name }}</strong> | {{ $invoice->customer->email }}
                        @if($invoice->customer->phone) | {{ $invoice->customer->phone }}@endif
                        @if($invoice->customer->organization || $invoice->customer->street_1 || $invoice->customer->city)
                            <br>
                            @if($invoice->customer->organization){{ $invoice->customer->organization }}, @endif
                            @if($invoice->customer->street_1){{ $invoice->customer->street_1 }}, @endif
                            @if($invoice->customer->city){{ $invoice->customer->city }}@endif
                            @if($invoice->customer->postal_code), {{ $invoice->customer->postal_code }}@endif
                            @if($invoice->customer->country_code) - {{ $invoice->customer->country_code }}@endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 50%;">Deskripsi</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 17%;">Harga</th>
                        <th class="text-right" style="width: 18%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-right">{{ number_format($item->unit_price_cents, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->total_cents, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 15px; color: #6b7280;">
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
                <span class="total-value">{{ number_format($invoice->subtotal_cents, 0, ',', '.') }}</span>
            </div>
            @if($invoice->discount_cents > 0)
                <div class="total-row discount">
                    <span class="total-label">Diskon</span>
                    <span class="total-value">-{{ number_format($invoice->discount_cents, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($invoice->tax_cents > 0)
                <div class="total-row tax">
                    <span class="total-label">Pajak</span>
                    <span class="total-value">{{ number_format($invoice->tax_cents, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span class="total-label">Total</span>
                <span class="total-value">{{ number_format($invoice->total_cents, 0, ',', '.') }} {{ strtoupper($invoice->currency) }}</span>
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
                    <strong>Lunas</strong>
                    @if($successfulPayment->paid_at)
                        | {{ \Carbon\Carbon::parse($successfulPayment->paid_at)->format('d M Y H:i') }}
                    @endif
                    | {{ ucfirst(str_replace('_', ' ', $successfulPayment->provider ?? 'N/A')) }}
                    @if($successfulPayment->provider_ref)
                        | Ref: {{ $successfulPayment->provider_ref }}
                    @endif
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div class="footer-note">
                <strong>Catatan:</strong> {{ $invoice->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Terima kasih atas kepercayaan Anda. Dokumen ini dibuat secara otomatis dan sah secara hukum.
        </div>
    </div>
</body>
</html>
