<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Print - {{ $isGroup ? ($groupId ?? 'Group') : $booking->booking_no }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            width: 72mm;
            margin: 0 auto;
            padding: 4mm 0;
            font-family: 'Courier New', Courier, monospace, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 11.5px;
            line-height: 1.35;
            color: #000000;
            background: #ffffff;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        .dashed-line {
            border-top: 1px dashed #000000;
            margin: 5px 0;
            height: 0;
        }

        .shop-header {
            text-align: center;
            margin-bottom: 4px;
        }

        .shop-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .shop-sub {
            font-size: 11px;
            margin-top: 1px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1.5px 0;
        }

        .entry-line {
            margin: 2px 0;
            word-break: break-all;
        }

        .footer-thanks {
            text-align: center;
            margin-top: 8px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Screen only action bar */
        @media screen {
            body {
                padding: 16px 0;
                background-color: #f1f5f9;
            }
            .thermal-sheet {
                background: #ffffff;
                width: 72mm;
                margin: 0 auto;
                padding: 6mm 4mm;
                box-shadow: 0 2px 10px rgba(0,0,0,0.15);
                border: 1px solid #cbd5e1;
            }
            .screen-actions {
                width: 72mm;
                margin: 0 auto 10px auto;
                display: flex;
                gap: 8px;
                justify-content: center;
            }
            .screen-actions button {
                padding: 4px 12px;
                font-size: 11.5px;
                font-weight: bold;
                cursor: pointer;
                border: 1px solid #334155;
                background: #ffffff;
                border-radius: 4px;
            }
            .screen-actions button.btn-print {
                background: #0f172a;
                color: #ffffff;
            }
        }

        @media print {
            .screen-actions {
                display: none !important;
            }
            .thermal-sheet {
                width: 72mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <button type="button" class="btn-print" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    <div class="thermal-sheet">
        <!-- Shop Name Header -->
        <div class="shop-header">
            <div class="shop-name">{{ config('app.name', 'JUST UDHARI') }}</div>
            <div class="shop-sub">Bhav Booking Receipt</div>
        </div>

        <div class="dashed-line"></div>

        @if(!($isGroup ?? false))
            @php
                $paid = $booking->trade_type === 'SELL' ? ($booking->received_amount ?? 0) : ($booking->given_amount ?? 0);
                if ($booking->payments && $booking->payments->count() > 0) {
                    $paid = $booking->payments->sum('amount');
                }
                $balance = max(0, $booking->total_amount - $paid);
            @endphp

            <!-- Booking No + Date -->
            <div class="info-row">
                <span>Booking No:</span>
                <span class="fw-bold">{{ $booking->booking_no }}</span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ optional($booking->booked_at)->format('d-m-Y h:i A') ?? '-' }}</span>
            </div>

            <!-- Customer Name + Mobile -->
            <div class="info-row">
                <span>Customer:</span>
                <span class="fw-bold">{{ optional($booking->customer)->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span>Mobile:</span>
                <span>{{ optional($booking->customer)->mobile ?? 'N/A' }}</span>
            </div>

            <!-- BUY/SELL + Metal -->
            <div class="info-row">
                <span>Type:</span>
                <span class="fw-bold">{{ $booking->trade_type }} &bull; {{ $booking->metal }}</span>
            </div>

            <div class="dashed-line"></div>

            <!-- Entries: weight × rate = amount, one per line -->
            <div class="fw-bold" style="margin-bottom: 2px;">ENTRIES:</div>
            @forelse($booking->items as $item)
                <div class="info-row entry-line">
                    <span>{{ number_format($item->weight, 3) }} gm &times; ₹{{ indian_money($item->rate) }}</span>
                    <span class="fw-bold">₹{{ indian_money($item->amount) }}</span>
                </div>
            @empty
                <div class="info-row entry-line">
                    <span>{{ number_format($booking->total_weight, 3) }} gm</span>
                    <span class="fw-bold">₹{{ indian_money($booking->total_amount) }}</span>
                </div>
            @endforelse

            <div class="dashed-line"></div>

            <!-- Total weight & Total amount -->
            <div class="info-row">
                <span>Total Weight:</span>
                <span class="fw-bold">{{ number_format($booking->total_weight, 3) }} gm</span>
            </div>
            <div class="info-row">
                <span class="fw-bold">TOTAL AMOUNT:</span>
                <span class="fw-bold" style="font-size: 13px;">₹{{ indian_money($booking->total_amount) }}</span>
            </div>

            <!-- Amount in words -->
            <div style="font-size: 10.5px; margin: 2px 0 4px 0; font-style: italic;">
                ({{ amount_to_indian_words($booking->total_amount) }})
            </div>

            <!-- Payments (mode + amount) -->
            @if($booking->payments && $booking->payments->count() > 0)
                <div class="dashed-line"></div>
                <div class="fw-bold" style="margin-bottom: 2px;">PAYMENTS:</div>
                @foreach($booking->payments as $payment)
                    <div class="info-row">
                        <span>{{ $payment->mode }}{{ $payment->online_method ? ' ('.$payment->online_method.')' : '' }}:</span>
                        <span>₹{{ indian_money($payment->amount) }}</span>
                    </div>
                @endforeach
            @endif

            <div class="dashed-line"></div>

            <!-- Paid / Balance -->
            <div class="info-row">
                <span>Paid ({{ $booking->trade_type === 'SELL' ? 'Received' : 'Given' }}):</span>
                <span class="fw-bold">₹{{ indian_money($paid) }}</span>
            </div>
            <div class="info-row">
                <span>Balance:</span>
                <span class="fw-bold">₹{{ indian_money($balance) }}</span>
            </div>

            <!-- Narration (short) -->
            @if(!empty($booking->narration))
                <div class="dashed-line"></div>
                <div style="font-size: 10.5px; word-break: break-word;">
                    <span class="fw-bold">Note:</span> {{ \Illuminate\Support\Str::limit($booking->narration, 80) }}
                </div>
            @endif

        @else
            <!-- Group Print: one section per booking + combined total -->
            @php
                $firstB = $bookings->first();
                $grandAmount = $bookings->sum('total_amount');
                $grandGoldWeight = $bookings->where('metal', 'GOLD')->sum('total_weight');
                $grandSilverWeight = $bookings->where('metal', 'SILVER')->sum('total_weight');
                $grandPaid = 0;
                foreach ($bookings as $b) {
                    if ($b->payments && $b->payments->count() > 0) {
                        $grandPaid += $b->payments->sum('amount');
                    } else {
                        $grandPaid += ($b->trade_type === 'SELL' ? ($b->received_amount ?? 0) : ($b->given_amount ?? 0));
                    }
                }
                $grandBalance = max(0, $grandAmount - $grandPaid);
            @endphp

            <div class="info-row">
                <span>Group Voucher:</span>
                <span class="fw-bold">{{ $groupId }}</span>
            </div>
            <div class="info-row">
                <span>Customer:</span>
                <span class="fw-bold">{{ optional($firstB->customer)->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span>Mobile:</span>
                <span>{{ optional($firstB->customer)->mobile ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ optional($firstB->booked_at)->format('d-m-Y h:i A') ?? '-' }}</span>
            </div>

            @foreach($bookings as $index => $b)
                <div class="dashed-line"></div>
                <div class="info-row fw-bold">
                    <span>#{{ $index + 1 }} {{ $b->booking_no }}</span>
                    <span>{{ $b->trade_type }} &bull; {{ $b->metal }}</span>
                </div>

                @forelse($b->items as $item)
                    <div class="info-row entry-line">
                        <span>{{ number_format($item->weight, 3) }} gm &times; ₹{{ indian_money($item->rate) }}</span>
                        <span>₹{{ indian_money($item->amount) }}</span>
                    </div>
                @empty
                    <div class="info-row entry-line">
                        <span>{{ number_format($b->total_weight, 3) }} gm</span>
                        <span>₹{{ indian_money($b->total_amount) }}</span>
                    </div>
                @endforelse

                <div class="info-row" style="margin-top: 2px;">
                    <span>Weight: {{ number_format($b->total_weight, 3) }} gm</span>
                    <span class="fw-bold">₹{{ indian_money($b->total_amount) }}</span>
                </div>

                @if(!empty($b->narration))
                    <div style="font-size: 10px; color: #333;">
                        Note: {{ \Illuminate\Support\Str::limit($b->narration, 60) }}
                    </div>
                @endif
            @endforeach

            <div class="dashed-line"></div>
            <div class="fw-bold text-center" style="margin-bottom: 3px;">COMBINED TOTALS</div>

            @if($grandGoldWeight > 0)
                <div class="info-row">
                    <span>Gold Weight:</span>
                    <span class="fw-bold">{{ number_format($grandGoldWeight, 3) }} gm</span>
                </div>
            @endif
            @if($grandSilverWeight > 0)
                <div class="info-row">
                    <span>Silver Weight:</span>
                    <span class="fw-bold">{{ number_format($grandSilverWeight, 3) }} gm</span>
                </div>
            @endif

            <div class="info-row">
                <span class="fw-bold">GRAND TOTAL:</span>
                <span class="fw-bold" style="font-size: 13px;">₹{{ indian_money($grandAmount) }}</span>
            </div>
            <div style="font-size: 10.5px; margin: 2px 0 4px 0; font-style: italic;">
                ({{ amount_to_indian_words($grandAmount) }})
            </div>

            <div class="info-row">
                <span>Total Paid:</span>
                <span class="fw-bold">₹{{ indian_money($grandPaid) }}</span>
            </div>
            <div class="info-row">
                <span>Balance:</span>
                <span class="fw-bold">₹{{ indian_money($grandBalance) }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        <!-- "Thank you" Footer -->
        <div class="footer-thanks">
            *** THANK YOU ***
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>

