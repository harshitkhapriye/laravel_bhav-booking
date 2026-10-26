@extends('layouts.app')

@section('title', 'Party Master - Just Udhari')

@section('content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Page Title Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 page-header-bar">
        <!-- Title with Orange Dot -->
        <div class="d-flex align-items-center gap-2">
            <span class="title-orange-dot"></span>
            <h1 class="page-main-heading m-0">Party Master</h1>
        </div>

        <!-- Action Buttons & Breadcrumbs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-buy-sell rounded-pill px-3 py-1" id="btnOpenNewPartyModal">
                <i class="fa-solid fa-plus me-1"></i> NEW PARTY
            </button>

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
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('bhav-booking.old') }}" id="menuOldBooking">
                            <span><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i> Old Booking</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between active-list-item" href="{{ route('parties.index') }}" id="menuPartyMaster">
                            <span><i class="fa-solid fa-users me-2 text-warning"></i> Party Master</span>
                            <i class="fa-solid fa-check text-success small ms-3" id="iconPartyCheck"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('bhav-booking.index') }}" class="btn-home-link" title="Dashboard">
                <i class="fa-solid fa-house"></i>
            </a>

            <span class="badge-bhav-breadcrumb rounded-pill px-3 py-1">
                <i class="fa-solid fa-chevron-right me-1 small"></i> Party Master
            </span>
        </div>
    </div>

    <!-- Filter Bar (One row, white rounded card) -->
    <div class="card custom-content-card border-0 shadow-sm mb-3">
        <div class="card-body p-2 p-md-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <!-- Search Customer / Party -->
                <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="min-width: 280px;">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="filterPartySearch">
                        Search Party
                    </label>
                    <div class="input-with-icon flex-grow-1">
                        <i class="fa-solid fa-magnifying-glass field-icon text-muted"></i>
                        <input type="text"
                               class="form-control form-control-sm rounded-pill ps-5 pe-3 filter-pill-input"
                               id="filterPartySearch"
                               placeholder="Name, mobile, number, shop..."
                               autocomplete="off">
                    </div>
                </div>

                <!-- Party Type Filter -->
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="filterPartyType">
                        Type:
                    </label>
                    <select class="form-select form-select-sm rounded-pill filter-pill-select" id="filterPartyType" style="min-width: 140px;">
                        <option value="ALL" selected>All Parties</option>
                        <option value="CUSTOMER">Customers</option>
                        <option value="SUPPLIER">Suppliers</option>
                    </select>
                </div>

                <!-- Page Size (Per Page) -->
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <label class="form-label small fw-bold text-secondary mb-0 text-nowrap" for="filterPerPage">
                        Show:
                    </label>
                    <select class="form-select form-select-sm rounded-pill filter-pill-select" id="filterPerPage" style="width: 80px;">
                        <option value="10" selected>10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Party Master Table Card -->
    <div class="card custom-content-card border-0 shadow-sm">
        <div class="card-body p-2 p-md-3">
            <div class="table-responsive parties-table-container">
                <table class="table table-sm table-hover align-middle mb-0 parties-table text-nowrap" id="partiesTable" style="min-width: 1050px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">SN</th>
                            <th class="text-center" style="width: 60px;">Photo</th>
                            <th style="min-width: 240px;">Party No / Name</th>
                            <th style="min-width: 130px;">Mobile</th>
                            <th style="min-width: 180px;">Shop / Business</th>
                            <th style="min-width: 160px;">District, State</th>
                            <th class="text-center" style="width: 90px;">Bookings</th>
                            <th class="text-center" style="width: 65px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody">
                        <tr class="table-loading-row">
                            <td colspan="8" class="py-5 text-center text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="fw-semibold">Loading parties...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-3 border-top" id="partiesPaginationContainer">
                <div class="text-muted small" id="partiesPaginationInfo">
                    Showing 0 to 0 of 0 entries
                </div>
                <nav aria-label="Parties pagination">
                    <ul class="pagination pagination-sm m-0 gap-1" id="partiesPaginationLinks">
                        <!-- Dynamic pagination buttons -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

</div>

<!-- Shared New Party Detail Modal Partial (used for both Create & Edit) -->
@include('partials.new-party-modal')

<!-- Dedicated Party View Modal Partial -->
@include('partials.party-view-modal')

<!-- Dynamic Application Route URLs & Location Data for Party Master -->
<script>
    window.INDIA_LOCATIONS = @json(config('india_locations'));
    window.APP_ROUTES = {
        partiesList: "{{ route('parties.list') }}",
        partiesShow: "{{ route('parties.show', ['customer' => '__ID__']) }}",
        partiesUpdate: "{{ route('parties.update', ['customer' => '__ID__']) }}",
        partiesDestroy: "{{ route('parties.destroy', ['customer' => '__ID__']) }}",
        customerStore: "{{ route('customers.store') }}",
        customerSearch: "{{ route('customers.search') }}"
    };
</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/bhav-common.js') }}" defer></script>
    <script src="{{ asset('js/parties.js') }}" defer></script>
@endsection
