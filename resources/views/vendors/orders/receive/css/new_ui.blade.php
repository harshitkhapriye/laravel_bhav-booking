<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* =====================================================================
   JU Order — new UI (create / edit order page)
   Only styling. Every id, name and JS hook from the old page is kept.
   Everything is scoped under .ju-order so other JU pages are not touched.
   ===================================================================== */
.ju-order {
    font-family: Outfit, system-ui, -apple-system, "Segoe UI", sans-serif;
    --ju-or: #F07A1A;
    --ju-or2: #FFA45C;
    --ju-or-d: #C2560A;
    --ju-soft: #FFF4EA;
    --ju-soft2: #FFF9F3;
    --ju-line: #F1DFCD;
    --ju-line2: #E7EAF0;
    --ju-ink: #1E2235;
    --ju-mut: #6B7280;
    --ju-green: #15803D;
    --ju-red: #DC2626;
    --premium-indigo: #F07A1A;
    --premium-indigo-soft: #FFF4EA;
    color: var(--ju-ink);
}
.ju-order .ju-wrap { max-width: 980px; margin: 0 auto; }
.ju-order .ju-card-shell {
    background: #fff; border: 1px solid var(--ju-line2); border-radius: 22px !important;
    box-shadow: 0 10px 30px rgba(30, 34, 53, .07); overflow: visible !important;
}
.ju-order .erp-body { background: #fff; border-radius: 22px; padding: 0 0 4px; }

/* ---------- header ---------- */
.ju-order .ju-head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;
    padding: 18px 22px 16px; border-radius: 22px 22px 0 0;
    background: linear-gradient(135deg, #FFF3E6 0%, #FFFFFF 70%); border-bottom: 1px solid var(--ju-line);
}
.ju-order .ju-head-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
.ju-order .ju-head-title h3 { margin: 0; font-size: 1.3rem; font-weight: 800; letter-spacing: -.01em; }
.ju-order .ju-head-title small { color: var(--ju-mut); font-weight: 600; font-size: .78rem; }
.ju-order .ju-head-meta { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.ju-order .ju-meta { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.ju-order .ju-meta label, .ju-order .ju-meta .footer-input-group label {
    margin: 0; font-size: .68rem !important; font-weight: 800; color: var(--ju-mut);
    text-transform: none; letter-spacing: .02em; display: flex; align-items: center; gap: 4px;
}
.ju-order .ju-meta label i { color: var(--ju-or); margin: 0 !important; font-size: .72rem; }
.ju-order .ju-meta .premium-input, .ju-order .ju-meta .footer-input, .ju-order .ju-meta .premium-select {
    height: 36px !important; border-radius: 10px !important; font-size: .82rem !important; padding: 0 10px !important;
    background: #fff !important; border: 1.5px solid var(--ju-line) !important; width: 150px; box-shadow: none !important;
}
.ju-order .ju-meta-rate .premium-select { width: 170px; color: var(--ju-or-d) !important; }
.ju-order .ju-meta-id #display_order_id {
    width: 140px; text-align: center; color: var(--ju-or-d) !important; background: var(--ju-soft) !important; font-weight: 900 !important;
}
.ju-order .ju-meta .select-wrapper { width: auto; }

/* design popup header */
.ju-order .ju-head { align-items: center; flex-wrap: nowrap; padding: 14px 18px; }
.ju-order .ju-ttl { margin: 0; font-size: 1.35rem; font-weight: 700; color: var(--ju-ink); white-space: nowrap; letter-spacing: -.01em; flex: 1 1 auto; }
.ju-order .ju-head-meta { gap: 8px; flex-wrap: nowrap; }
.ju-order .ju-head .ju-meta label { font-size: .64rem !important; font-weight: 700; }
.ju-order .ju-head .ju-meta .premium-input, .ju-order .ju-head .ju-meta .footer-input { height: 32px !important; width: 128px !important; font-size: .8rem !important; border-radius: 8px !important; padding: 0 8px !important; }
.ju-order .ju-head .ju-meta-id #display_order_id { width: 118px !important; height: 32px !important; border: 1.5px solid #F7B985 !important; }
.ju-order .ju-x {
    width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; flex: 0 0 auto; margin-left: 6px;
    background: #FFE7D1; color: #1E2235 !important; font-size: 1rem; text-decoration: none !important; transition: background .2s, transform .15s;
}
.ju-order .ju-x:hover { background: #F07A1A; color: #fff !important; }
.ju-order .ju-rate { grid-template-columns: 128px minmax(0, 260px) !important; }
@media (max-width: 767px) {
    .ju-order .ju-head { flex-wrap: wrap; position: relative; padding-right: 56px; }
    .ju-order .ju-ttl { font-size: 1.12rem; width: 100%; }
    .ju-order .ju-head-meta { flex-wrap: wrap; display: flex !important; }
    .ju-order .ju-head .ju-meta { flex: 1 1 30%; }
    .ju-order .ju-head .ju-meta .premium-input, .ju-order .ju-head .ju-meta .footer-input, .ju-order .ju-head .ju-meta-id #display_order_id { width: 100% !important; }
    .ju-order .ju-x { position: absolute; top: 12px; right: 12px; }
    .ju-order .ju-rate { grid-template-columns: 112px minmax(0, 1fr) !important; }
}

/* ---------- icon tiles ---------- */
.ju-order .ju-tile {
    width: 36px; height: 36px; border-radius: 11px; flex-shrink: 0; display: grid; place-items: center;
    background: #fff; border: 1px solid var(--ju-line); color: var(--ju-or); font-size: .95rem;
    box-shadow: 0 2px 6px rgba(240, 122, 26, .10);
}

/* ---------- sections ---------- */
.ju-order .ju-sec { padding: 16px 22px 4px; }
.ju-order .ju-cust .premium-form-group > label {
    font-size: .74rem !important; font-weight: 800; color: var(--ju-mut); margin-bottom: 6px;
}
.ju-order .ju-cust .premium-form-group > label i { color: var(--ju-or); }
.ju-order .ju-cust #name.summary-input {
    height: 48px !important; font-size: 1rem !important; border-radius: 12px !important; background: #fff !important;
    border: 1.5px solid #F3B784 !important; padding-left: 40px !important; box-shadow: 0 0 0 4px rgba(240, 122, 26, .06) !important;
}
.ju-order .ju-cust #name.summary-input:focus { border-color: var(--ju-or) !important; outline: 0; box-shadow: 0 0 0 4px rgba(240, 122, 26, .14) !important; }
.ju-order .ju-cust .input-with-icon-badge > i { top: 17px; left: 15px; color: var(--ju-or); }
.ju-order .btn-plus-premium {
    width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--ju-or), var(--ju-or2));
    box-shadow: 0 6px 14px rgba(240, 122, 26, .28);
}
.ju-order .btn-plus-premium:hover { transform: none; filter: brightness(1.05); box-shadow: 0 8px 18px rgba(240, 122, 26, .35); }
.ju-order #customerlist { border-radius: 12px; border-color: var(--ju-line); }
.ju-order #customerlist li:hover a, .ju-order #customerlist li.hover a { color: var(--ju-or-d); }
.ju-order #customerlist li:hover, .ju-order #customerlist li.hover { background: var(--ju-soft2); }

.ju-order .ju-cust-row { display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-top: 12px; align-items: end; }
.ju-order .ju-cust-row .footer-input-group { margin: 0; min-width: 0; }
.ju-order .ju-cust-row .footer-input-group label {
    font-size: .72rem; font-weight: 800; color: var(--ju-mut); letter-spacing: .02em; text-transform: none; margin-bottom: 6px;
}
.ju-order .ju-cust-row .footer-input-group label i { color: var(--ju-or); }
.ju-order .ju-remark .footer-input {
    height: 44px; border-radius: 12px; background: #fff; border: 1.5px solid var(--ju-line2); font-size: .88rem; padding: 0 14px;
}
.ju-order .ju-remark .footer-input:focus { border-color: var(--ju-or); transform: none; }
.ju-order .mini-upload {
    width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; cursor: pointer;
    border: 1.5px dashed #F3B784; background: var(--ju-soft2); transition: background .2s, border-color .2s;
}
.ju-order .mini-upload:hover { background: var(--ju-soft); border-color: var(--ju-or); }
.ju-order .mini-upload i { color: var(--ju-or) !important; }
.ju-order .btn-remove-sketch {
    position: absolute; top: -7px; right: -7px; width: 20px; height: 20px; border-radius: 50%; border: 0;
    background: var(--ju-red); color: #fff; font-size: .62rem; display: grid; place-items: center; padding: 0;
}

/* ---------- added items table ---------- */
.ju-order .item-list-container { margin: 14px 22px 4px !important; }
.ju-order .item-list-container .table-card {
    border: 1px solid var(--ju-line); border-radius: 16px; box-shadow: none !important; background: #fff; overflow: hidden;
}
.ju-order .item-list-container .table-card > div:first-child {
    background: linear-gradient(90deg, #FFF3E6, #fff) !important; border-bottom: 1px solid var(--ju-line) !important; padding: 10px 14px !important;
}
.ju-order .item-list-container .table-card > div:first-child span { color: var(--ju-ink) !important; font-size: .8rem; }
.ju-order .item-list-container .table-card > div:first-child i { color: var(--ju-or) !important; }
.ju-order .table-card table thead th {
    background: #FFF9F3; color: var(--ju-or-d); font-size: .68rem; padding: 9px 12px; border-bottom: 1px solid var(--ju-line); letter-spacing: .03em;
}
.ju-order .table-card table tbody td { padding: 10px 12px; font-size: .86rem; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
.ju-order .table-card table tbody tr:hover { background: #FFFBF7; }
.ju-order .table-card table tbody tr.bg-soft-indigo { background: var(--ju-soft) !important; border-left-color: var(--ju-or) !important; }
.ju-order .table-card .badge.bg-warning { background: #FEF3C7 !important; color: #92400E !important; font-size: .68rem; }
.ju-order .table-card .badge.bg-secondary { background: #E5E7EB !important; color: #374151 !important; font-size: .68rem; }
.ju-order .table-card .btn-link { width: 30px; height: 30px; padding: 0; border-radius: 9px; border: 1px solid var(--ju-line2); background: #fff; }
.ju-order .table-card .btn-link:hover { background: var(--ju-soft); border-color: #F3B784; }

/* ---------- accordions ---------- */
.ju-order .ju-acc {
    margin: 14px 22px 0; border: 1px solid var(--ju-line); border-radius: 16px; background: #fff;
    box-shadow: 0 2px 10px rgba(240, 122, 26, .05); transition: box-shadow .2s;
}
.ju-order .ju-acc.open { box-shadow: 0 6px 18px rgba(240, 122, 26, .08); }
.ju-order .ju-acc-h {
    width: 100%; display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 0; cursor: pointer; text-align: left;
    background: linear-gradient(90deg, #FFF3E6, #FFFFFF); border-radius: 16px; transition: background .2s;
}
.ju-order .ju-acc.open > .ju-acc-h { border-radius: 16px 16px 0 0; border-bottom: 1px solid var(--ju-line); }
.ju-order .ju-acc-h:hover { background: linear-gradient(90deg, #FFE9D3, #FFFFFF); }
.ju-order .ju-acc-h:focus { outline: none; box-shadow: inset 0 0 0 2px rgba(240, 122, 26, .25); }
.ju-order .ju-acc-h b { color: var(--ju-or-d); font-size: .92rem; font-weight: 800; }
.ju-order .ju-acc-sum {
    font-style: normal; font-size: .72rem; font-weight: 700; color: var(--ju-mut); background: #F3F4F6;
    padding: 3px 9px; border-radius: 999px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 50%;
}
.ju-order .ju-acc-sum:empty { display: none; }
.ju-order .ju-chev { margin-left: auto; color: var(--ju-or); transition: transform .25s; font-size: .8rem; }
.ju-order .ju-acc.open .ju-chev { transform: rotate(180deg); }
.ju-order .ju-acc-b { display: none; padding: 14px 16px 10px; }
.ju-order .ju-acc.open > .ju-acc-b { display: block; animation: juFade .22s ease; }
@keyframes juFade { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

/* ---------- field grid: label sits next to its field ---------- */
.ju-order .ju-grid { display: grid; grid-template-columns: 1fr 1fr; column-gap: 56px; row-gap: 10px; }
.ju-order .ju-span2 { grid-column: 1 / -1; }
.ju-order .ju-f { display: grid !important; grid-template-columns: 128px minmax(0, 1fr); align-items: center; column-gap: 10px; margin: 0 !important; }
.ju-order .ju-f > label {
    margin: 0 !important; font-size: .8rem !important; font-weight: 700; color: var(--ju-ink) !important; letter-spacing: 0;
    display: flex !important; align-items: center; gap: 8px; line-height: 1.15; text-transform: none !important;
}
.ju-order .ju-f > label i {
    width: 24px; height: 24px; border-radius: 7px; flex-shrink: 0; display: inline-grid; place-items: center; margin: 0 !important;
    background: var(--ju-soft); color: var(--ju-or); font-size: .7rem; border: 1px solid #F8DCC2;
}
.ju-order .ju-f > label::after { content: ":"; color: var(--ju-mut); margin-left: -4px; }
.ju-order .ju-f > label.d-flex::after { content: none; }
.ju-order .ju-f > label { grid-row: 1; grid-column: 1; }
.ju-order .ju-f > :not(label):not(.validation-error) { grid-row: 1; grid-column: 2; min-width: 0; }
.ju-order .ju-f > .validation-error { grid-row: 2; grid-column: 2; }

/* inputs */
.ju-order .ju-acc .premium-input, .ju-order .ju-acc .premium-select, .ju-order .ju-acc .summary-input {
    height: 40px !important; border-radius: 10px !important; background: #fff !important; border: 1.5px solid var(--ju-line2) !important;
    font-size: .88rem !important; font-weight: 700; padding: 0 12px !important; box-shadow: none !important; color: var(--ju-ink);
}
.ju-order .ju-acc .premium-input:focus, .ju-order .ju-acc .premium-select:focus, .ju-order .ju-acc .summary-input:focus,
.ju-order .ju-meta .premium-input:focus, .ju-order .ju-meta .footer-input:focus, .ju-order .ju-meta .premium-select:focus {
    border-color: var(--ju-or) !important; box-shadow: 0 0 0 3px rgba(240, 122, 26, .15) !important; outline: none;
}
.ju-order .ju-acc .premium-input[readonly], .ju-order .ju-acc .summary-input[readonly] { background: #F8FAFC !important; }
.ju-order #item_net_wt, .ju-order #item_fine_wt { background: var(--ju-soft2) !important; color: var(--ju-or-d) !important; font-weight: 900 !important; }
.ju-order #item_fine_wt { color: var(--ju-green) !important; background: #F0FDF4 !important; }
.ju-order .weight-input { font-size: .95rem !important; }
.ju-order .ju-acc .premium-input.text-danger, .ju-order .ju-acc .text-indigo.premium-input { color: var(--ju-ink) !important; }
.ju-order .ju-acc .select-wrapper { width: 100%; display: flex; flex-direction: column; }
.ju-order .ju-acc .select-wrapper > .validation-error { order: 5; }
.ju-order .ju-acc .input-with-icon-badge { position: relative; }
.ju-order .ju-acc .input-with-icon-badge > i { display: none; }
.ju-order .ju-acc .input-with-icon-badge .summary-input { padding-left: 12px !important; }
.ju-order .ju-acc .badge-right {
    top: 50% !important; transform: translateY(-50%); right: 10px !important; border: 0 !important; background: transparent !important;
    color: var(--ju-mut) !important; font-size: .7rem !important; font-weight: 800 !important; padding: 0 !important;
}
.ju-order #rate_unit_label { color: var(--ju-or-d) !important; }

/* multi-inputs (making + type, online + bank) */
.ju-order .ju-acc .premium-multi-input {
    border-radius: 10px !important; border: 1.5px solid var(--ju-line2) !important; background: #fff !important; box-shadow: none !important; height: 40px; overflow: visible;
}
.ju-order .ju-acc .premium-multi-input:focus-within { border-color: var(--ju-or) !important; box-shadow: 0 0 0 3px rgba(240, 122, 26, .15) !important; }
.ju-order .ju-acc .premium-multi-input > .premium-input { border: 0 !important; height: 37px !important; box-shadow: none !important; background: transparent !important; }
.ju-order .ju-acc .premium-input-select {
    height: 37px !important; background: var(--ju-soft) !important; color: var(--ju-or-d) !important; font-size: .78rem !important;
    border-left: 1px solid var(--ju-line) !important; border-radius: 0 9px 9px 0; padding: 0 8px !important;
}
/* unit tags on cash */
.ju-order .input-with-unit .unit-tag { background: transparent; color: var(--ju-mut); font-size: .72rem; }

/* select2 inside the new layout */
.ju-order .ju-acc .select2-container .select2-selection--single {
    height: 40px; border-radius: 10px; border: 1.5px solid var(--ju-line2); display: flex; align-items: center;
}
.ju-order .ju-acc .select2-container--focus .select2-selection--single,
.ju-order .ju-acc .select2-container--open .select2-selection--single { border-color: var(--ju-or); }
.ju-order .ju-acc .select2-selection__rendered { font-weight: 700; font-size: .88rem; color: var(--ju-ink) !important; padding-left: 12px !important; }
.ju-order .ju-acc .select2-selection__arrow { height: 38px !important; }
.select2-container--default .select2-results__option--highlighted[aria-selected] { background: #F07A1A !important; }

/* add item row */
.ju-order .ju-addrow { display: flex; justify-content: flex-end; padding: 12px 22px 0; }
.ju-order #add_item_btn.btn-premium-add {
    height: 44px; padding: 0 22px; border-radius: 12px; font-size: .9rem; letter-spacing: .02em; width: auto !important;
    background: linear-gradient(135deg, var(--ju-or), var(--ju-or2)); box-shadow: 0 8px 18px rgba(240, 122, 26, .28);
}
.ju-order #add_item_btn.btn-premium-add:hover { transform: translateY(-1px); filter: brightness(1.04); background: linear-gradient(135deg, var(--ju-or), var(--ju-or2)); }
.ju-order #add_item_btn.btn-success { background: linear-gradient(135deg, #16A34A, #22C55E) !important; box-shadow: 0 8px 18px rgba(22, 163, 74, .25); }

/* ---------- payment summary ---------- */
.ju-order .ju-stats { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
.ju-order .ju-stat {
    display: flex; align-items: center; gap: 8px; border: 1px solid var(--ju-line); border-radius: 999px; padding: 4px 12px 4px 6px; background: #fff;
}
.ju-order .ju-stat > label { margin: 0; font-size: .66rem !important; color: var(--ju-mut) !important; display: flex; align-items: center; gap: 5px; white-space: nowrap; }
.ju-order .ju-stat > label i { width: 22px; height: 22px; border-radius: 50%; background: var(--ju-soft); color: var(--ju-or); display: inline-grid; place-items: center; font-size: .62rem; margin: 0 !important; }
.ju-order .ju-stat .input-with-icon-badge { display: flex; align-items: center; gap: 4px; }
.ju-order .ju-stat .input-with-icon-badge > i { display: none; }
.ju-order .ju-stat .summary-input {
    width: 7.5ch !important; height: 24px !important; padding: 0 !important; border: 0 !important; background: transparent !important;
    font-size: .86rem !important; font-weight: 900; color: var(--ju-ink) !important; box-shadow: none !important; text-align: right;
}
.ju-order .ju-stat .badge-right { position: static; transform: none; border: 0; background: none; padding: 0; color: var(--ju-mut); font-size: .66rem; }
.ju-order #total_fine_display { color: var(--ju-green) !important; }
.ju-order .ju-pay { row-gap: 12px; }
.ju-order .ju-pay .ju-f > label.text-primary, .ju-order .ju-pay .ju-f > label.text-info, .ju-order .ju-pay .ju-f > label.small { color: var(--ju-ink) !important; font-weight: 700; }
.ju-order .ju-pay .summary-field > label.small { font-size: .8rem !important; text-transform: none !important; }

/* metal advance */
.ju-order .ju-f.ju-metal { display: block !important; }
.ju-order .ju-metal {
    border: 1.5px dashed #F3B784; border-radius: 14px; padding: 10px 12px; background: #FFFBF6;
}
.ju-order .ju-metal > .premium-form-group { display: grid; grid-template-columns: 128px minmax(0, 1fr); align-items: center; column-gap: 10px; margin: 0; }
.ju-order .ju-metal #toggle_metal_adv_details {
    margin: 0; color: var(--ju-or-d) !important; font-size: .8rem !important; font-weight: 800; justify-content: flex-start !important; gap: 6px;
}
.ju-order .ju-metal #toggle_metal_adv_details > span i { color: var(--ju-or); }
.ju-order .ju-metal #toggle_metal_adv_details > .fa-chevron-circle-up { color: var(--ju-or) !important; margin-left: 2px; }
.ju-order .ju-metal #metal_adv { color: var(--ju-or-d) !important; font-weight: 900 !important; }
.ju-order #metal_adv_details_dropdown {
    border-radius: 16px !important; border: 1px solid var(--ju-line) !important; box-shadow: 0 -12px 34px rgba(30, 34, 53, .18) !important;
}

/* bill box: Total bill | Paid | Balance due */
.ju-order .summary-receipt {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; margin: 16px 0 0 !important; padding: 12px 6px;
    border-radius: 14px; border: 1px solid var(--ju-line2); box-shadow: none; min-width: 0; background: #fff;
}
.ju-order .summary-receipt .receipt-row {
    display: flex; flex-direction: column; align-items: flex-start; justify-content: center; margin: 0; padding: 0 14px; border: 0;
}
.ju-order .summary-receipt .receipt-row + .receipt-row { border-left: 1px solid #EEF0F4; }
.ju-order .summary-receipt .receipt-total { padding-top: 0; margin-top: 0; border-top: 0; border-left: 1px dashed #F3B784 !important; }
.ju-order .summary-receipt .receipt-row > span:first-child { font-size: .66rem !important; letter-spacing: .04em; margin-bottom: 2px; }
.ju-order #total_payable_display { font-size: 1.3rem !important; color: var(--ju-ink) !important; }
.ju-order #advance_total_text { font-size: 1.3rem !important; }
.ju-order #balance_due_display { font-size: 1.3rem !important; }
.ju-order #balance_due_label { font-size: .66rem !important; }

/* customer balance strip */
.ju-order .status-glass-pill {
    margin-top: 10px; border-radius: 12px; background: #FFF7EF; border: 0; padding: 10px 14px; backdrop-filter: none;
}
.ju-order .status-glass-pill .pill-label { color: var(--ju-or-d); font-size: .75rem; gap: 6px; }
.ju-order .status-glass-pill .pill-label i { color: var(--ju-or); }
.ju-order .status-glass-pill .pill-data { justify-content: space-around; }
.ju-order .status-glass-pill .status-item { align-items: center; }
.ju-order .status-glass-pill .status-item span { font-size: .85rem; }

/* ---------- footer actions ---------- */
.ju-order .premium-footer-bar.ju-foot {
    position: sticky; bottom: 0; margin: 16px 0 0; padding: 12px 22px !important; border-radius: 0 0 22px 22px;
    display: flex; align-items: center; justify-content: center; gap: 12px; z-index: 50;
    background: rgba(255, 255, 255, .96); border-top: 1px solid var(--ju-line2); box-shadow: 0 -8px 24px rgba(30, 34, 53, .06);
}
.ju-order .ju-foot .actions { display: flex; gap: 10px; }
.ju-order #urgent_badge.premium-priority-badge {
    height: 46px; border-radius: 12px; padding: 0 18px; font-size: .8rem; letter-spacing: .04em; background: #fff;
    border: 1.5px solid var(--ju-line2); color: var(--ju-mut);
}
.ju-order #urgent_badge.premium-priority-badge:hover { border-color: #FCA5A5; color: var(--ju-red); }
.ju-order #urgent_badge.premium-priority-badge.active {
    background: linear-gradient(135deg, #EF4444, #F97316); color: #fff; border-color: transparent; animation: none;
    box-shadow: 0 8px 18px rgba(239, 68, 68, .3);
}
.ju-order .btn-premium-confirm {
    height: 46px; padding: 0 28px !important; border-radius: 12px; font-size: .92rem;
    background: linear-gradient(135deg, #16A34A, #22C55E); box-shadow: 0 8px 18px rgba(22, 163, 74, .28);
}
.ju-order .btn-premium-confirm:hover { transform: translateY(-1px); }

/* disabled until a customer is picked (old behaviour) */
.ju-order .ju-acc :disabled, .ju-order .ju-addrow :disabled, .ju-order .ju-cust-row :disabled { opacity: .55; cursor: not-allowed; }
.ju-order .ju-need-cust {
    margin: 10px 22px 0; padding: 9px 14px; border-radius: 12px; background: #FFF7EF; color: var(--ju-or-d);
    font-size: .8rem; font-weight: 700; display: flex; gap: 8px; align-items: center;
}

/* phone: sticky bill strip */
.ju-strip {
    position: fixed; left: 50%; transform: translate(-50%, -120%); top: 8px; z-index: 1040; display: flex; gap: 14px; align-items: center;
    background: #fff; border: 1px solid #F1DFCD; border-radius: 999px; padding: 7px 16px; box-shadow: 0 10px 24px rgba(30, 34, 53, .14);
    font-size: .76rem; font-weight: 700; color: #6B7280; transition: transform .28s ease; white-space: nowrap;
}
.ju-strip.show { transform: translate(-50%, 0); }
.ju-strip b { color: #1E2235; font-weight: 900; }
.ju-strip b.g { color: #15803D; } .ju-strip b.r { color: #DC2626; }

/* big visible scrollbar on this page */
html { scrollbar-color: #F3B784 #FFF4EA; }
body::-webkit-scrollbar { width: 12px; }
body::-webkit-scrollbar-track { background: #FFF4EA; }
body::-webkit-scrollbar-thumb { background: #F3B784; border-radius: 10px; border: 2px solid #FFF4EA; }

/* fixes */
.ju-order .ju-meta-id #display_order_id { flex: none !important; height: 36px !important; }
.ju-order .ju-acc .premium-multi-input > .premium-input, .ju-order .ju-acc .premium-multi-input > #metal_adv_dual_inputs { flex: 1 1 auto; min-width: 0; }
.ju-order .ju-metal #metal_adv_dual_inputs .premium-input { height: 37px !important; border: 0 !important; border-radius: 0 !important; }
.ju-order .ju-metal #metal_adv_type { min-width: 92px; }
.ju-order .ju-acc-h { user-select: none; }

/* ---------- phone / tablet ---------- */
@media (max-width: 767px) {
    .ju-order .ju-head { padding: 14px 14px 12px; }
    .ju-order .ju-head-title h3 { font-size: 1.1rem; }
    .ju-order .ju-head-meta { display: grid; grid-template-columns: 1fr 1fr; width: 100%; gap: 8px; }
    .ju-order .ju-meta .premium-input, .ju-order .ju-meta .footer-input, .ju-order .ju-meta .premium-select,
    .ju-order .ju-meta-rate .premium-select, .ju-order .ju-meta-id #display_order_id { width: 100% !important; }
    .ju-order .ju-sec { padding: 12px 14px 2px; }
    .ju-order .ju-cust-row { grid-template-columns: 1fr auto; }
    .ju-order .item-list-container { margin: 12px 14px 2px !important; }
    .ju-order .ju-acc { margin: 12px 12px 0; }
    .ju-order .ju-acc-b { padding: 12px 12px 8px; }
    .ju-order .ju-grid { grid-template-columns: 1fr; row-gap: 9px; }
    .ju-order .ju-f, .ju-order .ju-metal > .premium-form-group { grid-template-columns: 112px minmax(0, 1fr); }
    .ju-order .ju-f > label { font-size: .76rem !important; }
    .ju-order .ju-addrow { padding: 12px 12px 0; }
    .ju-order #add_item_btn.btn-premium-add { width: 100% !important; }
    .ju-order .summary-receipt { padding: 10px 2px; }
    .ju-order .summary-receipt .receipt-row { padding: 0 8px; }
    .ju-order #total_payable_display, .ju-order #advance_total_text, .ju-order #balance_due_display { font-size: 1.02rem !important; }
    .ju-order .status-glass-pill { flex-direction: row; flex-wrap: wrap; }
    .ju-order .status-glass-pill .pill-label { border: 0; width: 100%; margin: 0 0 4px; padding: 0; }
    .ju-order .premium-footer-bar.ju-foot { flex-direction: row !important; padding: 10px 12px !important; margin: 14px 0 0 !important; }
    .ju-order .ju-foot .actions { flex: 1; }
    .ju-order .btn-premium-confirm { flex: 1; justify-content: center; padding: 0 12px !important; font-size: .84rem; }
    .ju-order #urgent_badge.premium-priority-badge { padding: 0 12px; }
    .ju-order #metal_adv_details_dropdown {
        position: fixed !important; left: 8px !important; right: 8px !important; bottom: 8px !important; width: auto !important;
        max-height: 78vh; overflow-y: auto; z-index: 1065 !important;
    }
    .ju-order .table-card table { min-width: 640px; }
}

/* ===================== NEW ADDITION: design layout pieces ===================== */
.ju-order .ju-cust-top { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 12px; align-items: start; }
.ju-order .ju-cust-top .ju-cust-name > label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
.ju-order .ju-cust-top .ju-cust-name { margin: 0; }
.ju-order .ju-phone-in { height: 48px !important; border-radius: 12px !important; border: 1.5px solid var(--ju-line) !important; background: #fff !important; font-size: 1rem !important; font-weight: 600 !important; padding: 0 14px !important; color: var(--ju-ink) !important; }
.ju-order .ju-remark, .ju-order .ju-advq { margin: 12px 0 0 !important; min-width: 0; }
.ju-order .ju-remark label, .ju-order .ju-advq label { font-size: .78rem !important; font-weight: 700; color: var(--ju-ink) !important; text-transform: none !important; letter-spacing: 0 !important; margin-bottom: 6px; }
.ju-order .ju-remark label i, .ju-order .ju-advq label i { display: none; }
.ju-order .ju-remark .footer-input { height: 56px; border-radius: 12px; background: #fff; border: 1.5px solid var(--ju-line2); }
.ju-order .ju-advq .footer-input { width: 100%; height: 42px; border-radius: 10px; background: #fff; border: 1.5px solid var(--ju-line2); }
.ju-order .ju-advq .footer-input:focus, .ju-order .ju-remark .footer-input:focus { border-color: var(--ju-or); box-shadow: 0 0 0 3px rgba(240, 122, 26, .14); transform: none; }
.ju-order .ju-advq .input-with-unit { position: relative; }
/* item amount next to Add item */
.ju-order .ju-addrow { align-items: center; gap: 14px; }
.ju-order .ju-iamt { display: inline-flex; align-items: baseline; gap: 10px; }
.ju-order .ju-iamt span { font-size: .85rem; font-weight: 600; color: var(--ju-mut); }
.ju-order .ju-iamt b { font-size: 1.45rem; font-weight: 800; color: var(--ju-or); }
/* payment summary: chips in the header */
.ju-order #ju_acc_pay > .ju-acc-h { flex-wrap: wrap; row-gap: 6px; }
.ju-order #ju_acc_pay > .ju-acc-h .ju-stats { margin: 0; gap: 6px; }
.ju-order .ju-acc-h .ju-stat { padding: 2px 10px 2px 4px; }
.ju-order .ju-acc-h .ju-stat > label { display: none !important; }
.ju-order .ju-acc-h .ju-stat .summary-input { width: 6ch !important; font-size: .8rem !important; }
.ju-order .ju-chip { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 12px; border-radius: 999px; border: 1px solid #F7B985; background: #FFF4EA; color: var(--ju-or-d); font-size: .8rem; font-weight: 800; }
.ju-order .ju-chip em { font-style: normal; }
.ju-order .ju-pay { grid-template-columns: minmax(0, 560px) !important; }
.ju-order .ju-pay .ju-span2 { grid-column: 1 / -1; }
.ju-order .ju-tamt-in { background: #FFF4EA !important; color: var(--ju-or-d) !important; font-weight: 800 !important; border-color: #F7B985 !important; }
.ju-order .ju-tamt .input-with-unit { position: relative; }
/* footer: Urgent | Save & print | Save */
.ju-order .ju-saveprint {
    height: 46px; padding: 0 20px; border-radius: 12px; border: 1.5px solid var(--ju-or); background: #fff; color: var(--ju-or-d);
    font-weight: 800; font-size: .9rem; transition: background .2s, transform .15s;
}
.ju-order .ju-saveprint:hover { background: var(--ju-soft); transform: translateY(-1px); }
.ju-order .ju-foot .actions { margin: 0; }
@media (max-width: 767px) {
    .ju-order .ju-cust-top { grid-template-columns: 1fr; }
    .ju-order .ju-addrow { justify-content: space-between; }
    .ju-order .ju-iamt b { font-size: 1.15rem; }
    .ju-order .ju-saveprint { padding: 0 10px; font-size: .8rem; }
    .ju-order .ju-pay { grid-template-columns: 1fr !important; }
}

/* ===================== NEW ADDITION: page inside the board popup (?embed=1) ===================== */
html.ju-embed, html.ju-embed body { background: transparent !important; overflow: hidden !important; }
html.ju-embed body { visibility: hidden; }
html.ju-embed .ju-order { visibility: visible; position: fixed !important; inset: 0; z-index: 1035; overflow-y: auto; padding: 0 !important; margin: 0 !important; }
html.ju-embed .ju-order .ju-wrap { max-width: none; }
html.ju-embed .ju-order .ju-card-shell { border-radius: 18px !important; border: 0; min-height: 100%; }
html.ju-embed .modal, html.ju-embed .modal-backdrop, html.ju-embed .select2-container, html.ju-embed .swal2-container,
html.ju-embed #toast-container, html.ju-embed .full-page-overlay, html.ju-embed .daterangepicker, html.ju-embed .jp-root { visibility: visible; }
html.ju-embed .ju-strip { display: none !important; }
.jp-root .jp-drq { z-index: 2000 !important; }
@media (max-width: 767px) { html.ju-embed .ju-order .ju-card-shell { border-radius: 0 !important; } }

/* =====================================================================
   NEW ADDITION (pipeline design, v6): create / edit page = same sizes as the design popup
   (fonts, field heights, 4-column field grid with the centre gap, items table, payment box).
   Only CSS. Nothing from the old page is removed; ids / names / JS hooks are untouched.
   ===================================================================== */
.ju-order { --d-text: #1F2937; --d-muted: #6B7280; --d-line: #ECEEF2; --d-inbd: #C3CAD5; --d-acc: #F5C79E;
    --d-or: #F07A1A; --d-ord: #D9620A; --d-soft: #FFF4EA; --d-green: #16A34A; --d-red: #DC2626; font-size: 14px; }

/* header */
.ju-order .ju-head { padding: 12px 20px 10px; gap: 10px; background: linear-gradient(135deg, #FFF1E4 0%, #FFFFFF 70%); border-bottom: 1px solid var(--d-line); }
.ju-order .ju-ttl { font-size: 19px; font-weight: 700; color: var(--d-text); }
.ju-order .ju-head .ju-meta { gap: 1px; }
.ju-order .ju-head .ju-meta label { font-size: 9.5px !important; font-weight: 700; color: var(--d-muted); }
.ju-order .ju-head .ju-meta label i { font-size: 9.5px; }
.ju-order .ju-head .ju-meta .premium-input, .ju-order .ju-head .ju-meta .footer-input {
    height: 26px !important; width: 108px !important; font-size: 11px !important; border-radius: 6px !important;
    padding: 2px 5px !important; border: 1px solid var(--d-inbd) !important; font-weight: 500 !important; color: var(--d-text) !important; }
.ju-order .ju-head .ju-meta input[name="delivery_date"] { color: var(--d-red) !important; }
.ju-order .ju-head .ju-meta-id #display_order_id { height: 26px !important; width: 108px !important; font-weight: 700 !important;
    color: var(--d-ord) !important; background: var(--d-soft) !important; border: 1px solid #EDB585 !important; }
.ju-order .ju-x { width: 30px; height: 30px; border-radius: 9px; font-size: 14px; }

/* body spacing (design: 20px sides, 8px between blocks) */
.ju-order .ju-sec { padding: 12px 20px 0; }
.ju-order .item-list-container { margin: 8px 20px 0 !important; }
.ju-order .ju-acc { margin: 8px 20px 0; }
.ju-order .ju-addrow { padding: 8px 22px 0; }

/* customer + phone */
.ju-order .ju-cust-top { gap: 10px; }
.ju-order .ju-cust #name.summary-input {
    height: 41px !important; font-size: 15px !important; font-weight: 600 !important; border-radius: 10px !important;
    border: 1px solid var(--d-or) !important; box-shadow: 0 0 0 3px rgba(240, 122, 26, .18) !important; padding-left: 14px !important; color: var(--d-text) !important; }
.ju-order .ju-cust .input-with-icon-badge > i.fa-search { display: none; }
.ju-order .ju-phone-in { height: 41px !important; font-size: 15px !important; font-weight: 600 !important; border-radius: 10px !important; border: 1px solid var(--d-acc) !important; }
.ju-order .btn-plus-premium { width: 41px; height: 41px; border-radius: 10px; }
/* customer box shows only the name (display layer; the old value stays in #name) */
.ju-order .ju-namev { position: absolute; left: 1px; top: 1px; bottom: 1px; right: 1px; display: flex; align-items: center; padding: 0 14px;
    font-size: 15px; font-weight: 600; color: var(--d-text); background: transparent; pointer-events: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-radius: 10px; }
.ju-order .input-with-icon-badge.ju-has-namev:not(.ju-typing) #name { color: transparent !important; }
.ju-order .input-with-icon-badge.ju-typing .ju-namev { display: none; }

/* remark + advance */
.ju-order .ju-remark, .ju-order .ju-advq { margin: 8px 0 0 !important; }
.ju-order .ju-remark label, .ju-order .ju-advq label { font-size: 12px !important; font-weight: 700; color: var(--d-text) !important; margin-bottom: 3px; }
.ju-order .ju-remark .footer-input { height: 48px; border-radius: 8px; border: 1px solid var(--d-inbd); font-size: 13px; font-weight: 500; padding: 7px 10px; color: var(--d-text); }
.ju-order .ju-advq .footer-input { height: 31px; border-radius: 8px; border: 1px solid var(--d-inbd); font-size: 13px; font-weight: 500; padding: 7px 10px; }

/* accordions */
.ju-order .ju-acc { border: 1px solid var(--d-acc); border-radius: 12px; box-shadow: none !important; }
.ju-order .ju-acc-h { padding: 10px 12px; gap: 8px; border-radius: 12px; background: linear-gradient(90deg, #FFF4EA, #FFFFFF); }
.ju-order .ju-acc.open > .ju-acc-h { border-radius: 12px 12px 0 0; border-bottom: 1px solid var(--d-line); }
.ju-order .ju-acc-h b { font-size: 13px; font-weight: 800; color: var(--d-ord); }
.ju-order .ju-acc-h .ju-tile { width: 24px; height: 24px; border-radius: 7px; border: 0; box-shadow: 0 0 0 1px var(--d-acc); color: var(--d-ord); font-size: 11px; margin-right: 0; }
.ju-order .ju-acc-sum { font-size: 10.5px; font-weight: 600; padding: 1px 8px; background: #F3F4F6; color: var(--d-muted); }
.ju-order .ju-chev { color: var(--d-ord); font-size: 11px; }
.ju-order .ju-acc-b { padding: 10px 12px 12px; }

/* field grid: label | field | label | field, centre gap like the design */
.ju-order .ju-acc .ju-grid:not(.ju-pay) { grid-template-columns: max-content minmax(0, 1fr) max-content minmax(0, 1fr); column-gap: 8px; row-gap: 7px; align-items: center; }
.ju-order .ju-acc .ju-grid:not(.ju-pay) > .ju-f { grid-column: span 2; grid-template-columns: 110px minmax(0, 1fr); grid-template-columns: subgrid; column-gap: 8px; row-gap: 3px; }
.ju-order .ju-acc .ju-grid:not(.ju-pay) > .ju-f:nth-of-type(even) > label { margin-left: 64px !important; }
.ju-order .ju-acc .ju-grid:not(.ju-pay) > .ju-f.ju-span2 { grid-column: 1 / -1; }
.ju-order .ju-acc .ju-f > label { font-size: 12px !important; font-weight: 700; color: var(--d-text) !important; gap: 0; line-height: 1.2; padding-right: 2px; }
.ju-order .ju-acc .ju-f > label::after { content: " :"; white-space: pre; margin-left: 0; color: var(--d-muted); }
.ju-order .ju-acc .ju-f > label i { width: 20px; height: 20px; border-radius: 6px; border: 0; margin-right: 6px !important; background: #FFF1E4; color: var(--d-ord); font-size: 10px; }
.ju-order .ju-acc .ju-f > label.text-danger, .ju-order .ju-acc .ju-f > label.text-muted, .ju-order .ju-acc .ju-f > label.text-primary,
.ju-order .ju-acc .ju-f > label.text-success, .ju-order .ju-acc .ju-f > label.text-indigo { color: var(--d-text) !important; }
.ju-order #main_metal_label { text-transform: none; }

/* inputs + selects */
.ju-order .ju-acc .premium-input, .ju-order .ju-acc .premium-select, .ju-order .ju-acc .summary-input {
    height: 32px !important; padding: 7px 9px !important; font-size: 13px !important; font-weight: 500 !important; color: var(--d-text) !important;
    border: 1px solid var(--d-inbd) !important; border-radius: 8px !important; text-align: left !important; background: #fff !important; }
.ju-order .ju-acc select.premium-select { height: 34px !important; }
.ju-order .ju-acc .premium-input[readonly], .ju-order .ju-acc .summary-input[readonly] { background: #F8FAFC !important; color: var(--d-ord) !important; font-weight: 700 !important; }
.ju-order #item_net_wt, .ju-order #item_fine_wt { background: #F8FAFC !important; color: var(--d-ord) !important; font-weight: 700 !important; }
.ju-order .weight-input { font-size: 13px !important; }
.ju-order .ju-acc .text-primary.premium-input, .ju-order #metal_rate_global { color: var(--d-text) !important; font-weight: 500 !important; }
.ju-order #rate_unit_label { color: var(--d-muted) !important; font-size: 12px !important; font-weight: 600 !important; }
.ju-order .ju-acc .select2-container .select2-selection--single { height: 34px; border-radius: 8px; border: 1px solid var(--d-inbd); }
.ju-order .ju-acc .select2-selection__rendered { font-weight: 500; font-size: 13px; padding-left: 9px !important; }
.ju-order .ju-acc .select2-selection__arrow { height: 32px !important; }
/* unit inside the box (gm / ₹) */
.ju-order .ju-suf { position: relative; min-width: 0; }
.ju-order .ju-suf > input { padding-right: 36px !important; width: 100%; }
.ju-order .ju-suf::after { content: attr(data-u); position: absolute; right: 10px; top: 16px; transform: translateY(-50%); font-size: 12px; font-weight: 700; color: var(--d-text); pointer-events: none; }
/* making: amount box + its own type box (design) */
.ju-order .ju-acc .ju-f .premium-multi-input { border: 0 !important; background: transparent !important; height: auto; gap: 6px; box-shadow: none !important; }
.ju-order .ju-acc .ju-f .premium-multi-input:focus-within { box-shadow: none !important; }
.ju-order .ju-acc .ju-f .premium-multi-input > .ju-suf { flex: 1 1 auto; }
.ju-order .ju-acc .ju-f .premium-multi-input > .premium-input, .ju-order .ju-acc .ju-f .premium-multi-input > .ju-suf > .premium-input { border: 1px solid var(--d-inbd) !important; height: 32px !important; background: #fff !important; }
.ju-order .ju-acc .premium-input-select { height: 34px !important; border: 1px solid var(--d-inbd) !important; border-radius: 8px !important; background: #fff !important;
    color: var(--d-text) !important; font-size: 13px !important; font-weight: 500 !important; padding: 7px 9px !important; flex: 0 0 74px; }

/* added items table */
.ju-order .item-list-container .table-card { border: 1px solid var(--d-line); border-radius: 12px; }
.ju-order .item-list-container .table-card > div:first-child { padding: 7px 12px !important; gap: 6px; background: linear-gradient(90deg, #FFF4EA, #FFFFFF) !important; border-bottom: 1px solid var(--d-line) !important; }
.ju-order .item-list-container .table-card > div:first-child > i { display: none; }
.ju-order .item-list-container .table-card > div:first-child > span { font-size: 12.5px !important; font-weight: 800 !important; color: var(--d-text) !important; text-transform: none !important; }
.ju-order .ju-ic { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 700; color: #fff; line-height: 15px; background: linear-gradient(135deg, #F07A1A, #FFA45C); }
.ju-order .ju-itot { margin-left: auto; font-size: 12.5px; font-weight: 800; color: var(--d-ord); }
.ju-order .table-card table thead th { padding: 7px 9px; font-size: 12.5px; font-weight: 700; color: var(--d-ord); letter-spacing: 0; text-transform: none;
    text-align: left !important; background: linear-gradient(#FFF6EE, #FFFFFF); border-bottom: 0; box-shadow: inset 0 -2px 0 var(--d-line); }
.ju-order .table-card table thead th:nth-child(6) { text-align: right !important; }
.ju-order .table-card table tbody td { padding: 6px 9px; font-size: 12.5px; font-weight: 600; color: var(--d-text); text-align: left !important; vertical-align: middle; }
.ju-order .table-card table tbody td:nth-child(6) { text-align: right !important; }
.ju-order .table-card table tbody td:first-child { color: var(--d-text) !important; font-weight: 600 !important; }
.ju-order .table-card tbody td .d-flex.flex-column { align-items: flex-start !important; }
.ju-order .table-card tbody td:nth-child(6) .d-flex.flex-column { align-items: flex-end !important; }
.ju-order .table-card tbody td .fw-800.text-dark, .ju-order .table-card tbody td .fw-900 { font-size: 12.5px !important; font-weight: 700 !important; }
.ju-order .table-card tbody td small, .ju-order .table-card tbody td .small { display: block; font-size: 11px !important; font-weight: 500 !important; color: var(--d-muted) !important; margin: 0 !important; }
.ju-order .table-card tbody td .ju-rate-l { color: var(--d-ord) !important; font-weight: 700 !important; }
.ju-order .table-card tbody td .text-success { color: var(--d-green) !important; margin: 0 !important; }
.ju-order .table-card tbody td:nth-child(5) .fw-900 { color: var(--d-ord) !important; }
.ju-order .table-card tbody td:nth-child(6) .fw-900 { color: var(--d-text) !important; }
.ju-order .table-card tbody td .ju-hide { display: none !important; }
.ju-order .table-card .badge.ju-mchip { display: inline-block; padding: 2px 8px !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; }
.ju-order .table-card .badge.ju-mchip.gold { background: #FDF3D7 !important; color: #A9790B !important; }
.ju-order .table-card .badge.ju-mchip.silver { background: #EDF0F4 !important; color: #55606E !important; }
.ju-order .table-card tbody td .badge.bg-soft-warning { font-size: 10px !important; font-weight: 700 !important; padding: 1px 7px !important; border-radius: 999px !important; text-transform: none; }
.ju-order .table-card tbody td .badge.bg-soft-warning .fa-spin { animation: none; }
.ju-order .table-card tbody tr.ju-it { cursor: pointer; }
.ju-order .table-card tbody tr.ju-it.ju-locked { cursor: default; }
/* row actions: old Edit / Delete buttons stay in the row (hidden); the ⋮ menu clicks them */
.ju-order .table-card tbody td .ju-oldact { display: none !important; }
.ju-order .ju-itact { width: 30px; height: 30px; border: 1px solid var(--d-line); background: #fff; border-radius: 8px; font-size: 16px; font-weight: 800; color: var(--d-muted);
    line-height: 1; padding: 0; transition: background .2s, border-color .2s, color .2s, transform .15s; }
.ju-order .ju-itact:hover { border-color: var(--d-or); color: var(--d-ord); background: #FFF1E4; transform: scale(1.08); }
.ju-itm { position: fixed; z-index: 2100; min-width: 150px; padding: 4px; background: #fff; border: 1px solid #ECEEF2; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    font-family: Outfit, system-ui, sans-serif; display: none; }
.ju-itm.open { display: block; animation: juFade .18s ease; }
html.ju-embed .ju-itm { visibility: visible; }
.ju-itm button { display: flex; align-items: center; gap: 8px; width: 100%; text-align: left; border: 0; background: transparent; color: #1F2937; padding: 8px 12px; border-radius: 6px; font-size: 13px; }
.ju-itm button:hover:not(:disabled) { background: #FFF4EA; color: #D9620A; padding-left: 14px; }
.ju-itm button.del { color: #DC2626; } .ju-itm button.del:hover:not(:disabled) { background: #FEF2F2; color: #B91C1C; }
.ju-itm button:disabled { opacity: .5; cursor: not-allowed; }
.ju-itm small { display: block; padding: 2px 12px 6px; font-size: 11px; color: #6B7280; }

/* item amount + Add item */
.ju-order .ju-addrow { gap: 8px; }
.ju-order .ju-iamt span { font-size: 13px; font-weight: 700; color: var(--d-muted); }
.ju-order .ju-iamt b { font-size: 20px; font-weight: 700; color: var(--d-ord); }
.ju-order #add_item_btn.btn-premium-add { height: 37px !important; padding: 8px 16px !important; border-radius: 9px; font-size: 13px !important; font-weight: 600; letter-spacing: 0;
    text-transform: none; border: 1px solid var(--d-or); margin-left: 6px; }

/* payment summary */
.ju-order #ju_acc_pay > .ju-acc-h .ju-stats { gap: 6px; }
.ju-order .ju-chip { height: 20px; padding: 2px 9px; font-size: 11px; font-weight: 700; color: var(--d-ord); background: #FFF4EA; border: 1px solid var(--d-acc); gap: 4px; }
.ju-order .ju-chip i { font-size: 10px; }
.ju-order .ju-acc-h .ju-stat { height: 20px; padding: 2px 9px; gap: 3px; border: 1px solid var(--d-acc); background: #FFF4EA; border-radius: 999px; }
.ju-order .ju-acc-h .ju-stat .input-with-icon-badge { gap: 3px; }
.ju-order .ju-acc-h .ju-stat .summary-input { height: 16px !important; font-size: 11px !important; font-weight: 700 !important; color: var(--d-muted) !important;
    text-align: left !important; padding: 0 !important; border: 0 !important; background: transparent !important; min-width: 1ch; }
.ju-order .ju-acc-h .ju-stat .badge-right { font-size: 11px !important; font-weight: 700 !important; color: var(--d-muted) !important; text-transform: lowercase; }
.ju-order .ju-acc-h .ju-stat #total_fine_display { color: var(--d-green) !important; }
.ju-order .ju-pay { grid-template-columns: 140px minmax(0, 290px) minmax(0, 1fr) !important; column-gap: 8px; row-gap: 7px; align-items: center; }
.ju-order .ju-pay > .ju-f { grid-column: 1 / span 2; grid-template-columns: 140px minmax(0, 1fr); grid-template-columns: subgrid; column-gap: 8px; }
.ju-order .ju-pay > .ju-f.ju-span2 { grid-column: 1 / -1; }
.ju-order .ju-pay .ju-f > label, .ju-order .ju-pay .summary-field > label.small { white-space: nowrap; font-size: 12px !important; font-weight: 700 !important; color: var(--d-text) !important; text-transform: none !important; }
.ju-order .ju-pay .input-with-unit .unit-tag, .ju-order .ju-pay .badge-right { font-size: 12px !important; color: var(--d-muted) !important; }
.ju-order .ju-tamt-in { background: #FFF4EA !important; color: var(--d-ord) !important; font-weight: 800 !important; border-color: #EDB585 !important; }
.ju-order .ju-pay .ju-f .premium-multi-input > .premium-input { flex: 1 1 auto; }
.ju-order .ju-pay .ju-f .premium-multi-input > .premium-input-select { flex: 0 0 84px; min-width: 84px !important; }
/* metal advance row */
.ju-order .ju-metal { margin-top: 3px; padding: 8px 10px; border: 1px dashed var(--d-acc); border-radius: 10px; background: #fff; }
.ju-order .ju-metal > .premium-form-group { grid-template-columns: 122px minmax(0, 1fr); column-gap: 8px; }
.ju-order .ju-metal .premium-multi-input { display: flex; min-width: 0; }
.ju-order .ju-metal #toggle_metal_adv_details { font-size: 12.5px !important; font-weight: 800; color: var(--d-ord) !important; }
.ju-order .ju-metal .premium-multi-input { gap: 8px; }
.ju-order .ju-metal #metal_adv, .ju-order .ju-metal #metal_adv_dual_inputs .premium-input { height: 33px !important; border: 1px solid var(--d-inbd) !important; border-radius: 8px !important;
    font: 700 13px Outfit, system-ui, sans-serif !important; color: var(--d-ord) !important; background: #fff !important; text-align: left !important; padding: 7px 10px !important; }
.ju-order .ju-metal #metal_adv_dual_inputs { gap: 6px; }
.ju-order .ju-metal #metal_adv_dual_inputs.d-none, .ju-order .ju-metal #metal_adv.d-none { display: none !important; }
.ju-order .ju-metal #metal_adv_type { flex: 0 0 110px; min-width: 110px !important; text-transform: none; }
/* bill box */
.ju-order .summary-receipt { gap: 10px; margin: 10px 0 0 !important; padding: 10px 12px; border: 1px solid var(--d-line); border-radius: 12px; }
.ju-order .summary-receipt .receipt-row { padding: 0; }
.ju-order .summary-receipt .receipt-row + .receipt-row { border-left: 0; }
.ju-order .summary-receipt .receipt-total { border-left: 1px dashed #D9DDE3 !important; padding-left: 12px; }
.ju-order .summary-receipt .receipt-row > span:first-child { font-size: 11px !important; font-weight: 700 !important; letter-spacing: .3px; color: var(--d-muted) !important; }
.ju-order .summary-receipt #balance_due_label { color: var(--d-red) !important; }
.ju-order #total_payable_display, .ju-order #advance_total_text, .ju-order #balance_due_display { font-size: 18px !important; font-weight: 800 !important; }
.ju-order #advance_total_text { color: var(--d-green) !important; }
/* customer balance */
.ju-order .status-glass-pill { display: grid !important; grid-template-columns: auto 1fr; gap: 10px; margin-top: 8px; padding: 7px 12px; border-radius: 10px; background: #FFF8F2; }
.ju-order .status-glass-pill .pill-label { font-size: 11.5px; font-weight: 800; color: var(--d-ord); text-transform: none; border: 0; padding: 0; margin: 0; }
.ju-order .status-glass-pill .pill-label i { display: none; }
.ju-order .status-glass-pill .pill-data { display: grid !important; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.ju-order .status-glass-pill .status-item { display: flex; flex-direction: column; align-items: center; text-align: center; }
.ju-order .status-glass-pill .status-item small { font-size: 11.5px; font-weight: 600; color: var(--d-muted); text-transform: none; }
.ju-order .status-glass-pill .status-item span { font-size: 13px !important; font-weight: 700 !important; color: var(--d-text); }
.ju-order .status-glass-pill .status-item span.text-danger { color: var(--d-red) !important; }
.ju-order .status-glass-pill .status-item span.ju-jama { color: var(--d-green) !important; }

/* footer */
.ju-order .premium-footer-bar.ju-foot { padding: 10px 20px 14px !important; gap: 10px; margin-top: 8px; }
.ju-order #urgent_badge.premium-priority-badge { height: 44px; border-radius: 11px; padding: 0 16px; font-size: 12px; font-weight: 700; gap: 5px; }
.ju-order #urgent_badge.premium-priority-badge.active { background: linear-gradient(90deg, #EF4444, #F97316, #EF4444); border: 1px solid #EF4444; }
.ju-order .ju-saveprint { height: 42px; border-radius: 11px; padding: 0 20px; font-size: 14px; font-weight: 600; border: 1px solid var(--d-or); }
.ju-order .btn-premium-confirm { height: 39px; padding: 11px 34px !important; border-radius: 11px; font-size: 14px; font-weight: 600;
    background: linear-gradient(135deg, #15803D, #4ADE80); box-shadow: 0 6px 16px rgba(22, 163, 74, .3); }

/* bill strip under the header (shows when the bill box is out of view) */
.ju-order .ju-hstrip { display: none; position: sticky; z-index: 59; gap: 28px; align-items: baseline; padding: 7px 20px;
    background: linear-gradient(90deg, #FFE7D1, #FFF4EA); border-bottom: 1.5px solid #F7C9A1; font-size: 11px; font-weight: 700; color: var(--d-muted); }
.ju-order .ju-hstrip.show { display: flex; animation: juFade .25s ease; }
.ju-order .ju-hstrip span { display: flex; align-items: baseline; gap: 6px; }
.ju-order .ju-hstrip b { font-size: 15px; font-weight: 800; color: var(--d-text); }
.ju-order .ju-hstrip b.g { color: var(--d-green); }
.ju-order .ju-hstrip .due { margin-left: auto; color: var(--d-red); }
.ju-order .ju-hstrip .due b { color: var(--d-red); font-size: 16px; }
.ju-order .ju-hstrip .due.adv, .ju-order .ju-hstrip .due.adv b { color: var(--d-green); }
html.ju-embed .ju-order .ju-head { position: sticky; top: 0; z-index: 60; border-radius: 18px 18px 0 0; }
.ju-strip { display: none !important; }

/* phone (same as the design phone view) */
@media (max-width: 767px) {
    .ju-order .ju-sec { padding: 10px 16px 0; }
    .ju-order .item-list-container, .ju-order .ju-acc { margin-left: 16px !important; margin-right: 16px !important; }
    .ju-order .ju-acc .ju-grid:not(.ju-pay) { grid-template-columns: max-content minmax(0, 1fr); }
    .ju-order .ju-acc .ju-grid:not(.ju-pay) > .ju-f:nth-of-type(even) > label { margin-left: 0 !important; }
    .ju-order .ju-acc .ju-f > label { width: 110px; }
    .ju-order .ju-pay { grid-template-columns: 112px minmax(0, 1fr) !important; }
    .ju-order .ju-pay > .ju-f { grid-template-columns: 112px minmax(0, 1fr); grid-template-columns: subgrid; }
    .ju-order .ju-hstrip { flex-wrap: nowrap; justify-content: space-between; gap: 10px; padding: 7px 16px; font-size: 10.5px; }
    .ju-order .ju-hstrip span { flex-direction: column; gap: 1px; }
    .ju-order .ju-hstrip b { font-size: 14px; }
    .ju-order .ju-hstrip .due { text-align: right; align-items: flex-end; }
    .ju-order .status-glass-pill { grid-template-columns: 1fr; }
    .ju-order .ju-metal > .premium-form-group { grid-template-columns: 1fr; row-gap: 6px; }
    .ju-order .ju-metal #metal_adv_type { flex-basis: 100px; min-width: 100px !important; }
    .ju-order #add_item_btn.btn-premium-add { width: auto !important; }
}

/* =====================================================================
   NEW ADDITION (pipeline design, v6b): create / edit = artifact behaviour + look
   ===================================================================== */
/* customer + phone boxes: icon inside, like the design */
.ju-order .ju-cust-name .input-with-icon-badge::before, .ju-order .ju-phone::before {
    content: ""; position: absolute; left: 13px; top: 20px; transform: translateY(-50%); width: 17px; height: 17px; z-index: 2; pointer-events: none;
    background: no-repeat center / contain url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23D9620A' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='8' r='4'/%3E%3Cpath d='M4 21a8 8 0 0 1 16 0'/%3E%3C/svg%3E"); }
.ju-order .ju-phone { position: relative; }
.ju-order .ju-phone::before { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23D9620A' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z'/%3E%3C/svg%3E"); }
.ju-order .ju-cust #name.summary-input, .ju-order .ju-phone-in { padding-left: 38px !important; }
.ju-order .ju-namev { padding-left: 38px; }
.ju-order .ju-cust #name.summary-input::placeholder, .ju-order .ju-phone-in::placeholder { color: #9AA1AC; font-weight: 500; }
/* edit page: name box is read only, keep it looking like the design (not grey) */
.ju-order .ju-cust #name[readonly] { background: #fff !important; cursor: default !important; }

/* fields that the old code keeps locked until a customer is chosen: same look as the design, just faded */
.ju-order .ju-cust :disabled, .ju-order .ju-acc :disabled, .ju-order .ju-foot :disabled {
    background-color: #fff !important; opacity: .6; cursor: not-allowed; }
.ju-order .ju-remark .footer-input:disabled, .ju-order .ju-advq .footer-input:disabled { background: #fff !important; }
.ju-order .ju-acc .premium-input[readonly]:disabled { background: #F8FAFC !important; }

/* Order details: two-line labels for the long names (design) */
.ju-order .ju-acc .ju-f:has(#item_jewellery_type) > label, .ju-order .ju-acc .ju-f:has(#item_product_name) > label { max-width: 104px; white-space: normal; }

/* Item amount + Add item: shown only after weight / making / labour is typed (design) */
.ju-order .ju-addrow { display: none !important; }
.ju-order .ju-addrow.ju-show { display: flex !important; animation: juFade .22s ease; }

/* payment header chips: icon + value like the design (old inputs stay inside, only hidden) */
.ju-order .ju-acc-h .ju-stat { position: relative; }
.ju-order .ju-acc-h .ju-stat .input-with-icon-badge { position: absolute !important; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none; }
.ju-order .ju-statv { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: var(--d-muted); white-space: nowrap; line-height: 1; }
.ju-order .ju-statv i { font-size: 10px; color: var(--d-muted); }
.ju-order .ju-statv.fine, .ju-order .ju-statv.fine i { color: var(--d-green); }
.ju-order .ju-acc-h .ju-stat { padding: 2px 9px !important; background: #fff !important; border-color: var(--d-line) !important; }
.ju-order .ju-chip { background: #FFF4EA; }

/* popup: height follows the content (board reads it) */
html.ju-embed .ju-order .ju-card-shell { min-height: 0 !important; }
.ju-order .ju-remark .footer-input, .ju-order .ju-advq .footer-input { border-radius: 8px !important; }
</style>

{{-- ===================== NEW ADDITION (v8): Metal pay details - inline panel, artifact design =====================
     Same old markup/ids/classes; only the look changes. Old floating popup is now an inline block under the Metal adv. row. --}}
<style>
.ju-order .ju-metal #metal_adv_details_dropdown.ju-mp {
    position: static !important; grid-column: 1 / -1; width: auto !important; z-index: auto;
    margin: 10px 0 0; padding: 10px 0 0 !important; border: 0 !important; border-top: 1px dashed var(--d-acc) !important;
    border-radius: 0 !important; box-shadow: none !important; background: transparent !important;
}
/* header: "Metal pay details" + close */
.ju-order .ju-mp > .d-flex:first-child { border: 0 !important; padding: 0 !important; margin: 0 0 10px !important; }
.ju-order .ju-mp > .d-flex:first-child > span { display: inline-block; font-size: 12.5px !important; font-weight: 800 !important; color: var(--d-ord) !important; letter-spacing: 0 !important; text-transform: lowercase !important; }
.ju-order .ju-mp > .d-flex:first-child > span::first-letter { text-transform: uppercase; }
.ju-order .ju-mp > .d-flex:first-child > span > i { display: none; }
.ju-order .ju-mp #close_metal_adv_details { width: 24px; height: 24px; padding: 0; border-radius: 7px; background: var(--d-soft) !important; color: var(--d-ord) !important; font-size: 15px !important; font-weight: 800; line-height: 24px; opacity: 1; text-shadow: none; }
/* Gold / Silver tabs */
.ju-order .ju-mp #metal_adv_tabs_nav { gap: 8px; margin-bottom: 10px !important; }
.ju-order .ju-mp #metal_adv_tabs_nav.d-none { display: none !important; }
.ju-order .ju-mp .metal-adv-tab-btn { border: 1px solid var(--d-line) !important; border-radius: 9px !important; background: #fff !important; color: var(--d-muted) !important; box-shadow: none !important; transform: none !important; padding: 5px 10px; font-size: 12px; }
.ju-order .ju-mp .metal-adv-tab-btn > span:first-child { flex: 1; display: inline-block; text-align: center; text-transform: lowercase; font-weight: 800; }
.ju-order .ju-mp .metal-adv-tab-btn > span:first-child::first-letter { text-transform: uppercase; }
.ju-order .ju-mp .metal-adv-tab-btn > span:first-child > i { display: none; }
.ju-order .ju-mp .metal-adv-tab-btn > span:first-child { padding-left: 14px; background: radial-gradient(circle, #EAB308 3.5px, transparent 4px) left center / 8px 8px no-repeat; }
.ju-order .ju-mp .metal-adv-tab-btn.silver-tab > span:first-child { background-image: radial-gradient(circle, #94A3B8 3.5px, transparent 4px); }
.ju-order .ju-mp .metal-adv-tab-btn.active { border-color: var(--d-or) !important; background: linear-gradient(135deg, #FFE0C4, #FFF7F0) !important; color: var(--d-text) !important; }
.ju-order .ju-mp .metal-tab-status-badge { font-size: 10.5px; font-weight: 800; padding: 1px 8px; border: 0 !important; background: #F3F4F6 !important; color: var(--d-muted) !important; }
.ju-order .ju-mp .metal-tab-status-badge.badge-filled { background: #DCFCE7 !important; color: #15803D !important; }
.ju-order .ju-mp .metal-tab-status-badge.badge-active-gold { background: #FEF3C7 !important; color: #B45309 !important; }
/* panel = 4 column grid, labels on top */
.ju-order .ju-mp .metal-adv-panel { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px 10px; padding: 0 !important; border: 0 !important; background: transparent !important; box-shadow: none !important; }
.ju-order .ju-mp .metal-adv-panel.d-none { display: none !important; }
.ju-order .ju-mp .metal-adv-panel > .d-flex:first-child { display: none !important; }
.ju-order .ju-mp .metal-adv-panel > .d-flex.gap-1.mb-2 { display: contents !important; }
.ju-order .ju-mp .metal-adv-panel > .mb-2 { margin: 0 !important; min-width: 0; }
.ju-order .ju-mp .metal-adv-panel > .mb-2:nth-child(2)::before { content: "Metal type"; }
.ju-order .ju-mp #gold_adv_jewellery_type_row::before { content: "Gold jewellery type"; }
.ju-order .ju-mp #silver_adv_jewellery_type_row::before { content: "Silver jewellery type"; }
.ju-order .ju-mp .metal-adv-panel > .mb-2::before,
.ju-order .ju-mp .metal-floating-label { display: block !important; position: static !important; order: -1; margin: 0 0 3px; padding: 0; border: 0 !important; box-shadow: none; background: transparent; font-size: 10.5px; font-weight: 700; color: var(--d-muted) !important; letter-spacing: 0; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ju-order .ju-mp .metal-field-group { display: flex; flex-direction: column; width: auto !important; min-width: 0; }
.ju-order .ju-mp .metal-field-group > i, .ju-order .ju-mp [id$="_jewellery_type_row"] i.fa { display: none !important; }
.ju-order .ju-mp .metal-adv-panel .premium-input { text-align: left !important; font-weight: 600; color: var(--d-text); border: 1px solid var(--d-inbd) !important; }
.ju-order .ju-mp .metal-adv-panel select.premium-input { color: var(--d-text) !important; background: #fff !important; }
.ju-order .ju-mp .metal-adv-panel .premium-input[readonly] { color: var(--d-ord) !important; font-weight: 800; background: var(--d-soft) !important; border-color: var(--d-acc) !important; }
.ju-order .ju-mp .metal-field-group:has(input[id$="_adv_gross"])::after,
.ju-order .ju-mp .metal-field-group:has(input[id$="_adv_net"])::after,
.ju-order .ju-mp .metal-field-group:has(input[id$="_adv_fine"])::after { content: "gm"; }
.ju-order .ju-mp .metal-field-group:has(input[id$="_adv_amount"])::after { content: "₹"; }
.ju-order .ju-mp .metal-field-group::after { position: absolute; right: 9px; bottom: 8px; font-size: 11px; font-weight: 700; color: var(--d-muted); pointer-events: none; }
/* Next / Back / Done */
.ju-order .ju-mp .metal-both-nav-btn-wrapper { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 8px; margin: 2px 0 0 !important; }
.ju-order .ju-mp .metal-both-nav-btn-wrapper.d-none { display: none !important; }
.ju-order .ju-mp .metal-both-nav-btn-wrapper .btn { width: auto !important; min-width: 130px; flex: 0 0 auto; border-radius: 8px !important; font-size: 12px !important; font-weight: 800; padding: 6px 14px !important; box-shadow: none !important; }
.ju-order .ju-mp .switch-to-silver-btn { background: linear-gradient(135deg, #F59E42, #F07A1A) !important; color: #fff !important; }
.ju-order .ju-mp .switch-to-gold-btn { background: #fff !important; color: var(--d-text) !important; border: 1px solid var(--d-line) !important; }
.ju-order .ju-mp .done-metal-adv-btn { background: var(--d-green) !important; }
/* footer total */
.ju-order .ju-mp > .border-top:last-child { margin-top: 10px !important; padding-top: 10px !important; border-top: 1px dashed var(--d-line) !important; font-size: 12px !important; }
.ju-order .ju-mp > .border-top:last-child .text-muted { color: var(--d-muted) !important; font-weight: 700; }
.ju-order .ju-mp #total_metal_adv_amount_summary { color: var(--d-green) !important; font-size: 15px; font-weight: 800; }
@media (max-width: 640px) {
    .ju-order .ju-mp .metal-adv-panel { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ju-order .ju-mp .metal-both-nav-btn-wrapper .btn { flex: 1 1 0; min-width: 0; }
}
</style>
