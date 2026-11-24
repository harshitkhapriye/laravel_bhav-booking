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
                        <input type="text" id="keyword" class="jp-srch-in" placeholder="Order ID, naam, mobile, item, karigar" oninput="changeEntries()" autocomplete="off">
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
                    <button type="button" class="jp-btn-del jp-dr-btn" id="daterange-btn" title="Date range">
                        <span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-cal"></use></svg></span><span id="daterange-text">All Time</span>
                    </button>
                    <input type="hidden" id="reportrange" value="">
                    <div class="jp-ad-wrap">
                        <button type="button" class="jp-btn-del jp-ad-dots" aria-label="All orders details" title="All orders details">⋮</button>
                        <div class="jp-menu jp-ad-menu">
                            <div class="jp-adm-h">All orders details</div>
                            {{-- NEW ADDITION (pipeline design): Order summary popup --}}
                            <button type="button" class="jp-adm-it jp-sum" data-open="summary">
                                <span class="jp-adm-ic"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-chart"></use></svg></span></span>
                                <span class="jp-adm-tx"><b>Order summary</b><small>Saare orders ka hisaab</small></span>
                            </button>
                            @if($jpHasDeleted)
                                {{-- NEW ADDITION (pipeline design): Deleted orders (shows only when the backend route exists) --}}
                                <button type="button" class="jp-adm-it jp-del" data-open="deleted">
                                    <span class="jp-adm-ic"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span></span>
                                    <span class="jp-adm-tx"><b>Deleted orders</b><small>Hataye gaye orders, restore karo</small></span>
                                </button>
                            @endif
                            <button type="button" class="jp-adm-it jp-dlv" data-open="delivered">
                                <span class="jp-adm-ic"><span class="jp-ic jp-t-g"><svg viewBox="0 0 24 24"><use href="#ji-checkc"></use></svg></span></span>
                                <span class="jp-adm-tx"><b>Delivered orders</b><small>Customer ko diye gaye</small></span>
                            </button>
                            <div class="jp-adm-show">
                                <span>Har stage mein dikhao</span>
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
                                <label class="jp-all" title="Is stage ke saare order select karo"><input type="checkbox" id="select_all"> All</label>
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
                        <div class="jp-stg-t"><h2>Karigar list <span class="jp-count">0</span></h2><small>Karigar ke paas kaam chal raha</small></div>
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
                        <div class="jp-stg-t"><h2>Billing list <span class="jp-count">0</span></h2><small>Item purchase ho gaya, bill ke liye ready</small></div>
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
        <div class="jp-hint">Card ko drag karke next stage mein chhodo, ya card ka button use karo.</div>
    </div>

    <!-- ===== All orders details popup: Order summary / Deleted / Delivered ===== -->
    <div class="jp-dov" id="jp-dov">
        <div class="jp-bigm" id="jp-bigm">
            <div class="jp-bm-head">
                <div><h3 class="jp-dv-title">Order summary</h3><p class="jp-dv-sub">Saare orders ka hisaab: stage, karigar aur metal ke hisaab se</p></div>
                <div class="jp-tabs">
                    {{-- NEW ADDITION (pipeline design) --}}
                    <button type="button" data-t="summary" class="jp-on"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-chart"></use></svg></span>Order summary</button>
                    @if($jpHasDeleted)
                        {{-- NEW ADDITION (pipeline design) --}}
                        <button type="button" data-t="deleted"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-trash"></use></svg></span>Deleted</button>
                    @endif
                    <button type="button" data-t="delivered"><span class="jp-ic jp-t-g"><svg viewBox="0 0 24 24"><use href="#ji-checkc"></use></svg></span>Delivered</button>
                </div>
                <button type="button" class="jp-x" aria-label="Close" data-close-dov>✕</button>
            </div>
            <div class="jp-fbar">
                <input class="jp-srch jp-dv-q" placeholder="Customer, mobile ya order ID">
                <label class="jp-flbl jp-dv-showl">Show
                    <select class="jp-dv-show"><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option></select>
                    entries</label>
            </div>
            <div class="jp-dsum jp-dv-sum"></div>
            <div class="jp-dtbl-wrap">
                <table class="jp-dtbl">
                    <thead class="jp-dv-th"></thead>
                    <tbody class="jp-dv-tb"></tbody>
                </table>
            </div>
            <div class="jp-paging jp-dv-paging"></div>
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
