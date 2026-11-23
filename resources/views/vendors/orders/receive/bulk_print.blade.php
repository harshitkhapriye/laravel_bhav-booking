@php
    $totalGrossBatch = 0;
    $totalLessBatch = 0;
    $totalNetBatch = 0;
    $totalFineBatch = 0;
    $totalPayableBatch = 0;
    $totalAdvanceBatch = 0;
    $totalBalanceBatch = 0;
    $totalQtyBatch = 0;
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Batch Orders Printing Summary</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* ===== A4 LANDSCAPE PAGE SETUP ===== */
  @page {
    size: A4 landscape;
    margin: 8mm 10mm;
  }
  
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  body {
    font-family: "Outfit", sans-serif;
    font-size: 11px;
    color: #1e293b;
    background: #fff;
    line-height: 1.3;
  }

  .print-container {
    width: 100%;
    max-width: 297mm; /* A4 Landscape width */
    margin: 0 auto;
    box-sizing: border-box;
  }

  /* ===== Header Styling ===== */
  header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2.5px solid #b8860b;
    padding-bottom: 8px;
    margin-bottom: 12px;
  }

  .brand-logo {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .brand-logo img {
    max-height: 50px;
    width: auto;
  }

  .shop-details {
    text-align: left;
  }

  .shop-details h1 {
    margin: 0;
    font-size: 18px;
    color: #b8860b;
    font-weight: 800;
    letter-spacing: 0.5px;
  }

  .shop-details p {
    margin: 2px 0;
    font-size: 9.5px;
    color: #475569;
    font-weight: 500;
  }

  .report-title {
    text-align: right;
  }

  .report-title h2 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 0.3px;
  }

  .report-title p {
    margin: 2px 0;
    font-size: 9.5px;
    color: #64748b;
    font-weight: 600;
  }

  /* ===== Table Styling ===== */
  .batch-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin-bottom: 15px;
  }

  .batch-table th {
    background-color: #f8fafc !important;
    border-top: 1.5px solid #cbd5e0 !important;
    border-bottom: 2px solid #cbd5e0 !important;
    border-left: 1px solid #e2e8f0 !important;
    border-right: 1px solid #e2e8f0 !important;
    color: #0f172a !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    font-size: 8.5px;
    padding: 6px 4px;
    text-align: center;
    letter-spacing: 0.2px;
  }

  .batch-table td {
    border-bottom: 1px solid #e2e8f0 !important;
    border-left: 1.2px solid #f1f5f9 !important;
    border-right: 1.2px solid #f1f5f9 !important;
    padding: 6px 5px;
    vertical-align: top;
    font-size: 10px;
    color: #334155;
    font-weight: 500;
  }

  .batch-table tbody tr:nth-child(even) td {
    background-color: #f8fafc;
  }

  .batch-table tfoot td {
    background-color: #f1f5f9 !important;
    border-top: 2px solid #cbd5e0 !important;
    border-bottom: 2px solid #cbd5e0 !important;
    font-weight: 800;
    font-size: 10.5px;
    color: #0f172a;
    padding: 8px 5px;
    vertical-align: middle;
  }

  /* Text alignments */
  .text-left { text-align: left !important; }
  .text-right { text-align: right !important; }
  .text-center { text-align: center !important; }
  .font-bold { font-weight: 700 !important; }
  
  /* Badges */
  .badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 50px;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
    text-align: center;
    line-height: 1;
  }
  .badge-pending { background-color: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
  .badge-process { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
  .badge-completed { background-color: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
  .badge-partial { background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
  .badge-delivered { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

  /* Nested Item Box Styling */
  .item-box {
    margin-bottom: 4px;
    padding-bottom: 4px;
    border-bottom: 1px dashed #e2e8f0;
  }
  .item-box:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
  }
  .item-name {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1px;
  }
  .item-specs {
    font-size: 9px;
    color: #64748b;
    font-weight: 600;
  }

  /* Financial info styling */
  .fin-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
    font-size: 9.5px;
  }
  .fin-row:last-child {
    margin-bottom: 0;
  }
  .fin-label {
    color: #64748b;
    font-weight: 600;
  }
  .fin-val {
    font-weight: 700;
    color: #0f172a;
  }

  /* Print controls */
  .print-btn-container {
    text-align: center;
    margin-top: 15px;
    padding: 10px;
  }
  .print-btn {
    background: #b8860b;
    color: white;
    border: none;
    padding: 8px 25px;
    font-size: 12px;
    font-weight: 800;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(184, 134, 11, 0.2);
    transition: all 0.2s;
  }
  .print-btn:hover {
    background: #966f0a;
    transform: translateY(-1px);
  }

  @media print {
    .print-btn-container {
      display: none !important;
    }
    body {
      background: #white;
    }
  }
</style>
</head>
<body>
  <div class="print-container">
    <header>
      <div class="brand-logo">
        @php 
          $img_path = auth()->user()->shopbranch->logo_path ?: auth()->user()->shopbranch->image;
        @endphp
        @if($img_path)
          <img src="{{ asset($img_path) }}" alt="Shop Logo">
        @endif
        <div class="shop-details">
          <h1>{{ auth()->user()->shopbranch->branch_name }}</h1>
          <p>{{ auth()->user()->shopbranch->address ?? 'N/A' }}</p>
          <p><i class="fa fa-phone-alt" style="font-size: 8px;"></i> {{ auth()->user()->shopbranch->mobile_no ?? 'N/A' }} | GSTIN: {{ auth()->user()->shopbranch->gst_num ?? 'N/A' }}</p>
        </div>
      </div>
      <div class="report-title">
        <h2>BATCH ORDERS PRINT SUMMARY</h2>
        @if(str_contains(str_replace(' ', '', strtolower(auth()->user()->shopbranch->branch_name ?? '')), 'mgjewel'))
        <p>Jai Shree Mahakal</p>
        @endif
        <p>Date: {{ date('d-M-Y h:i A') }}</p>
      </div>
    </header>

    <table class="batch-table">
      <thead>
        <tr>
          <th width="3%">S.N</th>
          <th width="10%">Order ID & Date</th>
          <th width="15%" class="text-left">Customer Details</th>
          <th class="text-left">Ordered Items & Purity / Weights</th>
          <th width="14%">Summary Weights</th>
          <th width="16%">Financial Details</th>
          <th width="8%">Delivery</th>
          <th width="8%">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $index => $order)
          @php
            $orderGross = $order->items->sum(function($item) {
                return (float)$item->gross_wt * ($item->qty ?: 1);
            });
            $orderLess = $order->items->sum(function($item) {
                return (float)$item->less_wt * ($item->qty ?: 1);
            });
            $orderNet = $order->items->sum(function($item) {
                return (float)$item->net_wt * ($item->qty ?: 1);
            });
            $orderFine = $order->items->sum(function($item) {
                return (float)$item->fine_wt * ($item->qty ?: 1);
            });
            $orderQty = $order->items->sum('qty');
            $orderAdvance = (float)$order->cash_adv + (float)$order->online_adv;

            $totalGrossBatch += $orderGross;
            $totalLessBatch += $orderLess;
            $totalNetBatch += $orderNet;
            $totalFineBatch += $orderFine;
            $totalPayableBatch += (float)$order->total_payable;
            $totalAdvanceBatch += $orderAdvance;
            $totalBalanceBatch += (float)$order->balance_due;
            $totalQtyBatch += $orderQty;

            // Compute Status badge
            $totalItems = $order->items->count();
            $inProcessCount = $order->items->where('status', 'in_process')->count();
            $completedCount = $order->items->whereIn('status', ['completed', 'received'])->count();
            $pendingCount = $order->items->filter(function($item) {
                return !in_array($item->status, ['in_process', 'completed', 'received']);
            })->count();
            
            $computedStatus = 'pending';
            $badgeClass = 'badge-pending';
            if ($order->status == 3) {
                $computedStatus = 'delivered';
                $badgeClass = 'badge-delivered';
            } elseif ($completedCount === $totalItems) {
                $computedStatus = 'completed';
                $badgeClass = 'badge-completed';
            } elseif ($inProcessCount === $totalItems) {
                $computedStatus = 'in_process';
                $badgeClass = 'badge-process';
            } elseif ($pendingCount === $totalItems) {
                $computedStatus = 'pending';
                $badgeClass = 'badge-pending';
            } else {
                $computedStatus = 'partial';
                $badgeClass = 'badge-partial';
            }
          @endphp
          <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-center font-bold">
              <span style="color: #2563eb;">{{ $order->order_unique }}</span>
              <div style="font-size: 8.5px; color: #64748b; font-weight: 600; margin-top: 3px;">
                {{ date('d-M-Y', strtotime($order->order_date)) }}
              </div>
            </td>
            <td class="text-left">
              <div class="font-bold" style="color: #0f172a; font-size: 10.5px;">
                @if($order->custo_type === 's')
                  {{ $order->party->supplier_name ?? $order->party->name ?? $order->custo_name ?? 'N/A' }}
                @else
                  {{ $order->party->custo_full_name ?? $order->custo_name ?? 'Walk-in' }}
                @endif
              </div>
              <div style="font-size: 9px; color: #64748b; font-weight: 600; margin-top: 2px;">
                <i class="fa fa-phone-alt" style="font-size: 8px;"></i> 
                {{ ($order->custo_type === 's') ? ($order->party->mobile_no ?? $order->custo_phone ?? 'N/A') : ($order->party->custo_fone ?? $order->custo_phone ?? 'N/A') }}
              </div>
            </td>
            <td class="text-left">
              @foreach($order->items as $item)
                <div class="item-box">
                  <div class="item-name">{{ $item->product_name }}</div>
                  <div class="item-specs">
                    Purity/Touch: <strong style="color: #4f46e5;">{{ $item->purity ?? 'N/A' }} ({{ $item->touch ?? 0 }})</strong>
                    @if($item->size) | Size: <strong>{{ $item->size }}</strong> @endif
                    | Qty: <strong>{{ $item->qty }}</strong>
                  </div>
                  <div class="item-specs" style="color: #475569; margin-top: 1px;">
                    Gross: {{ number_format($item->gross_wt, 3) }}g 
                    | Less: {{ number_format($item->less_wt, 3) }}g 
                    | Net: {{ number_format($item->net_wt, 3) }}g 
                    | Wastage: {{ number_format($item->wastage_per, 3) }}g
                    | Fine: {{ number_format($item->fine_wt, 3) }}g
                  </div>
                </div>
              @endforeach
            </td>
            <td>
              <div class="fin-row">
                <span class="fin-label">Gross Wt:</span>
                <span class="fin-val text-danger">{{ number_format($orderGross, 3) }}g</span>
              </div>
              <div class="fin-row">
                <span class="fin-label">Less Wt:</span>
                <span class="fin-val">{{ number_format($orderLess, 3) }}g</span>
              </div>
              <div class="fin-row">
                <span class="fin-label">Net Wt:</span>
                <span class="fin-val text-success">{{ number_format($orderNet, 3) }}g</span>
              </div>
              <div class="fin-row" style="border-top: 1px dashed #e2e8f0; padding-top: 2px; margin-top: 2px;">
                <span class="fin-label">Fine Wt:</span>
                <span class="fin-val text-primary">{{ number_format($orderFine, 3) }}g</span>
              </div>
            </td>
            <td>
              <div class="fin-row">
                <span class="fin-label">Total Payable:</span>
                <span class="fin-val">₹{{ number_format($order->total_payable, 2) }}</span>
              </div>
              <div class="fin-row">
                <span class="fin-label">Cash/Online Adv:</span>
                <span class="fin-val text-success">₹{{ number_format($orderAdvance, 2) }}</span>
              </div>
              @php
                $goldAdvWt = $order->getGoldAdvWeight();
                $silverAdvWt = $order->getSilverAdvWeight();
                $goldAdvCat = $order->gold_adv_category ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_category : 'kacha');
                $silverAdvCat = $order->silver_adv_category ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_category : 'kacha');
                $metalAdvCat = $order->metal_adv_category ?: 'kacha';
              @endphp
              @if($goldAdvWt > 0)
                <div class="fin-row">
                  <span class="fin-label">Gold Adv ({{ ucfirst($goldAdvCat) }}):</span>
                  <span class="fin-val text-warning">{{ number_format($goldAdvWt, 3) }}g</span>
                </div>
              @endif
              @if($silverAdvWt > 0)
                <div class="fin-row">
                  <span class="fin-label">Silver Adv ({{ ucfirst($silverAdvCat) }}):</span>
                  <span class="fin-val" style="color: #475569;">{{ number_format($silverAdvWt, 3) }}g</span>
                </div>
              @endif
              @if(!$goldAdvWt && !$silverAdvWt && $order->metal_adv_wt > 0)
                <div class="fin-row">
                  <span class="fin-label">Metal Adv ({{ strtoupper($order->metal_adv_type) }} - {{ ucfirst($metalAdvCat) }}):</span>
                  <span class="fin-val text-warning">{{ number_format($order->metal_adv_wt, 3) }}g</span>
                </div>
              @endif
              <div class="fin-row" style="border-top: 1px dashed #e2e8f0; padding-top: 2px; margin-top: 2px;">
                <span class="fin-label">Balance Due:</span>
                <span class="fin-val font-bold {{ $order->balance_due < 0 ? 'text-success' : 'text-danger' }}">
                  {{ $order->balance_due < 0 ? 'Adv: +₹' . number_format(abs($order->balance_due), 2) : 'Bal: ₹' . number_format($order->balance_due, 2) }}
                </span>
              </div>
            </td>
            <td class="text-center font-bold" style="font-size: 9.5px;">
              {{ $order->delivery_date ? date('d-M-Y', strtotime($order->delivery_date)) : '-' }}
            </td>
            <td class="text-center">
              <span class="badge {{ $badgeClass }}">{{ $computedStatus }}</span>
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="text-right font-bold">BATCH TOTALS ({{ $orders->count() }} Orders):</td>
          <td class="text-left font-bold" style="font-size: 10px;">Total Qty: {{ $totalQtyBatch }} Items</td>
          <td>
            <div class="fin-row" style="font-size: 10px;">
              <span class="fin-label">Gross Wt:</span>
              <span class="fin-val text-danger">{{ number_format($totalGrossBatch, 3) }}g</span>
            </div>
            <div class="fin-row" style="font-size: 10px;">
              <span class="fin-label">Less Wt:</span>
              <span class="fin-val">{{ number_format($totalLessBatch, 3) }}g</span>
            </div>
            <div class="fin-row" style="font-size: 10px;">
              <span class="fin-label">Net Wt:</span>
              <span class="fin-val text-success">{{ number_format($totalNetBatch, 3) }}g</span>
            </div>
            <div class="fin-row" style="font-size: 10px; border-top: 1px solid #cbd5e0; padding-top: 1px; margin-top: 1px;">
              <span class="fin-label">Fine Wt:</span>
              <span class="fin-val text-primary">{{ number_format($totalFineBatch, 3) }}g</span>
            </div>
          </td>
          <td>
            <div class="fin-row" style="font-size: 10px;">
              <span class="fin-label">Total Payable:</span>
              <span class="fin-val">₹{{ number_format($totalPayableBatch, 2) }}</span>
            </div>
            <div class="fin-row" style="font-size: 10px;">
              <span class="fin-label">Total Advance:</span>
              <span class="fin-val text-success">₹{{ number_format($totalAdvanceBatch, 2) }}</span>
            </div>
            <div class="fin-row" style="font-size: 10px; border-top: 1px solid #cbd5e0; padding-top: 1px; margin-top: 1px;">
              <span class="fin-label">Total Balance:</span>
              <span class="fin-val {{ $totalBalanceBatch < 0 ? 'text-success' : 'text-danger' }}">
                {{ $totalBalanceBatch < 0 ? '+₹' . number_format(abs($totalBalanceBatch), 2) : '₹' . number_format($totalBalanceBatch, 2) }}
              </span>
            </div>
          </td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>

    <div style="display: flex; justify-content: space-between; margin-top: 30px; padding: 0 10px;">
      <div style="border-top: 1.2px solid #cbd5e0; width: 150px; text-align: center; padding-top: 5px; font-weight: 700; color: #475569; font-size: 9.5px;">
        Authorized Signatory
      </div>
      <div style="border-top: 1.2px solid #cbd5e0; width: 150px; text-align: center; padding-top: 5px; font-weight: 700; color: #475569; font-size: 9.5px;">
        Customer Signatures
      </div>
    </div>
  </div>

  <div class="print-btn-container">
    <button type="button" class="print-btn" onclick="window.print();"><i class="fa fa-print mr-1"></i> Click to Print Report</button>
  </div>
</body>
</html>
