@php
    $totalGross = 0;
    $totalFine = 0;
    $totalMakingSum = 0;
    $totalLabourSum = 0;
    $processOrders = isset($orders) && $orders->count() > 0 ? $orders : [];
@endphp

<tbody id="data_area">
@if(count($processOrders) > 0)
    @foreach($processOrders as $order)
        @php
            $items = $order->items;
            $firstItem = $items->first();

            $orderGross = 0;
            $orderFine = 0;
            $orderLess = 0;
            $orderNet = 0;
            $readyGross = 0;

            // Karigar Labour Calculations
            $orderLabourTotal = 0;
            $hasStockLabour = false;
            $primaryLabourRate = 0;
            $primaryLabourUnit = '/g';

            foreach ($items as $itm) {
                $stk = $itm->inventoryStock;
                $g = (float)($stk->gross ?? $itm->gross_wt ?? 0);
                $l = (float)($stk->less ?? $itm->less_wt ?? 0);
                $n = (float)($stk->net ?? $itm->net_wt ?? 0);
                $f = (float)($stk->fine ?? $itm->fine_wt ?? 0);

                $orderGross += $g;
                $orderLess += $l;
                $orderNet += $n;
                $orderFine += $f;

                if (in_array($itm->status, ['received', 'completed', 'delivered'])) {
                    $readyGross += $g;
                }

                if ($stk && !empty($stk->labour)) {
                    $lbrVal = (float)$stk->labour;
                    $lbrUnit = $stk->labour_unit ?? 'g';
                    $unitStr = ($lbrUnit === 'r') ? 'Flat' : (($lbrUnit === 'p') ? '%' : '/g');
                    if ($lbrVal > 0) {
                        $hasStockLabour = true;
                        if ($primaryLabourRate == 0) {
                            $primaryLabourRate = $lbrVal;
                            $primaryLabourUnit = $unitStr;
                        }
                        if ($lbrUnit === 'r') {
                            $orderLabourTotal += $lbrVal;
                        } elseif ($lbrUnit === 'p') {
                            $cost = (float)($stk->total ?? 0);
                            $orderLabourTotal += ($cost * $lbrVal) / 100;
                        } else {
                            $orderLabourTotal += $lbrVal * $g;
                        }
                    }
                }
            }

            // Customer Making Calculations (taking actual charge from inventory stock if available)
            $calculatedMakingTotal = 0;
            $hasStockCharge = false;
            foreach ($items as $itm) {
                $stk = $itm->inventoryStock;
                $gross = (float)($stk->gross ?? $itm->gross_wt ?? 0);
                
                if ($stk && (float)($stk->charge ?? 0) > 0) {
                    $hasStockCharge = true;
                    $calculatedMakingTotal += (float)$stk->charge;
                } else {
                    $mCharge = (float)($itm->making_charge ?? 0);
                    $mType = $itm->making_type ?? 'per_gram';
                    if ($mType === 'fixed') {
                        $calculatedMakingTotal += $mCharge * ($itm->qty ?? 1);
                    } elseif ($mType === 'percentage') {
                        $rate = (float)($order->metal_rate ?? 0);
                        $calculatedMakingTotal += (($gross * $rate) * $mCharge) / 100;
                    } else {
                        $calculatedMakingTotal += $mCharge * $gross;
                    }
                }
            }
            $orderMakingAmount = ($hasStockCharge || $calculatedMakingTotal > 0) ? $calculatedMakingTotal : (float)($order->total_making ?? 0);
            
            $firstMakingType = $firstItem ? ($firstItem->making_type ?? 'per_gram') : 'per_gram';
            $makingUnitLabel = ($firstMakingType === 'fixed') ? 'Flat' : (($firstMakingType === 'percentage') ? '%' : '/g');
            
            if ($firstItem && (float)($firstItem->making_charge ?? 0) > 0) {
                $makingRate = (float)$firstItem->making_charge;
            } elseif ($orderMakingAmount > 0 && $orderGross > 0) {
                $makingRate = round($orderMakingAmount / $orderGross, 2);
                $makingUnitLabel = '/g';
            } elseif ($orderMakingAmount > 0) {
                $makingRate = $orderMakingAmount;
                $makingUnitLabel = 'Flat';
            } else {
                $makingRate = (float)($order->making_rate ?? 0);
            }

            $totalGross += $orderGross;
            $totalFine += $orderFine;
            $totalMakingSum += $orderMakingAmount;
            $totalLabourSum += $orderLabourTotal;

            $deliveryDate = \Carbon\Carbon::parse($order->delivery_date ?? $order->order_date);
            $daysLeft = now()->diffInDays($deliveryDate, false);
            $deliveryClass = $daysLeft <= 2 ? 'text-danger' : ($daysLeft <= 5 ? 'text-warning' : 'text-success');

            $totalItems = $items->count();
            $readyItems = $items->whereIn('status', ['received', 'completed']);
            $deliveredItems = $items->where('status', 'delivered');
            $inProcessItems = $items->whereIn('status', ['in_process', 'pending']);
            $readyCount = $readyItems->count();
            $deliveredCount = $deliveredItems->count();
            $inProcessCount = $inProcessItems->count();
            $isAllDelivered = ($totalItems > 0 && $deliveredCount === $totalItems);
            $isAllReady = ($totalItems > 0 && ($readyCount === $totalItems || $order->status == 2));
            $isMulti = ($totalItems > 1);
            $isPartial = ($readyCount > 0 && $readyCount < $totalItems);

            $isHighlighted = (request('highlight_order_id') == $order->id);
        @endphp
        <tr class="{{ $isHighlighted ? 'highlighted-order-row' : '' }}" id="order-row-{{ $order->id }}">
            <!-- Column 1: S.No & Order ID -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    @if($isHighlighted)
                        <span class="badge badge-success px-1.5 py-0.5 mb-1 fw-800" style="font-size: 0.55rem; letter-spacing: 0.3px; background: #16a34a;">
                            <i class="fa fa-star mr-0.5"></i> JUST READY
                        </span>
                    @endif
                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-800 text-muted mb-1"
                        style="width: 25px; height: 25px; background: #ebf4ff; border: 1.2px solid #cbd5e0; font-size: 0.65rem;">
                        {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                    </div>
                    <div class="fw-800 text-dark" style="font-size: 0.68rem; white-space: nowrap; letter-spacing: 0.2px;">
                        #{{ $order->order_unique }}
                    </div>
                </div>
            </td>

            <!-- Column 2: Dates (Order Date & Delivery Date) -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="fw-900 text-dark mb-1" style="font-size: 0.8rem; line-height: 1.2;">
                        {{ date('d-M-Y', strtotime($order->order_date)) }}
                        <span class="small text-muted fw-700" style="font-size: 0.65rem; display: block; margin-top: 1px;">{{ $order->created_at->format('h:i A') }}</span>
                    </div>
                    <div style="font-size: 0.7rem; line-height: 1.2; margin-top: 3px;">
                        <span class="text-muted fw-700" style="font-size: 0.55rem; text-transform: uppercase;">Delivery:</span>
                        <span class="fw-800 {{ $deliveryClass }}">{{ date('d-M-Y', strtotime($order->delivery_date)) }}</span>
                    </div>
                </div>
            </td>

            <!-- Column 3: Customer Details -->
            <td class="text-left" style="padding-left: 12px !important; max-width: 160px; word-break: break-word;">
                <div class="fw-900 text-primary" style="font-size: 0.8rem; line-height: 1.15;">
                    @if($order->custo_type === 's')
                        {{ $order->party->supplier_name ?? $order->party->name ?? $order->custo_name ?? 'N/A' }}
                    @else
                        {{ $order->party->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer' }}
                    @endif
                </div>
                <div class="small text-muted fw-700 " style="font-size: 0.7rem; margin-top: 1px;">
                    <i class="fa fa-phone-alt mr-1" style="font-size: 0.55rem;"></i> 
                    {{ ($order->custo_type === 's') ? ($order->party->mobile_no ?? $order->custo_phone ?? '') : ($order->party->custo_fone ?? $order->custo_phone ?? '') }}
                </div>
            </td>

            <!-- Column 4: Product & Details -->
            <td class="text-left" style="padding-left: 12px !important; max-width: 210px; word-break: break-word;">
                <div class="d-flex align-items-center">
                    @if($order->design_image)
                        <div class="mr-2"
                            style="width: 34px; height: 34px; border-radius: 6px; overflow: hidden; border: 1.5px solid #eef2ff; box-shadow: 0 2px 4px rgba(0,0,0,0.08); flex-shrink: 0;">
                            <img src="{{ asset($order->design_image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endif
                    <div>
                        @if($isMulti)
                            <div class="custom-tooltip-wrapper" style="margin-bottom: 2px;">
                                <div class="badge rounded-pill fw-900"
                                    style="font-size: 0.58rem; padding: 1.5px 7px; background: #e6fffa; border: 1.2px solid #f97316; color: #ea580c; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                    <span>{{ $totalItems }} ITEMS</span>
                                    <i class="fa fa-info-circle text-danger" style="font-size: 0.55rem;"></i>
                                </div>
                                <div class="tooltip-content">
                                    <div class="fw-800 text-warning mb-2" style="font-size: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 4px;">
                                        <i class="fa fa-list mr-1"></i> Inventory Stock Details:
                                    </div>
                                    @foreach($items as $item)
                                        @php
                                            $stk = $item->inventoryStock;
                                            $itemMetal = strtolower($stk->stock_type ?? $item->metal_type ?? 'gold');
                                            $isGold = strpos($itemMetal, 'gold') !== false || stripos($item->product_name, 'gold') !== false;
                                            $isSilver = strpos($itemMetal, 'silver') !== false || stripos($item->product_name, 'silver') !== false;
                                            $metalTag = $isSilver ? 'SILVER' : ($isGold ? 'GOLD' : ucfirst($itemMetal));
                                            
                                            if ($stk && !empty($stk->tunch)) {
                                                $purityTag = ($stk->caret ? $stk->caret.'K ' : '') . number_format($stk->tunch, 1) . '%';
                                            } elseif ($stk && !empty($stk->caret)) {
                                                $purityTag = $stk->caret.'K';
                                            } else {
                                                $purityTag = $item->purity ?: ($item->touch ? $item->touch.'%' : '');
                                            }

                                            $itmGross = (float)($stk->gross ?? $item->gross_wt ?? 0);
                                            $itmLess = (float)($stk->less ?? $item->less_wt ?? 0);
                                            $itmNet = (float)($stk->net ?? $item->net_wt ?? 0);
                                            $itmFine = (float)($stk->fine ?? $item->fine_wt ?? 0);

                                            $itemStatus = $item->status ?? 'pending';
                                            $itemStatusText = 'READY (In Stock)';
                                            $itemStatusColor = '#48bb78';
                                            if ($itemStatus === 'delivered') {
                                                $itemStatusText = 'DELIVERED';
                                                $itemStatusColor = '#a0aec0';
                                            } elseif ($itemStatus === 'in_process' || $itemStatus === 'pending') {
                                                $itemStatusText = 'WITH KARIGAR';
                                                $itemStatusColor = '#ed8936';
                                            }
                                        @endphp
                                        <div class="mb-2 pb-1" style="border-bottom: 1px dashed rgba(255,255,255,0.15);">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-800 text-white" style="font-size: 0.75rem;">{{ $stk->name ?? $item->product_name }}</span>
                                                <span style="font-size: 0.62rem; font-weight: 800; color: {{ $itemStatusColor }};">[{{ $itemStatusText }}]</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.68rem;">
                                                <span class="badge" style="background: rgba(255,255,255,0.15); color: #fed7aa; font-size: 0.62rem; padding: 1px 5px;">
                                                    {{ $metalTag }} • {{ $purityTag }}
                                                </span>
                                                <span class="fw-700 text-light">Gross: <b class="text-white">{{ number_format($itmGross, 3) }}g</b></span>
                                            </div>
                                            @if($itmLess > 0)
                                                <div class="d-flex align-items-center justify-content-between text-muted mt-0.5" style="font-size: 0.63rem;">
                                                    <span>Less: {{ number_format($itmLess, 3) }}g</span>
                                                    <span>Net: {{ number_format($itmNet, 3) }}g | Fine: {{ number_format($itmFine, 3) }}g</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center justify-content-between text-muted mt-0.5" style="font-size: 0.63rem;">
                                                    <span>Fine: {{ number_format($itmFine, 3) }}g</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">
                                {{ $firstItem->inventoryStock->name ?? $firstItem->product_name }}...
                            </div>
                            <div style="font-size: 0.7rem; color: #4a5568; line-height: 1.2;">
                                <span class="fw-700 text-muted">Qty:</span> <span class="fw-800">{{ $items->sum('qty') }}</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fw-700 text-muted">Recv:</span> <span class="fw-800 text-success">{{ $readyCount }}</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fw-700 text-muted">Pend:</span> <span class="fw-800 text-warning">{{ $inProcessCount }}</span>
                            </div>
                        @else
                            @php
                                $firstStock = $firstItem ? $firstItem->inventoryStock : null;
                                $displayName = $firstStock->name ?? $firstItem->product_name ?? 'N/A';
                                $itemMetal = strtolower($firstStock->stock_type ?? $firstItem->metal_type ?? 'gold');
                                $isSilver = strpos($itemMetal, 'silver') !== false || stripos($displayName, 'silver') !== false;

                                if ($firstStock && !empty($firstStock->tunch)) {
                                    $purityStr = ($firstStock->caret ? $firstStock->caret.'K ' : '') . number_format($firstStock->tunch, 1) . '%';
                                } elseif ($firstStock && !empty($firstStock->caret)) {
                                    $purityStr = $firstStock->caret.'K';
                                } else {
                                    $purityStr = $firstItem->purity ?: ($firstItem->touch ? $firstItem->touch.'%' : 'N/A');
                                }
                            @endphp
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">{{ $displayName }}</div>
                            <div style="font-size: 0.7rem; color: #4a5568; line-height: 1.2;">
                                <span class="badge px-1 py-0 mr-1" style="font-size: 0.62rem; background: {{ $isSilver ? '#f1f5f9' : '#fef3c7' }}; color: {{ $isSilver ? '#334155' : '#92400e' }}; border: 1px solid {{ $isSilver ? '#cbd5e1' : '#fde68a' }};">
                                    {{ $isSilver ? 'SILVER' : 'GOLD' }}
                                </span>
                                <span class="fw-700 text-muted">Purity:</span> <span class="fw-800 text-indigo">{{ $purityStr }}</span>
                                @if($firstItem && $firstItem->size)
                                    <span class="text-muted mx-1">|</span> <span class="fw-700 text-muted">Size:</span> <span class="fw-800">{{ $firstItem->size }}</span>
                                @endif
                                <span class="text-muted mx-1">|</span> <span class="fw-700 text-muted">Qty:</span> <span class="fw-800">{{ $items->sum('qty') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </td>

            <!-- Column 5: Karigar -->
            <td class="text-left" style="padding-left: 12px !important; max-width: 150px; word-break: break-word;">
                <div class="fw-900 text-primary" style="font-size: 0.8rem; line-height: 1.15;">
                    {{ $order->karigar->name ?? ($order->latestIssue->karigar->name ?? 'Artisan') }}
                </div>
                <div class="small text-muted fw-700 " style="font-size: 0.7rem; margin-top: 1px;">
                    <i class="fa fa-phone-alt mr-1" style="font-size: 0.55rem;"></i> 
                    {{ $order->karigar->phone ?? $order->karigar->mobile_no ?? ($order->latestIssue->karigar->mobile_no ?? '') }}
                </div>
            </td>

            <!-- Column 6: Returned Wt / Fine (Actual Purchased Inventory Data) -->
            <td class="text-center">
                <div class="fw-900 text-danger" style="font-size: 0.9rem;">{{ number_format($orderGross, 3) }} <small>gm</small></div>
                <div class="small text-success fw-900" style="font-size: 0.7rem;">Fine: {{ number_format($orderFine, 3) }}g</div>
                @if($orderLess > 0)
                    <div class="small text-muted fw-700" style="font-size: 0.65rem; line-height: 1.1; margin-top: 2px;">
                        Net: {{ number_format($orderNet, 3) }}g <span class="text-danger">(Less: {{ number_format($orderLess, 3) }}g)</span>
                    </div>
                @endif
                @if($isPartial)
                    <div class="badge badge-soft-info border border-info fw-700 mt-1" style="font-size: 0.58rem; background: #ebf8ff; color: #2b6cb0;">
                        Ready: {{ number_format($readyGross, 3) }}g
                    </div>
                @endif
            </td>

            <!-- Column 7: Ghat Allowance -->
            <td class="text-center fw-900 text-muted" style="font-size: 0.9rem;">
                {{ number_format($order->ghat_allowance ?? 0, 1) }}%
            </td>

            <!-- Column 8: Making (Charged to Customer) -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="fw-900 text-dark" style="font-size: 0.88rem; line-height: 1.2;">
                        ₹{{ number_format($orderMakingAmount, 0) }}
                    </div>
                    <div class="small text-muted fw-700" style="font-size: 0.68rem; margin-top: 2px;">
                        ₹{{ number_format($makingRate, 0) }}<small class="text-muted fw-600">{{ $makingUnitLabel }}</small>
                    </div>
                </div>
            </td>

            <!-- Column 9: Labour Paid (Paid to Karigar) -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="fw-900 text-dark" style="font-size: 0.88rem; line-height: 1.2;">
                        @if($hasStockLabour && $orderLabourTotal > 0)
                            ₹{{ number_format($orderLabourTotal, 0) }}
                        @else
                            ₹0
                        @endif
                    </div>
                    <div class="small text-muted fw-700" style="font-size: 0.68rem; margin-top: 2px;">
                        @if($hasStockLabour)
                            ₹{{ number_format($primaryLabourRate, 0) }}<small class="text-muted fw-600">{{ $primaryLabourUnit }}</small>
                        @else
                            ₹0<small class="text-muted fw-600">/g</small>
                        @endif
                    </div>
                </div>
            </td>

            <!-- Column 10: Action -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    @if($isAllDelivered || $order->status == 3)
                        <span class="badge badge-soft-dark border border-dark fw-800 px-2 py-0.5" style="font-size: 0.6rem; line-height: 1.2; background-color: #edf2f7; color: #2d3748; border-color: #cbd5e0 !important;">
                            <i class="fa fa-check mr-1" style="font-size: 0.55rem;"></i> DELIVERED
                        </span>
                    @else
                        <span class="badge badge-soft-success border border-success fw-800 px-2 py-0.5 mb-1" style="font-size:0.6rem; line-height: 1.2; background-color: #f0fdf4; color: #166534; border-color: #86efac !important;">
                            <i class="fa fa-gift mr-1" style="font-size: 0.55rem;"></i> READY
                        </span>
                        <a href="{{ route('billing', ['type' => 'sale', 'order_id' => $order->id]) }}" class="btn btn-xs btn-primary rounded-pill px-3 fw-700 shadow-sm" style="font-size: 0.7rem;"><i class="fa fa-file-invoice mr-1"></i> Make Bill</a>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="10" class="text-center py-5">
            <div class="d-flex flex-column align-items-center py-4">
                <i class="fa fa-box-open mb-3 text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted fw-800 mb-0">No orders found matching the filter.</p>
            </div>
        </td>
    </tr>
@endif
</tbody>

@if(count($processOrders) > 0)
    <tfoot>
        <tr>
            <td colspan="5" class="text-right py-3">
                <span class="fw-800 text-muted uppercase" style="font-size: 0.8rem;">Page Totals :-</span>
            </td>
            <td class="text-center py-2">
                <div class="fw-900 text-danger" style="font-size: 0.95rem;">{{ number_format($totalGross, 3) }} <small>gm</small></div>
                <div class="small text-success fw-800">Fine: {{ number_format($totalFine, 3) }}</div>
            </td>
            <td></td>
            <td class="text-center py-2">
                <div class="fw-900 text-dark" style="font-size: 0.9rem;">₹{{ number_format($totalMakingSum, 0) }}</div>
                <div class="small text-muted fw-700" style="font-size: 0.65rem;">Total Making</div>
            </td>
            <td class="text-center py-2">
                <div class="fw-900 text-dark" style="font-size: 0.9rem;">₹{{ number_format($totalLabourSum, 0) }}</div>
                <div class="small text-muted fw-700" style="font-size: 0.65rem;">Total Labour</div>
            </td>
            <td></td>
        </tr>
    </tfoot>
@endif
