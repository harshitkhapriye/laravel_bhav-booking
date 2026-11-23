@php
  $receiptTotalGross = $order->items->sum(function($item) { return (float)$item->gross_wt * ($item->qty ?: 1); });
  $receiptTotalNet = $order->items->sum(function($item) { return (float)$item->net_wt * ($item->qty ?: 1); });
  $receiptTotalWastageGm = $order->items->sum(function($item) { return (float)($item->wastage_per ?? 0) * ($item->qty ?: 1); });
  $receiptTotalFine = $order->items->sum(function($item) { return (float)$item->fine_wt * ($item->qty ?: 1); });
  $receiptAvgWastagePer = $receiptTotalNet > 0 ? ($receiptTotalWastageGm / $receiptTotalNet) * 100 : 0;
  
  if (isset($latestIssue) && $latestIssue) {
      if ((float)$latestIssue->approx_weight > 0) {
          $receiptTotalGross = (float)$latestIssue->approx_weight;
      }
      if ((float)$latestIssue->net_weight > 0) {
          $receiptTotalNet = (float)$latestIssue->net_weight;
      }
      if ((float)$latestIssue->wastage_gm > 0) {
          $receiptTotalWastageGm = (float)$latestIssue->wastage_gm;
      }
      if ((float)$latestIssue->wastage_per > 0) {
          $receiptAvgWastagePer = (float)$latestIssue->wastage_per;
      } elseif ($receiptTotalNet > 0 && $receiptTotalWastageGm > 0) {
          $receiptAvgWastagePer = ($receiptTotalWastageGm / $receiptTotalNet) * 100;
      }
      if ((float)$latestIssue->approx_fine > 0) {
          $receiptTotalFine = (float)$latestIssue->approx_fine;
      }
  }
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>{{ isset($karigar) && $karigar ? 'Order Assign #' . $order->order_unique : 'Order Receipt #' . $order->order_unique }}</title>

@if(!isset($is_pdf) || !$is_pdf)
  <!-- Fonts & Icons for HTML preview screen -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endif

<style>
  /* ===== UNIVERSAL STYLES (HTML & PDF) ===== */
  html, body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .text-center { text-align: center !important; }
  .text-right { text-align: right !important; }
  .text-left { text-align: left !important; }
  .font-bold { font-weight: bold !important; }
  .uppercase { text-transform: uppercase !important; }

  .receipt-container {
    box-sizing: border-box;
  }

  /* Info Table Sizing */
  .info-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 2px 0 !important;
  }

  /* Items Table Sizing & Column Alignment Lock */
  .items-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 4px 0 !important;
    table-layout: fixed !important;
  }
  
  /* Financial Summary Table */
  .summary-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 4px 0 !important;
  }

  .header {
    margin-bottom: 8px;
  }
  .shop-title {
    font-size: 15px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.3px;
  }
  .shop-sub {
    font-size: 9.5px;
    line-height: 1.35;
    margin-top: 3px;
  }
  .receipt-title {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 10px;
    display: inline-block;
    margin: 6px 0 2px;
    letter-spacing: 0.5px;
  }

  .item-name {
    font-weight: bold;
    display: block;
  }

  .summary-table td.label {
    text-align: left;
  }
  .summary-table td.value {
    text-align: right;
    font-weight: bold;
  }

  /* Universal Premium Colors (Active on screen & PDF print) */
  .color-gold { color: #b8860b !important; }
  .color-green { color: #059669 !important; }
  .color-blue { color: #2563eb !important; }
  .color-red { color: #ef4444 !important; }
  .color-orange { color: #d97706 !important; }
  .color-dark { color: #0f172a !important; }

  @if(isset($is_pdf) && $is_pdf)
    /* ===== PDF ONLY: Standard Crisp Black Thermal Layout ===== */
    @page {
      size: 80mm auto;
      margin: 0;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 10px;
      color: #000000;
      width: 76mm;
      margin: 2mm auto;
      padding: 0;
      background: #ffffff;
    }
    
    /* PDF only: Override grey text to solid black to prevent halftone print patterns */
    .color-muted {
      color: #000000 !important;
    }

    .receipt-container {
      width: 76mm;
      margin: 0 auto;
      padding: 2mm 1mm;
    }
    .divider {
      border-bottom: 1px dashed #000000 !important;
      margin: 6px 0;
      display: block;
      width: 100%;
    }
    .double-divider {
      border-bottom: 1px dashed #000000 !important;
      margin: 6px 0;
      display: block;
      width: 100%;
    }
    .solid-divider {
      border-bottom: 1px solid #000000 !important;
      margin: 6px 0;
      display: block;
      width: 100%;
    }
    .border-sig {
      border-top: 1px dashed #000000 !important;
    }
    .info-table td {
      padding: 1.5px 0;
      vertical-align: top;
      font-size: 9.5px;
      line-height: 1.35;
    }
    .items-table th {
      font-size: 8.5px;
      font-weight: bold;
      text-align: left;
      padding: 3px 2px;
      border-bottom: 1px solid #000000;
      text-transform: uppercase;
    }
    .items-table td {
      padding: 4px 2px;
      vertical-align: top !important;
      font-size: 9.5px;
      line-height: 1.3;
      border-bottom: 1px dotted #cccccc;
    }
    .item-meta {
      font-size: 8px;
      margin-top: 2px;
    }
    .summary-table td {
      padding: 2.5px 0;
      font-size: 10px;
    }
    .grand-total-row td {
      font-size: 11px;
      font-weight: bold;
      padding: 3px 0;
    }
    .summary-table tr.grand-total-row.with-border-top td {
      border-top: 1px dashed #000000;
    }
    .balance-due-row td {
      font-size: 12.5px;
      font-weight: bold;
      border-top: 1.5px solid #000000;
      padding: 4px 0 2px;
    }
    .premium-box {
      border: 1px dashed #666666;
      padding: 6px 8px;
      margin: 5px 0;
      font-size: 9px;
      line-height: 1.35;
      background: #fdfaf2;
    }
    .receipt-title {
      border: 1px solid #999999;
      background: #f5f5f5;
      border-radius: 20px;
    }
    .footer-note {
      text-align: center !important;
      margin-top: 14px;
      font-size: 9px;
      line-height: 1.4;
    }
  @else
    /* ===== HTML Preview ONLY: Gorgeous Screen Colors ===== */
    html, body {
      margin: 0;
      padding: 0;
      background: #f1f5f9;
    }
    body {
      font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 11px;
      color: #1e293b;
    }
    
    /* Screen only: keep grey text smooth and readable */
    .color-muted {
      color: #475569 !important;
    }

    .receipt-container {
      width: 76mm;
      margin: 15px auto;
      padding: 4mm 3.5mm;
      background: #ffffff;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      border-radius: 12px;
      position: relative;
      border: 1px solid #e2e8f0;
      border-top: 4px solid #b8860b;
    }
    .divider {
      border-bottom: 1px dashed #cbd5e1;
      margin: 7px 0;
      display: block;
      width: 100%;
    }
    .double-divider {
      border-bottom: 1px dashed #cbd5e1;
      margin: 7px 0;
      display: block;
      width: 100%;
    }
    .solid-divider {
      border-bottom: 1px solid #e2e8f0;
      margin: 7px 0;
      display: block;
      width: 100%;
    }
    .border-sig {
      border-top: 1px dashed #94a3b8 !important;
    }
    .info-table td {
      padding: 2px 0;
      vertical-align: top;
      font-size: 10px;
      line-height: 1.35;
    }
    .items-table th {
      font-size: 9px;
      font-weight: 800;
      text-align: left;
      padding: 4px 2px;
      border-bottom: 1.5px solid #cbd5e1;
      text-transform: uppercase;
    }
    .items-table td {
      padding: 4px 2px;
      vertical-align: top !important;
      font-size: 10px;
      line-height: 1.35;
      border-bottom: 1px solid #f8fafc;
    }
    .item-meta {
      font-size: 8.5px;
      margin-top: 2px;
    }
    .summary-table td {
      padding: 2.5px 0;
      font-size: 10.5px;
    }
    .grand-total-row td {
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 0;
    }
    .summary-table tr.grand-total-row.with-border-top td {
      border-top: 1px dashed #cbd5e1;
    }
    .balance-due-row td {
      font-size: 13px;
      font-weight: 800;
      border-top: 1.5px solid #b8860b;
      padding: 5px 0 2px;
    }
    .premium-box {
      background: #fdfaf2;
      border: 1px solid #f1e4c3;
      border-radius: 6px;
      padding: 6px 8px;
      margin: 6px 0;
      font-size: 9.5px;
      line-height: 1.35;
      box-shadow: 0 1px 2px rgba(0,0,0,0.01);
    }
    .receipt-title {
      border: 1px solid #f1e4c3;
      background: #fdfaf2;
      border-radius: 20px;
    }
    .footer-note {
      text-align: center !important;
      margin-top: 15px;
      font-size: 9.5px;
      line-height: 1.45;
    }
  @endif

  /* Screen Action Bar (only visible on HTML) */
  .action-bar {
    width: 100%;
    max-width: 320px;
    margin: 15px auto 0;
    text-align: center;
    display: flex;
    gap: 10px;
    justify-content: center;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    font-weight: 700;
    border-radius: 50px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 3px 5px rgba(0,0,0,0.05);
  }

  .btn-print {
    background: #b8860b;
    color: white;
  }
  .btn-print:hover {
    background: #966f0a;
    transform: translateY(-1px);
  }
  .btn-back {
    background: #475569;
    color: white;
  }
  .btn-back:hover {
    background: #334155;
    transform: translateY(-1px);
  }
</style>
</head>
<body>

  @if(!isset($is_pdf) || !$is_pdf)
    <!-- Screen Actions (Hidden on PDF) -->
    <div class="action-bar">
      <button type="button" class="btn btn-print" onclick="window.open('{{ request()->fullUrlWithQuery(['pdf' => 1]) }}', '_blank');">
        <i class="fa fa-print"></i> Print Receipt
      </button>
      <a href="{{ route('vendor.orders.list') }}" class="btn btn-back">
        <i class="fa fa-arrow-left"></i> Go to Order List
      </a>
    </div>
  @endif

  <div class="receipt-container">
    <!-- Shop Header -->
    <div class="header text-center">
      <div class="shop-title uppercase color-dark">{{ auth()->user()->shopbranch->branch_name ?? auth()->user()->shopbranch->name ?? 'Our Shop' }}</div>
      <div class="shop-sub color-muted">
        {{ auth()->user()->shopbranch->address ?? 'Branch Address' }} <br> Ph: {{ auth()->user()->shopbranch->mobile_no ?? '-----' }}
        @if(auth()->user()->shopbranch->gst_num)
          <br>GSTIN: {{ auth()->user()->shopbranch->gst_num }}
        @endif
      </div>
      @if(isset($karigar) && $karigar)
        <div class="receipt-title uppercase color-green">Order Assign</div>
      @else
        <div class="receipt-title uppercase color-gold">Order Receipt</div>
      @endif
    </div>

    <div class="divider"></div>

    <!-- Compact Two-Column Metadata details (Combines Order + Customer Details) -->
    <table class="info-table">
      <tr>
        <td width="55%" class="color-muted">Order: <strong class="color-dark">#{{ $order->order_unique }}</strong></td>
        <td class="text-right color-muted" width="45%" align="right">Date: <strong class="color-dark">{{ date('d-m-Y', strtotime($order->order_date)) }}</strong></td>
      </tr>
      @if(isset($karigar) && $karigar)
        <tr>
          <td class="color-muted">Karigar: <strong class="color-blue" style="font-size: 11px;">{{ $karigar->name }}</strong></td>
          <td class="text-right color-muted" align="right">Mob: <strong class="color-dark">{{ $karigar->phone ?? $karigar->mobile_no ?? 'N/A' }}</strong></td>
        </tr>
      @endif
      <tr>
        <td class="color-muted">{{ $partyType }}: <strong class="color-dark">{{ $partyName }}</strong></td>
        <td class="text-right color-muted" align="right">Delivery: <strong class="color-gold">{{ $order->delivery_date ? date('d-m-Y', strtotime($order->delivery_date)) : 'NOT SET' }}</strong></td>
      </tr>
      <tr>
        <td class="color-muted">Mobile: <strong class="color-dark">{{ $partyPhone }}</strong></td>
        <td class="text-right" align="right">
          @if($order->is_urgent)
            <span class="color-red" style="font-weight: bold; font-size: 9px;"><i class="fa fa-exclamation-triangle"></i> * URGENT *</span>
          @endif
        </td>
      </tr>
      @if($partyAddress && $partyAddress !== 'N/A')
        <tr>
          <td colspan="2" class="color-muted">Address: <strong class="color-dark">{{ $partyAddress }}</strong></td>
        </tr>
      @endif
    </table>

    <div class="divider"></div>

    <!-- Items Specifications - Compact Tabular Format -->
    <div class="font-bold uppercase color-muted" style="font-size: 9px; margin-bottom: 4px; letter-spacing: 0.5px;">Item Specifications:</div>
    <table class="items-table">
      <thead>
        <tr>
          <th width="38%" class="text-left">Item / Purity</th>
          <th width="10%" class="text-center" style="text-align: center;">Qty</th>
          <th width="17%" class="text-right" style="text-align: right;">Gross</th>
          <th width="17%" class="text-right" style="text-align: right;">Net</th>
          <th width="18%" class="text-right" style="text-align: right;">Fine</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $idx => $item)
          @php
            $qty = $item->qty ?: 1;
            $itemGross = (float)$item->gross_wt * $qty;
            $itemNet = (float)$item->net_wt * $qty;
            $itemFine = (float)$item->fine_wt * $qty;
            $itemWastageGm = (float)($item->wastage_per ?? 0) * $qty;

            $purityStr = $item->purity;
            if (!$purityStr && $item->touch) {
                $purityStr = $item->touch . '%';
            } elseif ($purityStr && is_numeric($purityStr)) {
                $purityStr = $purityStr . '%';
            }
          @endphp
          <tr>
            <td class="text-left">
              <span class="item-name color-dark">{{ $idx+1 }}. {{ $item->product_name }}</span>
              <div class="item-meta color-muted">
                {{ strtoupper($item->metal_type) }} &bull; {{ $purityStr }}
                @if($item->size) &bull; Sz:{{ $item->size }} @endif
                @if($itemWastageGm > 0)
                  &bull; Wst:{{ number_format($itemWastageGm, 3) }}g
                @endif
              </div>
            </td>
            <td class="text-center font-bold color-dark" style="font-size: 10.5px; text-align: center;">{{ $qty }}</td>
            <td class="text-right color-muted" style="font-size: 9.5px; text-align: right;">{{ number_format($itemGross, 3) }}g</td>
            <td class="text-right font-bold color-green" style="font-size: 9.5px; text-align: right;">{{ number_format($itemNet, 3) }}g</td>
            <td class="text-right font-bold color-blue" style="font-size: 10px; text-align: right;">{{ number_format($itemFine, 3) }}g</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="divider"></div>

    <!-- Financial Breakdown / Karigar Issue Breakdown -->
    @if(isset($latestIssue) && $latestIssue)
      <div class="font-bold uppercase color-green" style="font-size: 9px; margin-bottom: 3px;">Material Issued to Karigar:</div>
      <table class="summary-table">
        @if($latestIssue->fine_issued_gm > 0)
          <tr>
            <td class="label color-muted">Gold Issued:</td>
            <td class="value color-orange" align="right">{{ number_format($latestIssue->fine_issued_gm, 3) }} g</td>
          </tr>
        @endif
        @if($latestIssue->fine_issued_silver > 0)
          <tr>
            <td class="label color-muted">Silver Issued:</td>
            <td class="value color-dark" align="right">{{ number_format($latestIssue->fine_issued_silver, 3) }} g</td>
          </tr>
        @endif
        @if(($latestIssue->cash_issued + $latestIssue->online_issued) > 0)
          <tr>
            <td class="label color-muted">Cash/Bank Issued:</td>
            <td class="value color-green" align="right">&#8377;{{ number_format($latestIssue->cash_issued + $latestIssue->online_issued, 2) }}</td>
          </tr>
        @endif
        @if($receiptTotalGross > 0)
          <tr>
            <td class="label color-muted">Approx Target Weight:</td>
            <td class="value color-dark" align="right">{{ number_format($receiptTotalGross, 3) }} g</td>
          </tr>
        @endif
        @if($receiptAvgWastagePer > 0 || $receiptTotalWastageGm > 0)
          <tr>
            <td class="label color-muted">Allowed Wastage:</td>
            <td class="value color-dark" align="right">
              @if($receiptAvgWastagePer > 0 && $receiptTotalWastageGm > 0)
                {{ number_format($receiptAvgWastagePer, 1) }}% ({{ number_format($receiptTotalWastageGm, 3) }}g)
              @elseif($receiptAvgWastagePer > 0)
                {{ number_format($receiptAvgWastagePer, 1) }}%
              @else
                {{ number_format($receiptTotalWastageGm, 3) }}g
              @endif
            </td>
          </tr>
        @endif
        @if($receiptTotalFine > 0)
          <tr class="grand-total-row with-border-top">
            <td class="label uppercase font-bold color-green">Total Fine:</td>
            <td class="value font-bold color-green" align="right" style="font-size: 11px;">{{ number_format($receiptTotalFine, 3) }} g</td>
          </tr>
        @endif
      </table>
    @else
      <table class="summary-table">
        @if($order->total_making > 0)
          <tr>
            <td class="label color-muted">Total Making Charges:</td>
            <td class="value color-dark" align="right">&#8377;{{ number_format($order->total_making, 2) }}</td>
          </tr>
        @endif
        @if($order->misc_charges > 0)
          <tr>
            <td class="label color-muted">Other/Misc Charges:</td>
            <td class="value" align="right">&#8377;{{ number_format($order->misc_charges, 2) }}</td>
          </tr>
        @endif
        <tr class="grand-total-row {{ ($order->total_making > 0 || $order->misc_charges > 0) ? 'with-border-top' : '' }}">
          <td class="label uppercase font-bold color-dark">Total Bill Value:</td>
          <td class="value color-dark" align="right">&#8377;{{ number_format($order->total_payable, 2) }}</td>
        </tr>
        
        <!-- Advances -->
        @if($order->cash_adv > 0)
          <tr>
            <td class="label color-muted"><i class="fa fa-money-bill-wave color-green" style="font-size: 8.5px;"></i> Cash Paid:</td>
            <td class="value color-green" align="right">- &#8377;{{ number_format($order->cash_adv, 2) }}</td>
          </tr>
        @endif
        @if($order->online_adv > 0)
          <tr>
            <td class="label color-muted"><i class="fa fa-university color-blue" style="font-size: 8.5px;"></i> {{ ($order->bank ?? auth()->user()->banking)->name ?? 'Online Adv' }}:</td>
            <td class="value color-green" align="right">- &#8377;{{ number_format($order->online_adv, 2) }}</td>
          </tr>
        @endif
        @php
          $goldAdvWt = $order->getGoldAdvWeight();
          $silverAdvWt = $order->getSilverAdvWeight();
          $goldAdvCat = $order->gold_adv_category ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_category : 'kacha');
          $silverAdvCat = $order->silver_adv_category ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_category : 'kacha');
          $metalAdvCat = $order->metal_adv_category ?: 'kacha';
        @endphp
        @if($goldAdvWt > 0)
          <tr>
            <td class="label color-muted"><i class="fa fa-gem color-orange" style="font-size: 8.5px;"></i> Gold Adv ({{ ucfirst($goldAdvCat) }}):</td>
            <td class="value color-orange" align="right">- {{ number_format($goldAdvWt, 3) }}g @if($order->gold_adv_amount > 0) (&#8377;{{ number_format($order->gold_adv_amount, 2) }}) @endif</td>
          </tr>
        @endif
        @if($silverAdvWt > 0)
          <tr>
            <td class="label color-muted"><i class="fa fa-gem color-dark" style="font-size: 8.5px;"></i> Silver Adv ({{ ucfirst($silverAdvCat) }}):</td>
            <td class="value color-dark" align="right">- {{ number_format($silverAdvWt, 3) }}g @if($order->silver_adv_amount > 0) (&#8377;{{ number_format($order->silver_adv_amount, 2) }}) @endif</td>
          </tr>
        @endif
        @if(!$goldAdvWt && !$silverAdvWt && $order->metal_adv_wt > 0)
          <tr>
            <td class="label color-muted"><i class="fa fa-gem color-orange" style="font-size: 8.5px;"></i> Metal Adv ({{ ucfirst($order->metal_adv_type) }} - {{ ucfirst($metalAdvCat) }}):</td>
            <td class="value color-orange" align="right">- {{ number_format($order->metal_adv_wt, 3) }}g</td>
          </tr>
        @endif
        
        <!-- Final Balance -->
        @if($order->balance_due < 0)
        <tr class="balance-due-row">
          <td class="label uppercase font-bold color-green">Advance:</td>
          <td class="value color-green" align="right" style="font-size: 13.5px;">&#8377;{{ number_format(abs($order->balance_due), 2) }}</td>
        </tr>
        @else
        <tr class="balance-due-row">
          <td class="label uppercase font-bold color-red">Balance Due:</td>
          <td class="value color-red" align="right" style="font-size: 13.5px;">&#8377;{{ number_format($order->balance_due, 2) }}</td>
        </tr>
        @endif
      </table>
    @endif

    <!-- Remarks / Notes -->
    @if($order->remarks)
      <div class="premium-box color-muted">
        <strong class="color-gold"><i class="fa fa-info-circle"></i> Instructions:</strong>
        {{ $order->remarks }}
      </div>
    @endif

    <!-- Signatures -->
    <table width="100%" style="margin-top: 25px; border-collapse: collapse; width: 100% !important;">
      <tr>
        <td class="text-left" width="50%" style="text-align: left; vertical-align: bottom;">
          <div class="color-muted border-sig" style="width: 32mm; text-align: center; padding-top: 3px; font-size: 9px; font-weight: bold; display: inline-block;">Customer Sig</div>
        </td>
        <td class="text-right" width="50%" align="right" style="text-align: right; vertical-align: bottom;">
          <div class="color-muted border-sig" style="width: 32mm; text-align: center; padding-top: 3px; font-size: 9px; font-weight: bold; display: inline-block;">Authorized Sig</div>
        </td>
      </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-note color-muted text-center">
      Thank you for your valuable order!<br>
      Please preserve this receipt.<br>
      @if(str_contains(str_replace(' ', '', strtolower(auth()->user()->shopbranch->branch_name ?? '')), 'mgjewel'))
      <strong class="color-gold" style="font-size: 9.5px; letter-spacing: 0.5px; display: block; margin-top: 3px;">JAI SHREE MAHAKAL</strong>
      @endif
    </div>
  </div>

</body>
</html>
