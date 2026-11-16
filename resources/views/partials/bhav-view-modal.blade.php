<!-- =========================================================================
     MODAL: VIEW BOOKING DETAILS (SHARED PARTIAL)
     ========================================================================= -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl view-booking-modal-dialog">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 12px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <h6 class="modal-title fw-bold m-0 fs-6" id="viewBookingModalLabel">
                        Booking Details - <span id="viewBookingNo">-</span>
                    </h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 g-lg-3">
                    <!-- LEFT COLUMN (~40% on >=992px) -->
                    <div class="col-12 col-lg-5 view-modal-col-left">
                        <!-- Top Meta Badges & Date/Time -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 p-2 bg-light rounded-3 mb-2 border">
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <span id="viewTradeTypeBadge" class="badge rounded-pill px-2 py-1 fw-bold">BUY</span>
                                <span id="viewMetalBadge" class="badge rounded-pill px-2 py-1 fw-bold d-inline-flex align-items-center gap-1">GOLD</span>
                                <span id="viewTypeBadge" class="badge rounded-pill px-2 py-1 fw-bold">Only Entry</span>
                                <span id="viewStatusBadge" class="badge rounded-pill px-2 py-1 fw-bold">PENDING</span>
                            </div>
                            <div class="text-muted fw-semibold" style="font-size: 11px;">
                                <i class="fa-regular fa-clock me-1 text-primary"></i>
                                <span id="viewBookingDateTime">-</span>
                            </div>
                        </div>

                        <!-- Cancelled Booking Alert (shown if status is CANCELLED) -->
                        <div id="viewCancelledAlert" class="alert alert-danger p-2 mb-2 small" style="display: none; font-size: 11.5px; border-left: 4px solid #dc2626;">
                            <div class="d-flex align-items-center gap-1 fw-bold mb-1 text-danger">
                                <i class="fa-solid fa-ban"></i> Cancelled on <span id="viewCancelledAt">-</span>
                            </div>
                            <div class="text-dark">
                                <span class="text-muted">Reason:</span> <strong id="viewCancelReason">-</strong>
                            </div>
                        </div>

                        <!-- Customer Details 2x2 Grid -->
                        <div class="card border rounded-3 mb-2 shadow-none bg-white">
                            <div class="card-body p-2">
                                <div class="fw-bold text-secondary text-uppercase mb-1 border-bottom pb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-user me-1 text-primary"></i> Customer Details
                                </div>
                                <div class="row g-2" style="font-size: 12px;">
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Name</span>
                                        <strong id="viewCustomerName" class="text-dark d-block text-truncate">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Customer No</span>
                                        <strong id="viewCustomerNo" class="text-dark d-block text-truncate">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Mobile</span>
                                        <strong id="viewCustomerMobile" class="text-dark d-block text-truncate">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">City</span>
                                        <strong id="viewCustomerCity" class="text-dark d-block text-truncate">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totals Stat Chips in 2x2 Grid (plus Paid / Balance if payments exist) -->
                        <div class="row g-1 text-center mb-2">
                            <div class="col-6">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">TOTAL WEIGHT</div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;" id="viewTotalWeight">0.000 gm</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">TOTAL AMOUNT</div>
                                    <div class="fw-bold text-primary" style="font-size: 13px;" id="viewTotalAmount">₹0.00</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">DELIVERED WEIGHT</div>
                                    <div class="fw-bold text-secondary" style="font-size: 13px;" id="viewDeliveredWeight">0.000 gm</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">PENDING WEIGHT</div>
                                    <div class="fw-bold text-warning-emphasis" style="font-size: 13px;" id="viewPendingWeight">0.000 gm</div>
                                </div>
                            </div>
                            <!-- VS TODAY Stat Chip -->
                            <div class="col-6" id="viewTotalsVsTodayCol">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">VS TODAY</div>
                                    <div class="fw-bold text-muted" style="font-size: 13px;" id="viewVsToday">—</div>
                                </div>
                            </div>
                            <!-- Extra Stat Chips shown only when payments exist -->
                            <div class="col-6" id="viewTotalsPaidCol" style="display: none;">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">PAID</div>
                                    <div class="fw-bold text-success" style="font-size: 13px;" id="viewTotalsPaid">₹0.00</div>
                                </div>
                            </div>
                            <div class="col-6" id="viewTotalsBalanceCol" style="display: none;">
                                <div class="p-2 border rounded-2 bg-light">
                                    <div class="text-muted fw-semibold" style="font-size: 10px; letter-spacing: 0.3px;">BALANCE</div>
                                    <div class="fw-bold text-danger" style="font-size: 13px;" id="viewTotalsBalance">₹0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Narration Box: one line with ellipsis + more toggle -->
                        <div class="p-2 border rounded-3 bg-light" id="viewNarrationWrapper" style="font-size: 12px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-secondary" style="font-size: 11px; letter-spacing: 0.3px;">
                                    <i class="fa-solid fa-pen-clip me-1 text-primary"></i> Narration / Remarks:
                                </strong>
                                <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold" id="btnToggleNarration" style="font-size: 11px; display: none;">More</button>
                            </div>
                            <span id="viewNarration" class="text-dark view-narration-collapsed fst-italic">None</span>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN (~60% on >=992px) -->
                    <div class="col-12 col-lg-7 view-modal-col-right">
                        <!-- Booking Items Table -->
                        <div class="card border rounded-3 mb-2 shadow-none bg-white">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                                    <div class="fw-bold text-secondary text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-list-ol me-1 text-primary"></i> Booking Items
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0 fw-bold" id="viewItemsCountBadge" style="font-size: 10px;">0 items</span>
                                </div>
                                <div class="table-responsive view-modal-table-scroll" style="max-height: 220px; overflow-y: auto;">
                                    <table class="table table-sm table-striped align-middle mb-0 text-center view-compact-table">
                                        <thead class="table-light text-secondary sticky-top">
                                            <tr>
                                                <th class="text-center" style="width: 36px;">#</th>
                                                <th class="text-end">WEIGHT (GM)</th>
                                                <th class="text-end">RATE (₹{{ rate_unit_label(null, false) }})</th>
                                                <th class="text-end">AMOUNT (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewItemsTableBody">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Section -->
                        <div class="card border rounded-3 mb-2 shadow-none bg-white" id="viewPaymentsCard">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                                    <div class="fw-bold text-secondary text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-money-bill-wave me-1 text-primary"></i> Payments
                                    </div>
                                    <div class="fw-bold" style="font-size: 11px;">
                                        Paid: <span class="text-success" id="viewPaymentsPaid">₹0.00</span> &nbsp;|&nbsp;
                                        Bal: <span id="viewPaymentsBalance" class="text-danger">₹0.00</span>
                                    </div>
                                </div>
                                <div class="table-responsive view-modal-table-scroll" style="max-height: 150px; overflow-y: auto;">
                                    <table class="table table-sm table-striped align-middle mb-0 text-center view-compact-table">
                                        <thead class="table-light text-secondary sticky-top">
                                            <tr>
                                                <th class="text-center" style="width: 36px;">#</th>
                                                <th class="text-start">MODE</th>
                                                <th class="text-start">METHOD / REF</th>
                                                <th class="text-end">AMOUNT (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewPaymentsTableBody">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- History Section: Collapsible card under payments, collapsed by default -->
                        <div class="card border rounded-3 shadow-none bg-white" id="viewHistoryCard">
                            <div class="card-header bg-white border-0 p-2 d-flex justify-content-between align-items-center"
                                 id="viewHistoryHeader"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#collapseViewHistory"
                                 aria-expanded="false"
                                 aria-controls="collapseViewHistory"
                                 style="cursor: pointer; user-select: none;">
                                <div class="fw-bold text-secondary text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Activity Log (<span id="viewHistoryCount">0</span>)
                                </div>
                                <div class="text-muted fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                    <span id="viewHistoryCollapseText">Show History</span>
                                    <i class="fa-solid fa-chevron-down" id="viewHistoryChevron" style="font-size: 10px; transition: transform 0.2s ease;"></i>
                                </div>
                            </div>
                            <div class="collapse" id="collapseViewHistory">
                                <div class="card-body p-2 pt-0 border-top">
                                    <div id="viewHistoryEmpty" class="text-center text-muted small py-3" style="display: none; font-size: 11px;">
                                        <i class="fa-regular fa-clock me-1"></i> No activity logs recorded yet.
                                    </div>
                                    <div class="table-responsive view-modal-table-scroll" id="viewHistoryTableWrapper" style="max-height: 180px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle mb-0 view-compact-table">
                                            <thead class="table-light text-secondary sticky-top">
                                                <tr>
                                                    <th style="width: 130px;">DATE & TIME</th>
                                                    <th style="width: 80px;">ACTION</th>
                                                    <th>CHANGES / SUMMARY</th>
                                                </tr>
                                            </thead>
                                            <tbody id="viewHistoryTableBody" style="font-size: 11.5px;">
                                                <!-- Dynamically populated from booking.logs -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer: Compact height with Print Voucher and Close -->
            <div class="modal-footer bg-light px-3 py-2 border-0 d-flex justify-content-between">
                <div class="btn-group btn-group-sm rounded-pill" id="viewModalPrintGroup" style="height: 32px;">
                    <button type="button" class="btn btn-outline-print btn-sm rounded-start-pill px-3 py-1 fw-bold" id="btnViewModalPrint" style="font-size: 12px;">
                        <i class="fa-solid fa-print me-1"></i> <span id="viewModalPrintLabel">Print Voucher</span>
                    </button>
                    <button type="button" class="btn btn-outline-print btn-sm rounded-end-pill dropdown-toggle dropdown-toggle-split px-2" data-bs-toggle="dropdown" aria-expanded="false" title="Print Format">
                        <span class="visually-hidden">Toggle print format</span>
                    </button>
                    <ul class="dropdown-menu shadow-sm py-1" style="min-width: 150px; font-size: 12px;">
                        <li><a class="dropdown-item py-1 px-3 d-flex align-items-center justify-content-between print-option-a4" href="#" data-format="a4"><span>A4</span></a></li>
                        <li><a class="dropdown-item py-1 px-3 d-flex align-items-center justify-content-between print-option-thermal" href="#" data-format="thermal"><span>Thermal (80mm)</span></a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-bold" data-bs-dismiss="modal" style="font-size: 12px; height: 32px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
