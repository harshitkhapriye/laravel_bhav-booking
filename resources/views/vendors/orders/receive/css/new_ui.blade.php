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
</style>
