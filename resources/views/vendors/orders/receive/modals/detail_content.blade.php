<div class="order-detail-premium">
    <!-- Top Header Section: Compact & Informative -->
    <div class="modal-top-section d-flex align-items-center mb-4 p-3 bg-white rounded-xl shadow-sm" style="border: 1.5px solid #eef2ff;">
        <!-- 1. Left: Small Design Image -->
        <div class="design-thumbnail mr-3 position-relative">
            <!-- Delivery Badge moved to top-left of this section -->
            <!-- <div class="delivery-corner-badge" style="position: absolute; top: -15px; left: -10px; z-index: 10;">
                <div class="p-1 px-2 rounded bg-soft-danger border border-danger shadow-sm text-center" style="min-width: 100px;">
                    <div class="fw-900 text-danger" style="font-size: 0.75rem;">{{ $order->delivery_date ? date('d-M-Y', strtotime($order->delivery_date)) : 'NOT SET' }}</div>
                    <div class="small fw-800 text-danger uppercase" style="font-size: 0.5rem; letter-spacing: 0.5px;">DELIVERY</div>
                </div>
            </div> -->

            @if($order->design_image)
                <div class="design-frame p-1 rounded-lg bg-light" style="border: 1.5px solid #eef2ff;">
                    <img src="{{ asset($order->design_image) }}" alt="Design" class="rounded" style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;" onclick="window.open(this.src)">
                </div>
            @else
                <div class="no-design-placeholder rounded d-flex align-items-center justify-content-center bg-soft-light" style="width: 70px; height: 70px; border: 1.5px dashed #cbd5e1;">
                    <i class="fa fa-image text-muted" style="font-size: 1.2rem;"></i>
                </div>
            @endif
        </div>

        <!-- 2. Middle: Customer Info & Udhari -->
        <div class="customer-quick-info flex-grow-1 pl-3 pr-3" style="border-left: 1.5px solid #f1f5f9; border-right: 1.5px solid #f1f5f9;">
            <div class="d-flex align-items-center">
                <div class="avatar-sm rounded-circle bg-premium-indigo text-white d-flex align-items-center justify-content-center fw-900 mr-2" style="width: 28px; height: 28px; font-size: 0.8rem;">
                    {{ substr($order->customer->custo_full_name ?? $order->custo_name ?? 'C', 0, 1) }}
                </div>
                <div>
                    <h5 class="fw-900 text-dark mb-0" style="line-height: 1.1; font-size: 1.15rem;">{{ $order->customer->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer' }}</h5>
                    <p class="text-muted small fw-700 mb-0"><i class="fa fa-phone-alt mr-1"></i> {{ $order->customer->custo_fone ?? $order->custo_phone ?? 'N/A' }}</p>
                </div>
            </div>
            
            @if($udhari)
                <div class="udhari-status d-flex g-3 mt-1">
                    <div class="udhari-pill">
                        <span class="label">Total Udhari:</span>
                        <span class="value {{ $udhari->custo_amount_status == 1 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($udhari->custo_amount, 2) }} {{ $udhari->custo_amount_status == 1 ? 'Dr' : 'Cr' }}
                        </span>
                    </div>
                    @if($udhari->custo_gold > 0)
                    <div class="udhari-pill ml-3">
                        <span class="label">Gold:</span>
                        <span class="value {{ $udhari->custo_gold_status == 1 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($udhari->custo_gold, 3) }}g {{ $udhari->custo_gold_status == 1 ? 'Dr' : 'Cr' }}
                        </span>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- 3. Right: Order Meta, Rate & Delivery -->
        <div class="order-meta-info text-right" style="min-width: 250px;">
            <div class="d-flex justify-content-end align-items-center mb-1">
                <span class="text-muted fw-800 small mr-2">ORDER ID:</span>
                <span class="text-premium-indigo fw-900" style="font-size: 1.1rem;">#{{ $order->order_unique }}</span>
            </div>
            
            <div class="d-flex justify-content-end align-items-center g-2">
                <div class="rate-badge mr-2">
                    <span class="small fw-700 text-muted mr-1">Rate:</span>
                    <span class="fw-900 text-dark">₹{{ number_format($order->metal_rate, 0) }}</span>
                    <span class="badge bg-soft-indigo ml-1" style="font-size: 0.6rem;">{{ $order->rate_type == 'fixed' ? 'FIXED' : 'MARKET' }}</span>
                </div>
                <span class="badge {{ $order->status == 2 ? 'badge-success' : ($order->status == 3 ? 'badge-dark' : 'badge-warning') }} px-2 py-1 rounded-pill fw-800" style="font-size: 0.7rem;">
                    {{ $order->status == 2 ? 'COMPLETED' : ($order->status == 3 ? 'DELIVERED' : 'IN PROCESS') }}
                </span>
            </div>

            <div class="mt-1 small fw-700 text-muted">
                <i class="fa fa-calendar-alt mr-1"></i> Order: {{ date('d-M-Y', strtotime($order->order_date)) }}
            </div>
            <div class="mt-1 small fw-700 text-muted">
                <i class="fa fa-clock mr-1"></i> Delivery: {{ $order->delivery_date ? date('d-M-Y', strtotime($order->delivery_date)) : 'NOT SET' }}
            </div>

            @if($order->is_urgent)
                <div class="text-danger fw-900 small blink mt-1"><i class="fa fa-bolt mr-1"></i>URGENT DELIVERY</div>
            @endif

            <div class="mt-2 text-right">
                <a href="{{ route('vendor.orders.print', $order->id) }}" target="_blank" class="btn btn-xs fw-800" style="border-radius: 50px; font-size: 0.7rem; border: 1.5px solid #b8860b; color: #b8860b; background: #fffdf6; padding: 3px 10px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(184,134,11,0.08);">
                    <i class="fa fa-print" style="font-size: 0.75rem;"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Part: Order Items List -->
        <div class="col-md-7" style="border-right: 1.5px solid #f1f5f9;">
            <h6 class="section-title-premium"><i class="fa fa-shopping-cart mr-2"></i>ITEM SPECIFICATIONS</h6>
            <div class="items-container pr-2" style="max-height: 420px; overflow-y: auto;">
                @foreach($order->items as $item)
                    <div class="order-item-card mb-3 p-3 rounded-lg bg-light-gray shadow-xs" style="border-left: 1.5px solid #6366f1; border-top: 1.5px solid #f8fafc; border-right: 1.5px solid #f8fafc; border-bottom: 1.5px solid #f8fafc;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-900 text-dark mb-0">{{ $item->product_name }}</h6>
                                <span class="badge bg-white border text-muted px-2 py-0 fw-800" style="font-size: 0.65rem;">{{ strtoupper($item->jewellery_type) }}</span>
                                @if($item->status == 'in_process')
                                    <span class="badge px-2 py-0 fw-800 ml-1" style="font-size: 0.65rem; border: 1px solid #fde68a; background-color: #fffbeb; color: #d97706;">IN PROCESS</span>
                                @elseif($item->status == 'completed')
                                    <span class="badge px-2 py-0 fw-800 ml-1" style="font-size: 0.65rem; border: 1px solid #a7f3d0; background-color: #ecfdf5; color: #047857;">COMPLETED</span>
                                @else
                                    <span class="badge px-2 py-0 fw-800 ml-1" style="font-size: 0.65rem; border: 1px solid #bae6fd; background-color: #f0f9ff; color: #0369a1;">PENDING</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $item->metal_type == 'GOLD' ? 'bg-soft-gold' : 'bg-soft-silver' }} fw-900">
                                    {{ $item->metal_type }} | {{ $item->touch }}%
                                </span>
                            </div>
                        </div>
                        
                        <div class="row text-center bg-white rounded p-2 mx-0 shadow-xs">
                            <div class="col-4 border-right">
                                <div class="text-muted small fw-800">Gross</div>
                                <div class="fw-900">{{ number_format($item->gross_wt, 3) }}g</div>
                            </div>
                            <div class="col-4 border-right">
                                <div class="text-muted small fw-800">Net</div>
                                <div class="fw-900">{{ number_format($item->net_wt, 3) }}g</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small fw-800">Fine</div>
                                <div class="fw-900 text-indigo">{{ number_format($item->fine_wt, 3) }}g</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                            <div class="small fw-700 text-muted">
                                Making: <span class="text-dark">₹{{ number_format($item->making_charge, 2) }}</span> 
                                <span class="badge bg-soft-info ml-1">{{ $item->making_type == 'fixed' ? 'Fixed' : ($item->making_type == 'per_piece' ? '/pcs' : '/g') }}</span>
                            </div>
                            <div class="small fw-700 text-muted">
                                Labour: <span class="text-dark">₹{{ number_format($item->labor_charge, 2) }}</span> 
                                
                            </div>
                            <div class="small fw-700 text-muted">
                                Qty: <span class="text-dark">{{ $item->qty }}</span> | Size: <span class="text-dark">{{ $item->size ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($order->remarks)
                <div class="remarks-box mt-3 p-3 bg-soft-light rounded border-left border-3 border-warning italic fw-700 text-muted small">
                    <i class="fa fa-quote-left mr-2 opacity-50"></i>{{ $order->remarks }}
                </div>
            @endif
        </div>

        <!-- Right Part: Financial Summary -->
        <div class="col-md-5">
            <h6 class="section-title-premium text-indigo"><i class="fa fa-receipt mr-2"></i>PAYMENT SUMMARY</h6>
            
            <div class="payment-card p-3 rounded-xl bg-white shadow-sm" style="border: 1.5px solid #eef2ff; border-top: none;">
                <div class="summary-row d-flex justify-content-between mb-1">
                    <span class="text-muted fw-700">Total Bill Value:</span>
                    <span class="fw-900 text-dark">₹{{ number_format($order->total_payable, 2) }}</span>
                </div>
                
                @if($order->misc_charges > 0)
                <div class="summary-row d-flex justify-content-between mb-1 small">
                    <span class="text-muted fw-700">Misc. Charges:</span>
                    <span class="fw-800 text-dark">+₹{{ number_format($order->misc_charges, 2) }}</span>
                </div>
                @endif

                <div class="divider my-3"></div>

                <!-- Advances Section -->
                <div class="advances-section">
                    <h7 class="fw-900 small text-success mb-1 d-block"><i class="fa fa-check-circle mr-1"></i> ADVANCES PAID</h7>
                    
                    <div class="summary-row d-flex justify-content-between mb-1">
                        <span class="text-muted fw-700">Cash Advance:</span>
                        <span class="fw-900 text-success">₹{{ number_format($order->cash_adv, 2) }}</span>
                    </div>

                    @if($order->online_adv > 0)
                    <div class="summary-row d-flex justify-content-between mb-1">
                        <span class="text-muted fw-700">Online Advance:</span>
                        <span class="fw-900 text-success">₹{{ number_format($order->online_adv, 2) }}</span>
                    </div>
                    @if($order->bank)
                        <div class="text-right mb-1"><span class="badge bg-soft-indigo text-indigo fw-800" style="font-size: 0.6rem;">{{ $order->bank->name }}</span></div>
                    @endif
                    @endif

                    @php
                        $goldAdvWt = $order->getGoldAdvWeight();
                        $silverAdvWt = $order->getSilverAdvWeight();
                        $goldAdvCat = $order->gold_adv_category ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_category : 'kacha');
                        $silverAdvCat = $order->silver_adv_category ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_category : 'kacha');
                        $metalAdvCat = $order->metal_adv_category ?: 'kacha';
                    @endphp

                    @if($goldAdvWt > 0)
                    <div class="summary-row d-flex justify-content-between mb-1">
                        <span class="text-muted fw-700">Gold Advance <span class="badge badge-light border text-uppercase" style="font-size: 0.65rem;">{{ $goldAdvCat }}</span>:</span>
                        <span class="fw-900 text-warning">{{ number_format($goldAdvWt, 3) }}g @if($order->gold_adv_amount > 0) (₹{{ number_format($order->gold_adv_amount, 2) }}) @endif</span>
                    </div>
                    @endif

                    @if($silverAdvWt > 0)
                    <div class="summary-row d-flex justify-content-between mb-1">
                        <span class="text-muted fw-700">Silver Advance <span class="badge badge-light border text-uppercase" style="font-size: 0.65rem;">{{ $silverAdvCat }}</span>:</span>
                        <span class="fw-900 text-secondary">{{ number_format($silverAdvWt, 3) }}g @if($order->silver_adv_amount > 0) (₹{{ number_format($order->silver_adv_amount, 2) }}) @endif</span>
                    </div>
                    @endif

                    @if(!$goldAdvWt && !$silverAdvWt && $order->metal_adv_wt > 0)
                    <div class="summary-row d-flex justify-content-between mb-1">
                        <span class="text-muted fw-700">Metal ({{ strtoupper($order->metal_adv_type) }}) <span class="badge badge-light border text-uppercase" style="font-size: 0.65rem;">{{ $metalAdvCat }}</span>:</span>
                        <span class="fw-900 text-warning">{{ number_format($order->metal_adv_wt, 3) }}g</span>
                    </div>
                    @endif
                </div>

                @if($order->balance_due < 0)
                <div class="grand-balance-card mt-3 p-3 rounded-lg text-center" style="border: 2px solid #bbf7d0; background-color: #f0fdf4;">
                    <div class="text-success fw-800 small mb-1 uppercase letter-spacing-1">FINAL ADVANCE</div>
                    <div class="fw-900 text-success" style="font-size: 2.2rem; line-height: 1;">
                        ₹{{ number_format(abs($order->balance_due), 2) }}
                    </div>
                </div>
                @else
                <div class="grand-balance-card mt-3 p-3 rounded-lg bg-soft-red text-center" style="border: 2px solid #fee2e2;">
                    <div class="text-danger fw-800 small mb-1 uppercase letter-spacing-1">FINAL BALANCE DUE</div>
                    <div class="fw-900 text-danger" style="font-size: 2.2rem; line-height: 1;">
                        ₹{{ number_format($order->balance_due, 2) }}
                    </div>
                </div>
                @endif

                <div class="mt-3 text-center text-muted fw-800 small">
                    <i class="fa fa-info-circle mr-1"></i> Payable at time of delivery
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-xl { border-radius: 20px; }
    .bg-soft-light { background-color: #f8fafc; }
    .bg-soft-indigo { background-color: #eef2ff; color: #4f46e5; }
    .bg-soft-red { background-color: #fef2f2; }
    .bg-soft-gold { background-color: #fef3c7; color: #92400e; }
    .bg-soft-silver { background-color: #f1f5f9; color: #475569; }
    .bg-soft-info { background-color: #e0f2fe; color: #0369a1; }
    .bg-soft-danger { background-color: #fff1f2; }
    
    .text-premium-indigo { color: #4338ca; }
    .bg-premium-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }
    
    .section-title-premium {
        font-size: 0.75rem;
        font-weight: 900;
        letter-spacing: 1.5px;
        color: #94a3b8;
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
    }

    .udhari-pill {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .udhari-pill .label { color: #64748b; margin-right: 6px; }

    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    
    .order-item-card {
        transition: transform 0.2s;
        background-color: #ffffff;
    }
    .order-item-card:hover {
        transform: translateX(5px);
        background-color: #f8fafc;
    }

    .divider { height: 1px; background: #e2e8f0; width: 100%; }

    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }

    .letter-spacing-1 { letter-spacing: 1px; }
    .uppercase { text-transform: uppercase; }

    /* Custom Scrollbar */
    .items-container::-webkit-scrollbar { width: 5px; }
    .items-container::-webkit-scrollbar-track { background: #f8fafc; }
    .items-container::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 10px; }
</style>
