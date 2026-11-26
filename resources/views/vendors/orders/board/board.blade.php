{{--
    =====================================================================
    JU Order pipeline (new UI) — the ONE page of the Order module.
    All orders · Karigar list · Billing list on one board; everything else
    (create / edit, details, summary, delivered, change status) opens as a popup.
    Used by receive/list, process/list, billing/index (each passes $activeStage).

    Old ids kept for receive/js/list_scripts.blade.php (old logic, unchanged):
      #keyword #status_filter #daterange-btn #daterange-text #reportrange .entries
      #select_all #order_table #data_area #paging_area #bulk_actions_bar #selected_count
      #bulk_assign_btn #bulk_delete_btn #bulk_print_btn #orderDetailModal #order_detail_area

    Create / Edit popup: the old create page is loaded in an iframe with ?embed=1.
    If that page does not answer within a few seconds (blocked iframe, JS error,
    X-Frame-Options DENY ...) the browser just opens the normal create/edit page,
    so the button never "does nothing". Set $jpPopup = false below to turn the popup off.

    Sections marked "NEW ADDITION" are features of the pipeline design that the
    old module did not have. Features that need a backend route only render when
    that route exists (Route::has), so nothing half-working is shown:
      - vendor.orders.deleted        GET  -> same JSON as list ({status, html, paging}) with deleted orders (listbody rows)
      - vendor.orders.restore {id}   POST -> restore a deleted order, returns {status, message}
      - vendor.orders.change_status  POST order_id, stage(order|karigar|billing|delivered) -> {status, message}  ("Skip")
    =====================================================================
--}}
@php
    $activeStage = $activeStage ?? 'order';
    // SWITCH: true = New order / Edit popup mein khulte hain (design).
    //         false = normal page pe khulte hain (purana tarika). Baaki sab same rehta hai.
    $jpPopup = true;
    $jpHasDeleted = \Illuminate\Support\Facades\Route::has('vendor.orders.deleted');
    $jpHasRestore = \Illuminate\Support\Facades\Route::has('vendor.orders.restore');
    $jpHasSkip = \Illuminate\Support\Facades\Route::has('vendor.orders.change_status');
@endphp
<div class="jp-root ju-board-page" data-active-stage="{{ $activeStage }}"
     data-url-order="{{ route('vendor.orders.list') }}"
     data-url-karigar="{{ route('vendor.orders.karigar_list') }}"
     data-url-billing="{{ route('vendor.orders.billing') }}"
     data-url-create="{{ route('vendor.orders.create') }}"
     data-popup="{{ $jpPopup ? 1 : 0 }}"
     data-url-deleted="{{ $jpHasDeleted ? route('vendor.orders.deleted') : '' }}"
     data-url-restore="{{ $jpHasRestore ? route('vendor.orders.restore', ':id') : '' }}"
     data-url-skip="{{ $jpHasSkip ? route('vendor.orders.change_status') : '' }}">
    @include('vendors.orders.board.icons')

    <div class="jp-board-wrap">
        <div class="jp-bmain">
            <!-- ===== header ===== -->
            <div class="jp-bhead" id="jp-bhead">
                <div class="jp-bh-r">
                    {{-- "New order" opens the create page in a popup (board/js); without JS the link still works --}}
                    <a href="{{ route('vendor.orders.create') }}" class="jp-btn-new jp-open-order">＋ New order</a>
                    <button type="button" class="jp-os-fab" aria-label="Order search"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-search"></use></svg></span></button>
                    <div class="jp-osrch" id="jp-osrch">
                        <input type="text" id="keyword" class="jp-srch-in" placeholder="Order ID, name, mobile, item, karigar" oninput="changeEntries()" autocomplete="off">
                        {{-- NEW ADDITION (pipeline design): search results list with "Change status" --}}
                        <div class="jp-os-res" id="jp-os-res" hidden></div>
                    </div>
                </div>
                <div class="jp-bh-r">
                    {{-- NEW ADDITION (pipeline design): Drafts of unsaved new orders (browser localStorage, no backend) --}}
                    <div class="jp-dr-wrap" id="jp-dr-wrap" hidden>
                        <button type="button" class="jp-btn-del jp-dr-btn2"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-file"></use></svg></span>Drafts <span class="jp-count" id="jp-dr-c">0</span></button>
                        <div class="jp-menu jp-dr-menu" id="jp-dr-menu"></div>
                    </div>
                    {{-- "All Time" date button hidden (design). It stays in the page because the old list_scripts
                         binds the date picker to #daterange-btn and sends #reportrange (empty = all time). --}}
                    <button type="button" class="jp-btn-del jp-dr-btn" id="daterange-btn" title="Date range" hidden style="display:none">
                        <span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-cal"></use></svg></span><span id="daterange-text">All Time</span>
                    </button>
                    <input type="hidden" id="reportrange" value="">
                    <div class="jp-ad-wrap">
                        <button type="button" class="jp-btn-del jp-ad-dots" aria-label="All orders details" title="All orders details">⋮<span class="jp-count" id="jp-dcount" hidden>0</span></button>
                        <div class="jp-menu jp-ad-menu">
                            <div class="jp-adm-h"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-clip"></use></svg></span>All orders details</div>
                            {{-- NEW ADDITION (pipeline design): Order summary popup --}}
                            <button type="button" class="jp-adm-it jp-sum" data-open="summary">
                                <span class="jp-adm-ic"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-chart"></use></svg></span></span>
                                <span class="jp-adm-tx"><b>Order summary</b><small>Overview of all orders</small></span><em class="jp-cnt-sum" hidden></em>
                            </button>
                            {{-- NEW ADDITION (pipeline design): Deleted orders. Always in the menu like the design;
                                 the list fills only when the backend route vendor.orders.deleted exists (see top comment). --}}
                            <button type="button" class="jp-adm-it jp-del" data-open="deleted">
                                <span class="jp-adm-ic"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span></span>
                                <span class="jp-adm-tx"><b>Deleted orders</b><small>Removed orders, restore here</small></span><em class="jp-cnt-del" hidden></em>
                            </button>
                            <button type="button" class="jp-adm-it jp-dlv" data-open="delivered">
                                <span class="jp-adm-ic"><span class="jp-ic jp-t-g"><svg viewBox="0 0 24 24"><use href="#ji-checkc"></use></svg></span></span>
                                <span class="jp-adm-tx"><b>Delivered orders</b><small>Handed over to customers</small></span><em class="jp-cnt-dlv" hidden></em>
                            </button>
                            <div class="jp-adm-show">
                                <span>Show per stage</span>
                                @include('layouts.theme.datatable.entry', ['default_entries' => 10])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== board: 3 stages ===== -->
            <div class="jp-board" id="jp-board">
                <section class="jp-col" data-stage="order">
                    <div class="jp-col-head">
                        <span class="jp-stg-ic"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-inbox"></use></svg></span></span>
                        <div class="jp-stg-t">
                            <h2>All orders <span class="jp-count">0</span></h2>
                            <div class="jp-col-ctl">
                                {{-- Status filter hata diya (design): All orders column hamesha "pending" orders dikhata hai.
                                     #status_filter id same rakhi hai kyunki purana list_scripts.blade.php isi ki value bhejta hai.
                                <select id="status_filter" onchange="changeEntries()" title="Status">
                                    <option value="all">All status</option>
                                    <option value="pending" selected>Pending</option>
                                    <option value="in_process">In process</option>
                                    <option value="completed">Completed / Ready</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                                --}}
                                <input type="hidden" id="status_filter" value="pending">
                                <label class="jp-all" title="Select all orders in this stage"><input type="checkbox" id="select_all"> All</label>
                            </div>
                        </div>
                        <div class="jp-col-sum"></div>
                        <a class="jp-plus jp-open-order" href="{{ route('vendor.orders.create') }}" aria-label="New order">+</a>
                    </div>
                    <div class="jp-col-list">
                        <table class="jp-tbl" id="order_table"><tbody id="data_area"></tbody></table>
                        <div id="paging_area" class="jp-paging"></div>
                    </div>
                </section>

                <section class="jp-col" data-stage="karigar">
                    <div class="jp-col-head">
                        <span class="jp-stg-ic"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-tool"></use></svg></span></span>
                        <div class="jp-stg-t"><h2>Karigar list <span class="jp-count">0</span></h2><small>Work in progress with karigar</small></div>
                        <div class="jp-col-sum"></div>
                    </div>
                    <div class="jp-col-list">
                        <table class="jp-tbl"><tbody class="jp-tb"></tbody></table>
                        <div class="jp-paging"></div>
                    </div>
                </section>

                <section class="jp-col" data-stage="billing">
                    <div class="jp-col-head">
                        <span class="jp-stg-ic"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-receipt"></use></svg></span></span>
                        <div class="jp-stg-t"><h2>Billing list <span class="jp-count">0</span></h2><small>Item purchased, ready for billing</small></div>
                        <div class="jp-col-sum"></div>
                    </div>
                    <div class="jp-col-list">
                        <table class="jp-tbl"><tbody class="jp-tb"></tbody></table>
                        <div class="jp-paging"></div>
                    </div>
                </section>
            </div>

            <!-- phone: stage buttons + scroll bar -->
            <div class="jp-sdots" id="jp-sdots">
                <button type="button" data-i="0" class="{{ $activeStage === 'order' ? 'jp-on' : '' }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-inbox"></use></svg></span>All orders</button>
                <button type="button" data-i="1" class="{{ $activeStage === 'karigar' ? 'jp-on' : '' }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-tool"></use></svg></span>Karigar</button>
                <button type="button" data-i="2" class="{{ $activeStage === 'billing' ? 'jp-on' : '' }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-receipt"></use></svg></span>Billing</button>
            </div>
            <div class="jp-sbar" id="jp-sbar"><i id="jp-sbar-t"></i></div>
        </div>
        <div class="jp-hint">Drag a card to the next stage, or use the button on the card.</div>
    </div>

    <!-- ===== All orders details popup: Order summary / Deleted / Delivered ===== -->
    <div class="jp-dov" id="jp-dov">
        <div class="jp-bigm" id="jp-bigm">
            <div class="jp-bm-head">
                <div><h3 class="jp-dv-title">Order summary</h3><p class="jp-dv-sub">Overview of all orders by stage, karigar and metal</p></div>
                <div class="jp-tabs">
                    {{-- NEW ADDITION (pipeline design) --}}
                    <button type="button" data-t="summary" class="jp-on"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-chart"></use></svg></span>Order summary</button>
                    {{-- NEW ADDITION (pipeline design): Deleted tab (list needs the backend route vendor.orders.deleted) --}}
                    <button type="button" data-t="deleted"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span>Deleted <span class="jp-dv-c2"></span></button>
                    <button type="button" data-t="delivered"><span class="jp-ic jp-t-g"><svg viewBox="0 0 24 24"><use href="#ji-checkc"></use></svg></span>Delivered <span class="jp-dv-c1"></span></button>
                </div>
                <button type="button" class="jp-x" aria-label="Close" data-close-dov>✕</button>
            </div>
            {{-- NEW ADDITION (pipeline design): filters like the design (work on the loaded list in the browser) --}}
            <div class="jp-fbar">
                <input class="jp-srch jp-dv-q jp-ph-ic" placeholder="Customer, mobile ya order ID" style="--phi: url(&quot;data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23D9620A'%20stroke-width%3D'1.9'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Ccircle%20cx%3D%2211%22%20cy%3D%2211%22%20r%3D%227%22%2F%3E%3Cpath%20d%3D%22m20%2020-3.5-3.5%22%2F%3E%3C%2Fsvg%3E&quot;);">
                <label class="jp-flbl"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-cal"></use></svg></span>Date range
                    <select class="jp-dv-rs"><option value="all">All time</option><option value="today">Today</option><option value="yesterday">Yesterday</option><option value="7">Last 7 days</option><option value="30">Last 30 days</option><option value="month">This month</option><option value="custom">Custom range</option></select></label>
                <div class="jp-drange jp-dv-cust" style="display:none">From <input type="date" class="jp-dv-from"> To <input type="date" class="jp-dv-to"></div>
                <select class="jp-dv-metal"><option value="all">All metal</option><option value="gold">Gold</option><option value="silver">Silver</option></select>
                <select class="jp-dv-pay"><option value="all">All payment</option><option value="paid">Paid</option><option value="due">Due</option></select>
                <button type="button" class="jp-chip jp-urg jp-dv-urg"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-bolt"></use></svg></span>Urgent only</button>
                <label class="jp-flbl jp-dv-showl" style="margin-left:auto">Show
                    <select class="jp-dv-show"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="0">All</option></select>
                    entries</label>
            </div>
            <div class="jp-dsum jp-dv-sum"></div>
            <div class="jp-dtbl-wrap">
                <table class="jp-dtbl">
                    <thead class="jp-dv-th"></thead>
                    <tbody class="jp-dv-tb"></tbody>
                </table>
            </div>
            <div class="jp-dpg jp-dv-pg"></div>
            <div class="jp-paging jp-dv-paging" hidden></div>
        </div>
    </div>
    <div class="jp-menu jp-dvm" id="jp-dvm"></div>

    <!-- ===== NEW ADDITION (pipeline design): Create / Edit order popup.
         Shows the old create / edit page (same form, same JS, same save) inside the popup. ===== -->
    <div class="jp-overlay jp-ov-order" id="jp-ov-order">
        <div class="jp-ofr">
            <div class="jp-ofr-load"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
            <iframe id="jp-order-frame" title="Order form" src="about:blank"></iframe>
        </div>
    </div>

    <!-- ===== NEW ADDITION (pipeline design): Change status popup ===== -->
    <div class="jp-overlay" id="jp-ov-cs">
        <div class="jp-modal jp-csm" id="jp-cs-modal"></div>
    </div>

    <!-- ===== NEW ADDITION (v9): "Delete order?" popup — shows what goes back where before the old M-PIN delete popup ===== -->
    <div class="jp-overlay" id="jp-ov-dl">
        <div class="jp-modal jp-dlm" id="jp-dl-modal"></div>
    </div>

    <!-- bulk actions (old markup + old functions) -->
    <div class="bulk-actions-bar" id="bulk_actions_bar" style="display: none;">
        <div class="selected-count d-flex align-items-center">
            <div class="count-badge" id="selected_count">0</div>
            <span>Selected</span>
        </div>
        <div class="action-buttons d-flex align-items-center">
            <button class="btn btn-sm" id="bulk_assign_btn" onclick="bulkAssign()"><i class="fa fa-user-plus mr-1"></i> Bulk Assign</button>
            <button class="btn btn-sm" id="bulk_delete_btn" onclick="bulkDelete()"><i class="fa fa-trash mr-1"></i> Bulk Delete</button>
            <button class="btn btn-sm" id="bulk_print_btn" onclick="bulkPrint()"><i class="fa fa-print mr-1"></i> Bulk Print</button>
        </div>
        <button class="btn btn-link jp-bulk-x" onclick="deselectAll()" aria-label="Clear selection"><i class="fa fa-times"></i></button>
    </div>

    <!-- View detail (old modal ids; content from receive/modals/detail_content) -->
    <div class="modal fade jp-vmodal" id="orderDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body" id="order_detail_area">
                    <div class="text-center py-5">
                        <i class="fa fa-spinner fa-spin mb-3" style="font-size: 2rem; color: #F07A1A;"></i>
                        <p class="text-muted">Loading order details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('vendors.orders.modals.assign_karigar')
@include('vendors.orders.modals.issue_material')
