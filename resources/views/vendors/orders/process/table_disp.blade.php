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
        @php
            // ---- new UI: display helpers only ----
            $jpIni = collect(preg_split('/\s+/', trim($partyName)))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
            $pendingCount = $assignedItems->where('status', 'in_process')->count();
            $receivedCount = $assignedItems->where('status', 'received')->count();
            $jpCharges = (float) $totalCashIssued + (float) $totalOnlineIssued;
            $sumCharges = ($sumCharges ?? 0) + $jpCharges;
            $sumAmount = ($sumAmount ?? 0) + (float) $parentOrder->total_payable;
            $firstMetal = strtolower($firstItem->metal_type ?? '');
            // NEW ADDITION (pipeline design): values for search results + Order summary popup
            $jpCanEdit = $parentOrder->status != 3 && !$parentOrder->items->where('status', 'delivered')->isNotEmpty();
            $jpRow = [
                'id' => $parentOrder->id, 'u' => $parentOrder->order_unique, 'cust' => $partyName, 'mob' => $partyPhone,
                'prod' => $firstItem->product_name ?? '', 'more' => max(0, $itemCount - 1), 'metal' => $firstItem->metal_type ?? '',
                'total' => (float) $parentOrder->total_payable, 'ddate' => $deliveryDate->format('d M Y'), 'urgent' => (bool) $parentOrder->is_urgent,
                'st' => 'karigar', 'kar' => $karigarName, 'kid' => $issue->karigar_id, 'pend' => $pendingCount, 'recv' => $receivedCount,
                'fine' => round((float) $totalApproxFine, 3), 'days' => $daysLeft,
            ];
        @endphp
        <tr class="jp-row" data-id="{{ $parentOrder->id }}" data-row='@json($jpRow)'>
            <td class="jp-cell">
                <article class="jp-card" data-stage="karigar" data-metal="{{ ucfirst($firstMetal) }}">
                    <div class="jp-card-top">
                        <span class="jp-av">{{ $jpIni }}</span>
                        <div class="jp-cust">{{ $partyName }}<small>{{ $partyPhone }}</small></div>
                        @if($pendingCount > 0)
                            <form action="{{ route('billing', ['type' => 'purchase']) }}" method="POST" class="jp-act-form">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $parentOrder->id }}">
                                <input type="hidden" name="karigar_id" value="{{ $issue->karigar_id }}">
                                <button type="submit" class="jp-act jp-green">Purchase order</button>
                            </form>
                        @else
                            <span class="jp-act jp-green jp-act-done">Completed</span>
                        @endif
                        <button type="button" class="jp-dots" aria-label="More actions">⋮</button>
                    </div>
                    <div class="jp-menu">
                        <a class="btn-view-order" href="javascript:void(0);" data-id="{{ $parentOrder->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-eye"></use></svg></span>View detail</a>
                        <a href="{{ route('vendor.orders.assign.print', $issue->id) }}" target="_blank"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-print"></use></svg></span>Print receipt</a>
                        {{-- NEW ADDITION (pipeline design): same menu as the design (old Edit / Delete routes, Change status popup) --}}
                        @if($jpCanEdit)
                            <a class="jp-open-order" href="{{ route('vendor.orders.edit', $parentOrder->id) }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-pencil"></use></svg></span>Edit order</a>
                        @endif
                        <button type="button" class="jp-cs-open" data-id="{{ $parentOrder->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-refresh"></use></svg></span>Change status</button>
                        @if($jpCanEdit)
                            <a class="jp-del btn-delete-order" href="javascript:void(0);" data-toggle="modal" data-target="#confirmDeleteModal"
                                data-delete-url="{{ route('vendor.orders.destroy', $parentOrder->id) }}"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span>Delete</a>
                        @endif
                    </div>
                    <div class="jp-mid">
                        <div class="jp-ml">
                            <div class="jp-prod">{{ $firstItem->product_name }} @if($isMulti)<span class="jp-more">+{{ $itemCount - 1 }} more</span>@endif</div>
                            <div class="jp-subrow">
                                <span class="jp-sid">#{{ $parentOrder->order_unique }}</span>
                                @if($isMulti)<span class="jp-sit"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-box"></use></svg></span>{{ $itemCount }} items</span>@endif
                                @if($parentOrder->is_urgent)<span class="jp-metal jp-urg jp-sm"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-bolt"></use></svg></span>Urgent</span>@endif
                            </div>
                        </div>
                        <div class="jp-tags">
                            @if($isMulti)
                                <span class="jp-pc jp-p">Received {{ $receivedCount }}/{{ $itemCount }}</span>
                            @endif
                            @if($daysLeft < 0)
                                <span class="jp-due-c jp-late">{{ abs($daysLeft) }} din overdue</span>
                            @elseif($daysLeft <= 3)
                                <span class="jp-due-c jp-soon">{{ $daysLeft == 0 ? 'Aaj delivery' : $daysLeft . ' din baaki' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="jp-grid">
                        <div class="jp-f"><span>Assigned karigar</span><b>{{ $karigarName }}</b></div>
                        <div class="jp-f jp-ftot"><span>Issued material</span><b>{{ $totalApproxWeight > 0 ? number_format($totalApproxWeight, 3) . ' g' : '—' }}</b><button type="button" class="jp-dtg" aria-label="Details show/hide">▾</button></div>
                        {{-- Design mein nahi hai: Fine (comment out)
                        <div class="jp-f"><span>Fine</span><b>{{ $totalApproxFine > 0 ? number_format($totalApproxFine, 3) . ' g' : '—' }}</b></div>
                        --}}
                        <div class="jp-f"><span>Ghat allowance</span><b>{{ number_format($avgWastagePer, 1) }}% · {{ number_format($totalWastageGm, 3) }} g</b></div>
                        <div class="jp-f"><span>Charges</span><b>{{ $jpCharges > 0 ? '₹' . number_format($jpCharges, 0) : '—' }}@if($totalCashIssued > 0 && $totalOnlineIssued > 0) <small>Cash ₹{{ number_format($totalCashIssued, 0) }} · Online ₹{{ number_format($totalOnlineIssued, 0) }}</small>@endif</b></div>
                        <div class="jp-f"><span>Assigned / Due</span><b>{{ $issue->created_at ? $issue->created_at->format('d M') : '—' }} → {{ $deliveryDate->format('d M') }}</b></div>
                    </div>
                </article>
            </td>
        </tr>
    @endforeach
@else
    <tr class="jp-empty"><td colspan="10"><div class="jp-empty-in">Abhi karigar ke paas koi order nahi</div></td></tr>
@endif
</tbody>
@if(count($processOrders) > 0)
    <tfoot>
        <tr>
            <td colspan="10" class="jp-sum" data-issued="{{ $totalGross }}" data-fine="{{ $totalFine }}" data-charges="{{ $sumCharges ?? 0 }}" data-amount="{{ $sumAmount ?? 0 }}" data-rendered="{{ $renderedCount }}"></td>
        </tr>
    </tfoot>
@endif
