@extends('layouts.vendors.app')

@section('title', 'Order Process List')

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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

        /* Boxed Table Style */
        .smart_dt {
            border-collapse: separate !important;
            border-spacing: 4px 4px !important;
            background: transparent !important;
            min-width: 1400px !important;
        }

        .smart_dt thead th {
            position: sticky;
            top: 0;
            background: #ffe2e6 !important;
            color: #991b1b !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            text-align: center;
            vertical-align: middle;
            padding: 8px 8px !important;
            border: 1px solid #fecaca !important;
            border-radius: 6px;
            z-index: 10;
            font-size: 0.75rem;
            letter-spacing: 0.2px;
            white-space: nowrap !important;
        }

        .smart_dt tbody td {
            text-align: center;
            vertical-align: middle;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            border-radius: 6px;
            padding: 6px 8px !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            line-height: 1.3 !important;
        }

        .smart_dt tfoot td {
            background: #fff5f5 !important;
            color: #991b1b !important;
            font-weight: 800;
            padding: 8px 8px !important;
            border: 1px solid #fecaca !important;
            border-radius: 6px;
            vertical-align: middle;
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

        .dropdown.show {
            position: relative;
            z-index: 1060 !important;
        }
        .smart_dt tbody tr:has(.dropdown.show) {
            position: relative !important;
            z-index: 1050 !important;
        }
        .smart_dt tbody td:has(.dropdown.show) {
            position: relative !important;
            z-index: 1050 !important;
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
            ['title' => 'Karigars Assign List', 'route' => 'vendor.orders.karigar_list', 'is_default' => 1],
            ['title' => 'All Orders', 'route' => 'vendor.orders.list'],
            ['title' => 'Ready to Deliver List', 'route' => 'vendor.orders.billing'],
        ])
    ];
    $path = ["Orders" => route('vendor.orders.list')];
    $data = new_component_array('newbreadcrumb', "Karigars Assign List", $path);
@endphp 
<x-new-bread-crumb :data=$data :anchor=$anchor/> 

<div class="order-module pb-5 mt-3 px-3">
    <div class="list-header">
        <div class="filter-section">
            <div class="filter-item">
                <span class="filter-label">Search</span>
                <div class="premium-input-group search-group" style="border: 1px solid #ced4da; background: #fff; border-radius: 20px; height: 36px; padding: 0 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-search" style="color: #a0aec0;"></i>
                    <input type="text" id="keyword" placeholder="Search Order/Karigar..." oninput="changeEntries()" autocomplete="off" style="border: none; background: transparent; width: 100%; font-size: 0.85rem; font-weight: 600; outline: none; padding: 0;">
                </div>
            </div>
            <div class="filter-item">
                <span class="filter-label">Date Range</span>
                <div class="input-group">
                    <button type="button" class="form-control" id="daterange-btn" style="width: 220px; border-radius: 20px; height: 36px; background: white; border: 1px solid #ced4da; font-size: 0.85rem; font-weight: 600; text-align: left; padding: 0 15px; display: flex; align-items: center; justify-content: space-between;">
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
        
        <a href="{{ route('vendor.orders.list') }}" class="back-btn">
            <i class="fa fa-chevron-left"></i>
        </a>
    </div>

    <div class="table-wrapper">
        <div class="table-responsive p-1">
            <table class="table smart_dt table_theme w-100 mb-0" id="order_table">
                <thead>
                    <tr>
                        <th width="120">S.No / ID</th>
                        <th width="150">Dates</th>
                        <th width="180" class="text-left">Customer Details</th>
                        <th width="180" class="text-left">Product</th>
                        <th width="160" class="text-left">Assigned Karigar</th>
                        <th width="150">Issued Material</th>
                        <th width="120">Ghat Allowance</th>
                        <th width="120">Charges</th>
                        <!-- <th width="120">Delivery Date</th> -->
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody id="data_area">
                    {{-- Data will be loaded via AJAX --}}
                </tbody>
            </table>
        </div>
        <div id="paging_area" class="mt-3">
            @if(isset($orders) && $orders)
                @include('layouts.theme.datatable.pagination', ['paginator' => $orders, 'type' => 1])
            @endif
        </div>
    </div>
</div>

@endsection

@section('javascript')
    <script src="{{ asset('theme/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/custom_datatable_96/my_datatable_96.js') }}"></script>
    @include('layouts.theme.js.datatable')
    
    <script>
        $(document).ready(function() {
            if($.fn.smartDataTable) {
                $.fn.smartDataTableDefaults.bodyHeight = 400;
                $('.smart_dt').smartDataTable();
            }

            // Fix dropdown stacking issues dynamically on show/hide
            $(document).on('show.bs.dropdown', '.dropdown', function () {
                $(this).closest('tr').addClass('tooltip-active-tr');
                $(this).closest('td').addClass('tooltip-active-td');
            });
            $(document).on('hide.bs.dropdown', '.dropdown', function () {
                $(this).closest('tr').removeClass('tooltip-active-tr');
                $(this).closest('td').removeClass('tooltip-active-td');
            });

            // Dynamic positioning of tooltips on hover to prevent clipping out of screen or scroll container
            $(document).on('mouseenter', '.custom-tooltip-wrapper', function() {
                const $wrapper = $(this);
                const $tooltip = $wrapper.find('.tooltip-content');
                
                // Raise stacking context of the parent td/tr to prevent overlap
                $wrapper.closest('td').addClass('tooltip-active-td');
                $wrapper.closest('tr').addClass('tooltip-active-tr');

                // Remove previous classes to measure naturally
                $tooltip.removeClass('tooltip-down tooltip-right tooltip-left');
                
                // Get bounding client rects
                const wrapperRect = $wrapper[0].getBoundingClientRect();
                const tooltipHeight = $tooltip.outerHeight();
                const tooltipWidth = $tooltip.outerWidth();
                
                // Find scroll container if any
                const $scrollContainer = $wrapper.closest('.table-responsive, .dataTables_scrollBody');
                let containerTop = 0;
                let containerBottom = window.innerHeight;
                let containerLeft = 0;
                let containerRight = window.innerWidth;
                
                if ($scrollContainer.length > 0) {
                    const containerRect = $scrollContainer[0].getBoundingClientRect();
                    containerTop = Math.max(containerTop, containerRect.top);
                    containerBottom = Math.min(containerBottom, containerRect.bottom);
                    containerLeft = Math.max(containerLeft, containerRect.left);
                    containerRight = Math.min(containerRight, containerRect.right);
                }
                
                // Check spacing in all four directions relative to the scroll container/viewport
                const spaceAbove = wrapperRect.top - containerTop;
                const spaceBelow = containerBottom - wrapperRect.bottom;
                const spaceRight = containerRight - wrapperRect.right;
                const spaceLeft = wrapperRect.left - containerLeft;
                
                // Decide best placement
                if (spaceAbove < tooltipHeight + 15) {
                    // Not enough space above! Let's see if we have space below
                    if (spaceBelow >= tooltipHeight + 15) {
                        $tooltip.addClass('tooltip-down');
                    } else if (spaceRight >= tooltipWidth + 15) {
                        $tooltip.addClass('tooltip-right');
                    } else if (spaceLeft >= tooltipWidth + 15) {
                        $tooltip.addClass('tooltip-left');
                    } else {
                        $tooltip.addClass('tooltip-down');
                    }
                }
            });

            $(document).on('mouseleave', '.custom-tooltip-wrapper', function() {
                $(this).find('.tooltip-content').removeClass('tooltip-down tooltip-right tooltip-left');
                $(this).closest('td').removeClass('tooltip-active-td');
                $(this).closest('tr').removeClass('tooltip-active-tr');
            });

            const list_url = "{{ route('vendor.orders.karigar_list') }}";
            let search_timeout = null;

            window.changeEntries = function() {
                if (search_timeout) clearTimeout(search_timeout);
                search_timeout = setTimeout(() => getresult(list_url), 300);
            };

            function getresult(url) {
                if($.fn.smartDataTableDestroy) $('.smart_dt').smartDataTableDestroy();
                
                $("#data_area").html('<tr><td colspan="11" class="text-center py-5"><div class="d-flex flex-column align-items-center py-4 bg-white border" style="border-radius: 12px;"><i class="fa fa-spinner fa-spin mb-3 text-primary" style="font-size: 2rem;"></i><p class="text-muted fw-800 mb-0">Fetching Data...</p></div></td></tr>');
                $("#paging_area").html('');

                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        "entries": $(".entries").val(),
                        "keyword": $("#keyword").val(),
                        "reportrange": $("#reportrange").val()
                    },
                    success: function (data) {
                        if (data.status === 'success' && data.html) {
                            var $temp = $('<table>').html(data.html);
                            var $tbody = $temp.find('tbody#data_area');
                            var $tfoot = $temp.find('tfoot');

                            if ($tbody.length > 0) {
                                $("#data_area").replaceWith($tbody);
                            } else {
                                $("#data_area").html(data.html);
                            }

                            if ($tfoot.length > 0) {
                                if ($("#order_table tfoot").length > 0) {
                                    $("#order_table tfoot").replaceWith($tfoot);
                                } else {
                                    $("#order_table").append($tfoot);
                                }
                            } else {
                                $("#order_table tfoot").remove();
                            }
                            
                            $("#paging_area").html(data.paging);
                            if($.fn.smartDataTable) $('.smart_dt').smartDataTable();
                        }
                    },
                    error: function () {
                        $("#data_area").html('<tr><td colspan="11" class="text-center py-5 text-danger fw-800"><i class="fa fa-exclamation-triangle mr-2"></i> Error loading data.</td></tr>');
                    }
                });
            }

            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                getresult($(this).attr('href'));
            });

            $(document).on('change', '.entries', function() {
                getresult(list_url);
            });

            // Trigger initial load
            getresult(list_url);

            $('#daterange-btn').daterangepicker({
                startDate: moment().subtract(10, 'years'),
                endDate: moment().add(10, 'years'),
                ranges: {
                    'All Time': [moment().subtract(10, 'years'), moment().add(10, 'years')],
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                opens: 'left'
            }, function (start, end, label) {
                if (label === 'All Time') {
                    $('#reportrange').val('');
                    $('#daterange-text').html('All Time');
                } else {
                    $('#reportrange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
                    $('#daterange-text').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
                }
                changeEntries();
            });

            
        });
    </script>
@endsection
