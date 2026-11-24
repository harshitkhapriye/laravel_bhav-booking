<script>
/* =====================================================================
   JU Order — new UI helpers (create / edit order page)
   Only display behaviour: collapsible sections, section summaries,
   phone bill strip. It reads the old fields; it never changes order logic.
   ===================================================================== */
(function ($) {
    if (!$('.ju-order').length) return;

    /* collapsible sections */
    $(document).on('click', '.ju-order .ju-acc-h', function () {
        var acc = $(this).closest('.ju-acc'), open = !acc.hasClass('open');
        acc.toggleClass('open', open);
        $(this).attr('aria-expanded', open);
    });
    $(document).on('keydown', '.ju-order .ju-acc-h', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); }
    });
    // a field inside a closed section gets focus (e.g. validation) -> open that section
    $(document).on('focusin', '.ju-order .ju-acc-b :input', function () {
        var acc = $(this).closest('.ju-acc');
        if (!acc.hasClass('open')) acc.addClass('open').find('> .ju-acc-h').attr('aria-expanded', true);
    });
    // validation from the old code marks fields .is-invalid -> make sure they are visible
    new MutationObserver(function () {
        $('.ju-order .ju-acc:not(.open)').each(function () {
            if ($(this).find('.is-invalid').length) $(this).addClass('open').find('> .ju-acc-h').attr('aria-expanded', true);
        });
    }).observe(document.querySelector('.ju-order'), { subtree: true, attributes: true, attributeFilter: ['class'] });

    /* small summaries in section headers */
    function txt(sel) { var o = $(sel).find('option:selected'); return o.length && o.val() ? $.trim(o.text()) : ''; }
    function num(sel) { var v = String($(sel).val() || '').replace(/,/g, ''); return parseFloat(v) || 0; }
    function summaries() {
        var d = [$('#item_metal_type').val(), txt('#item_purity_karat'), txt('#item_product_name') || txt('#item_jewellery_type')].filter(Boolean);
        $('#ju_sum_details').text(d.join(' · '));
        var g = num('#item_gross_wt'), f = num('#item_fine_wt');
        $('#ju_sum_weight').text(g > 0 ? ('Gross ' + g.toFixed(3) + ' g · Fine ' + f.toFixed(3) + ' g') : '');
    }
    $(document).on('input change', '.ju-order :input', function () { setTimeout(summaries, 0); });
    setInterval(summaries, 800);

    /* phone + desktop: bill strip when the bill box is out of view */
    var strip = $('<div class="ju-strip" aria-hidden="true">Total <b class="t">₹ 0</b> Paid <b class="g p">₹ 0</b> <span class="l">Due</span> <b class="r d">₹ 0</b></div>').appendTo('body');
    function stripSync() {
        var box = document.querySelector('.ju-order .summary-receipt');
        if (!box) return;
        var tot = num('#total_payable'), r = box.getBoundingClientRect(), vis = r.top < innerHeight && r.bottom > 0;
        var due = $('#balance_due_label').text().trim() || 'Balance Due';
        strip.find('.t').text($('#total_payable_display').text());
        strip.find('.p').text($('#advance_total_text').text());
        strip.find('.l').text(due);
        strip.find('.d').text($('#balance_due_display').text()).toggleClass('r', !/advance/i.test(due)).toggleClass('g', /advance/i.test(due));
        strip.toggleClass('show', tot > 0 && !vis && !$('#ju_acc_pay').hasClass('ju-hidden'));
    }
    $(window).on('scroll resize', stripSync);
    setInterval(stripSync, 700);
})(jQuery);
</script>

<script>
/* =====================================================================
   NEW ADDITION (pipeline design) — create / edit page extras.
   None of this changes the old order logic: it only reads the old fields,
   writes into the old cash_adv field, or clicks the old buttons.
   ===================================================================== */
(function ($) {
    if (!$('.ju-order').length) return;
    var num = function (sel) { return parseFloat(String($(sel).val() || '').replace(/,/g, '')) || 0; };
    var inr = function (n) { return '₹' + Math.round(n || 0).toLocaleString('en-IN'); };
    var isEdit = $('#is_edit_mode').val() === '1';

    /* --- phone box: mobile of the selected customer (display only) --- */
    function phoneSync() { var m = String($('#name').val() || '').match(/-\s*([+\d][\d\s]{5,})\s*$/); $('.ju-phone-in').val(m ? m[1].trim() : ''); }
    $(document).on('input change', '#name', function () { setTimeout(phoneSync, 0); });
    $(document).on('click', '.select_customer_order', function () { setTimeout(phoneSync, 0); });
    phoneSync();

    /* --- quick Advance box = old Cash Adv. field --- */
    var $cash = $('input[name="cash_adv"]'), $adv = $('.ju-adv-in');
    $adv.val($cash.val());
    // old rule stays: if no item is added yet, the old code clears cash_adv ("No payment needed!") -> the box shows the same
    $adv.on('input', function () { $cash.val($(this).val()).trigger('input'); var v = $cash.val(); if (String(v) !== String($(this).val())) $(this).val(v); });
    $cash.on('input change', function () { if (!$adv.is(':focus')) $adv.val($(this).val()); });

    /* --- item amount of the item being filled (same formula as the old running total) --- */
    function itemAmt() {
        var gross = num('#item_gross_wt'); if (gross <= 0) { $('#ju_item_amt').text('₹0'); return; }
        var metal = String($('#item_metal_type').val() || 'gold').toLowerCase(), karat = $('#item_purity_karat').val();
        var unit = (typeof window.getMetalRateUnit === 'function') ? (window.getMetalRateUnit(metal, karat) || 1) : 1;
        var perGram = num('#metal_rate_global') / unit, fine = num('#item_fine_wt'), qty = parseInt($('#item_qty').val()) || 1;
        var mk = num('#item_making'), lb = num('#item_labor'), type = $('#item_making_type').val(), mkTotal;
        if (type === 'per_piece') mkTotal = mk * qty + lb * qty; else if (type === 'per_gram') mkTotal = mk * gross * qty + lb * qty; else mkTotal = mk + lb * qty;
        $('#ju_item_amt').text(inr(fine * perGram * qty + mkTotal));
    }
    $(document).on('input change', '#item_gross_wt,#item_less_wt,#item_wastage_per,#item_wastage_percent,#item_metal_touch,#item_qty,#item_making,#item_making_type,#item_labor,#metal_rate_global,#item_purity_karat,#item_metal_type', function () { setTimeout(itemAmt, 0); });

    /* --- bill chip + Total amount box = old Total bill --- */
    setInterval(function () { var t = num('#total_payable'); $('#ju_chip_bill').text(inr(t)); $('.ju-tamt-in').val(Math.round(t).toLocaleString('en-IN')); itemAmt(); }, 500);

    /* --- Save & print: old save, then the old "Print Receipt" choice --- */
    var autoPrint = false;
    $('.ju-saveprint').on('click', function () { autoPrint = true; $('#createOrderForm').trigger('submit'); setTimeout(function () { autoPrint = false; }, 15000); });
    new MutationObserver(function () { if (!autoPrint) return; var b = document.querySelector('.swal2-confirm'); if (b && b.offsetParent) { autoPrint = false; b.click(); } }).observe(document.body, { childList: true, subtree: true });

    /* =================================================================
       Popup (embed) mode: page opened inside the board popup
       ================================================================= */
    var embed = document.documentElement.classList.contains('ju-embed');
    var DK = 'juOrderDrafts', draftId = new URLSearchParams(location.search).get('draft');
    function drGet() { try { return JSON.parse(localStorage.getItem(DK) || '[]') || []; } catch (x) { return []; } }
    function drSet(L) { try { localStorage.setItem(DK, JSON.stringify(L)); } catch (x) {} }
    function tellParent(what) { try { window.parent.postMessage({ juOrder: what }, '*'); } catch (x) {} }
    var dirty = false;
    $(document).on('input change', '#createOrderForm :input', function (e) { if (e.originalEvent) dirty = true; });
    $(document).on('click', '#add_item_btn, #urgent_badge', function (e) { if (e.originalEvent) dirty = true; });

    function draftData() {
        var f = {};
        $('#createOrderForm').find('input[name], select[name], textarea[name]').each(function () {
            if (!this.name || this.name === '_token' || this.type === 'file') return;
            f[this.name] = $(this).val();
        });
        var items = []; try { items = JSON.parse($('#items_json').val() || '[]') || []; } catch (x) {}
        return { name: String($('#name').val() || '').replace(/^[^/]*\//, '').replace(/\s*-\s*[+\d][\d\s]*$/, '') || 'Bina naam', n: items.length, fields: f };
    }
    function saveDraft() {
        var d = draftData(), L = drGet(), id = draftId || ('d' + Date.now());
        L = L.filter(function (x) { return x.id !== id; });
        L.unshift({ id: id, at: Date.now(), name: d.name, n: d.n, fields: d.fields });
        drSet(L.slice(0, 20)); tellParent('drafts');
    }
    window.juEmbedClose = function () {
        if (!isEdit && dirty) { $('#jp-drq').addClass('jp-open'); return; }
        tellParent('close');
    };
    $(document).on('click', '#jp-drq [data-a]', function () {
        var a = $(this).data('a'); $('#jp-drq').removeClass('jp-open');
        if (a === 'back') return;
        if (a === 'save') saveDraft(); else if (draftId) { drSet(drGet().filter(function (x) { return x.id !== draftId; })); tellParent('drafts'); }
        tellParent('close');
    });
    $(document).on('click', '.ju-order .ju-x', function (e) { if (!embed) return; e.preventDefault(); window.juEmbedClose(); });
    $(document).on('keydown', function (e) { if (embed && e.key === 'Escape' && !$('.modal.show, .swal2-container').length) window.juEmbedClose(); });

    /* restore a draft (fields were filled before the old script started; here we only refresh the extras) */
    if (window.__juDraftApplied) {
        dirty = true;
        if ($('#is_urgent').val() == '1') $('#urgent_badge').addClass('active');
        if ($('#custo_id').val() && typeof window.fetchCustomerUdhar === 'function') setTimeout(function () { try { fetchCustomerUdhar($('#custo_id').val()); } catch (x) {} }, 300);
        phoneSync(); $adv.val($cash.val());
    }
})(jQuery);
</script>
