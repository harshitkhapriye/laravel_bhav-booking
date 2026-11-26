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
        @php
            // ---- new UI: display helpers only ----
            $custName = $order->custo_type === 's'
                ? ($order->party->supplier_name ?? $order->party->name ?? $order->custo_name ?? 'N/A')
                : ($order->party->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer');
            $custPhone = ($order->custo_type === 's')
                ? ($order->party->mobile_no ?? $order->custo_phone ?? '')
                : ($order->party->custo_fone ?? $order->custo_phone ?? '');
            $jpIni = collect(preg_split('/\s+/', trim($custName)))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
            $jpProd = $firstItem ? ($firstItem->inventoryStock->name ?? $firstItem->product_name) : 'Order';
            $jpKar = $order->karigar->name ?? ($order->latestIssue->karigar->name ?? 'Artisan');
            $sumBill = ($sumBill ?? 0) + (float) $order->total_payable;
            $sumCollect = ($sumCollect ?? 0) + max(0, (float) $order->balance_due);
            $firstMetal = strtolower($firstItem->metal_type ?? '');
            $jpDone = ($isAllDelivered || $order->status == 3);
            // NEW ADDITION (pipeline design): values for search results + Order summary popup
            $jpCanEdit = !$jpDone && $deliveredCount === 0;
            $jpRow = [
                'id' => $order->id, 'u' => $order->order_unique, 'cust' => $custName, 'mob' => $custPhone, 'prod' => $jpProd,
                'more' => max(0, $totalItems - 1), 'metal' => $firstItem->metal_type ?? '', 'total' => (float) $order->total_payable,
                'ddate' => $order->delivery_date ? date('d M Y', strtotime($order->delivery_date)) : '', 'urgent' => (bool) $order->is_urgent,
                'st' => 'billing', 'kar' => $jpKar, 'fine' => round($orderFine, 3), 'labour' => $orderLabourTotal,
            ];
        @endphp
        <tr class="jp-row {{ $isHighlighted ? 'highlighted-order-row' : '' }}" id="order-row-{{ $order->id }}" data-id="{{ $order->id }}" data-row='@json($jpRow)'>
            <td class="jp-cell">
                <article class="jp-card {{ $isHighlighted ? 'jp-hl' : '' }}" data-stage="billing" data-metal="{{ ucfirst($firstMetal) }}">
                    <div class="jp-card-top">
                        <span class="jp-av">{{ $jpIni }}</span>
                        <div class="jp-cust">{{ $custName }}<small>{{ $custPhone ?: '—' }}</small></div>
                        @if($jpDone)
                            <span class="jp-act jp-green jp-act-done">Delivered</span>
                        @else
                            <a href="{{ route('billing', ['type' => 'sale', 'order_id' => $order->id]) }}" class="jp-act jp-green">Sell &amp; deliver</a>
                        @endif
                        <button type="button" class="jp-dots" aria-label="More actions">⋮</button>
                    </div>
                    <div class="jp-menu">
                        <a class="btn-view-order" href="javascript:void(0);" data-id="{{ $order->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-eye"></use></svg></span>View detail</a>
                        <a href="{{ route('vendor.orders.print', $order->id) }}" target="_blank"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-print"></use></svg></span>Print receipt</a>
                        {{-- ===== NEW ADDITION (v9): "Assign remaining" + "Purchase remaining" for partly done orders =====
                             Assign remaining   -> items still pending (not given to any karigar) -> same old openAssignModal().
                             Purchase remaining -> items still with a karigar (in_process) -> same old purchase form
                                                   (route billing type=purchase, order_id + karigar_id), one entry per karigar. --}}
                        @php
                            $jpBLeft = $jpDone ? collect() : $items->where('status', 'pending');
                            $jpBLeftNet = (float) $jpBLeft->sum(fn($it) => (float) $it->net_wt * ($it->qty ?: 1));
                            $jpBLeftWg = (float) $jpBLeft->sum(fn($it) => (float) ($it->wastage_per ?? 0) * ($it->qty ?: 1));
                            $jpBWithIds = $jpDone ? [] : $items->where('status', 'in_process')->pluck('id')->all();
                            $jpBPur = [];
                            if ($jpBWithIds) {
                                foreach (($order->karigarIssues ?? collect()) as $jpBIs) {
                                    $jpBIds = is_array($jpBIs->order_item_id) ? $jpBIs->order_item_id : [];
                                    $jpBMine = $jpBIds ? array_intersect($jpBIds, $jpBWithIds) : $jpBWithIds;
                                    if (!$jpBMine) continue;
                                    $jpBK = $jpBIs->karigar_id;
                                    $jpBPur[$jpBK]['name'] = $jpBIs->karigar->name ?? 'Karigar';
                                    $jpBPur[$jpBK]['ids'] = array_unique(array_merge($jpBPur[$jpBK]['ids'] ?? [], $jpBMine));
                                }
                            }
                        @endphp
                        @if($jpBLeft->count() > 0)
                            <a href="javascript:void(0);" class="jp-assign-left"
                                onclick="openAssignModal({{ $order->id }}, '{{ $order->order_unique }}', '{{ addslashes($jpBLeft->first()->product_name ?? '') }}', {{ (float) $jpBLeft->sum(fn($it) => (float) $it->gross_wt * ($it->qty ?: 1)) }}, {{ (float) $jpBLeft->sum(fn($it) => (float) $it->less_wt * ($it->qty ?: 1)) }}, {{ $jpBLeftNet }}, {{ $jpBLeftNet > 0 ? round($jpBLeftWg / $jpBLeftNet * 100, 2) : 0 }}, {{ $jpBLeftWg }}, {{ (float) $jpBLeft->sum(fn($it) => (float) $it->fine_wt * ($it->qty ?: 1)) }}, '{{ $jpBLeft->first()->touch ?? '' }}')"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-tool"></use></svg></span>Assign remaining ({{ $jpBLeft->count() }})</a>
                        @endif
                        @foreach($jpBPur as $jpBKid => $jpBP)
                            <form action="{{ route('billing', ['type' => 'purchase']) }}" method="POST" class="jp-menu-form jp-purchase-left">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <input type="hidden" name="karigar_id" value="{{ $jpBKid }}">
                                <button type="submit"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-box"></use></svg></span>Purchase remaining ({{ count($jpBP['ids']) }}){{ count($jpBPur) > 1 ? ' · ' . $jpBP['name'] : '' }}</button>
                            </form>
                        @endforeach
                        {{-- NEW ADDITION (pipeline design): same menu as the design (old Edit / Delete routes, Change status popup) --}}
                        @if($jpCanEdit)
                            <a class="jp-open-order" href="{{ route('vendor.orders.edit', $order->id) }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-pencil"></use></svg></span>Edit order</a>
                        @endif
                        <button type="button" class="jp-cs-open" data-id="{{ $order->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-refresh"></use></svg></span>Change status</button>
                        @if($jpCanEdit)
                            <a class="jp-del btn-delete-order" href="javascript:void(0);" data-toggle="modal" data-target="#confirmDeleteModal"
                                data-delete-url="{{ route('vendor.orders.destroy', $order->id) }}"
                                {{-- NEW ADDITION (v9): delete impact data for the "Delete order?" popup --}}
                                @include('vendors.orders.board.del_info', ['jpDelOrder' => $order])><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span>Delete</a>
                        @endif
                    </div>
                    <div class="jp-mid">
                        <div class="jp-ml">
                            <div class="jp-prod">{{ $jpProd }} @if($isMulti)<span class="jp-more">+{{ $totalItems - 1 }} more</span>@endif</div>
                            <div class="jp-subrow">
                                <span class="jp-sid">#{{ $order->order_unique }}</span>
                                @if($isMulti)<span class="jp-sit"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-box"></use></svg></span>{{ $totalItems }} items</span>@endif
                                @if($order->is_urgent)<span class="jp-metal jp-urg jp-sm"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-bolt"></use></svg></span>Urgent</span>@endif
                            </div>
                        </div>
                        <div class="jp-tags">
                            @if($isHighlighted)<span class="jp-pc jp-g">Just ready</span>@endif
                            @if($isPartial)
                                <span class="jp-pc jp-p">Ready {{ $readyCount }}/{{ $totalItems }}</span>
                            @elseif(!$jpDone)
                                <span class="jp-pc jp-g">Ready</span>
                            @endif
                        </div>
                    </div>
                    <div class="jp-grid">
                        <div class="jp-f"><span>Karigar</span><b>{{ $jpKar }}</b></div>
                        <div class="jp-f jp-ftot"><span>Returned wt</span><b>{{ number_format($orderGross, 3) }} g</b><button type="button" class="jp-dtg" aria-label="Details show/hide">▾</button></div>
                        {{-- Design mein nahi hai: Fine (comment out)
                        <div class="jp-f"><span>Fine</span><b>{{ number_format($orderFine, 3) }} g</b></div>
                        --}}
                        <div class="jp-f"><span>Ghat allowance</span><b>{{ number_format($order->ghat_allowance ?? 0, 1) }}%</b></div>
                        <div class="jp-f"><span>Making</span><b>{{ $orderMakingAmount > 0 ? '₹' . number_format($orderMakingAmount, 0) : '—' }}</b></div>
                        <div class="jp-f"><span>Labour paid</span><b>{{ ($hasStockLabour && $orderLabourTotal > 0) ? '₹' . number_format($orderLabourTotal, 0) : '—' }}</b></div>
                        {{-- Design mein nahi hai: Total amount (comment out)
                        <div class="jp-f"><span>Total amount</span><b>{{ $order->total_payable > 0 ? '₹' . number_format($order->total_payable, 0) : '—' }}</b></div>
                        --}}
                        <div class="jp-f"><span>Order / Delivery</span><b>{{ date('d M', strtotime($order->order_date)) }} → {{ $order->delivery_date ? date('d M', strtotime($order->delivery_date)) : '—' }}</b></div>
                    </div>
                </article>
            </td>
        </tr>
    @endforeach
@else
    <tr class="jp-empty"><td colspan="10"><div class="jp-empty-in">No orders ready for billing</div></td></tr>
@endif
</tbody>
@if(count($processOrders) > 0)
    <tfoot>
        <tr>
            <td colspan="10" class="jp-sum" data-labour="{{ $totalLabourSum }}" data-making="{{ $totalMakingSum }}" data-bill="{{ $sumBill ?? 0 }}" data-collect="{{ $sumCollect ?? 0 }}"
                data-gross="{{ $totalGross }}" data-fine="{{ $totalFine }}"></td>
        </tr>
    </tfoot>
@endif
