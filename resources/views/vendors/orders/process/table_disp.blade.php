@php
    $totalGross = 0;
    $totalFine = 0;
    $renderedCount = 0;
    
    $processOrders = isset($orders) && $orders->count() > 0 ? $orders : [];
@endphp

<tbody id="data_area">
@if(count($processOrders) > 0)
    @foreach($processOrders as $order)
         @php
            $issue = $order; // $order is a JewelryOrderIssue grouped record here!
            $parentOrder = $issue->order;
            $karigar = $issue->karigar;
            
            // Get all active issues for this order assigned to this specific Karigar
            $karigarIssuesForThisGroup = $parentOrder->karigarIssues->where('karigar_id', $issue->karigar_id);
            
            $hasItemIds = false;
            $assignedItemIds = [];
            foreach ($karigarIssuesForThisGroup as $subIssue) {
                if (is_array($subIssue->order_item_id) && !empty($subIssue->order_item_id)) {
                    $hasItemIds = true;
                    $assignedItemIds = array_merge($assignedItemIds, $subIssue->order_item_id);
                }
            }
            $assignedItemIds = array_unique($assignedItemIds);
            
            if ($hasItemIds) {
                // Modern item-wise assignment: filter by the assigned item IDs
                $assignedItems = $parentOrder->items->filter(function($item) use ($assignedItemIds) {
                    return in_array($item->id, $assignedItemIds);
                });
            } else {
                // Old record fallback: all in-process or received items are assumed assigned to this Karigar
                $assignedItems = $parentOrder->items->filter(function($item) {
                    return in_array($item->status, ['in_process', 'received']);
                });
            }
            
            $firstItem = $assignedItems->first();
            if (!$firstItem) {
                // If there are absolutely no active items for this Karigar group, skip rendering this row!
                continue;
            }
            
            $renderedCount++;
            
            $itemCount = $assignedItems->count();
            $isMulti = $itemCount > 1;

            // Aggregated calculations over all active issues for this specific Karigar
            $totalApproxWeight = 0;
            $totalApproxFine = 0;
            $totalCashIssued = 0;
            $totalOnlineIssued = 0;
            $totalWastageGm = 0;
            $wastagePerList = [];
            $deliveryDates = [];
            
            foreach ($karigarIssuesForThisGroup as $subIssue) {
                $isAssigned = false;
                if (is_array($subIssue->order_item_id) && !empty($subIssue->order_item_id)) {
                    foreach ($subIssue->order_item_id as $id) {
                        $item = $parentOrder->items->firstWhere('id', $id);
                        if ($item) {
                            $isAssigned = true;
                            break;
                        }
                    }
                } else {
                    $isAssigned = true;
                }
                
                if ($isAssigned) {
                    $totalApproxWeight += (float)($subIssue->approx_weight ?? 0);
                    $totalApproxFine += (float)($subIssue->approx_fine ?? 0);
                    $totalCashIssued += (float)($subIssue->cash_issued ?? 0);
                    $totalOnlineIssued += (float)($subIssue->online_issued ?? 0);
                    $totalWastageGm += (float)($subIssue->wastage_gm ?? 0);
                    if ($subIssue->wastage_per) {
                        $wastagePerList[] = (float)$subIssue->wastage_per;
                    }
                    if ($subIssue->delivery_date) {
                        $deliveryDates[] = $subIssue->delivery_date;
                    }
                }
            }
            
            $avgWastagePer = !empty($wastagePerList) ? (array_sum($wastagePerList) / count($wastagePerList)) : 0;
            $karigarName = $karigar->name ?? 'Artisan';
            
            $latestDeliveryDate = !empty($deliveryDates) ? max($deliveryDates) : ($parentOrder->delivery_date ?? $parentOrder->order_date);
            $deliveryDate = \Carbon\Carbon::parse($latestDeliveryDate);
            $daysLeft = now()->diffInDays($deliveryDate, false);
            $deliveryClass = $daysLeft <= 2 ? 'text-danger' : ($daysLeft <= 5 ? 'text-warning' : 'text-success');

            $totalGross += (float)$totalApproxWeight;
            $totalFine += (float)$totalApproxFine;
            
            $party = $parentOrder->party;
            if ($parentOrder->custo_type === 's') {
                $partyName = $party->supplier_name ?? $party->name ?? 'Supplier';
                $partyPhone = $party->mobile_no ?? 'N/A';
                $avatarChar = substr($partyName, 0, 1);
                $avatarBg = '#f5f3ff'; 
                $avatarColor = '#6d28d9';
            } else {
                $partyName = $party->custo_full_name ?? 'Walk-in';
                $partyPhone = $party->custo_fone ?? 'N/A';
                $avatarChar = substr($partyName, 0, 1);
                $avatarBg = '#fff5f5'; 
                $avatarColor = '#e53e3e';
            }
            
            $assignedProductNames = $assignedItems->pluck('product_name')->implode(', ');
        @endphp
        <tr>
            <!-- Merged Column 1: S.No & Order ID -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-800 text-muted mb-2"
                        style="width: 25px; height: 25px; background: #ebf4ff; border: 1.2px solid #cbd5e0; font-size: 0.65rem;">
                        {{ ($orders->currentPage() - 1) * $orders->perPage() + $renderedCount }}
                    </div>
                    <div class="fw-800 text-dark" style="font-size: 0.65rem; white-space: nowrap; letter-spacing: 0.2px;">
                        #{{ $parentOrder->order_unique }}
                    </div>
                </div>
            </td>
            <td class="text-center">
                @if($issue)
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="fw-900 text-info mb-1" style="font-size: 0.8rem;">
                            {{ $issue->created_at->format('d-M-Y') }}
                        </div>
                        <div style="font-size: 0.7rem; line-height: 1.2;">
                            <span class="text-muted fw-700" style="font-size: 0.55rem; text-transform: uppercase;">Ordered:</span>
                            <span class="fw-800 text-dark">{{ date('d-M-Y', strtotime($parentOrder->order_date)) }}</span>
                        </div>
                        <div style="font-size: 0.7rem; line-height: 1.2; margin-top: 3px;">
                            <span class="text-muted fw-700" style="font-size: 0.55rem; text-transform: uppercase;">Delivery:</span>
                            <span class="fw-800 text-dark">{{ date('d-M-Y', strtotime($issue->delivery_date ?? $parentOrder->delivery_date)) }}</span>
                        </div>
                    </div>
                @else
                    <div class="fw-900 text-dark" style="font-size: 0.8rem;">{{ date('d-M-Y', strtotime($parentOrder->order_date)) }}</div>
                @endif
            </td>
            <td class="text-left" style="padding-left: 15px !important;">
                <div class="fw-900 text-primary" style="font-size: 0.8rem; line-height: 1.15;">{{ $partyName }}</div>
                <div class="small text-muted fw-700" style="font-size: 0.7rem; margin-top: 3px;">
                    <i class="fa fa-phone-alt mr-1" style="font-size: 0.55rem;"></i> {{ $partyPhone }}
                </div>
            </td>
            <td class="text-left" style="padding-left: 12px !important; max-width: 200px; word-break: break-word;">
                <div class="d-flex align-items-center">
                    @if($parentOrder->design_image)
                        <div class="mr-2"
                            style="width: 30px; height: 30px; border-radius: 6px; overflow: hidden; border: 1.5px solid #eef2ff; box-shadow: 0 2px 4px rgba(0,0,0,0.08); flex-shrink: 0;">
                            <img src="{{ asset($parentOrder->design_image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endif
                    <div>
                        @if($isMulti)
                            @php
                                $receivedCount = $assignedItems->where('status', 'received')->count();
                                $inProcessCount = $assignedItems->where('status', 'in_process')->count();
                            @endphp
                            <div class="custom-tooltip-wrapper" style="margin-bottom: 2px;">
                                <div class="badge bg-soft-primary text-primary border-primary fw-900"
                                    style="font-size: 0.55rem; padding: 1px 6px; background: #e6fffa; border: 1px solid #b2f5ea; color: #319795; cursor: pointer;">
                                    {{ $itemCount }} ITEMS <i class="fa fa-info-circle ml-1" style="font-size: 0.55rem;"></i>
                                </div>
                                <div class="tooltip-content">
                                    <div class="fw-800 text-warning mb-1" style="font-size: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 4px;">Assigned Items:</div>
                                    @foreach($assignedItems as $item)
                                        <div class="tooltip-item-line">
                                            <span style="font-weight: 700;">{{ $item->product_name }}</span>
                                            <span style="font-weight: 800; color: #a3b8cc;">(Gross: {{ number_format($item->gross_wt, 3) }}g)</span>
                                            @if($item->status === 'received')
                                                <span class="badge badge-success ml-1" style="font-size:0.55rem; padding:1px 4px;">Received</span>
                                            @elseif($item->status === 'in_process')
                                                <span class="badge badge-warning ml-1" style="font-size:0.55rem; padding:1px 4px;">In Process</span>
                                            @else
                                                <span class="badge badge-secondary ml-1" style="font-size:0.55rem; padding:1px 4px;">{{ ucfirst($item->status) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">{{ $firstItem->product_name ?? 'Item' }}...</div>
                        @else
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">
                                {{ $firstItem->product_name ?? 'N/A' }}
                                @if($firstItem)
                                    @if($firstItem->status === 'received')
                                        <span class="badge badge-success ml-1" style="font-size: 0.6rem; padding: 1px 4px;">Received</span>
                                    @elseif($firstItem->status === 'in_process')
                                        <span class="badge badge-warning ml-1" style="font-size: 0.6rem; padding: 1px 4px;">In Process</span>
                                    @else
                                        <span class="badge badge-secondary ml-1" style="font-size: 0.6rem; padding: 1px 4px;">{{ ucfirst($firstItem->status) }}</span>
                                    @endif
                                @endif
                            </div>
                        @endif
                        <div style="font-size: 0.7rem; color: #4a5568; line-height: 1.2;">
                           <span class="fw-700 text-muted">Qty:</span> <span class="fw-800">{{ $assignedItems->sum('qty') }}</span>
                           @if($isMulti)
                               <span class="fw-700 text-muted ml-2">Recv:</span> <span class="fw-800 text-success">{{ $receivedCount }}</span>
                               <span class="fw-700 text-muted ml-2">Pend:</span> <span class="fw-800 text-warning">{{ $inProcessCount }}</span>
                           @endif
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-left" style="padding-left: 15px !important;">
                <div class="fw-900 text-primary" style="font-size: 0.8rem; line-height: 1.15;">
                    {{ $karigarName }}
                </div>
            </td>
            <td class="text-center">
                <div class="fw-900 text-danger" style="font-size: 0.85rem; line-height: 1.15;">{{ number_format($totalApproxWeight, 3) }} <small style="font-size: 0.65rem;">gm</small></div>
                <div class="small text-success fw-900" style="font-size: 0.65rem; background: #f0fff4; border-radius: 3px; padding: 0.5px 4px; display: inline-block; margin-top: 3px;">Fine: {{ number_format($totalApproxFine, 3) }}g</div>
            </td>
            <td class="text-center">
                <div class="fw-900 text-muted" style="font-size: 0.85rem; line-height: 1.15;">{{ number_format($avgWastagePer, 1) }}%</div>
                <div class="small text-danger fw-900" style="font-size: 0.65rem; background: #fff5f5; border-radius: 3px; padding: 0.5px 4px; display: inline-block; margin-top: 3px;">
                    {{ number_format($totalWastageGm, 3) }} gm
                </div>
            </td>
            <td class="text-center" style="line-height: 1.25;">
                @php
                    $cash = (float)$totalCashIssued;
                    $online = (float)$totalOnlineIssued;
                    $total = $cash + $online;
                @endphp
                @if($total > 0)
                    <div class="fw-900 text-dark" style="font-size: 0.85rem; white-space: nowrap;">
                        ₹{{ number_format($total, 0) }}<span class="text-muted fw-700 ml-1" style="font-size: 0.62rem; letter-spacing: 0.2px;">/advance</span>
                    </div>
                    <div class="small fw-800 text-muted d-flex flex-column align-items-center" style="font-size: 0.68rem; margin-top: 1px;">
                        @if($cash > 0 && $online > 0)
                            <div>
                                <span class="text-success">Csh: ₹{{ number_format($cash, 0) }}</span>
                                <span class="text-muted mx-0.5">|</span>
                                <span class="text-indigo">Onl: ₹{{ number_format($online, 0) }}</span>
                            </div>
                        @elseif($cash > 0)
                            <span class="text-success">Csh: ₹{{ number_format($cash, 0) }}</span>
                        @else
                            <span class="text-indigo">Onl: ₹{{ number_format($online, 0) }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-muted fw-700" style="font-size: 0.8rem;">-</div>
                @endif
            </td>
            <td class="text-center">
                @php
                    $pendingCount = $assignedItems->where('status', 'in_process')->count();
                    $itemIdsStr = !empty($assignedItemIds) ? implode(',', $assignedItemIds) : '';
                @endphp
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-icon btn-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" type="button"
                        data-toggle="dropdown"
                        style="border: 1.2px solid #cbd5e0; width: 26px; height: 26px; background: #fff; border-radius: 50% !important; margin: 0 auto; outline: none; transition: all 0.2s;">
                        <i class="fa fa-ellipsis-v text-muted" style="font-size: 0.75rem;"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-lg border-0"
                        style="border-radius: 12px; min-width: 160px; z-index: 1050;">
                        @if($pendingCount > 0)
                            <form action="{{ route('billing', ['type' => 'purchase']) }}" method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $parentOrder->id }}">
                                <input type="hidden" name="karigar_id" value="{{ $issue->karigar_id }}">
                                <button type="submit" class="dropdown-item py-2 fw-800 small text-success border-0 bg-transparent w-100 text-left" style="outline: none;">
                                    <i class="fa fa-reply text-success mr-2"></i> Purchase
                                </button>
                            </form>
                        @else
                            <span class="dropdown-item py-2 fw-800 small text-muted" style="cursor: not-allowed; opacity: 0.6;">
                                <i class="fa fa-check text-muted mr-2"></i> Completed
                            </span>
                        @endif
                        <a class="dropdown-item py-2 fw-800 small text-dark" href="{{ route('vendor.orders.assign.print', $issue->id) }}" target="_blank">
                            <i class="fa fa-print text-primary mr-2"></i> Print Receipt
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="10" class="text-center py-5">
            <div class="d-flex flex-column align-items-center py-4">
                <i class="fa fa-info-circle mb-3 text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted fw-800 mb-0">No orders currently in process.</p>
            </div>
        </td>
    </tr>
@endif
</tbody>

@if(count($processOrders) > 0)
    <tfoot>
        <tr>
            <td colspan="5" class="text-right py-2">
                <span class="fw-800 text-muted uppercase" style="font-size: 0.75rem;">Process Totals :-</span>
            </td>
            <td class="text-center py-1">
                <div class="fw-900 text-danger" style="font-size: 0.85rem;">{{ number_format($totalGross, 3) }} <small style="font-size: 0.65rem;">gm</small></div>
                <div class="small text-success fw-800" style="font-size: 0.65rem;">Fine: {{ number_format($totalFine, 3) }}</div>
            </td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
@endif
