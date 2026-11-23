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
        <tr style="border-left: 4px solid {{ $isMulti ? '#319795' : 'transparent' }};" data-status="{{ $computedStatus }}">
            <!-- Column 1: S.No / Checkbox / Order ID -->
            <td class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <!-- Checkbox & S.No side-by-side -->
                    <div class="d-flex align-items-center justify-content-center" style="gap: 5px; margin-bottom: 2px;">
                        <input type="checkbox" class="order_checkbox" value="{{ $order->id }}"
                            style="width: 14px; height: 14px; cursor: pointer; margin-right: 1px; vertical-align: middle;">
                        <div class="d-flex align-items-center justify-content-center rounded-circle fw-800 text-muted"
                            style="width: 18px; height: 18px; background: #ebf4ff; border: 1.2px solid #cbd5e0; font-size: 0.65rem; vertical-align: middle;">
                            {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                        </div>
                    </div>

                    <!-- Order ID down of that -->
                    <div class="fw-800 text-primary" style="font-size: 0.65rem; white-space: nowrap; letter-spacing: 0.2px; margin-top: 1px;">
                        {{ $order->order_unique }}
                    </div>

                    @if($order->is_urgent)
                        <div style="margin-top: 2px;">
                            <span class="badge bg-danger text-white px-1 py-0.5"
                                style="font-size: 0.5rem; animation: pulse 1.5s infinite; border-radius: 50px; line-height: 1;"><i
                                    class="fa fa-bolt"></i> URGENT</span>
                        </div>
                    @endif
                </div>
            </td>

            <!-- Column 2: Customer Details -->
            <td class="text-left" style="padding-left: 12px !important; max-width: 160px; word-break: break-word;">
                <div class="fw-900 text-dark" style="font-size: 0.8rem; line-height: 1.15;">
                    @if($order->custo_type === 's')
                        {{ $order->party->supplier_name ?? $order->party->name ?? $order->custo_name ?? 'N/A' }}
                    @else
                        {{ $order->party->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer' }}
                    @endif
                </div>
                <div class="small text-muted fw-700" style="font-size: 0.7rem; margin-top: 1px;">
                    <i class="fa fa-phone-alt mr-1" style="font-size: 0.55rem;"></i> 
                    {{ ($order->custo_type === 's') ? ($order->party->mobile_no ?? $order->custo_phone ?? '') : ($order->party->custo_fone ?? $order->custo_phone ?? '') }}
                </div>
            </td>

            <!-- Column 3: Product & Details -->
            <td class="text-left" style="padding-left: 12px !important; max-width: 200px; word-break: break-word;">
                <div class="d-flex align-items-center">
                    @if($order->design_image)
                        <div class="mr-2"
                            style="width: 30px; height: 30px; border-radius: 6px; overflow: hidden; border: 1.5px solid #eef2ff; box-shadow: 0 2px 4px rgba(0,0,0,0.08); flex-shrink: 0;">
                            <img src="{{ asset($order->design_image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endif
                    <div>
                        @if($isMulti)
                            <div class="custom-tooltip-wrapper" style="margin-bottom: 2px;">
                                <div class="badge bg-soft-primary text-primary border-primary fw-900"
                                    style="font-size: 0.55rem; padding: 1px 6px; background: #e6fffa; border: 1px solid #b2f5ea; color: #319795; cursor: pointer;">
                                    {{ $itemCount }} ITEMS <i class="fa fa-info-circle ml-1" style="font-size: 0.5rem;"></i>
                                </div>
                                <div class="tooltip-content">
                                    <div class="fw-800 text-warning mb-1" style="font-size: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 4px;">Items List Details:</div>
                                    @foreach($order->items as $item)
                                        <div class="tooltip-item-line">
                                            <span style="font-weight: 700;">{{ $item->product_name }}</span>
                                            <span style="font-weight: 800; color: #a3b8cc;">(Gross: {{ number_format($item->gross_wt, 3) }}g)</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">{{ $firstItem->product_name }}...</div>
                        @else
                            <div class="fw-800 text-dark" style="font-size: 0.8rem; line-height: 1.15; margin-bottom: 2px;">{{ $firstItem->product_name ?? 'N/A' }}</div>
                        @endif
                        <div style="font-size: 0.7rem; color: #4a5568; line-height: 1.2;">
                            <span class="fw-700 text-muted">Purity:</span> <span class="fw-800 text-indigo">{{ $firstItem->purity ?? 'N/A' }}</span>
                            @if($firstItem->size)
                                <span class="text-muted mx-1">|</span> <span class="fw-700 text-muted">Size:</span> <span class="fw-800">{{ $firstItem->size }}</span>
                            @endif
                            <span class="text-muted mx-1">|</span> <span class="fw-700 text-muted">Qty:</span> <span class="fw-800">{{ $order->items->sum('qty') }}</span>
                        </div>
                    </div>
                </div>
            </td>

            <!-- Column 4: Weights -->
            <td class="text-center">
                <div class="fw-900 text-danger" style="font-size: 0.85rem; line-height: 1.15;">{{ number_format($orderGross, 3) }}
                    <small style="font-size: 0.65rem;">gm</small>
                </div>
                <div class="small text-success fw-900"
                    style="font-size: 0.65rem; background: #f0fff4; border-radius: 3px; padding: 0.5px 4px; display: inline-block; margin-top: 1px;">
                    Fine: {{ number_format($orderFine, 3) }}</div>
            </td>

            <!-- Column 5: Amount Details -->
            <td class="text-center" style="line-height: 1.25;">
                <div class="fw-900 text-dark" style="font-size: 0.85rem;">₹{{ number_format($order->total_payable, 0) }}</div>
                <div class="small fw-800 text-muted d-flex flex-column align-items-center">
                    <div style="font-size: 0.7rem;">
                        <span class="text-info">Adv: ₹{{ number_format((float) $order->cash_adv + (float) $order->online_adv, 0) }}</span>
                        @if($gAdv > 0 && $sAdv > 0)
                            <span class="text-warning ml-1">| Au: {{ number_format($gAdv, 3) }}g</span>
                            <span class="text-secondary ml-1">| Ag: {{ number_format($sAdv, 3) }}g</span>
                        @elseif($gAdv > 0)
                            <span class="text-warning ml-1">| Au: {{ number_format($gAdv, 3) }}g</span>
                        @elseif($sAdv > 0)
                            <span class="text-secondary ml-1">| Ag: {{ number_format($sAdv, 3) }}g</span>
                        @elseif($order->metal_adv_wt > 0)
                            <span class="text-warning ml-1">| Mtl: {{ number_format($order->metal_adv_wt, 3) }}g</span>
                        @endif
                    </div>
                    @if($order->balance_due < 0)
                        <span class="badge bg-soft-success text-success px-1 py-0.5"
                            style="font-size: 0.65rem; background: #f0fff4; border-radius: 3px; display: inline-block; margin-top: 1px;">
                            Adv: +₹{{ number_format(abs($order->balance_due), 0) }}</span>
                    @else
                        <span class="badge bg-soft-danger text-danger px-1 py-0.5"
                            style="font-size: 0.65rem; background: #fff5f5; border-radius: 3px; display: inline-block; margin-top: 1px;">
                            Bal: ₹{{ number_format($order->balance_due, 0) }}</span>
                    @endif
                </div>
            </td>

            <!-- Column 6: Karigar Status -->
            <td class="text-center status-cell" data-order-id="{{ $order->id }}">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    @if($computedStatus === 'delivered')
                        <span class="badge badge-dark px-1.5 py-0.5"
                            style="border-radius: 50px; font-size: 0.6rem; background: #edf2f7; color: #2d3748; border: 1px solid #e2e8f0; line-height: 1;">
                            Delivered</span>
                    @elseif($computedStatus === 'completed')
                        <span class="badge badge-success px-1.5 py-0.5"
                            style="border-radius: 50px; font-size: 0.6rem; background: #f0fff4; color: #38a169; border: 1px solid #c6f6d5; line-height: 1;">
                            Completed</span>
                    @elseif($computedStatus === 'in_process')
                        <span class="badge badge-warning px-1.5 py-0.5"
                            style="border-radius: 50px; font-size: 0.6rem; background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; line-height: 1;">
                            In Process</span>
                    @elseif($computedStatus === 'partial')
                        <span class="badge badge-warning px-1.5 py-0.5"
                            style="border-radius: 50px; font-size: 0.59rem; background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; opacity: 0.85; line-height: 1; margin-bottom: 2px;">
                            Part Assigned</span>
                        <button class="btn btn-sm btn-primary rounded-pill fw-800"
                            onclick="openAssignModal({{ $order->id }}, '{{ $order->order_unique }}', '{{ $firstItem->product_name ?? '' }}', {{ $orderGross }}, {{ $orderLess }}, {{ $orderNet }}, {{ $orderWastagePer }}, {{ $orderWastageGm }}, {{ $orderFine }}, '{{ $firstItem->touch ?? '' }}')"
                            style="font-size: 0.8rem; border: none; background: linear-gradient(135deg, #ef6c00, #ff9800); color: white; padding: 2px 8px; line-height: 1.1; box-shadow: 0 2px 4px rgba(239, 108, 0, 0.15); margin-top: 1px;">
                            <i class="fa fa-user-plus mr-0.5" style="font-size: 0.6rem;"></i> Assign
                        </button>
                    @else
                        <span class="badge badge-info px-1.5 py-0.5"
                            style="border-radius: 50px; font-size: 0.65rem; background: #ebf8ff; color: #3182ce; border: 1px solid #bee3f8; opacity: 0.85; line-height: 1; margin-bottom: 2px;">
                            Pending</span>
                        <button class="btn btn-sm btn-primary rounded-pill fw-800"
                            onclick="openAssignModal({{ $order->id }}, '{{ $order->order_unique }}', '{{ $firstItem->product_name ?? '' }}', {{ $orderGross }}, {{ $orderLess }}, {{ $orderNet }}, {{ $orderWastagePer }}, {{ $orderWastageGm }}, {{ $orderFine }}, '{{ $firstItem->touch ?? '' }}')"
                            style="font-size: 0.8rem; border: none; background: linear-gradient(135deg, #ef6c00, #ff9800); color: white; padding: 2px 8px; line-height: 1.1; box-shadow: 0 2px 4px rgba(239, 108, 0, 0.15); margin-top: 1px;">
                            <i class="fa fa-user-plus mr-0.5" style="font-size: 0.6rem;"></i> Assign
                        </button>
                    @endif
                </div>
            </td>

            <!-- Column 7: Dates (Issue / Delivery) -->
            <td class="text-center" style="line-height: 1.25; max-width: 140px; white-space: nowrap;">
                <div style="font-size: 0.7rem; margin-bottom: 2px;">
                    <span class="text-muted fw-700" style="font-size: 0.55rem; text-transform: uppercase;">Issued:</span>
                    <span class="fw-800 text-dark">{{ date('d-M-Y', strtotime($order->order_date)) }}</span>
                </div>
                <div style="font-size: 0.7rem;">
                    <span class="text-muted fw-700" style="font-size: 0.55rem; text-transform: uppercase;">Delivery:</span>
                    <span class="fw-900 {{ $deliveryClass }}">{{ $order->delivery_date ? date('d-M-Y', strtotime($order->delivery_date)) : '-' }}</span>
                </div>
                @if($order->delivery_date)
                    <div class="small fw-800 {{ $deliveryClass }}" style="font-size: 0.55rem; opacity: 0.9; margin-top: 1px;">
                        ({{ $daysLeft >= 0 ? $daysLeft . ' Days Left' : abs($daysLeft) . ' Days Overdue' }})
                    </div>
                @endif
            </td>

            <!-- Column 8: Action -->
            <td class="text-center">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-icon btn-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" type="button"
                        data-toggle="dropdown"
                        style="border: 1.2px solid #cbd5e0; width: 26px; height: 26px; background: #fff; border-radius: 50% !important; margin: 0 auto; outline: none; transition: all 0.2s;">
                        <i class="fa fa-ellipsis-v text-muted" style="font-size: 0.75rem;"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-lg border-0"
                        style="border-radius: 12px; min-width: 160px; z-index: 1050;">
                        <a class="dropdown-item py-2 fw-800 small btn-view-order" href="javascript:void(0);" data-id="{{ $order->id }}"><i
                                class="fa fa-eye text-primary mr-2"></i> View Detail</a>
                        @if(!$hasDeliveredItems)
                            <a class="dropdown-item py-2 fw-800 small" href="{{ route('vendor.orders.edit', $order->id) }}"><i class="fa fa-edit text-info mr-2"></i> Edit Order</a>
                        @endif
                        <a class="dropdown-item py-2 fw-800 small" href="{{ route('vendor.orders.print', $order->id) }}" target="_blank"><i class="fa fa-print text-success mr-2"></i> Print Receipt</a>
                        @if(!$hasDeliveredItems)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item py-2 fw-800 small text-danger btn-delete-order" href="javascript:void(0);"
                                data-toggle="modal" data-target="#confirmDeleteModal"
                                data-delete-url="{{ route('vendor.orders.destroy', $order->id) }}"><i class="fa fa-trash mr-2"></i> Delete</a>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <p class="text-muted fw-800">No orders found for the selected criteria.</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>

@if($orders->count() > 0)
    <tfoot>
        <tr>
            <td colspan="3" class="text-right py-2">
                <span class="fw-800 text-muted uppercase" style="font-size: 0.75rem;">Totals :-</span>
            </td>
            <!-- Weights Totals -->
            <td class="text-center py-1">
                <div class="fw-900 text-danger" style="font-size: 0.85rem;">{{ number_format($totalGross, 3) }}
                    <small style="font-size: 0.65rem;">gm</small>
                </div>
                <div class="small text-success fw-800" style="font-size: 0.65rem;">Fine: {{ number_format($totalFine, 3) }}</div>
            </td>
            <!-- Amount Details Totals -->
            <td class="text-center py-1">
                <div class="fw-900 text-dark" style="font-size: 0.95rem;">₹{{ number_format($totalPayable, 0) }}</div>
                <div class="d-flex flex-column align-items-center" style="font-size: 0.7rem; line-height: 1.2;">
                    <div class="small text-info fw-900">Adv: ₹{{ number_format($totalAdvance, 0) }}</div>
                    @if($totalGoldAdv > 0 || $totalSilverAdv > 0)
                        @if($totalGoldAdv > 0)<div class="small text-warning fw-900">Au Adv: {{ number_format($totalGoldAdv, 3) }}g</div>@endif
                        @if($totalSilverAdv > 0)<div class="small text-secondary fw-900">Ag Adv: {{ number_format($totalSilverAdv, 3) }}g</div>@endif
                    @elseif($totalMetalAdvance > 0)
                        <div class="small text-warning fw-900">Metal: {{ number_format($totalMetalAdvance, 3) }}g</div>
                    @endif
                    @if($totalBalance < 0)
                        <div class="small text-success fw-900" style="background: #f0fff4; border-radius: 3.5px; padding: 0.5px 4px; margin-top: 1px;">
                            Adv: +₹{{ number_format(abs($totalBalance), 0) }}</div>
                    @else
                        <div class="small text-danger fw-900" style="background: #fff5f5; border-radius: 3.5px; padding: 0.5px 4px; margin-top: 1px;">
                            Bal: ₹{{ number_format($totalBalance, 0) }}</div>
                    @endif
                </div>
            </td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
@endif