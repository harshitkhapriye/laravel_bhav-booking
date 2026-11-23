<style>
    :root {
        --premium-indigo: #ff8a3d;
        --premium-indigo-soft: #eef2ff;
        --premium-emerald: #10b981;
        --premium-emerald-soft: #ecfdf5;
        --premium-rose: #f43f5e;
        --premium-rose-soft: #fff1f2;
        --premium-slate: #1e293b;
        --premium-slate-light: #64748b;
        --glass-bg: rgba(255, 255, 255, 0.8);
        --premium-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }


    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    .erp-body {
        background: #f8fafc;
    }

    .fw-800 {
        font-weight: 800;
    }

    .fw-900 {
        font-weight: 900;
    }

    .text-indigo {
        color: var(--premium-indigo);
    }

    .bg-soft-indigo {
        background: var(--premium-indigo-soft);
    }

    .bg-soft-light {
        background: #f1f5f9;
    }

    .rounded-xl {
        border-radius: 16px;
    }

    .border-dashed {
        border: 2px dashed #e2e8f0;
    }

    /* Glass Panels */
    .glass-panel {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
    }

    /* Premium Entry Card */
    .premium-entry-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #edf2f7;
        overflow: hidden;
        margin-bottom: 15px;
        /* Reduced from 25px */
    }

    .premium-card-header {
        background: #fdfdfd;
        padding: 10px 15px;
        /* Reduced from 15px 20px */
        border-bottom: 1px solid #f1f5f9;
    }

    .premium-card-body {
        padding: 15px;
    }

    /* Reduced from 25px */
    .item-count-badge {
        background: var(--premium-indigo-soft);
        color: var(--premium-indigo);
        padding: 6px 16px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.75rem;
        border: 1px solid var(--premium-indigo);
    }

    /* Form Groups */
    .premium-form-group {
        margin-bottom: 1rem;
    }

    .premium-form-group label {
        display: block;
        font-size: 0.85rem !important;
        font-weight: 900;
        color: var(--premium-slate-light);
        margin-bottom: 6px;
        text-transform: none;
        letter-spacing: 0.5px;
    }

    .premium-input,
    .premium-select {
        width: 100%;
        height: 42px;
        padding: 0 16px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 24px;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--premium-slate);
        transition: all 0.3s ease;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .premium-input:focus,
    .premium-select:focus {
        border-color: var(--premium-indigo);
        background: #fff;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01), 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    .weight-input {
        font-size: 1.1rem !important;
        letter-spacing: 0.5px;
        background: #fff !important;
        border-color: #cbd5e1 !important;
    }

    /* Units & Icons */
    .input-with-unit {
        position: relative;
        display: flex;
        align-items: center;
    }

    .unit-tag {
        position: absolute;
        right: 12px;
        font-weight: 800;
        font-size: 0.7rem;
        color: var(--premium-slate-light);
        background: #fff;
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Add Button */
    .btn-premium-add {
        background: var(--premium-slate);
        color: #fff;
        border: none;
        height: 42px;
        border-radius: 24px;
        font-weight: 900;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-premium-add:hover {
        background: var(--premium-indigo);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(79, 70, 229, 0.2);
    }

    /* Summary Card */
    .summary-input {
        width: 100%;
        height: 42px;
        border: none;
        background: #f8fafc;
        font-weight: 900;
        padding: 0 16px 0 35px;
        border-radius: 24px;
        font-size: 1rem;
        color: var(--premium-slate);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .input-with-icon-badge {
        position: relative;
    }

    .input-with-icon-badge i {
        position: absolute;
        left: 12px;
        top: 13px;
        color: var(--premium-slate-light);
        font-size: 0.9rem;
    }

    .badge-right {
        position: absolute;
        right: 10px;
        top: 10px;
        font-weight: 800;
        font-size: 0.65rem;
        background: #fff;
        padding: 2px 6px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    /* List Table Table Styles */
    .table-card {
        border-radius: 20px;
        overflow: hidden;
    }

    .table-card table thead th {
        background: #f8fafc;
        color: var(--premium-slate-light);
        font-weight: 800;
        font-size: 0.65rem;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9;
        padding: 15px;
    }

    .table-card table tbody td {
        padding: 15px;
        font-size: 1rem;
        font-weight: 700;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Multi Input & Select */
    .premium-multi-input {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .premium-multi-input .premium-input {
        border: none;
        background: transparent;
        box-shadow: none;
    }

    .premium-input-select {
        border: none;
        background: var(--premium-indigo-soft);
        color: var(--premium-indigo);
        font-weight: 800;
        font-size: 0.7rem;
        height: 42px;
        padding: 0 10px;
        cursor: pointer;
        outline: none;
    }

    /* Status Glass Pill - Improved Spacing */
    .status-glass-pill {
        background: rgba(241, 245, 249, 0.7);
        backdrop-filter: blur(8px);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 8px 15px;
        width: 100%;
        display: flex;
        align-items: center;
    }

    .pill-label {
        font-size: 0.65rem;
        font-weight: 900;
        color: var(--premium-slate-light);
        
        padding-right: 15px;
        /* margin-right: 15px; */
        white-space: nowrap;
        display: flex;
        align-items: center;
    }

    .pill-data {
        display: flex;
        flex-grow: 1;
        justify-content: space-between;
        gap: 10px;
    }

    .status-item {
        display: flex;
        flex-direction: column;
        min-width: 60px;
    }

    .status-item small {
        font-size: 0.6rem;
        font-weight: 900;
        color: var(--premium-slate-light);
        text-transform: uppercase;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }

    .status-item span {
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: -0.2px;
    }

    /* Footer Bar Refined - Final Polish */
    .premium-footer-bar {
        position: sticky;
        bottom: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-top: 2px solid #edf2f7;
        margin: 40px -25px -25px -25px;
        padding: 20px 30px !important;
        box-shadow: 0 -15px 50px rgba(0, 0, 0, 0.1);
        border-radius: 0 0 24px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
    }

    .footer-input-group {
        display: flex;
        flex-direction: column;
        margin-right: 25px;
        min-width: 140px;
    }

    .footer-input-group label {
        font-size: 0.65rem;
        font-weight: 900;
        color: var(--premium-slate-light);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
    }

    .footer-input-group label i {
        color: var(--premium-indigo);
        margin-right: 5px;
        font-size: 0.8rem;
    }

    .footer-input {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 24px;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--premium-slate);
        padding: 8px 18px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 45px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .footer-input:focus {
        outline: none;
        border-color: var(--premium-indigo);
        background: #fff;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01), 0 4px 12px rgba(255, 138, 61, 0.15);
        transform: translateY(-1px);
    }

    .btn-premium-confirm {
        background: linear-gradient(135deg, var(--premium-emerald) 0%, #059669 100%);
        color: #fff;
        border: none;
        padding: 10px 30px;
        border-radius: 24px;
        font-weight: 900;
        font-size: 0.95rem;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        transition: all 0.3s;
        display: flex;
        align-items: center;
    }

    .btn-premium-confirm:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-premium-reset {
        background: #fff;
        color: var(--premium-slate-light);
        border: 1.5px solid #e2e8f0;
        padding: 10px 25px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.85rem;
        margin-right: 12px;
        transition: all 0.2s;
    }

    .btn-premium-reset:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--premium-slate);
    }

    /* Input with Action */
    .input-with-action {
        display: flex;
        align-items: center;
    }

    .btn-plus-premium {
        background: var(--premium-indigo);
        color: #fff;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .btn-plus-premium:hover {
        transform: rotate(90deg);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    /* Priority Badge */
    .premium-priority-badge {
        background: #f1f5f9;
        color: var(--premium-slate-light);
        padding: 0 15px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        cursor: pointer;
        font-weight: 900;
        font-size: 0.7rem;
        transition: all 0.3s;
        border: 1.5px solid transparent;
    }

    .premium-priority-badge.active {
        background: var(--premium-rose-soft);
        color: var(--premium-rose);
        border-color: var(--premium-rose);
        animation: pulse 2s infinite;
    }

    .premium-priority-badge i {
        margin-right: 6px;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .summary-receipt {
        padding: 12px 18px;
        background: #fff;
        border-radius: 20px;
        border: 1px solid #edf2f7;
        min-width: 240px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .receipt-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 5px;
    }

    .receipt-total {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 2px dashed #f1f5f9;
    }

    .bg-soft-danger {
        background-color: #fff1f2;
    }

    /* Responsive Adjustments for Mobile */
    @media (max-width: 767px) {

        .row.align-items-end.bg-soft-light.border-dashed>div,
        .row.g-3.mb-3>div {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            margin-bottom: 12px;
        }

        .premium-form-group label {
            font-size: 0.65rem !important;
            margin-bottom: 4px !important;
        }

        .premium-input,
        .premium-select,
        .footer-input {
            border-radius: 24px !important;
            height: 40px !important;
            font-size: 0.8rem !important;
            padding: 0 15px !important;
        }

        .summary-input {
            border-radius: 24px !important;
            height: 40px !important;
            font-size: 0.8rem !important;
            padding: 0 15px 0 35px !important;
        }

        .premium-footer-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            margin: 20px -15px -15px -15px;
            padding: 15px !important;
            position: relative !important;
            bottom: auto;
            border-radius: 0 0 16px 16px;
        }

        .footer-input-group {
            margin-right: 0;
            min-width: 100%;
        }

        .footer-input-group.flex-grow-1 {
            width: 100%;
        }

        .status-glass-pill {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .pill-label {
            border-right: none;
            border-bottom: 1.5px solid #cbd5e1;
            padding-right: 0;
            padding-bottom: 5px;
            margin-right: 0;
            margin-bottom: 5px;
            width: 100%;
        }

        .pill-data {
            width: 100%;
            justify-content: space-between;
            gap: 10px;
        }

        .summary-receipt {
            margin-left: 0 !important;
            width: 100%;
            min-width: 100%;
            margin-top: 15px;
        }
    }

    /* Global Customer Search Dropdown Definition */
    #customerlist {
        list-style-type: none;
        padding: 0;
        margin: 0;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        width: 100%;
        display: none;
        position: absolute;
        z-index: 9999;
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        margin-top: 5px;
    }

    #customerlist.active {
        display: block !important;
    }

    #customerlist li {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
    }

    #customerlist li:last-child {
        border-bottom: none;
    }

    #customerlist li.hover,
    #customerlist li:hover {
        background-color: #f8fafc;
    }

    #customerlist li a {
        text-decoration: none;
        color: var(--premium-slate);
        display: block;
        font-weight: 700;
        font-size: 0.9rem;
    }

    #customerlist li:hover a {
        color: var(--premium-indigo);
    }

    #customerlist li.loading {
        text-align: center;
        color: #64748b;
        font-style: italic;
        font-size: 0.8rem;
    }

    .add-icon-box {
        display: grid;
        place-items: center;
        background: var(--premium-indigo);
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
    }

    /* Full Page Loader Overlay */
    .full-page-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(4px);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .loader-content {
        text-align: center;
        animation: loaderFadeIn 0.5s ease-out;
    }

    .spinner-premium {
        width: 60px;
        height: 60px;
        border: 4px solid var(--premium-indigo-soft);
        border-top: 4px solid var(--premium-indigo);
        border-radius: 50%;
        animation: spinPremium 1s linear infinite;
        margin: 0 auto 20px;
        box-shadow: 0 0 15px rgba(255, 138, 61, 0.1);
    }

    .loader-text {
        font-size: 1.2rem;
        font-weight: 900;
        color: var(--premium-slate);
        letter-spacing: 2px;
        margin-bottom: 8px;
    }

    .loader-subtext {
        font-size: 0.8rem;
        color: var(--premium-slate-light);
        font-weight: 700;
    }

    @keyframes spinPremium {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes loaderFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Disabled Form Fields Styling */
    .premium-input:disabled,
    .premium-select:disabled,
    .premium-input-select:disabled,
    .footer-input:disabled,
    .btn-premium-confirm:disabled,
    .btn-premium-add:disabled,
    .premium-priority-badge.disabled {
        background-color: #f1f5f9 !important;
        opacity: 0.65;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }

    /* Metal Advance Rate Label styling matching billing payment screen */
    #gold_adv_rate_amount_container:before,
    #silver_adv_rate_amount_container:before,
    #metal_adv_rate_amount_container:before {
        display: none !important;
    }

    .metal-adv-tab-btn {
        flex: 1;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 6px 8px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        position: relative;
    }
    .metal-adv-tab-btn:hover {
        transform: translateY(-1px);
        border-color: #cbd5e1;
    }
    .metal-adv-tab-btn.active.gold-tab {
        background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
        color: #92400e !important;
        border-color: #f59e0b !important;
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.22) !important;
        transform: scale(1.02);
    }
    .metal-adv-tab-btn.active.silver-tab {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
        color: #0f172a !important;
        border-color: #64748b !important;
        box-shadow: 0 4px 10px rgba(100, 116, 139, 0.22) !important;
        transform: scale(1.02);
    }

    .metal-tab-status-badge {
        font-size: 0.62rem;
        font-weight: 900;
        padding: 2px 6px;
        border-radius: 50px;
        line-height: 1.1;
        letter-spacing: 0.2px;
        transition: all 0.2s ease;
    }
    .metal-tab-status-badge.badge-empty {
        background: rgba(148, 163, 184, 0.18);
        color: #64748b;
        border: 1px solid rgba(148, 163, 184, 0.3);
    }
    .metal-tab-status-badge.badge-filled {
        background: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #86efac !important;
    }
    .metal-tab-status-badge.badge-active-gold {
        background: #fef08a !important;
        color: #854d0e !important;
        border: 1px solid #facc15 !important;
    }
    .metal-tab-status-badge.badge-active-silver {
        background: #e2e8f0 !important;
        color: #334155 !important;
        border: 1px solid #94a3b8 !important;
    }

    .metal-adv-panel.gold-panel {
        background: #fffdf5;
        border: 1.5px solid #fef08a;
        border-radius: 14px;
        padding: 10px 12px;
        box-shadow: inset 0 1px 3px rgba(245, 158, 11, 0.04);
        transition: all 0.25s ease;
    }
    .metal-adv-panel.silver-panel {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 12px;
        box-shadow: inset 0 1px 3px rgba(100, 116, 139, 0.04);
        transition: all 0.25s ease;
    }

    /* Main Row Dual Input Active Highlights */
    #gold_adv_wt_main.active-metal-input {
        border: 2px solid #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
        background: #fefce8 !important;
        color: #92400e !important;
    }
    #silver_adv_wt_main.active-metal-input {
        border: 2px solid #64748b !important;
        box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.25) !important;
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* Metal Advance Floating Labels */
    .metal-field-group {
        position: relative;
    }
    .metal-floating-label {
        position: absolute;
        top: -7px;
        left: 12px;
        font-size: 0.58rem;
        font-weight: 800;
        color: #b45309;
        background: #ffffff;
        padding: 0 5px;
        border-radius: 4px;
        line-height: 1.1;
        z-index: 5;
        pointer-events: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        border: 1px solid #fde68a;
        display: none;
        white-space: nowrap;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }
    .metal-floating-label.active {
        display: block !important;
    }
    .metal-floating-label.text-indigo {
        color: #4338ca !important;
        border-color: #c7d2fe !important;
    }
    .metal-floating-label.text-warning {
        color: #b45309 !important;
        border-color: #fde047 !important;
    }
</style>