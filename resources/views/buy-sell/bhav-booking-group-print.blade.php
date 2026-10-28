<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Booking Group - Just Udhari</title>

    <!-- Google Font: Quicksand -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 500;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            padding: 24px;
        }

        .no-print-bar {
            max-width: 850px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .btn-screen-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-screen-print {
            background-color: #f57c00;
            color: #ffffff;
        }

        .btn-screen-print:hover {
            background-color: #d35400;
        }

        .btn-screen-close {
            background-color: #e2e8f0;
            color: #334155;
        }

        .btn-screen-close:hover {
            background-color: #cbd5e1;
        }

        .voucher-sheet {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1e2a45;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 700;
            color: #f57c00;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-title .brand-dot {
            width: 10px;
            height: 10px;
            background-color: #1e2a45;
            border-radius: 50%;
            display: inline-block;
        }

        .voucher-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .voucher-meta-box {
            text-align: right;
        }

        .voucher-date {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }

        .badge-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge-buy {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-sell {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-gold {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-silver {
            background-color: #f1f5f9;
            color: #475569;
        }

        .booking-section {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .booking-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .booking-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e2a45;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .voucher-table th {
            background-color: #1e2a45;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 6px 10px;
            text-align: left;
        }

        .voucher-table th.text-end,
        .voucher-table td.text-end {
            text-align: right;
        }

        .voucher-table th.text-center,
        .voucher-table td.text-center {
            text-align: center;
        }

        .voucher-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            color: #1e293b;
        }

        .voucher-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .booking-summary-row {
            display: flex;
            justify-content: flex-end;
            gap: 24px;
            padding: 8px 0 0 0;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }

        .combined-totals-card {
            background: #f8fafc;
            border: 2px solid #1e2a45;
            border-radius: 8px;
            padding: 12px 18px;
            margin-top: 18px;
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .combined-totals-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e2a45;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .combined-totals-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .combined-val-item {
            display: flex;
            flex-direction: column;
        }

        .combined-val-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .combined-val-amount {
            font-size: 15px;
            font-weight: 700;
            color: #1e2a45;
        }

        .narration-box {
            background: #fffbeb;
            border: 1px dashed #fcd34d;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 24px;
            font-size: 12px;
            page-break-inside: avoid;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 36px;
            padding-top: 12px;
            page-break-inside: avoid;
        }

        .signature-line {
            border-top: 1px dashed #94a3b8;
            padding-top: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }

            .no-print,
            .no-print-bar {
                display: none !important;
            }

            .voucher-sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            .voucher-header {
                border-bottom: 2px solid #000000 !important;
            }

            .voucher-table th {
                background-color: #000000 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }
    </style>
</head>
<body>

    @php
        $firstBooking = $bookings->first();
        $customer = $firstBooking ? $firstBooking->customer : null;
        $grandAmount = $bookings->sum('total_amount');
        $grandPaid = $bookings->sum(function($b) {
            return $b->trade_type === 'SELL' ? $b->received_amount : $b->given_amount;
        });
        $goldBookings = $bookings->where('metal', 'GOLD');
        $silverBookings = $bookings->where('metal', 'SILVER');
        $goldWeight = $goldBookings->sum('total_weight');
        $silverWeight = $silverBookings->sum('total_weight');
    @endphp

    <!-- Screen Action Bar (Hidden during Print) -->
    <div class="no-print-bar no-print">
        <div style="font-weight: 700; color: #1e2a45;">
            <i class="fa-solid fa-layer-group text-primary me-2"></i> Bhav Booking Group Voucher
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-screen-action btn-screen-print" onclick="window.print();">
                <i class="fa-solid fa-print"></i> Print Voucher
            </button>
            <button type="button" class="btn-screen-action btn-screen-close" onclick="window.close();">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
        </div>
    </div>

    <!-- Voucher Printable Sheet -->
    <div class="voucher-sheet">

        <!-- Voucher Header -->
        <div class="voucher-header">
            <div>
                <div class="brand-title">
                    <span class="brand-dot"></span> JUST UDHARI
                </div>
                <div class="voucher-subtitle">Bhav Booking Group Confirmation Voucher</div>
            </div>
            <div class="voucher-meta-box">
                <div style="font-size: 13px; font-weight: 700; color: #1e2a45;">{{ count($bookings) }} Booking(s) Saved Together</div>
                <div class="voucher-date">
                    <i class="fa-regular fa-calendar-days me-1"></i>
                    {{ optional($firstBooking->booked_at)->format('d-m-Y h:i A') ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Customer & Group Summary -->
        <div class="info-grid">
            <!-- Customer Card -->
            <div class="info-card">
                <div class="info-card-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Customer Name:</span>
                    <span class="info-val">{{ optional($customer)->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer No:</span>
                    <span class="info-val">{{ optional($customer)->customer_no ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mobile Number:</span>
                    <span class="info-val">{{ optional($customer)->mobile ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">City:</span>
                    <span class="info-val">{{ optional($customer)->city ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Group Trade Card -->
            <div class="info-card">
                <div class="info-card-title">Group Summary</div>
                <div class="info-row">
                    <span class="info-label">Trade Type:</span>
                    <span class="info-val">
                        <span class="badge-pill {{ $firstBooking->trade_type === 'BUY' ? 'badge-buy' : 'badge-sell' }}">
                            {{ $firstBooking->trade_type }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Entry Type:</span>
                    <span class="info-val">{{ str_replace('_', ' ', $firstBooking->entry_type) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Grand Total:</span>
                    <span class="info-val" style="color: #0f172a; font-weight: 700;">₹{{ indian_money($grandAmount) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Paid:</span>
                    <span class="info-val" style="color: #166534; font-weight: 700;">₹{{ indian_money($grandPaid) }}</span>
                </div>
            </div>
        </div>

        <!-- Bookings Sections (One per booking in DB order) -->
        @foreach($bookings as $booking)
            <div class="booking-section">
                <div class="booking-section-header">
                    <div class="booking-section-title">
                        <span>{{ $booking->booking_no }}</span>
                        <span class="badge-pill {{ $booking->metal === 'GOLD' ? 'badge-gold' : 'badge-silver' }}">
                            {{ $booking->metal }}
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                        Weight: {{ number_format($booking->total_weight, 3) }} gm &bull; Amount: ₹{{ indian_money($booking->total_amount) }}
                    </div>
                </div>

                <!-- Items Table -->
                <table class="voucher-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="width: 80px;">Metal</th>
                            <th class="text-end">Weight (gm)</th>
                            <th class="text-end">Rate / 10gm</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->items as $item)
                            <tr>
                                <td class="text-center">{{ $item->sort_order }}</td>
                                <td>
                                    <span class="badge-pill {{ ($item->metal ?? $booking->metal) === 'GOLD' ? 'badge-gold' : 'badge-silver' }}">
                                        {{ $item->metal ?? $booking->metal }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace">{{ number_format($item->weight, 3) }}</td>
                                <td class="text-end font-monospace">₹{{ indian_money($item->rate) }}</td>
                                <td class="text-end font-monospace font-semibold">₹{{ indian_money($item->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No line items recorded</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Payments for this booking if any -->
                @if($booking->payments && $booking->payments->count() > 0)
                    <div style="font-size: 11px; font-weight: 700; color: #475569; margin: 8px 0 4px 0; text-transform: uppercase;">
                        Allocated Payments
                    </div>
                    <table class="voucher-table" style="margin-bottom: 6px;">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Mode</th>
                                <th>Method / Ref</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->payments as $payment)
                                <tr>
                                    <td>
                                        <span class="badge-pill {{ $payment->mode === 'CASH' ? 'badge-buy' : 'badge-silver' }}">
                                            {{ $payment->mode }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $payment->online_method ?? '-' }}
                                        @if($payment->reference_no)
                                            <span style="color: #64748b; font-size: 11px;">({{ $payment->reference_no }})</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace font-semibold">₹{{ indian_money($payment->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <div class="booking-summary-row">
                    <div>
                        Booking Total: <strong>₹{{ indian_money($booking->total_amount) }}</strong>
                        <div style="font-size: 11px; font-style: italic; color: #64748b; font-weight: normal; margin-top: 1px;">
                            {{ amount_to_indian_words($booking->total_amount) }}
                        </div>
                    </div>
                    <div>Paid: <strong style="color: #166534;">₹{{ indian_money($booking->trade_type === 'SELL' ? $booking->received_amount : $booking->given_amount) }}</strong></div>
                    <div>Balance: <strong>₹{{ indian_money(max(0, $booking->total_amount - ($booking->trade_type === 'SELL' ? $booking->received_amount : $booking->given_amount))) }}</strong></div>
                </div>
            </div>
        @endforeach

        <!-- Combined Totals Box -->
        <div class="combined-totals-card">
            <div class="combined-totals-title">Combined Group Summary</div>
            <div class="combined-totals-grid">
                @if($goldWeight > 0)
                    <div class="combined-val-item">
                        <span class="combined-val-label">Total Gold Weight</span>
                        <span class="combined-val-amount" style="color: #92400e;">{{ number_format($goldWeight, 3) }} gm</span>
                    </div>
                @endif
                @if($silverWeight > 0)
                    <div class="combined-val-item">
                        <span class="combined-val-label">Total Silver Weight</span>
                        <span class="combined-val-amount" style="color: #475569;">{{ number_format($silverWeight, 3) }} gm</span>
                    </div>
                @endif
                <div class="combined-val-item">
                    <span class="combined-val-label">Combined Amount</span>
                    <span class="combined-val-amount">₹{{ indian_money($grandAmount) }}</span>
                    <span style="font-size: 11px; font-style: italic; color: #64748b; font-weight: normal; margin-top: 2px;">
                        {{ amount_to_indian_words($grandAmount) }}
                    </span>
                </div>
                <div class="combined-val-item">
                    <span class="combined-val-label">Total Paid</span>
                    <span class="combined-val-amount" style="color: #166534;">₹{{ indian_money($grandPaid) }}</span>
                </div>
                <div class="combined-val-item">
                    <span class="combined-val-label">Remaining Balance</span>
                    <span class="combined-val-amount" style="color: #dc2626;">₹{{ indian_money(max(0, $grandAmount - $grandPaid)) }}</span>
                </div>
            </div>
        </div>

        @if($firstBooking->narration)
            <div class="narration-box">
                <div style="font-weight: 700; color: #92400e; margin-bottom: 2px;">NARRATION / REMARKS:</div>
                <div>{{ $firstBooking->narration }}</div>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signatures-grid">
            <div class="signature-line">
                Customer Signature
            </div>
            <div class="signature-line">
                Authorized Signatory (Just Udhari)
            </div>
        </div>

    </div>

</body>
</html>
