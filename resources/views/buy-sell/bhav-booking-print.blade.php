<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Booking - {{ $booking->booking_no }} - Just Udhari</title>

    <!-- Google Font: Quicksand -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        /* Base Screen & Print Typography */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            padding: 24px;
        }

        /* Screen Action Bar (Floating on Screen, Hidden during Print) */
        .no-print-bar {
            max-width: 800px;
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

        /* Voucher Printable Sheet */
        .voucher-sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        /* Voucher Header */
        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1e2a45;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .brand-title {
            font-size: 26px;
            font-weight: 700;
            color: #f57c00;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-title .brand-dot {
            width: 12px;
            height: 12px;
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
            margin-top: 4px;
        }

        .voucher-meta-box {
            text-align: right;
        }

        .voucher-no {
            font-size: 18px;
            font-weight: 700;
            color: #1e2a45;
            letter-spacing: 0.5px;
        }

        .voucher-date {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }

        /* Two-Column Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
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

        /* Items Table */
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .voucher-table th {
            background-color: #1e2a45;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 8px 12px;
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
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            color: #1e293b;
        }

        .voucher-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Totals Block */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .totals-table {
            width: 320px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 12px;
            font-size: 12px;
        }

        .totals-table tr.total-highlight {
            border-top: 2px solid #1e2a45;
            border-bottom: 2px solid #1e2a45;
            background-color: #f8fafc;
            font-weight: 700;
            font-size: 14px;
        }

        /* Narration */
        .narration-box {
            background: #fffbeb;
            border: 1px dashed #fcd34d;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 32px;
            font-size: 12px;
        }

        .narration-title {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        /* Signatures */
        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 48px;
            padding-top: 16px;
        }

        .signature-line {
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        /* Print Media Styles */
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

            .totals-table tr.total-highlight {
                border-top: 2px solid #000000 !important;
                border-bottom: 2px solid #000000 !important;
            }
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }
    </style>
</head>
<body>

    <!-- Screen Action Bar (Hidden during Print) -->
    <div class="no-print-bar no-print">
        <div style="font-weight: 700; color: #1e2a45;">
            <i class="fa-solid fa-receipt text-primary me-2"></i> Bhav Booking Voucher Preview
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
                <div class="voucher-subtitle">Bhav Booking Confirmation Voucher</div>
            </div>
            <div class="voucher-meta-box">
                <div class="voucher-no">{{ $booking->booking_no }}</div>
                <div class="voucher-date">
                    <i class="fa-regular fa-calendar-days me-1"></i>
                    {{ optional($booking->booked_at)->format('d-m-Y h:i A') ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Customer & Trade Details Cards -->
        <div class="info-grid">
            <!-- Customer Card -->
            <div class="info-card">
                <div class="info-card-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Customer Name:</span>
                    <span class="info-val">{{ optional($booking->customer)->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer No:</span>
                    <span class="info-val">{{ optional($booking->customer)->customer_no ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mobile Number:</span>
                    <span class="info-val">{{ optional($booking->customer)->mobile ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">City:</span>
                    <span class="info-val">{{ optional($booking->customer)->city ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Trade Card -->
            <div class="info-card">
                <div class="info-card-title">Booking Details</div>
                <div class="info-row">
                    <span class="info-label">Trade Type:</span>
                    <span class="info-val">
                        <span class="badge-pill {{ $booking->trade_type === 'BUY' ? 'badge-buy' : 'badge-sell' }}">
                            {{ $booking->trade_type }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metal:</span>
                    <span class="info-val">
                        <span class="badge-pill {{ $booking->metal === 'GOLD' ? 'badge-gold' : 'badge-silver' }}">
                            {{ $booking->metal }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Entry Type:</span>
                    <span class="info-val">
                        {{ $booking->entry_type === 'ONLY_ENTRY' ? 'Only Entry' : 'Bhav Cut + Ledger' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-val" style="font-weight: 700;">
                        {{ $booking->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="voucher-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th class="text-end">WEIGHT (GM)</th>
                    <th class="text-end">RATE (₹/10GM)</th>
                    <th class="text-end">AMOUNT (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->items as $index => $item)
                    <tr>
                        <td class="text-center" style="color: #64748b; font-weight: 600;">
                            {{ $item->sort_order ?? ($index + 1) }}
                        </td>
                        <td class="text-end" style="font-weight: 600;">
                            {{ number_format((float) $item->weight, 3) }} gm
                        </td>
                        <td class="text-end">
                            ₹{{ number_format((float) $item->rate, 2) }}
                        </td>
                        <td class="text-end" style="font-weight: 700; color: #0f172a;">
                            ₹{{ number_format((float) $item->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: #94a3b8; padding: 20px;">
                            No items recorded.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Summary Block -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="info-label text-end">Total Weight:</td>
                    <td class="info-val text-end">{{ number_format((float) $booking->total_weight, 3) }} gm</td>
                </tr>
                <tr class="total-highlight">
                    <td class="text-end">Total Amount:</td>
                    <td class="text-end" style="color: {{ $booking->trade_type === 'BUY' ? '#166534' : '#991b1b' }};">
                        ₹{{ number_format((float) $booking->total_amount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="info-label text-end">Delivered Weight:</td>
                    <td class="info-val text-end">{{ number_format((float) $booking->delivered_weight, 3) }} gm</td>
                </tr>
                <tr>
                    <td class="info-label text-end">Pending Weight:</td>
                    <td class="info-val text-end" style="font-weight: 700; color: #f57c00;">
                        {{ number_format((float) $booking->pending_weight, 3) }} gm
                    </td>
                </tr>
                @if((float) $booking->given_amount > 0)
                    <tr>
                        <td class="info-label text-end">Given Amount:</td>
                        <td class="info-val text-end text-danger">₹{{ number_format((float) $booking->given_amount, 2) }}</td>
                    </tr>
                @endif
                @if((float) $booking->received_amount > 0)
                    <tr>
                        <td class="info-label text-end">Received Amount:</td>
                        <td class="info-val text-end text-success">₹{{ number_format((float) $booking->received_amount, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Narration / Remarks -->
        @if(!empty($booking->narration))
            <div class="narration-box">
                <div class="narration-title">Narration / Remarks</div>
                <div>{{ $booking->narration }}</div>
            </div>
        @endif

        <!-- Signatures Block -->
        <div class="signatures-grid">
            <div class="signature-line">
                Customer Signature
            </div>
            <div class="signature-line">
                Authorized Signatory (Just Udhari)
            </div>
        </div>

    </div>

    <!-- Auto Print Script -->
    <script>
        // Trigger browser print dialog as soon as page finishes loading
        window.addEventListener('load', function () {
            // Small delay to ensure Google Fonts and stylesheets have rendered
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
