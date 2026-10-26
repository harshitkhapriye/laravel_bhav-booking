<!-- =========================================================================
     MODAL: VIEW BOOKING DETAILS (SHARED PARTIAL)
     ========================================================================= -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 18px 24px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <h5 class="modal-title fw-bold m-0" id="viewBookingModalLabel">
                        Booking Details - <span id="viewBookingNo">-</span>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Navigation Tabs: Details & History -->
                <ul class="nav nav-pills mb-3 gap-2" id="viewModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-1 fw-bold small" id="tabViewDetailsBtn" data-bs-toggle="pill" data-bs-target="#tabViewDetails" type="button" role="tab" aria-selected="true">
                            <i class="fa-solid fa-file-invoice me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-1 fw-bold small" id="tabViewHistoryBtn" data-bs-toggle="pill" data-bs-target="#tabViewHistory" type="button" role="tab" aria-selected="false">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> History (<span id="viewHistoryCount">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="viewModalTabContent">
                    <!-- Tab 1: Details -->
                    <div class="tab-pane fade show active" id="tabViewDetails" role="tabpanel">
                        <!-- Top Badges & Meta -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-light rounded-3 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span id="viewTradeTypeBadge" class="badge rounded-pill px-3 py-1 fw-bold">BUY</span>
                                <span id="viewMetalBadge" class="badge rounded-pill px-3 py-1 fw-bold">GOLD</span>
                                <span id="viewTypeBadge" class="badge rounded-pill px-3 py-1 fw-bold">Only Entry</span>
                                <span id="viewStatusBadge" class="badge rounded-pill px-3 py-1 fw-bold">PENDING</span>
                            </div>
                            <div class="small text-muted fw-semibold">
                                <i class="fa-regular fa-clock me-1 text-primary"></i>
                                <span id="viewBookingDateTime">-</span>
                            </div>
                        </div>

                        <!-- Customer Details Box -->
                        <div class="card border rounded-3 mb-3 shadow-none bg-white">
                            <div class="card-body p-3">
                                <div class="fw-bold text-secondary small text-uppercase mb-2 border-bottom pb-1">
                                    <i class="fa-solid fa-user me-1 text-primary"></i> Customer Details
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-12 col-sm-6">
                                        <span class="text-muted">Name:</span> <strong id="viewCustomerName" class="text-dark">-</strong>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <span class="text-muted">Customer No:</span> <strong id="viewCustomerNo" class="text-dark">-</strong>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <span class="text-muted">Mobile:</span> <strong id="viewCustomerMobile" class="text-dark">-</strong>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <span class="text-muted">City:</span> <strong id="viewCustomerCity" class="text-dark">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items Table -->
                        <div class="card border rounded-3 mb-3 shadow-none bg-white">
                            <div class="card-body p-3">
                                <div class="fw-bold text-secondary small text-uppercase mb-2 border-bottom pb-1">
                                    <i class="fa-solid fa-list-ol me-1 text-primary"></i> Booking Items
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle mb-0 text-center">
                                        <thead class="table-light small text-secondary">
                                            <tr>
                                                <th class="py-2 text-center" style="width: 45px;">#</th>
                                                <th class="py-2 text-end">WEIGHT (GM)</th>
                                                <th class="py-2 text-end">RATE (₹{{ rate_unit_label(null, false) }})</th>
                                                <th class="py-2 text-end">AMOUNT (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewItemsTableBody" class="small">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Section -->
                        <div class="card border rounded-3 mb-3 shadow-none bg-white" id="viewPaymentsCard">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                                    <div class="fw-bold text-secondary small text-uppercase">
                                        <i class="fa-solid fa-money-bill-wave me-1 text-primary"></i> Payments
                                    </div>
                                    <div class="small fw-bold">
                                        Paid: <span class="text-success" id="viewPaymentsPaid">₹0.00</span> &nbsp;|&nbsp;
                                        Balance: <span id="viewPaymentsBalance" class="text-danger">₹0.00</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle mb-0 text-center">
                                        <thead class="table-light small text-secondary">
                                            <tr>
                                                <th class="py-2 text-center" style="width: 45px;">#</th>
                                                <th class="py-2 text-start">MODE</th>
                                                <th class="py-2 text-start">METHOD / REF</th>
                                                <th class="py-2 text-end">AMOUNT (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewPaymentsTableBody" class="small">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Totals Grid -->
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="small text-muted fw-semibold" style="font-size: 11px;">TOTAL WEIGHT</div>
                                    <div class="fw-bold text-dark fs-6" id="viewTotalWeight">0.000 gm</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="small text-muted fw-semibold" style="font-size: 11px;">TOTAL AMOUNT</div>
                                    <div class="fw-bold text-primary fs-6" id="viewTotalAmount">₹0.00</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="small text-muted fw-semibold" style="font-size: 11px;">DELIVERED WEIGHT</div>
                                    <div class="fw-bold text-secondary fs-6" id="viewDeliveredWeight">0.000 gm</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="small text-muted fw-semibold" style="font-size: 11px;">PENDING WEIGHT</div>
                                    <div class="fw-bold text-warning-emphasis fs-6" id="viewPendingWeight">0.000 gm</div>
                                </div>
                            </div>
                        </div>

                        <!-- Narration Box -->
                        <div class="p-3 border rounded-3 bg-light small" id="viewNarrationWrapper">
                            <strong class="text-secondary d-block mb-1">
                                <i class="fa-solid fa-pen-clip me-1 text-primary"></i> Narration / Remarks:
                            </strong>
                            <span id="viewNarration" class="text-dark fst-italic">None</span>
                        </div>
                    </div>

                    <!-- Tab 2: History (Activity Log) -->
                    <div class="tab-pane fade" id="tabViewHistory" role="tabpanel">
                        <div class="card border rounded-3 shadow-none bg-white">
                            <div class="card-body p-3">
                                <div class="fw-bold text-secondary small text-uppercase mb-2 border-bottom pb-1">
                                    <i class="fa-solid fa-timeline me-1 text-primary"></i> Activity Log
                                </div>
                                <div id="viewHistoryEmpty" class="text-center text-muted small py-4" style="display: none;">
                                    <i class="fa-regular fa-clock me-1"></i> No activity logs recorded yet.
                                </div>
                                <div class="table-responsive" id="viewHistoryTableWrapper">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light small text-secondary">
                                            <tr>
                                                <th style="width: 160px;">DATE & TIME</th>
                                                <th style="width: 100px;">ACTION</th>
                                                <th>CHANGES / SUMMARY</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewHistoryTableBody" class="small">
                                            <!-- Dynamically populated from booking.logs -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-print rounded-pill px-4 py-2" id="btnViewModalPrint">
                    <i class="fa-solid fa-print me-2"></i> Print Voucher
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
