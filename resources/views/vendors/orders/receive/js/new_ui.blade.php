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
        var m = String($('#item_metal_type').val() || ''); m = m ? m.charAt(0).toUpperCase() + m.slice(1).toLowerCase() : '';
        var d = [[m, txt('#item_purity_karat')].filter(Boolean).join(' '), txt('#item_product_name') || txt('#item_jewellery_type')].filter(Boolean);
        if (!(txt('#item_product_name') || txt('#item_jewellery_type'))) d.push('no product selected');
        $('#ju_sum_details').text(d.join(' · '));
        var g = num('#item_gross_wt'), f = num('#item_fine_wt');
        $('#ju_sum_weight').text(g > 0 ? ('Gross ' + g.toFixed(3) + ' g · Fine ' + f.toFixed(3) + ' g') : 'Weight not entered yet');
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
        return { name: String($('#name').val() || '').replace(/^[^/]*\//, '').replace(/\s*-\s*[+\d][\d\s]*$/, '') || 'No name', n: items.length, fields: f };
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

<script>
/* =====================================================================
   NEW ADDITION (pipeline design, v6): create / edit page looks like the design.
   Display only. The old item rows, totals and labels are still written by the old
   scripts.blade.php; this only re-shows them the design way:
   - customer box shows only the name (the old "num/name - mobile" value stays in #name)
   - added items: count + total in the heading, ⋮ menu (clicks the old Edit / Delete buttons),
     row click = old Edit, extra lines hidden, totals without paise
   - bill box / customer balance labels in design text, bill strip under the header
   ===================================================================== */
(function ($) {
    if (!$('.ju-order').length) return;
    var inr = function (n) { return '₹' + Math.round(n || 0).toLocaleString('en-IN'); };
    var money = function (t) { var v = parseFloat(String(t || '').replace(/[^\d.\-]/g, '')); return isNaN(v) ? null : v; };
    var cap = function (t) { t = String(t || '').trim().toLowerCase(); return t.charAt(0).toUpperCase() + t.slice(1); };
    function watch(el, fn, opts) { if (!el) return; fn(); new MutationObserver(fn).observe(el, opts || { childList: true, characterData: true, subtree: true }); }

    /* ---- customer box: show only the name ---- */
    var $name = $('#name'), $nbox = $name.parent();
    var $nv = $('<span class="ju-namev" aria-hidden="true"></span>').appendTo($nbox);
    function nameView() {
        var v = String($name.val() || ''), n = v.replace(/^[^/]*\//, '').replace(/\s*-\s*[+\d][\d\s]*$/, '');
        var show = v.indexOf('/') > -1 && n && n !== v;
        $nv.text(show ? n : ''); $nbox.toggleClass('ju-has-namev', !!show);
    }
    // while the user types in the box (search), show the real value
    $(document).on('keydown input', '#name', function (e) { if (!$name.prop('readonly')) $nbox.addClass('ju-typing'); });
    $(document).on('blur', '#name', function () { $nbox.removeClass('ju-typing'); });
    $(document).on('click', '.select_customer_order', function () { $nbox.removeClass('ju-typing'); });
    $(document).on('input change', '#name', nameView);
    $(document).on('click', '.select_customer_order', function () { setTimeout(nameView, 0); });
    setInterval(nameView, 600); nameView();

    /* ---- added items ---- */
    var $card = $('#item_list_container .table-card'), $hd = $card.children('div').first();
    var $ic = $('<span class="ju-ic">0</span>'), $tot = $('<span class="ju-itot"></span>');
    $hd.children('span').first().attr('data-old', $hd.children('span').first().text()).text('Added items').after($ic);
    $hd.append($tot);
    function decorate() {
        var sum = 0, n = 0;
        $('#item_list_body > tr').each(function () {
            var $tr = $(this), td = $tr.children('td'); n++;
            if (!$tr.hasClass('ju-it')) {
                $tr.addClass('ju-it');
                // product: name without "(type)"
                var $nm = td.eq(1).children('div').first(); var t = $nm.text(); $nm.attr('title', t).text(t.replace(/\s*\([^)]*\)\s*$/, ''));
                // metal: chip, then purity, then rate (design order)
                var $chip = td.eq(2).children('.badge').first(), mt = $.trim($chip.text());
                $chip.addClass('ju-mchip ' + (/silver/i.test(mt) ? 'silver' : 'gold')).text(cap(mt));
                var $rate = td.eq(2).children('div').eq(1), $pur = td.eq(2).children('div').eq(2);
                $rate.addClass('ju-rate-l').text($rate.text().replace(/^\s*Rate:\s*/, 'Rate ').replace(/\s+\//, '/'));
                $pur.insertBefore($rate);
                // size: "—" when empty
                var $sz = td.eq(1).children('small').first(); $sz.text($sz.text().replace(/Size:\s*\|/, 'Size: — |'));
                // charges: "M: ₹<making total> | L: ₹<labour>" like the design (numbers from the same row)
                var $cs = td.eq(4).find('.d-flex > div'), lab = money(($cs.first().text().match(/L:\s*₹\s*([\d,.]+)/) || [])[1]) || 0, rm = money($cs.last().text());
                if (rm !== null) { $cs.first().text('M: ' + inr(rm - lab) + ' | L: ' + inr(lab)); $cs.last().text(inr(rm)); }
                // weight: hide the wastage line
                td.eq(3).find('.d-flex > div').each(function () { if (/^\s*Wast/.test($(this).text())) $(this).addClass('ju-hide'); });
                // total: hide "Metal: ₹" line, show rupees without paise
                var $tw = td.eq(5).find('.d-flex > div'); $tw.first().addClass('ju-hide');
                var $tv = $tw.last(); var tv = money($tv.text()); if (tv !== null) $tv.attr('data-amt', tv).text(inr(tv));
                // actions: keep the old buttons (hidden), add ⋮
                var $act = td.eq(6).children('div').first().addClass('ju-oldact');
                var locked = $act.find('button:disabled').length > 0; $tr.toggleClass('ju-locked', locked);
                td.eq(6).append('<button type="button" class="ju-itact" aria-label="Item actions">⋮</button>');
            }
            var a = parseFloat(td.eq(5).find('[data-amt]').attr('data-amt')); if (!isNaN(a)) sum += a;
        });
        $ic.text(n); $tot.text(n ? 'Total ' + inr(sum) : '');
    }
    watch(document.getElementById('item_list_body'), decorate, { childList: true });

    // ⋮ menu: Edit / Delete = the old buttons of that row
    var $menu = $('<div class="ju-itm" role="menu"></div>').appendTo('body'), menuRow = null;
    function closeMenu() { $menu.removeClass('open'); menuRow = null; }
    $(document).on('click', '.ju-order .ju-itact', function (e) {
        e.stopPropagation();
        var $tr = $(this).closest('tr'); if (menuRow === $tr[0] && $menu.hasClass('open')) return closeMenu();
        var btns = $tr.find('.ju-oldact button'), ed = btns.eq(0), del = btns.eq(1), lock = ed.is(':disabled');
        $menu.html('<button type="button" data-k="0"' + (lock ? ' disabled' : '') + '><i class="fa fa-pencil-alt"></i> ' + (ed.hasClass('text-success') ? 'Cancel edit' : 'Edit') + '</button>'
            + '<button type="button" class="del" data-k="1"' + (lock || del.css('display') === 'none' ? ' disabled' : '') + '><i class="fa fa-trash"></i> Delete</button>'
            + (lock ? '<small>' + (ed.attr('title') || 'In process with karigar') + '</small>' : ''));
        menuRow = $tr[0];
        var r = this.getBoundingClientRect(); $menu.addClass('open');
        var h = $menu.outerHeight() || 90; $menu.css({ left: Math.max(8, r.right - 150) + 'px', top: (r.bottom + h + 8 > innerHeight ? r.top - h - 4 : r.bottom + 4) + 'px' });
    });
    $menu.on('click', 'button', function () { var k = +$(this).data('k'), row = menuRow; closeMenu(); if (row) $(row).find('.ju-oldact button').eq(k).trigger('click'); });
    $(document).on('click', function (e) { if (!$(e.target).closest('.ju-itm, .ju-itact').length) closeMenu(); });
    $('.ju-order').on('scroll', closeMenu); $(window).on('scroll resize', closeMenu);
    // row click = old Edit (design: "click karke item edit karo")
    $(document).on('click', '#item_list_body > tr.ju-it', function (e) {
        if ($(e.target).closest('button, a, .ju-itact').length) return;
        var ed = $(this).find('.ju-oldact button').eq(0); if (ed.length && !ed.is(':disabled')) ed.trigger('click');
    });

    /* ---- bill box: design text, rupees without paise ---- */
    $('.summary-receipt .receipt-row > span:first-child').each(function () { $(this).text($(this).text().replace(/:\s*$/, '')); });
    ['total_payable_display', 'advance_total_text', 'balance_due_display'].forEach(function (id) {
        var el = document.getElementById(id); if (!el) return;
        watch(el, function () { var v = money(el.textContent); var t = v === null ? el.textContent : inr(Math.abs(v)); if (el.textContent !== t) el.textContent = t; });
    });
    /* ---- customer balance: design text ---- */
    $('.status-glass-pill .pill-label').contents().filter(function () { return this.nodeType === 3 && $.trim(this.nodeValue); }).each(function () { this.nodeValue = ' Customer balance'; });
    ['cash_label', 'gold_label', 'silver_label'].forEach(function (id) {
        var el = document.getElementById(id); if (!el) return;
        watch(el, function () { var t = cap(el.textContent); if (el.textContent !== t) el.textContent = t;
            $(el).next('span').toggleClass('ju-jama', /jama/i.test(t)); });
    });
    /* ---- rate label: "Gold rate" (unit stays in the box: /gm) ---- */
    var ml = document.getElementById('main_metal_label');
    watch(ml, function () { var t = cap(String(ml.textContent).replace(/\s*\(.*\)\s*$/, '')); if (ml.textContent !== t) ml.textContent = t; });

    /* ---- header chips: width = value ---- */
    setInterval(function () { $('.ju-acc-h .ju-stat .summary-input').each(function () { this.style.setProperty('width', (String(this.value).length + 0.4) + 'ch', 'important'); }); }, 500);

    /* ---- Add item button text: "Add item" / "Edit item" (the old code writes it in capitals) ---- */
    var ab = document.getElementById('add_item_btn');
    watch(ab, function () { $(ab).contents().filter(function () { return this.nodeType === 3 && /[A-Z]{3}/.test(this.nodeValue); }).each(function () { this.nodeValue = ' ' + cap(this.nodeValue); }); });

    /* ---- customer balance amounts: rupees without paise ---- */
    var cb = document.getElementById('cust_cash_bal');
    watch(cb, function () { var v = money(cb.textContent); if (v === null) return; var t = inr(v); if (cb.textContent !== t) cb.textContent = t; });

    /* ---- bill strip under the header when the bill box is out of view ---- */
    var $hs = $('<div class="ju-hstrip" aria-hidden="true"><span>Total bill<b class="t">₹0</b></span><span>Paid<b class="g p">₹0</b></span><span class="due"><em class="l" style="font-style:normal">Due</em><b class="d">₹0</b></span></div>').insertAfter('.ju-order .ju-head');
    var scroller = document.documentElement.classList.contains('ju-embed') ? document.querySelector('.ju-order') : window;
    function hstrip() {
        var box = document.querySelector('.ju-order .summary-receipt'); if (!box) return;
        var tot = parseFloat(String($('#total_payable').val() || '0').replace(/,/g, '')) || 0;
        var head = document.querySelector('.ju-order .ju-head'), hb = head.getBoundingClientRect(), r = box.getBoundingClientRect();
        var vis = r.top < innerHeight - 60 && r.bottom > hb.bottom;
        var adv = /advance/i.test($('#balance_due_label').text());
        $hs.css('top', (document.documentElement.classList.contains('ju-embed') ? head.offsetHeight : 0) + 'px');
        $hs.find('.t').text($('#total_payable_display').text()); $hs.find('.p').text($('#advance_total_text').text());
        $hs.find('.d').text($('#balance_due_display').text()); $hs.find('.l').text(adv ? 'Advance' : 'Due'); $hs.find('.due').toggleClass('adv', adv);
        $hs.toggleClass('show', tot > 0 && !vis);
    }
    $(scroller).on('scroll', hstrip); $(window).on('resize', hstrip); setInterval(hstrip, 600);
})(jQuery);
</script>

<script>
/* =====================================================================
   NEW ADDITION (pipeline design, v6b): create / edit behaves like the artifact
   - sections start closed; Order details + Item weight open when an item is being edited,
     Payment opens the first time a gross weight is typed (new order)
   - "Item amount + Add item" row shows only after weight / making / labour is typed
   - payment header chips show icon + value (old inputs stay in the form)
   - popup height follows the content (sent to the board)
   Display only: the old fields, buttons and save stay the same.
   ===================================================================== */
(function ($) {
    if (!$('.ju-order').length) return;
    var isEdit = $('#is_edit_mode').val() === '1';
    var num = function (sel) { return parseFloat(String($(sel).val() || '').replace(/,/g, '')) || 0; };
    function setOpen(id, on) { var a = $(id); a.toggleClass('open', on); a.children('.ju-acc-h').attr('aria-expanded', on); }
    ['#ju_acc_details', '#ju_acc_weight', '#ju_acc_pay'].forEach(function (id) { setOpen(id, false); });

    var W = ['#item_gross_wt', '#item_less_wt', '#item_wastage_per', '#item_wastage_percent', '#item_making', '#item_labor'];
    var payAuto = isEdit, wasEditing = false;
    function rowState() {
        var any = W.some(function (s) { return num(s) > 0; });
        var editing = $('#add_item_btn').hasClass('btn-success') || $('#item_list_body tr.bg-soft-indigo').length > 0;
        $('.ju-order .ju-addrow').toggleClass('ju-show', any || editing);
        if (editing && !wasEditing) {   // old "edit item" loaded the item into the form
            setOpen('#ju_acc_details', true); setOpen('#ju_acc_weight', true);
            var el = document.getElementById('ju_acc_details'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        wasEditing = editing;
        if (!payAuto && num('#item_gross_wt') > 0) { payAuto = true; setOpen('#ju_acc_pay', true); }
    }
    $(document).on('input change', W.join(','), function () { setTimeout(rowState, 0); });
    setInterval(rowState, 400); rowState();
    // anything typed in a closed section's fields (e.g. product picked) keeps the old behaviour of opening it via focus

    /* payment header chips: icon + value */
    var CH = [['#total_items_display', 'fa-layer-group', function (v) { return (parseInt(v) || 0) + ' pcs'; }, ''],
              ['#total_fine_display', 'fa-check-circle', function (v) { return (parseFloat(String(v).replace(/,/g, '')) || 0).toFixed(3) + ' gm'; }, 'fine'],
              ['#total_making_charges', 'fa-hammer', function (v) { return '₹' + Math.round(parseFloat(String(v).replace(/,/g, '')) || 0).toLocaleString('en-IN'); }, '']];
    CH.forEach(function (c) {
        var $in = $(c[0]); if (!$in.length) return;
        var $v = $('<span class="ju-statv ' + c[3] + '"><i class="fa ' + c[1] + '"></i><em style="font-style:normal"></em></span>').appendTo($in.closest('.ju-stat'));
        var f = function () { $v.find('em').text(c[2]($in.val())); };
        f(); setInterval(f, 500);
    });

    /* popup: tell the board how tall the form is */
    if (document.documentElement.classList.contains('ju-embed') && window.parent !== window) {
        var shell = document.querySelector('.ju-order .ju-card-shell'), last = 0;
        var send = function () { var h = Math.ceil(shell.getBoundingClientRect().height); if (Math.abs(h - last) > 2) { last = h; try { window.parent.postMessage({ juOrder: 'h', h: h }, '*'); } catch (x) {} } };
        if (window.ResizeObserver) new ResizeObserver(send).observe(shell);
        setInterval(send, 500); send();
    }
})(jQuery);
</script>

{{-- ===================== NEW ADDITION (v8): Metal pay panel look (display only) =====================
     The old inputs carry inline "!important" pill styles (radius 20px, icon padding) that CSS can't override,
     so they are reset here once with style.setProperty. No value, id, name or handler is touched. --}}
<script>
(function () {
    function juMpLook() {
        var box = document.getElementById('metal_adv_details_dropdown');
        if (!box) return;
        box.querySelectorAll('.metal-adv-panel .premium-input').forEach(function (el) {
            el.style.setProperty('border-radius', '8px', 'important');
            el.style.setProperty('padding-left', '10px', 'important');
            el.style.setProperty('padding-right', el.matches('[id$="_adv_gross"],[id$="_adv_net"],[id$="_adv_fine"],[id$="_adv_amount"]') ? '30px' : '10px', 'important');
            el.style.setProperty('height', '32px', 'important');
            el.style.setProperty('font-size', '12.5px', 'important');
            el.style.removeProperty('border-left');
            if (el.readOnly) el.style.removeProperty('background');
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', juMpLook); else juMpLook();
})();
</script>
