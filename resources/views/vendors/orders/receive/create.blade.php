@extends('layouts.vendors.app')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('theme/plugins/daterangepicker/daterangepicker.css') }}">
    @include('vendors.orders.receive.css.styles')
    @include('vendors.orders.receive.css.new_ui')
    {{-- NEW ADDITION: design styles (used by the draft popup) --}}
    @include('vendors.orders.board.css')
@endsection

@section('content')
    {{-- NEW ADDITION (pipeline design): this page also opens inside the board popup (?embed=1) --}}
    <script>
        if (/[?&]embed=1/.test(location.search) || window.self !== window.top) {
            document.documentElement.classList.add('ju-embed');
            // tell the board popup that the form page has loaded (else the board opens this page normally)
            document.addEventListener('DOMContentLoaded', function () { try { window.parent.postMessage({ juOrder: 'ready' }, '*'); } catch (x) {} });
        }
    </script>
    @php 
        $isEdit = isset($order);
        $anchor = [];
        if ($isEdit) {
            $anchor[] = header_btn_generate('New', route('vendor.orders.create'));
        }
        $anchor[] = list_menu_generate([
            ['title' => 'All Orders', 'route' => 'vendor.orders.list', 'is_default' => 1],
            ['title' => 'Karigars Assign List', 'route' => 'vendor.orders.karigar_list'],
            ['title' => 'Ready to Deliver List', 'route' => 'vendor.orders.billing'],
        ]);
        $path = ["Orders" => route('vendor.orders.list')];
        $title = $isEdit ? "Edit Order #".$order->order_unique : "New Order";
        $data = new_component_array('newbreadcrumb', $title, $path);
    @endphp
    <x-new-bread-crumb :data=$data :anchor=$anchor />


    <div id="full_page_loader" class="full-page-overlay" style="display: none;">
        <div class="loader-content">
            <div class="spinner-premium"></div>
            <div class="loader-text">SAVING ORDER...</div>
            <div class="loader-subtext">Please do not refresh or close the page</div>
        </div>
    </div>

    <div class="order-module ju-order">
        <div class="ju-wrap">
            <div class="erp-box ju-card-shell">
                <div class="erp-body">
                    <form id="createOrderForm" action="{{ $isEdit ? route('vendor.orders.update', $order->id) : route('vendor.orders.store') }}" method="POST"
                        enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        <input type="hidden" name="custo" id="custo_id" value="{{ $isEdit ? $order->custo_id : '' }}">
                        <input type="hidden" name="order_unique" id="order_unique"
                            value="{{ $isEdit ? $order->order_unique : 'ORD-' . mt_rand(100000, 999999) }}">
                        <input type="hidden" name="is_urgent" id="is_urgent" value="{{ $isEdit ? $order->is_urgent : '0' }}">
                        <input type="hidden" id="is_edit_mode" value="{{ $isEdit ? '1' : '0' }}">
                        <input type="hidden" name="items_json" id="items_json" value="{{ $isEdit ? json_encode($order->items) : '' }}">
                        <input type="hidden" name="custo_type" id="custo_type" value="{{ $isEdit ? $order->custo_type : 'c' }}">

                    <!-- HEADER: title + dates + order id (design popup header) -->
                    <div class="ju-head">
                        <h3 class="ju-ttl">{{ $isEdit ? 'Edit order #'.$order->order_unique : 'Create new order' }}</h3>
                        <div class="ju-head-meta">
                            <div class="ju-meta">
                                <label><i class="fa fa-calendar mr-1"></i> Order Date</label>
                                <input type="date" name="order_date" class="premium-input fw-800"
                                    min="2000-01-01" max="2099-12-31"
                                    value="{{ $isEdit ? $order->order_date : \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d') }}">
                            </div>
                            <div class="ju-meta">
                                <label class="small text-muted fw-800"><i
                                        class="fa fa-calendar-check mr-1 text-danger"></i> Delivery date</label>
                                <input type="date" name="delivery_date"
                                    class="footer-input text-danger fw-900" style="width: 150px;"
                                    min="{{ date('Y-m-d') }}" max="2099-12-31"
                                    value="{{ $isEdit ? $order->delivery_date : date('Y-m-d', strtotime('+7 days')) }}">
                            </div>
                            <div class="ju-meta ju-meta-id">
                                <label><i class="fa fa-hashtag mr-1"></i> Order ID</label>
                                <input type="text" id="display_order_id"
                                    class="premium-input bg-soft-light fw-900 text-indigo" value="{{ $isEdit ? $order->order_unique : '' }}"
                                    readonly style="flex: 1;">
                            </div>
                        </div>
                        <a class="ju-x" href="{{ route('vendor.orders.list') }}" aria-label="Close">✕</a>
                    </div>

                    <!-- CUSTOMER (design: name | phone, then remark, then advance) -->
                    <div class="ju-sec ju-cust">
                        <div class="ju-cust-top">
                            <div class="premium-form-group ju-cust-name">
                                <label><i class="fa fa-user mr-1"></i> Customer Name / Mobile</label>
                                <div class="input-with-action">
                                    <div class="input-with-icon-badge w-100 position-relative">
                                        <i class="fa fa-search"></i>
                                        <input type="text" name="name" id="name"
                                            class="summary-input w-100" placeholder="Customer name"{{-- placeholder text as in the design (was "Customer name / mobile"); search still works by name and mobile --}}
                                            oninput="searchCustomerOrder($(this))"
                                            value="{{ $isEdit ? ($order->custo_type === 's' ? ($order->party->supplier_num . '/' . $order->party->supplier_name . ' - ' . $order->party->mobile_no) : ($order->party->custo_num . '/' . $order->party->custo_full_name . ' - ' . $order->party->custo_fone)) : '' }}"
                                            {{ $isEdit ? 'readonly style=background-color:#f8fafc;cursor:not-allowed;' : '' }}
                                            autocomplete="off" autofocus>
                                        <ul id="customerlist" class="customer-dropdown shadow-lg"></ul>
                                    </div>
                                    @if(!$isEdit)
                                    <button type="button" class="btn-plus-premium ml-2"
                                        data-toggle="modal" data-target="#custo_modal" onclick="setinputnametocustomodal($('#name'), $('#custo_id').val() || false)"><i
                                            class="fa fa-plus"></i></button>
                                    @endif
                                </div>
                            </div>
                            {{-- NEW ADDITION (pipeline design): phone box. Display only - fills from the selected customer, not sent to the server. --}}
                            <div class="ju-phone">
                                <input type="text" class="premium-input ju-phone-in" placeholder="Phone number" readonly tabindex="-1">
                            </div>
                        </div>
                        <div class="footer-input-group ju-remark">
                            <label class="small text-muted fw-800"><i
                                    class="fa fa-comment-dots mr-1 text-warning"></i> Remark</label>
                            <input type="text" name="remarks" class="footer-input"
                                value="{{ $isEdit ? $order->remarks : '' }}"
                                placeholder="Design details or customer notes..." autocomplete="off">{{-- placeholder text as in the design --}}
                        </div>
                        {{-- Design mein nahi hai: "Design sketch" upload (comment out)
                        <div class="footer-input-group ju-sketch">
                            <label class="small text-muted fw-800"><i
                                    class="fa fa-camera mr-1 text-primary"></i> Sketch</label>
                            <div class="d-flex align-items-center">
                                <div class="mini-upload"
                                    onclick="document.getElementById('design_file').click()"
                                    title="Attach PNG/JPG">
                                    <input type="file" id="design_file" name="design_file"
                                        accept="image/*" style="display:none">
                                    <i class="fa fa-upload text-indigo"
                                        style="font-size: 1.1rem;"></i>
                                </div>
                                <div id="image_preview_container" class="ml-2"
                                    style="display:none; position: relative;">
                                    <div class="canvas-thumb"
                                        style="border: 2px solid #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.12);">
                                        <canvas id="sketch_canvas"
                                            style="height: 40px; width: 40px; display: block; background: #fff;"></canvas>
                                    </div>
                                    <button type="button" class="btn-remove-sketch"
                                        onclick="clearSketch()" title="Remove Sketch">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        --}}
                        {{-- NEW ADDITION (pipeline design): quick "Advance" box. It writes into the old Cash Adv. field (name="cash_adv"), no new data. --}}
                        <div class="footer-input-group ju-advq">
                            <label><i class="fa fa-wallet mr-1"></i> Advance (₹)</label>
                            <div class="input-with-unit">
                                <input type="text" class="footer-input ju-adv-in" inputmode="decimal" placeholder="0" autocomplete="off">
                                <span class="unit-tag">₹</span>
                            </div>
                        </div>
                    </div>

                    <!-- ADDED ITEMS -->
                    <div class="item-list-container my-4" id="item_list_container" style="display: none;">
                        <div class="table-card glass-panel shadow-lg">
                            <div class="p-3 bg-white border-bottom d-flex align-items-center">
                                <i class="fa fa-shopping-basket text-primary mr-2"></i>
                                <span class="fw-900 text-dark small text-uppercase">Items Summary</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th>Product</th>
                                            <th class="text-center">Metal</th>
                                            <th class="text-center">Weight</th>
                                            <th class="text-center">Charges</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center" width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item_list_body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ORDER DETAILS (collapsible) -->
                    <div class="ju-acc open" id="ju_acc_details">
                        <div class="ju-acc-h" role="button" tabindex="0" aria-expanded="true">
                            <span class="ju-tile"><i class="fa fa-file-alt"></i></span>
                            <b>Order details</b>
                            <em class="ju-acc-sum" id="ju_sum_details"></em>
                            <i class="fa fa-chevron-down ju-chev"></i>
                        </div>
                        <div class="ju-acc-b">
                            <div class="ju-grid">
                    {{-- Design mein nahi hai: "Rate Fix" field (comment out). Backend ko wahi default value hidden input se jaati hai.
                    <div class="premium-form-group ju-f ju-span2 ju-rate">
                        <label><i class="fa fa-clock mr-1"></i> Rate Fix</label>
                        <div class="select-wrapper">
                            <select name="rate_type" class="premium-select fw-800 text-primary"
                                style="border-color: var(--premium-indigo);">
                                <option value="fixed" {{ $isEdit && $order->rate_type == 'fixed' ? 'selected' : '' }}>Rate Fixed (Today)</option>
                                <option value="delivery" {{ $isEdit && $order->rate_type == 'delivery' ? 'selected' : '' }}>Rate on Delivery</option>
                            </select>
                        </div>
                    </div>
                    --}}
                    <input type="hidden" name="rate_type" value="{{ $isEdit && $order->rate_type ? $order->rate_type : 'fixed' }}">
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-cube mr-1"></i> Metal</label>
                        <select id="item_metal_type" class="premium-select fw-800">
                            <option>GOLD</option>
                            <option>SILVER</option>
                        </select>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-star mr-1"></i> Carat</label>
                        <div class="select-wrapper">
                            <select id="item_purity_karat" class="premium-select text-center fw-800">
                                <option value="">-</option>
                                <option value="18K">18K</option>
                                <option value="20K">20K</option>
                                <option value="22K" selected>22K</option>
                                <option value="24K">24K</option>
                            </select>
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-percentage mr-1"></i> % Purity</label>
                        <input type="text" step="0.01" id="item_metal_touch" inputmode="decimal"
                            class="premium-input text-center fw-800" value="91.67" readonly style="background-color: #f1f5f9; cursor: not-allowed;" autocomplete="off">
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-primary fw-800"><i class="fa fa-gem mr-1"></i> <span id="main_metal_label">GOLD RATE (1gm)</span></label>
                        <div class="input-with-icon-badge">
                            <input type="text" name="metal_rate_global" id="metal_rate_global" class="premium-input fw-900 text-primary" inputmode="decimal"
                                value="{{ $isEdit ? number_format($order->metal_rate, 2, '.', '') : '' }}"
                                placeholder="0.00" autocomplete="off">
                            <span class="badge-right fw-900" id="rate_unit_label" style="right: 10px; top: 50%; transform: translateY(-50%); position: absolute; font-size: 0.75rem;">/gm</span>
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-list-alt mr-1"></i> Jewellery type</label>
                        <div class="select-wrapper">
                            <select id="item_jewellery_type" class="premium-select">
                                <option value="">Choose Type...</option>
                            </select>
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-info-circle mr-1"></i> Product name</label>
                        <div class="select-wrapper">
                            <select id="item_product_name" class="premium-select">
                                <option value="">Choose Product...</option>
                            </select>
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-ruler mr-1"></i> Size</label>
                        {{-- placeholder text as in the design (was "S") --}}
                        <input type="text" id="item_size" inputmode="decimal" class="premium-input text-center"
                            placeholder="e.g. 12, 2.4" autocomplete="off">
                    </div>
                    <div class="premium-form-group ju-f">
                        <label><i class="fa fa-layer-group mr-1"></i> Qty</label>
                        <input type="text" id="item_qty" inputmode="decimal"
                            class="premium-input text-center fw-800" value="1" autocomplete="off">
                    </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITEM WEIGHT (collapsible) -->
                    <div class="ju-acc open" id="ju_acc_weight">
                        <div class="ju-acc-h" role="button" tabindex="0" aria-expanded="true">
                            <span class="ju-tile"><i class="fa fa-balance-scale"></i></span>
                            <b>Item weight</b>
                            <em class="ju-acc-sum" id="ju_sum_weight"></em>
                            <i class="fa fa-chevron-down ju-chev"></i>
                        </div>
                        <div class="ju-acc-b">
                            <div class="ju-grid">
                    <div class="premium-form-group ju-f">
                        <label class="text-danger fw-800"><i class="fa fa-weight mr-1"></i> Gross (gm)</label>
                        <div class="ju-suf" data-u="gm">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" inputmode="decimal" id="item_gross_wt"
                                class="premium-input text-danger fw-900 weight-input"
                                placeholder="0.000" autocomplete="off">
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-muted fw-800"><i class="fa fa-minus-circle mr-1"></i> Less (gm)</label>
                        <div class="ju-suf" data-u="gm">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" id="item_less_wt" inputmode="decimal"
                                class="premium-input text-muted fw-800 weight-input"
                                placeholder="0.000" autocomplete="off">
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="fw-800"><i class="fa fa-fire mr-1"></i> Wastage (gm)</label>
                        <div class="ju-suf" data-u="gm">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" id="item_wastage_per" inputmode="decimal"
                                class="premium-input fw-800 weight-input"  placeholder="0.000" autocomplete="off">
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="fw-800"><i class="fa fa-percent mr-1"></i> Wastage (%)</label>
                        <input type="text" id="item_wastage_percent" inputmode="decimal"
                            class="premium-input fw-800 weight-input"  placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-primary fw-800"><i class="fa fa-balance-scale mr-1"></i> Net (gm)</label>
                        <div class="ju-suf" data-u="gm">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" id="item_net_wt"
                                class="premium-input text-primary fw-900 bg-soft-indigo weight-input"
                                readonly placeholder="0.000">
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-success fw-800"><i class="fa fa-check-circle mr-1"></i> Fine (gm)</label>
                        <div class="ju-suf" data-u="gm">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" id="item_fine_wt"
                                class="premium-input text-success fw-900 weight-input" readonly
                                placeholder="0.000">
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-indigo fw-800"><i class="fa fa-hammer mr-1"></i> Making (₹)</label>
                        <div class="premium-multi-input d-flex">
                            <div class="ju-suf" data-u="₹">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                                <input type="text" id="item_making" inputmode="decimal"
                                    class="premium-input text-indigo fw-800" placeholder="0"
                                    style="flex: 1; min-width: 0;" autocomplete="off">
                            </div>
                            <select id="item_making_type"
                                class="premium-input-select text-indigo fw-800"
                                style="width: auto; border-left: 1px solid #e2e8f0; padding: 0 5px;">
                                <option value="per_gram">/gm</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                    <div class="premium-form-group ju-f">
                        <label class="text-indigo fw-800"><i class="fa fa-tools mr-1"></i> Labour (₹)</label>
                        <div class="ju-suf" data-u="₹">{{-- NEW ADDITION (pipeline design): unit shown inside the box --}}
                            <input type="text" id="item_labor" inputmode="decimal"
                                class="premium-input text-indigo fw-800" placeholder="0" autocomplete="off">
                        </div>
                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="ju-addrow">
                        {{-- NEW ADDITION (pipeline design): amount of the item being filled (display only, same formula as the old total) --}}
                        <span class="ju-iamt"><span>Item amount</span><b id="ju_item_amt">₹0</b></span>
                        <button type="button" class="btn-premium-add w-100" id="add_item_btn"
                            style=" height: 42px;
                            white-space: nowrap;
                            font-size: 12px;
                            padding: 0px 10px;">
                            <i class="fa fa-plus-circle mr-1"></i> ADD ITEM
                        </button>
                    </div>

                    <!-- PAYMENT SUMMARY (collapsible) -->
                    <div class="ju-acc open" id="ju_acc_pay" style="overflow: visible;">
                        <div class="ju-acc-h" role="button" tabindex="0" aria-expanded="true">
                            <span class="ju-tile"><i class="fa fa-credit-card"></i></span>
                            <b>Payment summary</b>
                            <div class="ju-stats">
                                {{-- NEW ADDITION (pipeline design): bill chip (copy of Total bill) --}}
                                <span class="ju-chip ju-chip-bill"><i class="fa fa-receipt"></i> <em id="ju_chip_bill">₹0</em></span>
                                <div class="summary-field ju-stat">
                                    <label class="small text-muted fw-800 text-uppercase"><i class="fa fa-info-circle mr-1"></i> Total Items</label>
                                    <div class="input-with-icon-badge">
                                        <i class="fa fa-shopping-basket"></i>
                                        <input type="text" id="total_items_display" class="summary-input bg-soft-light" readonly value="0">
                                        <span class="badge-right">PCS</span>
                                    </div>
                                </div>
                                <div class="summary-field ju-stat">
                                    <label class="small text-muted fw-800 text-uppercase"><i
                                            class="fa fa-star mr-1"></i> Total Fine</label>
                                    <div class="input-with-icon-badge">
                                        <i class="fa fa-balance-scale"></i>
                                        <input type="text" name="total_fine_display"
                                            id="total_fine_display"
                                            class="summary-input bg-soft-success text-success" readonly
                                            value="0.000">
                                        <span class="badge-right">gm</span>
                                    </div>
                                </div>
                                <div class="summary-field ju-stat">
                                    <label class="small text-muted fw-800 text-uppercase"><i
                                            class="fa fa-hammer mr-1"></i> Making (₹)</label>
                                    <div class="input-with-icon-badge">
                                        <i class="fa fa-hammer"></i>
                                        <input type="text" name="total_making_charges"
                                            id="total_making_charges" class="summary-input bg-soft-light"
                                            readonly value="0">
                                        <span class="badge-right">₹</span>
                                    </div>
                                </div>
                            </div>
                            <i class="fa fa-chevron-down ju-chev"></i>
                        </div>
                        <div class="ju-acc-b">
                            <div class="ju-grid ju-pay">
                                <div class="summary-field ju-f">
                                    <label class="small text-muted fw-800 text-uppercase"><i
                                            class="fa fa-plus-circle mr-1"></i> Others (₹)</label>
                                    <div class="input-with-icon-badge">
                                        <i class="fa fa-plus-circle"></i>
                                        <input type="text" name="misc_charges" id="misc_charges" inputmode="decimal"
                                            class="summary-input" value="{{ $isEdit ? number_format($order->misc_charges, 0, '.', '') : '0' }}" autocomplete="off">
                                        <span class="badge-right">₹</span>
                                    </div>
                                </div>
                                {{-- NEW ADDITION (pipeline design): "Total amount" box (copy of Total bill, read only) --}}
                                <div class="premium-form-group ju-f ju-tamt">
                                    <label><i class="fa fa-file-invoice mr-1"></i> Total amount (₹)</label>
                                    <div class="input-with-unit"><input type="text" class="premium-input ju-tamt-in" readonly tabindex="-1" value="0"><span class="unit-tag">₹</span></div>
                                </div>
                                <div class="premium-form-group ju-f">
                                    <label class="text-primary"><i class="fa fa-wallet mr-1"></i> Cash Adv. (₹)</label>
                                    <div class="input-with-unit">
                                        <input type="text" name="cash_adv" class="premium-input fw-800" inputmode="decimal"
                                            value="{{ $isEdit ? number_format($order->cash_adv, 0, '.', '') : '' }}"
                                            placeholder="0.00" autocomplete="off">
                                        <span class="unit-tag">₹</span>
                                    </div>
                                </div>
                                <div class="premium-form-group ju-f">
                                    <label class="text-info"><i class="fa fa-university mr-1"></i> Online Adv. (₹)</label>
                                    <div class="premium-multi-input">
                                        <input type="text" name="online_adv" class="premium-input fw-800" inputmode="decimal"
                                            value="{{ $isEdit ? number_format($order->online_adv, 0, '.', '') : '' }}"
                                            placeholder="0.00" style="flex: 1;" autocomplete="off">
                                        <select name="bank_id" class="premium-input-select"
                                            style="min-width: 120px; border-left: 1px solid #e2e8f0;">
                                            <option value="">Choose Bank...</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ $isEdit && $order->bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                                              @php
                                    $goldAdvGross = $isEdit ? ($order->gold_adv_gross ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_gross : 0)) : 0;
                                    $goldAdvNet = $isEdit ? ($order->gold_adv_net ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_net : 0)) : 0;
                                    $goldAdvTunch = $isEdit ? ($order->gold_adv_tunch ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_tunch : 0)) : 0;
                                    $goldAdvWt = $isEdit ? ($order->gold_adv_wt ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_wt : 0)) : 0;
                                    $goldAdvRate = $isEdit ? ($order->gold_adv_rate ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_rate : 0)) : 0;
                                    $goldAdvAmount = $isEdit ? ($order->gold_adv_amount ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_amount : 0)) : 0;
                                    $goldAdvJewelleryType = $isEdit ? ($order->gold_adv_jewellery_type ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_jewellery_type : '')) : '';
                                    $goldAdvCategory = $isEdit ? ($order->gold_adv_category ?: ($order->metal_adv_type == 'gold' ? ($order->metal_adv_category ?: 'kacha') : 'kacha')) : 'kacha';

                                    $silverAdvGross = $isEdit ? ($order->silver_adv_gross ?: ($order->metal_adv_type == 'silver' ? $order->silver_adv_gross : 0)) : 0;
                                    $silverAdvNet = $isEdit ? ($order->silver_adv_net ?: ($order->metal_adv_type == 'silver' ? $order->silver_adv_net : 0)) : 0;
                                    $silverAdvTunch = $isEdit ? ($order->silver_adv_tunch ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_tunch : 0)) : 0;
                                    $silverAdvWt = $isEdit ? ($order->silver_adv_wt ?: ($order->metal_adv_type == 'silver' ? $order->silver_adv_wt : 0)) : 0;
                                    $silverAdvRate = $isEdit ? ($order->silver_adv_rate ?: ($order->metal_adv_type == 'silver' ? $order->silver_adv_rate : 0)) : 0;
                                    $silverAdvAmount = $isEdit ? ($order->silver_adv_amount ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_amount : 0)) : 0;
                                    $silverAdvJewelleryType = $isEdit ? ($order->silver_adv_jewellery_type ?: ($order->metal_adv_type == 'silver' ? $order->silver_adv_jewellery_type : '')) : '';
                                    $silverAdvCategory = $isEdit ? ($order->silver_adv_category ?: ($order->metal_adv_type == 'silver' ? ($order->metal_adv_category ?: 'kacha') : 'kacha')) : 'kacha';

                                    $metalAdvCategory = $isEdit ? ($order->metal_adv_category ?: 'kacha') : 'kacha';
                                    $activeMetalType = ($goldAdvWt > 0 && $silverAdvWt > 0) ? 'both' : ($isEdit ? ($order->metal_adv_type ?: ($silverAdvWt > 0 ? 'silver' : 'gold')) : 'gold');
                                @endphp
                                <div class="ju-f ju-span2 ju-metal">
                                    <div class="premium-form-group mb-0 position-relative">
                                        <label class="text-warning d-flex justify-content-between align-items-center" id="toggle_metal_adv_details" style="cursor: pointer;" title="Click to open metal details">
                                            <span><i class="fa fa-cube mr-1"></i> Metal Adv. (gm)</span>
                                            <i class="fa fa-chevron-circle-up text-primary" style="font-size: 11px;"></i>
                                        </label>
                                        <input type="hidden" name="metal_adv_category" id="metal_adv_category" value="{{ $metalAdvCategory }}">
                                        <div class="premium-multi-input">
                                            <!-- Single Metal Input (Gold OR Silver) -->
                                            <input type="text" name="metal_adv" id="metal_adv" inputmode="decimal"
                                                value="{{ $isEdit ? number_format($order->metal_adv_wt, 3, '.', '') : '' }}"
                                                class="premium-input fw-800 {{ $activeMetalType == 'both' ? 'd-none' : '' }}" placeholder="0.000" autocomplete="off">

                                            <!-- Dual Metal Inputs (Gold + Silver) -->
                                            <div id="metal_adv_dual_inputs" class="d-flex w-100 gap-1 {{ $activeMetalType == 'both' ? '' : 'd-none' }}" style="flex: 1;">
                                                <input type="text" name="gold_adv_wt_main" id="gold_adv_wt_main" inputmode="decimal"
                                                    value="{{ $isEdit && $goldAdvWt > 0 ? number_format($goldAdvWt, 3, '.', '') : '' }}"
                                                    class="form-control premium-input fw-800 text-center px-1" placeholder="G: 0.000" style="height: 40px; font-size: 0.78rem; border-radius: 12px 0 0 12px !important; color: #b45309; background: #fefce8;" autocomplete="off" title="Gold Advance Weight">
                                                <input type="text" name="silver_adv_wt_main" id="silver_adv_wt_main" inputmode="decimal"
                                                    value="{{ $isEdit && $silverAdvWt > 0 ? number_format($silverAdvWt, 3, '.', '') : '' }}"
                                                    class="form-control premium-input fw-800 text-center px-1" placeholder="S: 0.000" style="height: 40px; font-size: 0.78rem; border-radius: 0 !important; border-left: none; color: #334155; background: #f8fafc;" autocomplete="off" title="Silver Advance Weight">
                                            </div>

                                            <select name="metal_adv_type" id="metal_adv_type" class="premium-input-select" style="min-width: 85px;">
                                                <option value="gold" {{ $activeMetalType == 'gold' ? 'selected' : '' }}>GOLD</option>
                                                <option value="silver" {{ $activeMetalType == 'silver' ? 'selected' : '' }}>SILVER</option>
                                                <option value="both" {{ $activeMetalType == 'both' ? 'selected' : '' }}>BOTH</option>
                                            </select>
                                        </div>

                                        <!-- Collapsible dropdown details panel -->
                                        {{-- OLD (floating popup above the field) - kept for reference:
                                        <div id="metal_adv_details_dropdown" class="card shadow-lg position-absolute" style="display: none; z-index: 1060; width: 330px; bottom: 55px; right: 0; border-radius: 16px; border: 1px solid #edf2f7; background: #ffffff; box-shadow: 0 -10px 25px rgba(0,0,0,0.15); padding: 15px !important;">
                                        --}}
                                        {{-- ===== NEW ADDITION: same id + classes, opens inline under the Metal adv. row (artifact design). Old JS .show()/.hide() works as before. Styles in css/new_ui (v8 block). ===== --}}
                                        <div id="metal_adv_details_dropdown" class="card shadow-lg position-absolute ju-mp" style="display: none;">
                                            <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                                                <span class="fw-900 text-indigo text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem;"><i class="fa fa-cube text-warning mr-1"></i> METAL PAY DETAILS</span>
                                                <button type="button" class="close" id="close_metal_adv_details" style="font-size: 1.2rem; border: none; background: transparent; cursor: pointer; color: #64748b;">&times;</button>
                                            </div>

                                            <!-- Metal Tabs (Visible when BOTH is selected) -->
                                            <div id="metal_adv_tabs_nav" class="d-flex gap-1 mb-2 {{ $activeMetalType == 'both' ? '' : 'd-none' }}">
                                                <button type="button" class="metal-adv-tab-btn gold-tab {{ $activeMetalType == 'silver' ? '' : 'active' }}" data-tab="gold_adv_panel">
                                                    <span><i class="fa fa-circle mr-1" style="color: #eab308; font-size: 8px;"></i> GOLD</span>
                                                    <span class="metal-tab-status-badge {{ $goldAdvWt > 0 ? 'badge-filled' : ($activeMetalType != 'silver' ? 'badge-active-gold' : 'badge-empty') }}" id="gold_tab_badge">{{ $goldAdvWt > 0 ? number_format($goldAdvWt, 3) . 'g' : '0.000g' }}</span>
                                                </button>
                                                <button type="button" class="metal-adv-tab-btn silver-tab {{ $activeMetalType == 'silver' ? 'active' : '' }}" data-tab="silver_adv_panel">
                                                    <span><i class="fa fa-circle mr-1" style="color: #94a3b8; font-size: 8px;"></i> SILVER</span>
                                                    <span class="metal-tab-status-badge {{ $silverAdvWt > 0 ? 'badge-filled' : ($activeMetalType == 'silver' ? 'badge-active-silver' : 'badge-empty') }}" id="silver_tab_badge">{{ $silverAdvWt > 0 ? number_format($silverAdvWt, 3) . 'g' : '0.000g' }}</span>
                                                </button>
                                            </div>

                                            <!-- GOLD SECTION PANEL -->
                                            <div id="gold_adv_panel" class="metal-adv-panel gold-panel {{ $activeMetalType == 'silver' ? 'd-none' : '' }}">
                                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom" style="border-color: #fef08a !important;">
                                                    <span class="fw-900" style="color: #92400e; font-size: 0.75rem; letter-spacing: 0.5px;">
                                                        <i class="fa fa-gem mr-1" style="color: #eab308;"></i> GOLD
                                                    </span>
                                                    <span class="badge badge-pill bg-white fw-900 shadow-xs" id="gold_panel_status_badge" style="border: 1px solid #fde047; font-size: 0.62rem; color: #b45309 !important;">
                                                        ACTIVE
                                                    </span>
                                                </div>

                                                <!-- Row 0: Gold Category (Kacha vs Bullion) -->
                                                <div class="mb-2">
                                                    <select name="gold_adv_category" id="gold_adv_category" class="form-control premium-input font-weight-bold" style="height: 34px; font-size: 0.8rem; border-radius: 20px !important; color: #b45309; border: 1.5px solid #fde047; background: #fffbeb;">
                                                        <option value="kacha" {{ $goldAdvCategory == 'kacha' ? 'selected' : '' }}>Kacha</option>
                                                        <option value="bullion" {{ $goldAdvCategory == 'bullion' ? 'selected' : '' }}>Bullion</option>
                                                    </select>
                                                </div>

                                                <!-- Row 1: Jewellery Type -->
                                                <div class="mb-2" id="gold_adv_jewellery_type_row">
                                                    <div class="position-relative">
                                                        <i class="fa fa-gem position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="gold_adv_jewellery_type" id="gold_adv_jewellery_type" autocomplete="off" 
                                                            value="{{ $goldAdvJewelleryType }}"
                                                            placeholder="Gold Jewellery Type..." class="form-control premium-input text-left" style="height: 34px; font-size: 0.82rem; padding-left: 32px !important; border-radius: 20px !important;">
                                                        <ul id="gold_adv_jewellery_sample_list" class="dropdown-menu w-100 shadow-lg" style="display:none; position:absolute; top:36px; left:0; z-index:1070; max-height:150px; overflow-y:auto; font-size:0.82rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;"></ul>
                                                    </div>
                                                </div>

                                                <!-- Row 2: Gross & Net -->
                                                <div class="d-flex gap-1 mb-2">
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label {{ $goldAdvGross > 0 ? 'active' : '' }}" id="label_gold_adv_gross">Gross (gm)</span>
                                                        <input type="text" name="gold_adv_gross" id="gold_adv_gross" inputmode="decimal"
                                                            value="{{ $goldAdvGross > 0 ? number_format($goldAdvGross, 3, '.', '') : '' }}" 
                                                            placeholder="Gross (gm)" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label {{ $goldAdvNet > 0 ? 'active' : '' }}" id="label_gold_adv_net">Net (gm)</span>
                                                        <input type="text" name="gold_adv_net" id="gold_adv_net" inputmode="decimal"
                                                            value="{{ $goldAdvNet > 0 ? number_format($goldAdvNet, 3, '.', '') : '' }}" 
                                                            placeholder="Net (gm)" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; border-radius: 0 20px 20px 0 !important; border-left: none;">
                                                    </div>
                                                </div>

                                                <!-- Row 3: Tunch & Fine -->
                                                <div class="d-flex gap-1 mb-2">
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label {{ $goldAdvTunch > 0 ? 'active' : '' }}" id="label_gold_adv_tunch" style="left: 14px;">Tunch %</span>
                                                        <i class="fa fa-tag position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="gold_adv_tunch" id="gold_adv_tunch" inputmode="decimal"
                                                            value="{{ $goldAdvTunch > 0 ? number_format($goldAdvTunch, 2, '.', '') : '' }}" 
                                                            placeholder="Tunch %" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; padding-left: 25px !important; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label text-warning {{ $goldAdvWt > 0 ? 'active' : '' }}" id="label_gold_adv_fine">Fine (gm)</span>
                                                        <input type="text" name="gold_adv_wt" id="gold_adv_fine" readonly
                                                            value="{{ $goldAdvWt > 0 ? number_format($goldAdvWt, 3, '.', '') : '' }}" 
                                                            placeholder="Fine (gm)" class="form-control premium-input text-center font-weight-bold" style="height: 34px; font-size: 0.82rem; border-radius: 0 20px 20px 0 !important; border-left: none; color: #b45309; background: #fefce8 !important;">
                                                    </div>
                                                </div>

                                                <!-- Row 4: Rate & Amount -->
                                                <div class="d-flex gap-1 mb-2" id="gold_adv_rate_amount_container" style="position: relative;">
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label text-indigo {{ $goldAdvRate > 0 ? 'active' : '' }}" id="label_gold_adv_rate" style="left: 14px;">Rate</span>
                                                        <i class="fa fa-line-chart position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #4f46e5; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="gold_adv_rate" id="gold_adv_rate" inputmode="decimal"
                                                            value="{{ $goldAdvRate > 0 ? number_format($goldAdvRate, 0, '.', '') : '' }}" 
                                                            placeholder="Rate" class="form-control premium-input text-center text-indigo" style="height: 34px; font-size: 0.82rem; padding-left: 30px !important; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label text-indigo {{ $goldAdvAmount > 0 ? 'active' : '' }}" id="label_gold_adv_amount" style="left: 14px;">Amount (₹)</span>
                                                        <i class="fa fa-inr position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #4f46e5; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="gold_adv_amount" id="gold_adv_amount" 
                                                            value="{{ $goldAdvAmount > 0 ? number_format($goldAdvAmount, 2, '.', '') : '' }}" 
                                                            placeholder="Amount (₹)" readonly class="form-control premium-input text-center font-weight-bold" style="height: 34px; font-size: 0.82rem; padding-left: 25px !important; border-radius: 0 20px 20px 0 !important; border-left: none; color: #4f46e5; background: #f1f5f9 !important;">
                                                    </div>
                                                </div>

                                                <div class="metal-both-nav-btn-wrapper {{ $activeMetalType == 'both' ? '' : 'd-none' }} mt-2">
                                                    <button type="button" class="btn btn-xs btn-warning w-100 switch-to-silver-btn fw-900 shadow-xs" style="border-radius: 20px; font-size: 0.72rem; padding: 5px 10px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none;">
                                                        Next: Silver Details <i class="fa fa-arrow-right ml-1"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- SILVER SECTION PANEL -->
                                            <div id="silver_adv_panel" class="metal-adv-panel silver-panel {{ $activeMetalType == 'silver' ? '' : 'd-none' }}">
                                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom" style="border-color: #e2e8f0 !important;">
                                                    <span class="fw-900" style="color: #0f172a; font-size: 0.75rem; letter-spacing: 0.5px;">
                                                        <i class="fa fa-cube mr-1" style="color: #64748b;"></i> SILVER
                                                    </span>
                                                    <span class="badge badge-pill bg-white fw-900 shadow-xs" id="silver_panel_status_badge" style="border: 1px solid #cbd5e1; font-size: 0.62rem; color: #475569 !important;">
                                                        ACTIVE
                                                    </span>
                                                </div>

                                                <!-- Row 0: Silver Category (Kacha vs Bullion) -->
                                                <div class="mb-2">
                                                    <select name="silver_adv_category" id="silver_adv_category" class="form-control premium-input font-weight-bold" style="height: 34px; font-size: 0.8rem; border-radius: 20px !important; color: #334155; border: 1.5px solid #cbd5e1; background: #f8fafc;">
                                                        <option value="kacha" {{ $silverAdvCategory == 'kacha' ? 'selected' : '' }}>Kacha</option>
                                                        <option value="bullion" {{ $silverAdvCategory == 'bullion' ? 'selected' : '' }}>Bullion</option>
                                                    </select>
                                                </div>

                                                <!-- Row 1: Jewellery Type -->
                                                <div class="mb-2" id="silver_adv_jewellery_type_row">
                                                    <div class="position-relative">
                                                        <i class="fa fa-cube position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="silver_adv_jewellery_type" id="silver_adv_jewellery_type" autocomplete="off" 
                                                            value="{{ $silverAdvJewelleryType }}"
                                                            placeholder="Silver Jewellery Type..." class="form-control premium-input text-left" style="height: 34px; font-size: 0.82rem; padding-left: 32px !important; border-radius: 20px !important;">
                                                        <ul id="silver_adv_jewellery_sample_list" class="dropdown-menu w-100 shadow-lg" style="display:none; position:absolute; top:36px; left:0; z-index:1070; max-height:150px; overflow-y:auto; font-size:0.82rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;"></ul>
                                                    </div>
                                                </div>

                                                <!-- Row 2: Gross & Net -->
                                                <div class="d-flex gap-1 mb-2">
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label {{ $silverAdvGross > 0 ? 'active' : '' }}" id="label_silver_adv_gross">Gross (gm)</span>
                                                        <input type="text" name="silver_adv_gross" id="silver_adv_gross" inputmode="decimal"
                                                            value="{{ $silverAdvGross > 0 ? number_format($silverAdvGross, 3, '.', '') : '' }}" 
                                                            placeholder="Gross (gm)" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label {{ $silverAdvNet > 0 ? 'active' : '' }}" id="label_silver_adv_net">Net (gm)</span>
                                                        <input type="text" name="silver_adv_net" id="silver_adv_net" inputmode="decimal"
                                                            value="{{ $silverAdvNet > 0 ? number_format($silverAdvNet, 3, '.', '') : '' }}" 
                                                            placeholder="Net (gm)" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; border-radius: 0 20px 20px 0 !important; border-left: none;">
                                                    </div>
                                                </div>

                                                <!-- Row 3: Tunch & Fine -->
                                                <div class="d-flex gap-1 mb-2">
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label {{ $silverAdvTunch > 0 ? 'active' : '' }}" id="label_silver_adv_tunch" style="left: 14px;">Tunch %</span>
                                                        <i class="fa fa-tag position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="silver_adv_tunch" id="silver_adv_tunch" inputmode="decimal"
                                                            value="{{ $silverAdvTunch > 0 ? number_format($silverAdvTunch, 2, '.', '') : '' }}" 
                                                            placeholder="Tunch %" class="form-control premium-input text-center" style="height: 34px; font-size: 0.82rem; padding-left: 25px !important; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="w-50 position-relative metal-field-group">
                                                        <span class="metal-floating-label text-secondary {{ $silverAdvWt > 0 ? 'active' : '' }}" id="label_silver_adv_fine">Fine (gm)</span>
                                                        <input type="text" name="silver_adv_wt" id="silver_adv_fine" readonly
                                                            value="{{ $silverAdvWt > 0 ? number_format($silverAdvWt, 3, '.', '') : '' }}" 
                                                            placeholder="Fine (gm)" class="form-control premium-input text-center font-weight-bold" style="height: 34px; font-size: 0.82rem; border-radius: 0 20px 20px 0 !important; border-left: none; color: #334155; background: #f8fafc !important;">
                                                    </div>
                                                </div>

                                                <!-- Row 4: Rate & Amount -->
                                                <div class="d-flex gap-1 mb-2" id="silver_adv_rate_amount_container" style="position: relative;">
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label text-indigo {{ $silverAdvRate > 0 ? 'active' : '' }}" id="label_silver_adv_rate" style="left: 14px;">Rate</span>
                                                        <i class="fa fa-line-chart position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #4f46e5; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="silver_adv_rate" id="silver_adv_rate" inputmode="decimal"
                                                            value="{{ $silverAdvRate > 0 ? number_format($silverAdvRate, 0, '.', '') : '' }}" 
                                                            placeholder="Rate" class="form-control premium-input text-center text-indigo" style="height: 34px; font-size: 0.82rem; padding-left: 30px !important; border-radius: 20px 0 0 20px !important;">
                                                    </div>
                                                    <div class="position-relative w-50 metal-field-group">
                                                        <span class="metal-floating-label text-indigo {{ $silverAdvAmount > 0 ? 'active' : '' }}" id="label_silver_adv_amount" style="left: 14px;">Amount (₹)</span>
                                                        <i class="fa fa-inr position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #4f46e5; font-size: 0.8rem; z-index: 1;"></i>
                                                        <input type="text" name="silver_adv_amount" id="silver_adv_amount" 
                                                            value="{{ $silverAdvAmount > 0 ? number_format($silverAdvAmount, 2, '.', '') : '' }}" 
                                                            placeholder="Amount (₹)" readonly class="form-control premium-input text-center font-weight-bold" style="height: 34px; font-size: 0.82rem; padding-left: 25px !important; border-radius: 0 20px 20px 0 !important; border-left: none; color: #4f46e5; background: #f1f5f9 !important;">
                                                    </div>
                                                </div>

                                                <div class="metal-both-nav-btn-wrapper {{ $activeMetalType == 'both' ? '' : 'd-none' }} mt-2 d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary w-50 switch-to-gold-btn fw-800" style="border-radius: 20px; font-size: 0.72rem; padding: 4px 8px;">
                                                        <i class="fa fa-arrow-left mr-1"></i> Back: Gold
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-success w-50 done-metal-adv-btn fw-900 shadow-xs" style="border-radius: 20px; font-size: 0.72rem; padding: 4px 8px; background: #059669; border: none; color: white;">
                                                        <i class="fa fa-check mr-1"></i> Done
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Summary Footer inside Dropdown -->
                                            <div class="border-top pt-2 mt-2 d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
                                                {{-- OLD text: Total Metal Value: --}}<span class="text-muted fw-700">Total metal value</span>
                                                <span class="fw-900 text-indigo" id="total_metal_adv_amount_summary">₹ 0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="summary-receipt ml-auto">
                                <div class="receipt-row">
                                    <span class="small text-muted fw-800 text-uppercase">Total Bill:</span>
                                    <span class="fw-900" id="total_payable_display"
                                        style="font-size: 1.3rem; color: var(--premium-slate);">₹ 0.00</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="small text-muted fw-800 text-uppercase">Paid Amount:</span>
                                    <span class="fw-900 text-success" id="advance_total_text"
                                        style="font-size: 1rem;">₹ 0.00</span>
                                </div>
                                <div class="receipt-total receipt-row">
                                    <span class="fw-800 text-danger text-uppercase" id="balance_due_label"
                                        style="font-size: 0.7rem;">Balance Due</span>
                                    <span class="fw-900 text-danger" id="balance_due_display"
                                        style="font-size: 1.7rem;">₹ 0.00</span>
                                </div>
                                <input type="hidden" name="total_payable" id="total_payable">
                                <input type="hidden" name="balance_due" id="balance_due">
                            </div>
                            <div class="status-glass-pill d-flex align-items-center">
                                <div class="pill-label">
                                    <i class="fa fa-history"></i> BALANCE
                                </div>
                                <div class="pill-data d-flex gap-2">
                                    <div class="status-item">
                                        <small id="cash_label">Cash</small>
                                        <span id="cust_cash_bal" class="text-muted fw-900">₹ 0</span>
                                    </div>
                                    <div class="status-item">
                                        <small id="gold_label">Gold</small>
                                        <span id="cust_gold_bal" class="text-muted fw-900">0.000</span>
                                    </div>
                                    <div class="status-item">
                                        <small id="silver_label">Silver</small>
                                        <span id="cust_silver_bal" class="text-muted fw-900">0.000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER ACTIONS (design: Urgent | Save & print | Save) -->
                    <div class="premium-footer-bar glass-panel ju-foot">
                        <div class="premium-priority-badge {{ $isEdit && $order->is_urgent ? 'active' : '' }}" id="urgent_badge"
                            onclick="toggleUrgent()">
                            <i class="fa fa-bolt"></i> <span>URGENT</span>
                        </div>
                        {{-- NEW ADDITION (pipeline design): Save & print = the old save, then the old "Print Receipt" choice is picked automatically --}}
                        <button type="button" class="ju-saveprint"><i class="fa fa-print mr-1"></i> {{ $isEdit ? 'Save & print' : 'Create & print' }}</button>
                        <div class="actions">
                            <button type="submit" class="btn-premium-confirm px-5 py-3 shadow-lg">
                                <i class="fa fa-check mr-2"></i> {{ $isEdit ? 'Save changes' : 'Create order' }}
                            </button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW ADDITION (pipeline design): "Draft save karein?" popup when a new order is closed without saving --}}
    <div class="jp-root">
        <div id="jp-drq" class="jp-drq">
            <div class="jp-drq-box" role="dialog" aria-label="Save draft">
                <div class="jp-drq-ic"><i class="fa fa-file-alt"></i></div>
                <h4>Save as draft?</h4>
                <p>This order has not been saved yet. Save it as a draft and finish it later from "Drafts".</p>
                <div class="jp-drq-f">
                    <button type="button" class="jp-b" data-a="back">Back to form</button>
                    <button type="button" class="jp-b jp-danger" data-a="discard">No, discard</button>
                    <button type="button" class="jp-b jp-pri" data-a="save">Save draft</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    @include('vendors.girvi.javascripts.common')
    @php
        $shopId = auth()->check() ? auth()->user()->shop_id : null;
        $branchId = auth()->check() ? auth()->user()->branch_id : null;
        $goldKarats = get_shop_metal_karats('1', $shopId, $branchId);
        $silverKarats = get_shop_metal_karats('2', $shopId, $branchId);
    @endphp
    <script>
        window.metalRates = {
            gold: {{ $rates['gold'] ?? 0 }},
            silver: {{ $rates['silver'] ?? 0 }}
        };
        window.defaultCategories = @json(categories(3)->pluck('name'));
        window.goldKaratsData = @json($goldKarats);
        window.silverKaratsData = @json($silverKarats);
    </script>
    @include('vendors.orders.receive.js.draft_restore')
    @include('vendors.orders.receive.js.scripts')
    @include('vendors.orders.receive.js.new_ui')
    @include('vendors.commonpages.newcustomerwithcategory')
@endsection
