@extends('layouts.vendors.app')

@section('title', 'Ready to Deliver List')

@section('stylesheet')
    <link href="{{ asset('assets/custom_datatable_96/my_datatable_96.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('theme/plugins/daterangepicker/daterangepicker.css') }}">
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
            border-spacing: 6px 8px !important;
            background: transparent !important;
            min-width: 1400px !important;
            table-layout: fixed !important;
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

        .smart_dt thead th {
            position: sticky;
            top: 0;
            background: #ffe2e6 !important;
            color: #991b1b !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            text-align: center;
            vertical-align: middle;
            padding: 12px 8px !important;
            border: 1px solid #fecaca !important;
            border-radius: 10px;
            z-index: 10;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .smart_dt tbody td {
            text-align: center;
            vertical-align: middle;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            border-radius: 10px;
            padding: 10px 8px !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .smart_dt tfoot td {
            background: #fff5f5 !important;
            color: #991b1b !important;
            font-weight: 800;
            padding: 10px 10px !important;
            border: 1px solid #fecaca !important;
            border-radius: 10px;
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
            width: 280px;
            background-color: rgba(26, 32, 44, 0.98);
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
            border-color: rgba(26, 32, 44, 0.98) transparent transparent transparent;
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
            border-color: transparent transparent rgba(26, 32, 44, 0.98) transparent;
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
            border-color: transparent rgba(26, 32, 44, 0.98) transparent transparent;
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
            border-color: transparent transparent transparent rgba(26, 32, 44, 0.98);
        }

        /* Highlighted Order Row */
        .highlighted-order-row {
            position: relative !important;
            z-index: 5 !important;
        }
        .highlighted-order-row td {
            background: #f0fdf4 !important;
            border: 1.5px solid #22c55e !important;
            box-shadow: inset 0 0 0 1px #86efac, 0 4px 12px rgba(34, 197, 94, 0.15) !important;
            animation: highlightPulse 2.5s ease-in-out infinite;
        }

        @keyframes highlightPulse {
            0%, 100% {
                background: #f0fdf4 !important;
            }
            50% {
                background: #dcfce7 !important;
            }
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
    @include('vendors.orders.board.css')
@endsection

@section('content')
@php
     // Design mein nahi hai (comment out): "New" button + lists menu. Ab ek hi pipeline page hai -
     // New order board ke header mein hai, Karigar / Billing lists board ke columns hain.
     /* 
    $anchor = [
        header_btn_generate('New', route('vendor.orders.create')),
        list_menu_generate([
            ['title' => 'Ready to Deliver List', 'route' => 'vendor.orders.billing', 'is_default' => 1],
            ['title' => 'All Orders', 'route' => 'vendor.orders.list'],
            ['title' => 'Karigars Assign List', 'route' => 'vendor.orders.karigar_list'],
        ])
    ];
     */
     $anchor = [];
    $path = ["Orders" => route('vendor.orders.list')];
    $data = new_component_array('newbreadcrumb', "Ready to Deliver List", $path);
@endphp 
<x-new-bread-crumb :data=$data :anchor=$anchor/>
    @if(request('incomplete_order') || request('items_left'))
        @php
            $iOrder = request('incomplete_order');
            $iLeft = (int)request('items_left', 1);
            $iOrderStr = $iOrder ? (str_starts_with($iOrder, '#') ? $iOrder : '#' . $iOrder) : '';
            $iItemLabel = $iLeft === 1 ? '1 item' : $iLeft . ' items';
        @endphp
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center justify-content-between p-3 mb-3 shadow-sm" role="alert" style="border-radius: 12px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: 1px solid #7dd3fc; color: #0369a1;">
            <div class="d-flex align-items-center">
                <i class="fa fa-info-circle mr-2" style="font-size: 1.25rem; color: #0284c7;"></i>
                <span style="font-size: 0.88rem; font-weight: 700;">
                    <strong>Notice:</strong> {{ $iItemLabel }} left of order {{ $iOrderStr }}.
                </span>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #0369a1; opacity: 0.8; outline: none; padding: 0.75rem 1rem;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @include('vendors.orders.board.board', ['activeStage' => 'billing'])
@endsection

@section('javascript')
    @include('layouts.vendors.js.passwork-popup')
    @include('vendors.orders.receive.js.list_scripts')
    @include('vendors.orders.board.js')
@endsection
