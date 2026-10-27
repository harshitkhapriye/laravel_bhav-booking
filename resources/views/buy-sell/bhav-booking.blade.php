@extends('layouts.app')

@section('title', 'Bhav Booking - Just Udhari')

@section('content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- A) Page Title Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 page-header-bar">
        <!-- Title with Orange Dot -->
        <div class="d-flex align-items-center gap-2">
            <span class="title-orange-dot"></span>
            <h1 class="page-main-heading m-0">Bhav Booking</h1>
        </div>

        <!-- Action Buttons & Breadcrumbs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Today's Rate Button -->
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-1 d-flex align-items-center gap-2 text-dark bg-white shadow-sm" id="btnTodayRateModal" title="Click to view or set today's bullion rates">
                <i class="fa-solid fa-coins text-warning"></i>
                <span class="small fw-bold text-nowrap">Today's Rate:</span>
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle small" id="badgeTodayGold">Gold: ₹...</span>
                <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle small" id="badgeTodaySilver">Silver: ₹...</span>
            </button>

            <!-- Keyboard Shortcuts Link -->
            <button type="button" class="btn btn-light rounded-pill px-3 py-1 small fw-bold text-secondary border shadow-sm" id="btnOpenShortcutsModal" title="Keyboard Shortcuts (Ctrl+S, Alt+B, etc.)">
                <i class="fa-solid fa-keyboard me-1 text-primary"></i> Shortcuts
            </button>

            <button type="button" class="btn btn-outline-buy-sell rounded-pill px-3 py-1">
                <i class="fa-solid fa-bag-shopping me-1"></i> BUY-SELL
            </button>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn btn-navy-list rounded-pill px-3 py-1 dropdown-toggle" data-bs-toggle="dropdown" id="dropdownListBtn">
                    <i class="fa-solid fa-bars me-1"></i> LIST
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="listDropdownMenu">
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between active-list-item" href="{{ route('bhav-booking.index') }}" id="menuCurrentList">
                            <span><i class="fa-solid fa-list-ul text-warning me-2"></i> Current List</span>
                            <i class="fa-solid fa-check text-success small ms-3" id="iconCurrentCheck"></i>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('bhav-booking.old') }}" id="menuOldBooking">
                            <span><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i> Old Booking</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('parties.index') }}" id="menuPartyMaster">
                            <span><i class="fa-solid fa-users me-2 text-muted"></i> Party Master</span>
                        </a>
                    </li>
                </ul>
            </div>

            <a href="#" class="btn-home-link" title="Dashboard">
                <i class="fa-solid fa-house"></i>
            </a>

            <span class="badge-bhav-breadcrumb rounded-pill px-3 py-1">
                <i class="fa-solid fa-chevron-right me-1 small"></i> Bhav Booking
            </span>
        </div>
    </div>

    <!-- Main Content Grid (Two Cards: Left Form & Right List) -->
    <div class="row g-2 g-xl-3">

        <!-- B) Left Card: Form -->
        <div class="col-12 col-xl-6">
            <div class="card custom-content-card h-100 shadow-sm border-0">
                <div class="card-body p-2 p-md-3">

                    <!-- Editing Mode Indicator Banner -->
                    <div class="edit-mode-indicator alert alert-warning align-items-center justify-content-between py-1 px-3 mb-2 rounded-pill shadow-sm d-none" id="editModeIndicator">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-warning-emphasis"></i>
                            <span class="small fw-bold text-dark">Editing <span id="editingBookingNoLabel">BB-000001</span></span>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle ms-2" style="font-size: 10px; padding: 2px 6px;">
                                <i class="fa-solid fa-lock me-1"></i> Customer cannot be changed
                            </span>
                        </div>
                        <a href="#" id="linkCancelEditTop" class="text-danger fw-bold small text-decoration-none">
                            <i class="fa-solid fa-xmark me-1"></i> Cancel
                        </a>
                    </div>

                    <!-- BUY / SELL Toggle Switch -->
                    <div class="d-flex justify-content-center mb-2">
                        <div class="trade-toggle-container mode-sell" id="tradeToggle" data-mode="SELL">
                            <div class="trade-toggle-pill" id="tradeTogglePill"></div>
                            <button type="button" class="trade-toggle-option" id="btnBuy" data-type="BUY">
                                <i class="fa-solid fa-arrow-down me-1"></i> BUY
                            </button>
                            <button type="button" class="trade-toggle-option active" id="btnSell" data-type="SELL">
                                <i class="fa-solid fa-arrow-up me-1"></i> SELL
                            </button>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <form id="bhavBookingForm" autocomplete="off" onsubmit="return false;">
                        
                        <!-- Hidden inputs for form data -->
                        <input type="hidden" name="trade_type" id="hiddenTradeType" value="SELL">
                        <input type="hidden" name="customer_id" id="hiddenCustomerId" value="">
                        <input type="hidden" name="amount" id="hiddenAmount" value="0.00">

                        <!-- CUSTOMER NO / NAME -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" for="customerSearchInput">
                                CUSTOMER NO / NAME :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-with-icon flex-grow-1 position-relative" id="customerSearchWrapper">
                                        <i class="fa-solid fa-user field-icon" id="customerFieldIcon"></i>
                                        <input type="text"
                                               class="form-control form-input-pill ps-5 pe-5"
                                               id="customerSearchInput"
                                               placeholder="Select Customer..."
                                               autocomplete="off">
                                        <button type="button"
                                                class="btn-customer-clear btn-clear-input"
                                                id="customerClearBtn"
                                                style="display: none;"
                                                title="Clear selected customer"
                                                aria-label="Clear customer">&times;</button>
                                        <!-- Autocomplete Results Dropdown -->
                                        <div class="customer-dropdown-menu shadow" id="customerSearchResults" style="display: none;"></div>
                                    </div>
                                    <button type="button" class="btn btn-round-navy flex-shrink-0" id="btnOpenAddCustomerModal" title="Add Customer">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </div>
                                <!-- Customer details info line (mobile & city) -->
                                <div id="customerSelectedInfo" class="customer-info-line text-muted small mt-1 ps-2" style="display: none;"></div>
                                <!-- Validation error for customer -->
                                <div id="customerError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- TYPE -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" for="typeSelect">
                                TYPE :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="input-with-icon">
                                    <i class="fa-solid fa-cube field-icon"></i>
                                    <select class="form-select form-input-pill ps-5" id="typeSelect">
                                        <option value="ONLY_ENTRY" selected>Only Entry</option>
                                        <option value="BHAV_CUT_LEDGER">Bhav Cut + Ledger</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- CATEGORY -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" for="categorySelect">
                                CATEGORY :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="input-with-icon">
                                    <i class="fa-solid fa-cube field-icon"></i>
                                    <select class="form-select form-input-pill ps-5" id="categorySelect">
                                        <option value="GOLD" selected>Gold</option>
                                        <option value="SILVER">Silver</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- WEIGHT (Dynamic Label) -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" id="labelWeight" for="inputWeight">
                                SELL WEIGHT :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-with-suffix flex-grow-1">
                                        <input type="text" inputmode="decimal" class="form-control form-input-pill pe-5" id="inputWeight" placeholder="0.000">
                                        <span class="field-suffix">GM</span>
                                    </div>
                                    <button type="button" class="btn btn-round-navy flex-shrink-0" id="btnAddWeight" title="Add Weight Entry">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <div id="weightError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- RATE (Dynamic Label: Quoted per 10 Grams) -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" id="labelRate" for="inputRate">
                                SELL RATE {{ rate_unit_label() }} :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="input-with-suffix">
                                    <input type="text" inputmode="decimal" class="form-control form-input-pill pe-5" id="inputRate" value="165000">
                                    <span class="field-suffix">₹</span>
                                </div>
                                <span class="small text-muted ps-2 fst-italic d-inline-block mt-1" id="rateTodayHint" style="display: none;">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> (today's rate)
                                </span>
                                <div id="rateError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- AMOUNT -->
                        <div class="row mb-2 align-items-center form-row-group">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" for="inputAmount">
                                AMOUNT :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="input-with-icon input-with-suffix">
                                    <i class="fa-solid fa-calculator field-icon"></i>
                                    <input type="text" class="form-control form-input-pill ps-5 pe-5 bg-light-readonly" id="inputAmount" value="0.00" readonly>
                                    <span class="field-suffix">₹</span>
                                </div>
                            </div>
                        </div>

                        <!-- ENTRIES MINI-TABLE (Multi-weight rows) -->
                        <div class="row mb-2 form-row-group" id="entriesRowWrapper" style="display: none;">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label pt-1" id="labelEntries">
                                ENTRIES (<span id="entriesCountBadge">0</span>) :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="entries-table-card rounded-3 border bg-white shadow-sm p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm entries-table mb-0 align-middle">
                                            <thead class="bg-light text-secondary small">
                                                <tr>
                                                    <th class="text-center py-2 col-entry-sn">#</th>
                                                    <th class="text-end py-2 col-entry-wt">WEIGHT (GM)</th>
                                                    <th class="text-end py-2 col-entry-rt">RATE (₹)</th>
                                                    <th class="text-end py-2 col-entry-amt">AMOUNT (₹)</th>
                                                    <th class="text-center py-2 col-entry-del"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="entriesTableBody">
                                                <!-- Dynamic rows rendered from single source of truth in JS -->
                                            </tbody>
                                            <tfoot class="table-light fw-bold small">
                                                <tr>
                                                    <td class="text-center text-muted">TOTAL</td>
                                                    <td class="text-end text-primary" id="entriesTotalWeight">0.000 gm</td>
                                                    <td></td>
                                                    <td class="text-end text-success" id="entriesTotalAmount">₹0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="entriesGlobalError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- PAYMENT (Dynamic Label: SELL -> RECEIVED :, BUY -> GIVEN :) -->
                        <div class="row mb-2 align-items-center form-row-group" id="paymentRowGroup">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label" id="labelPayment" for="inputPaymentAmount">
                                RECEIVED :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap" style="min-width: 0; width: 100%;">
                                    <!-- Payment Mode Toggle (Cash / Online) -->
                                    <div class="payment-mode-toggle-container mode-cash flex-shrink-0" id="paymentModeToggle" data-mode="CASH">
                                        <div class="payment-mode-toggle-pill" id="paymentModeTogglePill"></div>
                                        <button type="button" class="payment-mode-toggle-btn active" id="btnPaymentCash" data-mode="CASH">Cash</button>
                                        <button type="button" class="payment-mode-toggle-btn" id="btnPaymentOnline" data-mode="ONLINE">Online</button>
                                    </div>

                                    <!-- Online Method (Shown only when Online is selected) -->
                                    <div id="paymentOnlineFields" class="d-none align-items-center flex-shrink-0">
                                        <select class="form-select form-input-pill py-1 px-2 small" id="paymentOnlineMethod" style="width: auto; min-width: 82px; font-size: 11.5px; height: 38px;">
                                            <option value="UPI" selected>UPI</option>
                                            <option value="BANK">Bank</option>
                                            <option value="CARD">Card</option>
                                            <option value="CHEQUE">Cheque</option>
                                        </select>
                                    </div>

                                    <!-- Payment Amount Input (Takes remaining width) -->
                                    <div class="input-with-suffix flex-grow-1" style="min-width: 0;">
                                        <input type="text" inputmode="decimal" class="form-control form-input-pill pe-5" id="inputPaymentAmount" placeholder="0.00" style="min-width: 0;">
                                        <span class="field-suffix">₹</span>
                                    </div>

                                    <!-- Add Payment Button -->
                                    <button type="button" class="btn btn-round-navy flex-shrink-0" id="btnAddPayment" title="Add Payment Entry">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <div id="paymentError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- PAYMENT ENTRIES MINI-TABLE -->
                        <div class="row mb-2 form-row-group" id="paymentEntriesRowWrapper" style="display: none;">
                            <label class="col-12 col-sm-4 text-sm-end form-label-label pt-1" id="labelPaymentEntries">
                                PAYMENTS (<span id="paymentEntriesCountBadge">0</span>) :
                            </label>
                            <div class="col-12 col-sm-8">
                                <div class="entries-table-card rounded-3 border bg-white shadow-sm p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm entries-table mb-0 align-middle">
                                            <thead class="bg-light text-secondary small">
                                                <tr>
                                                    <th class="text-center py-2 col-entry-sn" style="width: 35px;">#</th>
                                                    <th class="py-2 text-start">MODE</th>
                                                    <th class="text-end py-2" style="width: 140px;">AMOUNT (₹)</th>
                                                    <th class="text-center py-2 col-entry-del" style="width: 35px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentEntriesTableBody">
                                                <!-- Dynamic payment rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUMMARY LINE: Paid: ₹x | Balance: ₹y -->
                        <div class="row mb-2 form-row-group" id="paymentSummaryRow">
                            <div class="col-12 col-sm-8 offset-sm-4">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 rounded bg-light border small fw-bold" id="paymentSummaryBox">
                                    <span class="text-secondary">
                                        Paid: <span id="paymentSummaryPaid" class="text-dark">₹0.00</span>
                                    </span>
                                    <span>
                                        Balance: <span id="paymentSummaryBalance" class="text-success">₹0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Dashed Divider -->
                        <div class="dashed-line-divider my-2"></div>

                        <!-- Narration / Remarks -->
                        <div class="mb-2">
                            <div class="remarks-input-wrapper">
                                <i class="fa-solid fa-pen remarks-icon"></i>
                                <textarea class="form-control remarks-textarea" id="inputNarration" rows="2" maxlength="255" placeholder="Enter narration or transaction remarks..."></textarea>
                            </div>
                            <div id="narrationCounter" class="text-end small text-muted mt-0 pe-2" style="font-size: 11px;">0/255</div>
                        </div>

                        <!-- WhatsApp Toggle Pill -->
                        <div class="d-flex justify-content-center mb-2">
                            <div class="whatsapp-pill-badge">
                                <i class="fa-brands fa-whatsapp text-success whatsapp-icon"></i>
                                <span class="whatsapp-text">Send on WhatsApp</span>
                                <div class="form-check form-switch m-0 ms-2">
                                    <input class="form-check-input whatsapp-switch-input" type="checkbox" role="switch" id="whatsappSwitch">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                            <button type="button" class="btn btn-outline-print rounded-pill px-4 py-2" id="btnPrint">
                                <i class="fa-solid fa-print me-2"></i> PRINT
                            </button>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-gradient-save rounded-pill px-5 py-2" id="btnSave">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> SAVE
                                </button>
                                <a href="#" class="btn-cancel-edit text-muted text-decoration-underline small fw-semibold" id="linkCancelEdit" style="display: none;">
                                    Cancel edit
                                </a>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- C) Right Card: List -->
        <div class="col-12 col-xl-6">
            <div class="card custom-content-card h-100 shadow-sm border-0">
                <div class="card-body p-2 p-md-3 d-flex flex-column">

                    <!-- List Header & View Badge -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-3 py-1 text-white d-inline-flex align-items-center gap-2" id="listHeadingBadge" style="background-color: var(--navy-dark); font-size: 13px; font-weight: 700; letter-spacing: 0.3px;">
                                <i class="fa-solid fa-list-ul text-warning" id="listHeadingIcon"></i>
                                <span id="listHeadingText">Current Bookings</span>
                            </span>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btnExportBookings" title="Export filtered bookings to CSV">
                            <i class="fa-solid fa-file-csv"></i> Export
                        </button>
                    </div>

                    <!-- Filter Bar Row 1: Search Party & Date Range -->
                    <div class="row g-2 align-items-center mb-2">
                        <!-- Search Party -->
                        <div class="col-12 col-md-6">
                            <div class="input-with-icon">
                                <i class="fa-solid fa-magnifying-glass field-icon text-muted"></i>
                                <input type="text" class="form-control form-input-pill ps-5" id="filterPartyInput" placeholder="Search Party, Mobile, Booking No...">
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="col-12 col-md-6 position-relative" id="dateRangePickerWrapper">
                            <input type="hidden" name="date_from" id="dateFilterFrom" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="date_to" id="dateFilterTo" value="{{ date('Y-m-d') }}">
                            <div class="input-with-suffix cursor-pointer">
                                <input type="text"
                                       class="form-control form-input-pill text-center pe-5 cursor-pointer"
                                       id="dateRangeInput"
                                       value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}"
                                       readonly>
                                <span class="field-suffix text-muted">
                                    <i class="fa-regular fa-calendar-days"></i>
                                </span>
                            </div>
                            <!-- Presets Dropdown -->
                            <div class="date-range-dropdown shadow" id="dateRangeDropdown" style="display: none;">
                                <button type="button" class="date-preset-btn active" data-preset="today">Today</button>
                                <button type="button" class="date-preset-btn" data-preset="yesterday">Yesterday</button>
                                <button type="button" class="date-preset-btn" data-preset="last7">Last 7 Days</button>
                                <button type="button" class="date-preset-btn" data-preset="last30">Last 30 Days</button>
                                <button type="button" class="date-preset-btn" data-preset="thisMonth">This Month</button>
                                <button type="button" class="date-preset-btn" data-preset="lastMonth">Last Month</button>
                                <button type="button" class="date-preset-btn" data-preset="custom">Custom Range</button>
                                
                                <div class="date-custom-panel" id="customDatePanel" style="display: none;">
                                    <div class="mb-2">
                                        <label class="form-label" for="customDateFrom">From:</label>
                                        <input type="date" class="form-control form-control-sm" id="customDateFrom" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" for="customDateTo">To:</label>
                                        <input type="date" class="form-control form-control-sm" id="customDateTo" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="text-danger small mb-2" id="customDateError" style="display: none;">From date cannot be after To date.</div>
                                    <button type="button" class="btn btn-navy-list btn-sm w-100 rounded-pill py-1" id="btnApplyCustomDate">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Bar Row 2: Trade, Metal, Type, Page Size -->
                    <div class="row g-2 align-items-center mb-3">
                        <!-- Trade Filter -->
                        <div class="col-3 col-sm-3">
                            <select class="form-select form-input-pill text-center" id="filterTradeSelect">
                                <option value="ALL" selected>Trade: All</option>
                                <option value="BUY">Buy</option>
                                <option value="SELL">Sell</option>
                            </select>
                        </div>

                        <!-- Metal Filter -->
                        <div class="col-3 col-sm-3">
                            <select class="form-select form-input-pill text-center" id="filterMetalSelect">
                                <option value="ALL" selected>Metal: All</option>
                                <option value="GOLD">Gold</option>
                                <option value="SILVER">Silver</option>
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div class="col-3 col-sm-3">
                            <select class="form-select form-input-pill text-center" id="filterTypeSelect">
                                <option value="ALL" selected>Type: All</option>
                                <option value="ONLY_ENTRY">Only Entry</option>
                                <option value="BHAV_CUT_LEDGER">Bhav Cut + Ledger</option>
                            </select>
                        </div>

                        <!-- Page Size Selector -->
                        <div class="col-3 col-sm-3">
                            <select class="form-select form-input-pill text-center" id="pageSizeSelect">
                                <option value="10" selected>10 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                            </select>
                        </div>
                    </div>

                    <!-- Thin Loading Bar -->
                    <div class="table-loading-bar mb-1" id="tableLoadingBar"></div>

                    <!-- Horizontally Scrollable Table Container -->
                    <div class="table-responsive-container flex-grow-1">
                        <table class="table bhav-table text-center align-middle mb-0" id="bhavBookingTable">
                            <thead>
                                <!-- Header Row 1 -->
                                <tr class="header-row-primary">
                                    <th rowspan="2" class="col-sn">SN</th>
                                    <th rowspan="2" class="col-datetime">DATE / TIME</th>
                                    <th rowspan="2" class="col-customer">CUSTOMER</th>
                                    <th colspan="3" class="col-group-buy border-bottom-0 col-buy">BUY DETAILS</th>
                                    <th colspan="3" class="col-group-sell border-bottom-0 col-sell">SELL DETAILS</th>
                                    <th rowspan="2" class="col-given-received">GIVEN / RECEIVED</th>
                                    <th rowspan="2" class="col-action">ACTION</th>
                                </tr>
                                <!-- Header Row 2 -->
                                <tr class="header-row-sub">
                                    <th class="sub-col col-buy">WT</th>
                                    <th class="sub-col col-buy">RATE</th>
                                    <th class="sub-col col-buy group-end">AMT</th>
                                    <th class="sub-col col-sell">WT</th>
                                    <th class="sub-col col-sell">RATE</th>
                                    <th class="sub-col col-sell group-end">AMT</th>
                                </tr>
                            </thead>
                            <tbody id="bhavTableBody">
                                <!-- Initial Loading State -->
                                <tr class="table-loading-row">
                                    <td colspan="11" class="py-5 text-center text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="fw-semibold">Loading bookings...</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-total-row">
                                    <td colspan="3" class="text-end fw-bold pe-3">TOTAL :-</td>
                                    <td class="text-success fw-bold col-buy" id="totalBuyWt">0.000 gm</td>
                                    <td class="col-buy"></td>
                                    <td class="text-success fw-bold col-buy group-end" id="totalBuyAmt">₹0.00</td>
                                    <td class="text-danger fw-bold col-sell" id="totalSellWt">0.000 gm</td>
                                    <td class="col-sell"></td>
                                    <td class="text-danger fw-bold col-sell group-end" id="totalSellAmt">₹0.00</td>
                                    <td class="col-given-received">
                                        <div class="text-danger fw-bold" id="totalGiven">G: ₹0.00</div>
                                        <div class="text-success fw-bold" id="totalReceived">R: ₹0.00</div>
                                    </td>
                                    <td class="col-action"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination Bar -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-auto border-top" id="listPaginationContainer">
                        <div class="text-muted small" id="listPaginationInfo">
                            Showing 0–0 of 0
                        </div>
                        <nav aria-label="Bookings pagination">
                            <ul class="pagination pagination-sm m-0 gap-1" id="listPaginationLinks">
                                <!-- Dynamic pagination buttons -->
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- =========================================================================
     MODAL: NEW PARTY DETAIL
     ========================================================================= -->
@include('partials.new-party-modal')

<!-- Shared Modals -->
@include('partials.bhav-view-modal')
@include('partials.bhav-save-success-modal')
@include('partials.daily-rate-modal')
@include('partials.shortcuts-modal')

<!-- Pass dynamic application route URLs & Bhav config to JavaScript -->
<script>
    window.BHAV_CONFIG = @json(config('bhav'));
    window.INDIA_LOCATIONS = @json(config('india_locations'));
    window.APP_ROUTES = {
        customerSearch: "{{ route('customers.search') }}",
        customerRecent: "{{ route('customers.recent') }}",
        customerStore: "{{ route('customers.store') }}",
        bhavBookingIndex: "{{ route('bhav-booking.index') }}",
        bhavBookingOld: "{{ route('bhav-booking.old') }}",
        bhavBookingStore: "{{ route('bhav-bookings.store') }}",
        bhavBookingList: "{{ route('bhav-bookings.list') }}",
        bhavBookingShow: "{{ route('bhav-bookings.show', ['bhavBooking' => '__ID__']) }}",
        bhavBookingUpdate: "{{ route('bhav-bookings.update', ['bhavBooking' => '__ID__']) }}",
        bhavBookingDelete: "{{ route('bhav-bookings.destroy', ['bhavBooking' => '__ID__']) }}",
        bhavBookingPrint: "{{ route('bhav-bookings.print', ['bhavBooking' => '__ID__']) }}",
        bhavBookingExport: "{{ route('bhav-bookings.export') }}",
        bhavBookingLogAction: "{{ route('bhav-bookings.log-action', ['bhavBooking' => '__ID__']) }}",
        dailyRatesToday: "{{ route('daily-rates.today') }}",
        dailyRatesStore: "{{ route('daily-rates.store') }}"
    };
</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/bhav-common.js') }}" defer></script>
    <script src="{{ asset('js/bhav-booking.js') }}" defer></script>
@endsection


