@extends('layouts.vendors.app')

@section('title', 'Order Receive List')

@section('stylesheet')
    <link href="{{ asset('assets/custom_datatable_96/my_datatable_96.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('theme/plugins/daterangepicker/daterangepicker.css') }}">
    @include('layouts.theme.css.datatable')
@endsection

@section('css')
   
    <style>
        .list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-group {
            width: 250px;
        }

        .filter-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #4a5568;
            white-space: nowrap;
        }

        .back-btn {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #fa9b02ff, #c16412ff);
            transform: scale(1.1);
            color: white;
        }

        /* Boxed Table Style Matching Buy-Sell */
        .smart_dt {
            border-collapse: separate !important;
            border-spacing: 4px 2px !important;
            background: transparent !important;
            min-width: 1000px !important;
            table-layout: fixed !important;
        }

        .smart_dt thead th {
            position: sticky;
            top: 0;
            background: #dde1f7 !important;
            color: #1a202c !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            text-align: center;
            vertical-align: middle;
            padding: 5px 5px !important;
            border: 1px solid #cbd5e0 !important;
            border-radius: 6px;
            z-index: 10;
            font-size: 0.72rem;
            letter-spacing: 0.2px;
            white-space: nowrap !important;
        }

        .smart_dt tbody td {
            text-align: center;
            vertical-align: middle;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            border-radius: 6px;
            padding: 3px 6px !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            line-height: 1.2 !important;
        }

        .smart_dt tfoot td {
            background: #f8fafc !important;
            color: #2d3748 !important;
            font-weight: 800;
            padding: 6px 8px !important;
            border: 1px solid #dde1f7 !important;
            border-radius: 6px;
            vertical-align: middle;
        }

        .urgent-badge {
            background: #fee2e2;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-left: 5px;
        }

        /* Beautiful CSS Tooltip */
        .custom-tooltip-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .custom-tooltip-wrapper .tooltip-content {
            visibility: hidden;
            width: 240px;
            background-color: rgba(26, 32, 44, 1);
            backdrop-filter: blur(10px);
            color: #fff;
            text-align: left;
            border-radius: 12px;
            padding: 10px 14px;
            position: absolute;
            z-index: 999;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.2s, visibility 0.2s;
            box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.75rem;
            line-height: 1.4;
            pointer-events: none;
        }

        .custom-tooltip-wrapper .tooltip-content::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: rgba(26, 32, 44, 1) transparent transparent transparent;
        }

        .custom-tooltip-wrapper:hover .tooltip-content {
            visibility: visible;
            opacity: 1;
        }

        /* Ensure hovered cell/row stack above others to prevent overlapping by adjacent cells */
        .tooltip-active-td {
            position: relative !important;
            z-index: 99 !important;
        }
        .tooltip-active-tr {
            position: relative !important;
            z-index: 98 !important;
        }
        .smart_dt tbody td:has(.custom-tooltip-wrapper:hover) {
            position: relative !important;
            z-index: 99 !important;
        }
        .smart_dt tbody tr:has(.custom-tooltip-wrapper:hover) {
            position: relative !important;
            z-index: 98 !important;
        }

        /* Downward Class override */
        .custom-tooltip-wrapper .tooltip-content.tooltip-down {
            bottom: auto;
            top: 125%;
            left: 50%;
            transform: translateX(-50%);
        }
        .custom-tooltip-wrapper .tooltip-content.tooltip-down::after {
            top: auto;
            bottom: 100%;
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            border-color: transparent transparent rgba(26, 32, 44, 1) transparent;
        }

        /* Sideways Right Class override */
        .custom-tooltip-wrapper .tooltip-content.tooltip-right {
            bottom: auto;
            top: 50%;
            left: 105%;
            transform: translateY(-50%);
        }
        .custom-tooltip-wrapper .tooltip-content.tooltip-right::after {
            top: 50%;
            left: auto;
            right: 100%;
            transform: translateY(-50%);
            border-color: transparent rgba(26, 32, 44, 1) transparent transparent;
        }

        /* Sideways Left Class override */
        .custom-tooltip-wrapper .tooltip-content.tooltip-left {
            bottom: auto;
            top: 50%;
            left: auto;
            right: 105%;
            transform: translateY(-50%);
        }
        .custom-tooltip-wrapper .tooltip-content.tooltip-left::after {
            top: 50%;
            left: 100%;
            right: auto;
            transform: translateY(-50%);
            border-color: transparent transparent transparent rgba(26, 32, 44, 1);
        }

        .tooltip-item-line {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 4px 0;
            gap: 15px;
        }
        .tooltip-item-line:last-child {
            border-bottom: none;
        }
        /* Fix dropdown stacking overlap issue */
        tr.active-dropdown-row {
            position: relative !important;
            z-index: 1050 !important;
        }
        tr.active-dropdown-row td {
            position: relative !important;
            z-index: 1050 !important;
        }

        /* Fix scrollbar header alignment */
        .table-responsive {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        /* Prevent Datatables scrollbody vertical overflow scroller from displacing header alignment */
        .dataTables_scrollBody, .dt-body, .dataTables_scroll {
            overflow-y: visible !important;
            overflow-x: auto !important;
            max-height: none !important;
            height: auto !important;
        }

        /* Sync custom datatable header alignment */
        .dt-header {
            padding-right: 10px !important;
        }

        /* Mobile View - Fluid width, complete word wrap, and scaled down font sizes */
        @media (max-width: 768px) {
            
            .smart_dt thead th {
                white-space: normal !important;
                font-size: 0.65rem !important;
                padding: 6px 4px !important;
                letter-spacing: 0px !important;
            }
            
            .list-header {
                display: block !important;
                position: relative !important;
                padding-top: 50px !important; /* Make room for back button */
            }
            
            .list-header .back-btn {
                position: absolute !important;
                top: 15px !important;
                right: 15px !important;
            }
            
            .filter-section {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 15px !important;
                width: 100% !important;
            }
            
            .filter-item {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 6px !important;
                width: 100% !important;
            }
            
            .filter-item .filter-label {
                align-self: flex-start !important;
            }
            
            .search-group {
                width: 100% !important;
            }
            
            .filter-item .input-group,
            .filter-item #daterange-btn {
                width: 100% !important;
            }
            
            .filter-item select,
            .filter-item input {
                width: 100% !important;
            }
            
            .filter-item div {
                width: 100% !important;
            }
        }
    </style>
@endsection

@section('content')
    @php 
        $anchor = [
            header_btn_generate('New', route('vendor.orders.create')),
            list_menu_generate([
                ['title' => 'All Orders', 'route' => 'vendor.orders.list', 'is_default' => 1],
                ['title' => 'Karigars Assign List', 'route' => 'vendor.orders.karigar_list'],
                ['title' => 'Ready to Deliver List', 'route' => 'vendor.orders.billing'],
            ])
        ];
        $path = ["Orders" => route('vendor.orders.list')];
        $data = new_component_array('newbreadcrumb', "All Orders", $path);
    @endphp
    <x-new-bread-crumb :data=$data :anchor=$anchor />

    <div class="order-module pb-5 mt-3 px-3">
        <div class="list-header">
            <div class="filter-section">
                <div class="filter-item">
                    <span class="filter-label">Search</span>
                    <div class="premium-input-group search-group"
                        style="border: 1px solid #ced4da; background: #fff; border-radius: 20px; height: 36px; padding: 0 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-search" style="color: #a0aec0;"></i>
                        <input type="text" id="keyword" placeholder="Customer or Order ID..." oninput="changeEntries()"
                            autocomplete="off"
                            style="border: none; background: transparent; width: 100%; font-size: 0.85rem; font-weight: 600; outline: none; padding: 0;">
                    </div>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Status</span>
                    <div class="input-group">
                        <select id="status_filter" class="form-control" onchange="changeEntries()"
                            style="border-radius: 20px; height: 36px; background: white; border: 1px solid #ced4da; font-size: 0.85rem; font-weight: 600; padding: 0 15px; outline: none; width: 175px; cursor: pointer;">
                            <option value="all" selected>All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_process">In Process</option>
                            <option value="completed">Completed / Ready</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Date Range</span>
                    <div class="input-group">
                        <button type="button" class="form-control" id="daterange-btn"
                            style="width: 220px; border-radius: 20px; height: 36px; background: white; border: 1px solid #ced4da; font-size: 0.85rem; font-weight: 600; text-align: left; padding: 0 15px; display: flex; align-items: center; justify-content: space-between;">
                            <span id="daterange-text">All Time</span>
                            <i class="fa fa-calendar-alt text-muted"></i>
                        </button>
                    </div>
                    <input type="hidden" id="reportrange" value="">
                </div>
                <div class="filter-item">
                    <span class="filter-label">Show</span>
                    <div style="width: 80px;">
                        @include('layouts.theme.datatable.entry', ['default_entries' => 10])
                    </div>
                </div>
            </div>

            <a href="{{ route('vendor.orders.create') }}" class="back-btn">
                <i class="fa fa-chevron-left"></i>
            </a>
        </div>

        <div class="bulk-actions-bar shadow-2xl" id="bulk_actions_bar"
            style="display: none; background: rgba(26, 32, 44, 0.95); backdrop-filter: blur(10px); color: white; padding: 10px 20px; border-radius: 100px; position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%); z-index: 1000; align-items: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);">
            <div class="selected-count fw-800 d-flex align-items-center"
                style="font-size: 0.9rem; border-right: 1px solid rgba(255,255,255,0.1); padding-right: 20px; margin-right: 15px;">
                <div class="count-badge mr-2"
                    style="background: #3182ce; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;"
                    id="selected_count">0</div>
                <span style="letter-spacing: 0.5px;">Selected</span>
            </div>
            <div class="action-buttons d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-primary rounded-pill px-3 fw-800 mx-1" id="bulk_assign_btn" onclick="bulkAssign()"
                    style="background: #3182ce; border: none; font-size: 0.75rem; height: 34px; transition: all 0.2s;">
                    <i class="fa fa-user-plus mr-1"></i> Bulk Assign
                </button>
                <button class="btn btn-sm btn-danger rounded-pill px-3 fw-800 mx-1" id="bulk_delete_btn" onclick="bulkDelete()"
                    style="background: #e53e3e; border: none; font-size: 0.75rem; height: 34px; transition: all 0.2s;">
                    <i class="fa fa-trash mr-1"></i> Bulk Delete
                </button>
                <button class="btn btn-sm btn-info rounded-pill px-3 fw-800 mx-1" id="bulk_print_btn" onclick="bulkPrint()"
                    style="background: #38b2ac; border: none; font-size: 0.75rem; height: 34px; transition: all 0.2s;">
                    <i class="fa fa-print mr-1"></i> Bulk Print
                </button>
            </div>
            <button class="btn btn-icon btn-link p-0 ml-3 text-white opacity-50 hover-opacity-100" onclick="deselectAll()"
                style="text-decoration: none; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s;">
                <i class="fa fa-times" style="font-size: 1rem;"></i>
            </button>
        </div>

        <div class="table-wrapper">
            <div class="table-responsive p-1">
                <table class="table smart_dt table_theme w-100 mb-0" id="order_table">
                    <thead>
                        <tr>
                            <th width="120">
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="checkbox" id="select_all"
                                        style="width: 16px; height: 16px; cursor: pointer; margin-right: 8px;">
                                    <span>S.No / ID</span>
                                </div>
                            </th>
                            <th width="160" class="text-left">Customer Details</th>
                            <th width="200" class="text-left">Product & Details</th>
                            <th width="155">Weight (Gross/Fine)</th>
                            <th width="130">Amount Details</th>
                            <th width="115">Karigar Status</th>
                            <th width="140">Dates </th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                        @if(isset($orders) && $orders->count() > 0)
                            @include('vendors.orders.receive.listbody')
                        @else
                            <tbody id="data_area">
                                <!-- <tr>
                                    <td colspan="13" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center py-4 bg-white border"
                                            style="border-radius: 12px;">
                                            <i class="fa fa-spinner fa-spin mb-3 text-primary" style="font-size: 2rem;"></i>
                                            <p class="text-muted fw-800 mb-0">Loading Orders...</p>
                                        </div>
                                    </td>
                                </tr> -->
                            </tbody>
                        @endif
                </table>
            </div>
            <div id="paging_area" class="mt-3">
                @if(isset($orders) && $orders)
                    @include('layouts.theme.datatable.pagination', ['paginator' => $orders, 'type' => 1])
                @endif
            </div>
        </div>
    </div>

    @include('vendors.orders.modals.assign_karigar')
    @include('vendors.orders.modals.issue_material')

  
    <div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class=" border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: #f1f5f9; width: 32px; height: 32px; border-radius: 50%; opacity: 1; outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-0" id="order_detail_area">
                    <div class="text-center py-5">
                        <i class="fa fa-spinner fa-spin text-primary mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted fw-800">Loading Order Details ...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('javascript')
    @include('layouts.vendors.js.passwork-popup')
    @include('vendors.orders.receive.js.list_scripts')
@endsection