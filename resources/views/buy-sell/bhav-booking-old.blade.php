@extends('layouts.app')

@section('title', 'Old Booking List - Just Udhari')

@section('content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Page Title Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 page-header-bar">
        <!-- Title with Orange Dot -->
        <div class="d-flex align-items-center gap-2">
            <span class="title-orange-dot"></span>
            <h1 class="page-main-heading m-0">Old Booking List</h1>
        </div>

        <!-- Action Buttons & Breadcrumbs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('bhav-booking.index') }}" class="btn btn-outline-buy-sell rounded-pill px-3 py-1 text-decoration-none">
                <i class="fa-solid fa-plus me-1"></i> NEW
            </a>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn btn-navy-list rounded-pill px-3 py-1 dropdown-toggle" data-bs-toggle="dropdown" id="dropdownListBtn">
                    <i class="fa-solid fa-bars me-1"></i> LIST
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="listDropdownMenu">
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('bhav-booking.index') }}" id="menuCurrentList">
                            <span><i class="fa-solid fa-list-ul text-warning me-2"></i> Current List</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between active-list-item" href="{{ route('bhav-booking.old') }}" id="menuOldBooking">
                            <span><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Old Booking</span>
                            <i class="fa-solid fa-check text-success small ms-3" id="iconOldCheck"></i>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('parties.index') }}" id="menuPartyMaster">
                            <span><i class="fa-solid fa-users me-2 text-muted"></i> Party Master</span>
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('bhav-booking.index') }}" class="btn-home-link" title="Dashboard" aria-label="Dashboard">
                <i class="fa-solid fa-house"></i>
            </a>

            <span class="badge-bhav-breadcrumb rounded-pill px-3 py-1">
                <i class="fa-solid fa-chevron-right me-1 small"></i> Old Booking List
            </span>
        </div>
    </div>

    <!-- Filter Bar (One row, white rounded card) -->
    <div class="card custom-content-card border-0 shadow-sm mb-3">
        <div class="card-body p-2 p-md-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <!-- Search Customer -->
                <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="min-width: 240px;">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="filterPartyInput">
                        Search Customer
                    </label>
                    <div class="input-with-icon flex-grow-1">
                        <i class="fa-solid fa-user field-icon text-muted"></i>
                        <input type="text" class="form-control form-input-pill ps-5" id="filterPartyInput" placeholder="Name or Number...">
                    </div>
                </div>

                <!-- Date Range Picker with Presets -->
                <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="min-width: 260px;">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="dateRangeInput">
                        Date ( From - To )
                    </label>
                    <div class="position-relative flex-grow-1" id="dateRangePickerWrapper">
                        <input type="hidden" name="date_from" id="dateFilterFrom" value="">
                        <input type="hidden" name="date_to" id="dateFilterTo" value="">
                        <div class="input-with-suffix cursor-pointer">
                            <input type="text"
                                   class="form-control form-input-pill text-center pe-5 cursor-pointer"
                                   id="dateRangeInput"
                                   placeholder="All Dates"
                                   value=""
                                   readonly>
                            <span class="field-suffix text-muted">
                                <i class="fa-regular fa-calendar-days"></i>
                            </span>
                        </div>
                        <!-- Presets Dropdown -->
                        <div class="date-range-dropdown shadow" id="dateRangeDropdown" style="display: none;">
                            <button type="button" class="date-preset-btn active" data-preset="all">All Dates</button>
                            <button type="button" class="date-preset-btn" data-preset="today">Today</button>
                            <button type="button" class="date-preset-btn" data-preset="yesterday">Yesterday</button>
                            <button type="button" class="date-preset-btn" data-preset="last7">Last 7 Days</button>
                            <button type="button" class="date-preset-btn" data-preset="last30">Last 30 Days</button>
                            <button type="button" class="date-preset-btn" data-preset="thisMonth">This Month</button>
                            <button type="button" class="date-preset-btn" data-preset="lastMonth">Last Month</button>
                            <button type="button" class="date-preset-btn" data-preset="custom">Custom Range</button>

                            <div class="date-custom-panel" id="customDatePanel" style="display: none;">
                                <div class="mb-2">
                                    <label class="form-label" for="customDateFrom">From:</label>
                                    <input type="date" class="form-control form-control-sm" id="customDateFrom">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label" for="customDateTo">To:</label>
                                    <input type="date" class="form-control form-control-sm" id="customDateTo">
                                </div>
                                <div class="text-danger small mb-2" id="customDateError" style="display: none;">From date cannot be after To date.</div>
                                <button type="button" class="btn btn-navy-list btn-sm w-100 rounded-pill py-1" id="btnApplyCustomDate">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Show Select -->
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="pageSizeSelect">
                        Show
                    </label>
                    <select class="form-select form-input-pill text-center" id="pageSizeSelect" style="width: 80px;">
                        <option value="10" selected>10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Export to CSV Button -->
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btnExportBookings" title="Export filtered bookings to CSV">
                    <i class="fa-solid fa-file-csv"></i> Export
                </button>

                <!-- Far Right: Round Orange Back Button -->
                <button type="button" class="btn btn-round-orange ms-auto" id="btnBackNavigation" title="Back" aria-label="Back">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card custom-content-card border-0 shadow-sm">
        <div class="card-body p-2 p-md-3">
            <!-- Thin Loading Bar -->
            <div class="table-loading-bar mb-1" id="oldTableLoadingBar"></div>

            <div class="table-responsive-container">
                <table class="table old-bhav-table align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 75px;">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <input type="checkbox" class="form-check-input m-0 cursor-pointer" id="selectAllRowsCheckbox" title="Select all rows" aria-label="Select all rows">
                                    <span>SN</span>
                                </div>
                            </th>
                            <th class="text-center" style="min-width: 135px;">Date/Time</th>
                            <th class="text-start" style="min-width: 170px;">C. No/C. Name</th>
                            <th class="text-start" style="min-width: 170px;">Narration Remark</th>
                            <th class="text-end" style="min-width: 120px;">Sell Weight (Gm)</th>
                            <th class="text-end" style="min-width: 110px;">Sell Rate (₹)</th>
                            <th class="text-end" style="min-width: 120px;">Buy Weight (Gm)</th>
                            <th class="text-end" style="min-width: 110px;">Buy Rate (₹)</th>
                            <th class="text-center" style="width: 65px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="oldBhavTableBody">
                        <tr class="table-loading-row">
                            <td colspan="9" class="py-5 text-center text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="fw-semibold">Loading old bookings...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-3 border-top" id="oldPaginationContainer">
                <div class="text-muted small" id="oldPaginationInfo">
                    Showing 0 to 0 of 0 entries
                </div>
                <nav aria-label="Old bookings pagination">
                    <ul class="pagination pagination-sm m-0 gap-1" id="oldPaginationLinks">
                        <!-- Dynamic pagination buttons -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

</div>

<!-- Shared View Booking Modal Partial -->
@include('partials.bhav-view-modal')

<!-- Pass dynamic application route URLs & Bhav config to JavaScript -->
<script>
    window.BHAV_CONFIG = @json(config('bhav'));
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
        bhavBookingLogAction: "{{ route('bhav-bookings.log-action', ['bhavBooking' => '__ID__']) }}"
    };
</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/bhav-common.js') }}?v={{ filemtime(public_path('js/bhav-common.js')) }}" defer></script>
    <script src="{{ asset('js/bhav-booking-old.js') }}?v={{ filemtime(public_path('js/bhav-booking-old.js')) }}" defer></script>
@endsection
