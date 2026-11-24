@php
    $totalGross = 0;
    $totalFine = 0;
    $totalPayable = 0;
    $totalAdvance = 0;
    $totalMetalAdvance = 0;
    $totalGoldAdv = 0;
    $totalSilverAdv = 0;
    $totalBalance = 0;
@endphp

<tbody id="data_area">
    @forelse($orders as $index => $order)
        @php
            $firstItem = $order->items->first();
            $itemCount = $order->items->count();
            $isMulti = $itemCount > 1;

            $deliveryDate = \Carbon\Carbon::parse($order->delivery_date ?? $order->order_date);
            $daysLeft = now()->diffInDays($deliveryDate, false);
            $deliveryClass = $daysLeft <= 2 ? 'text-danger' : ($daysLeft <= 5 ? 'text-warning' : 'text-success');

            $orderGross = $order->items->sum(function($item) {
                return (float)$item->gross_wt * ($item->qty ?: 1);
            });
            $orderLess = $order->items->sum(function($item) {
                return (float)$item->less_wt * ($item->qty ?: 1);
            });
            $orderNet = $order->items->sum(function($item) {
                return (float)$item->net_wt * ($item->qty ?: 1);
            });
            
            // Wastage Gm is the sum of wastage_per (since wastage_per in DB stores absolute grams)
            $orderWastageGm = $order->items->sum(function($item) {
                return (float)($item->wastage_per ?? 0) * ($item->qty ?: 1);
            });

            // Calculate the actual average wastage percentage based on total wastage gm and net weight
            $orderWastagePer = $orderNet > 0 ? ($orderWastageGm / $orderNet) * 100 : 0;

            $orderFine = $order->items->sum(function($item) {
                return (float)$item->fine_wt * ($item->qty ?: 1);
            });

            $gAdv = $order->getGoldAdvWeight();
            $sAdv = $order->getSilverAdvWeight();

            $totalGross += $orderGross;
            $totalFine += $orderFine;
            $totalPayable += (float) $order->total_payable;
            $totalAdvance += ((float) $order->cash_adv + (float) $order->online_adv);
            $totalMetalAdvance += (float) ($order->metal_adv_wt ?? 0);
            $totalGoldAdv += $gAdv;
            $totalSilverAdv += $sAdv;
            $totalBalance += (float) $order->balance_due;

            $totalItems = $order->items->count();

            $inProcessCount = $order->items->where('status', 'in_process')->count();
            $completedCount = $order->items->whereIn('status', ['completed', 'received'])->count();

            $assignedCount = $order->items->whereIn('status', [
                'in_process',
                'completed',
                'received'
            ])->count();

            $pendingCount = $totalItems - $assignedCount;

            $computedStatus = 'pending';

            if ($order->status == 3) {
                $computedStatus = 'delivered';
            } elseif ($completedCount === $totalItems) {
                $computedStatus = 'completed';
            } elseif ($assignedCount === $totalItems) {
                // All items are assigned (some may be completed, some still in process)
                $computedStatus = 'in_process';
            } elseif ($pendingCount === $totalItems) {
                $computedStatus = 'pending';
            } else {
                $computedStatus = 'partial';
            }

            $hasDeliveredItems = ($order->status == 3 || $order->items->where('status', 'delivered')->isNotEmpty());
        @endphp
        @php
            // ---- new UI: display helpers only ----
            $custName = $order->custo_type === 's'
                ? ($order->party->supplier_name ?? $order->party->name ?? $order->custo_name ?? 'N/A')
                : ($order->party->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer');
            $custPhone = ($order->custo_type === 's')
                ? ($order->party->mobile_no ?? $order->custo_phone ?? '')
                : ($order->party->custo_fone ?? $order->custo_phone ?? '');
            $custIni = collect(preg_split('/\s+/', trim($custName)))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
            $cashOnlineAdv = (float) $order->cash_adv + (float) $order->online_adv;
            $balDue = (float) $order->balance_due;
            $firstMetal = strtolower($firstItem->metal_type ?? '');
            $jpStage = $computedStatus === 'delivered' ? 'delivered' : 'order';
            $jpPaid = (float) $order->total_payable - $balDue;
            $jpRow = [
                'id' => $order->id, 'u' => $order->order_unique, 'cust' => $custName, 'mob' => $custPhone,
                'prod' => $firstItem->product_name ?? '', 'more' => max(0, $itemCount - 1),
                'metal' => $firstItem->metal_type ?? '', 'purity' => $firstItem->purity ?? '',
                'kar' => $computedStatus === 'delivered' ? ($order->karigar->name ?? '') : '',
                'fine' => round($orderFine, 3), 'total' => (float) $order->total_payable, 'paid' => $jpPaid, 'due' => $balDue,
                'odate' => $order->order_date ? date('d M Y', strtotime($order->order_date)) : '',
                'ddate' => $order->delivery_date ? date('d M Y', strtotime($order->delivery_date)) : '',
                'urgent' => (bool) $order->is_urgent, 'print' => route('vendor.orders.print', $order->id),
                // NEW ADDITION (pipeline design): extra values for search results + Order summary popup
                'st' => $computedStatus, 'gross' => round($orderGross, 3), 'items' => $itemCount, 'days' => $order->delivery_date ? $daysLeft : null,
                'edit' => $hasDeliveredItems ? '' : route('vendor.orders.edit', $order->id),
            ];
            // NEW ADDITION (pipeline design): order saved without weight -> card asks to "Edit order" and shows the remark
            $jpNoWeight = $orderGross <= 0 && !$hasDeliveredItems;
        @endphp
        <tr class="jp-row" data-status="{{ $computedStatus }}" data-id="{{ $order->id }}" data-row='@json($jpRow)'>
            <td class="jp-cell">
                <article class="jp-card" data-stage="{{ $jpStage }}" data-metal="{{ ucfirst($firstMetal) }}">
                    <div class="jp-card-top">
                        @if($jpStage === 'order')
                            <input type="checkbox" class="order_checkbox jp-chk" value="{{ $order->id }}" title="Select">
                        @endif
                        <span class="jp-av">{{ $custIni }}</span>
                        <div class="jp-cust">{{ $custName }}<small>{{ $custPhone ?: '—' }}</small></div>
                        @if($jpNoWeight && $computedStatus === 'pending')
                            {{-- NEW ADDITION (pipeline design): weight abhi nahi bhara -> pehle order edit karo --}}
                            <a class="jp-act jp-act-edit jp-open-order" href="{{ route('vendor.orders.edit', $order->id) }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-pencil"></use></svg></span>Edit order</a>
                        @elseif($computedStatus === 'pending' || $computedStatus === 'partial')
                            <button type="button" class="jp-act"
                                onclick="openAssignModal({{ $order->id }}, '{{ $order->order_unique }}', '{{ $firstItem->product_name ?? '' }}', {{ $orderGross }}, {{ $orderLess }}, {{ $orderNet }}, {{ $orderWastagePer }}, {{ $orderWastageGm }}, {{ $orderFine }}, '{{ $firstItem->touch ?? '' }}')">{{ $computedStatus === 'partial' ? 'Assign remaining' : 'Assign karigar' }}</button>
                        @endif
                        <button type="button" class="jp-dots" aria-label="More actions">⋮</button>
                    </div>
                    <div class="jp-menu">
                        <a class="btn-view-order" href="javascript:void(0);" data-id="{{ $order->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-eye"></use></svg></span>View detail</a>
                        @if(!$hasDeliveredItems)
                            <a class="jp-open-order" href="{{ route('vendor.orders.edit', $order->id) }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-pencil"></use></svg></span>Edit order</a>
                        @endif
                        <a href="{{ route('vendor.orders.print', $order->id) }}" target="_blank"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-print"></use></svg></span>Print receipt</a>
                        {{-- NEW ADDITION (pipeline design): Change status popup (uses the old Assign / Purchase / Sale actions) --}}
                        <button type="button" class="jp-cs-open" data-id="{{ $order->id }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-refresh"></use></svg></span>Change status</button>
                        @if(!$hasDeliveredItems)
                            <a class="jp-del btn-delete-order" href="javascript:void(0);" data-toggle="modal" data-target="#confirmDeleteModal"
                                data-delete-url="{{ route('vendor.orders.destroy', $order->id) }}"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span>Delete</a>
                        @endif
                    </div>
                    <div class="jp-mid">
                        <div class="jp-ml">
                            <div class="jp-prod">{{ $firstItem->product_name ?? 'Order' }} @if($isMulti)<span class="jp-more">+{{ $itemCount - 1 }} more</span>@endif</div>
                            <div class="jp-subrow">
                                <span class="jp-sid">#{{ $order->order_unique }}</span>
                                @if($isMulti)<span class="jp-sit"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-box"></use></svg></span>{{ $itemCount }} items</span>@endif
                                @if($order->is_urgent)<span class="jp-metal jp-urg jp-sm"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-bolt"></use></svg></span>Urgent</span>@endif
                            </div>
                        </div>
                        <div class="jp-tags">
                            @if($computedStatus === 'partial')
                                <span class="jp-pc jp-w">Assigned {{ $assignedCount }}/{{ $totalItems }}</span>
                            @elseif($computedStatus === 'in_process')
                                <span class="jp-pc jp-p">With karigar</span>
                            @elseif($computedStatus === 'completed')
                                <span class="jp-pc jp-g">Ready</span>
                            @elseif($computedStatus === 'delivered')
                                <span class="jp-pc jp-g">Delivered</span>
                            @endif
                            @if($computedStatus !== 'delivered' && $order->delivery_date)
                                @if($daysLeft < 0)
                                    <span class="jp-due-c jp-late">{{ abs($daysLeft) }} din overdue</span>
                                @elseif($daysLeft <= 3)
                                    <span class="jp-due-c jp-soon">{{ $daysLeft == 0 ? 'Aaj delivery' : $daysLeft . ' din baaki' }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="jp-grid">
                        @if($jpNoWeight && $order->remarks)
                        {{-- NEW ADDITION (pipeline design): no weight yet -> show the remark --}}
                        <div class="jp-f jp-ftot jp-frem"><span><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-msg"></use></svg></span>Remark</span><b>{{ $order->remarks }}</b><button type="button" class="jp-dtg" aria-label="Details show/hide">▾</button></div><div class="jp-f jp-fgap" aria-hidden="true"></div>
                        @else
                        <div class="jp-f"><span>Gross / Fine</span><b>{{ $orderGross > 0 ? number_format($orderGross, 3) . ' g / ' . number_format($orderFine, 3) . ' g' : '—' }}</b></div>
                        <div class="jp-f jp-ftot"><span>Total amount</span><b>{{ $order->total_payable > 0 ? '₹' . number_format($order->total_payable, 0) : '—' }}</b><button type="button" class="jp-dtg" aria-label="Details show/hide">▾</button></div>
                        @endif
                        <div class="jp-f"><span>Advance</span><b class="jp-plus-amt">{{ $cashOnlineAdv > 0 ? '₹' . number_format($cashOnlineAdv, 0) : '—' }}@if($gAdv > 0) <small>+ Au {{ number_format($gAdv, 3) }}g</small>@endif @if($sAdv > 0) <small>+ Ag {{ number_format($sAdv, 3) }}g</small>@endif</b></div>
                        <div class="jp-f"><span>{{ $balDue < 0 ? 'Advance extra' : 'Balance' }}</span><b class="{{ $balDue < 0 ? 'jp-plus-amt' : 'jp-minus-amt' }}">{{ $balDue != 0 ? '₹' . number_format(abs($balDue), 0) : '—' }}</b></div>
                        <div class="jp-f" style="grid-column:1/-1"><span>Order / Delivery</span><b>{{ date('d M', strtotime($order->order_date)) }} → {{ $order->delivery_date ? date('d M', strtotime($order->delivery_date)) : '—' }}</b></div>
                    </div>
                </article>
            </td>
        </tr>
    @empty
        <tr class="jp-empty"><td colspan="8"><div class="jp-empty-in">Koi order nahi mila</div></td></tr>
    @endforelse
</tbody>

@if($orders->count() > 0)
    <tfoot>
        <tr>
            <td colspan="8" class="jp-sum" data-total="{{ $totalPayable }}" data-paid="{{ $totalPayable - $totalBalance }}" data-rem="{{ $totalBalance }}"
                data-gross="{{ $totalGross }}" data-fine="{{ $totalFine }}" data-gadv="{{ $totalGoldAdv }}" data-sadv="{{ $totalSilverAdv }}"></td>
        </tr>
    </tfoot>
@endif
