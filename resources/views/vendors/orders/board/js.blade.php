<script>
/* =====================================================================
   JU Order pipeline board — UI behaviour.
   OLD LOGIC IS NOT CHANGED:
   - "All orders" column is still loaded by receive/js/list_scripts (#data_area).
   - Karigar + Billing columns are loaded from their old routes with the same params.
   - Every card button runs the old action (openAssignModal, purchase form, sale link,
     .btn-view-order, old edit / print / delete links).
   Parts marked NEW ADDITION are pipeline-design features that the old module did not have.
   ===================================================================== */
$(function () {
    var $root = $('.ju-board-page');
    if (!$root.length) return;
    var $board = $('#jp-board');
    var STAGES = ['order', 'karigar', 'billing'];
    var STG = { order: 'All orders', karigar: 'Karigar list', billing: 'Billing list', delivered: 'Delivered', pending: 'All orders', partial: 'All orders', in_process: 'Karigar list', completed: 'Billing list' };
    var activeStage = $root.data('active-stage') || 'order';
    var URL = { order: $root.data('url-order'), karigar: $root.data('url-karigar'), billing: $root.data('url-billing'), create: $root.data('url-create'),
        deleted: $root.data('url-deleted') || '', restore: $root.data('url-restore') || '', skip: $root.data('url-skip') || '' };
    var CSRF = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() || '';
    var isMob = function () { return window.innerWidth <= 620; };
    var col = function (st) { return $board.find('.jp-col[data-stage="' + st + '"]'); };
    var inr = function (n) { n = Math.round(+n || 0); return '₹' + Math.abs(n).toLocaleString('en-IN'); };
    var gm = function (n) { return (+n || 0).toFixed(3) + ' g'; };
    var esc = function (s) { return $('<div>').text(s == null ? '' : String(s)).html(); };
    var ic = function (n, c) { return '<span class="jp-ic' + (c ? ' ' + c : '') + '"><svg viewBox="0 0 24 24"><use href="#ji-' + n + '"></use></svg></span>'; };
    var ini = function (s) { return String(s || '').split(/\s+/).filter(Boolean).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase(); };
    function toast(m) { if (typeof toastr !== 'undefined') toastr.info(m); }
    function rowData(tr) { try { return JSON.parse(tr.getAttribute('data-row') || 'null'); } catch (x) { return null; } }

    /* ---------- billing page extras (same as the old billing/index script) ---------- */
    var qs = new URLSearchParams(window.location.search);
    var highlightOrderId = qs.get('highlight_order_id'), incompleteOrder = qs.get('incomplete_order'), itemsLeft = qs.get('items_left');
    if (incompleteOrder || itemsLeft) {
        var c0 = parseInt(itemsLeft) || 1, os0 = incompleteOrder ? (incompleteOrder.startsWith('#') ? incompleteOrder : '#' + incompleteOrder) : '';
        window.toastr && toastr.info(((c0 === 1 ? '1 item' : c0 + ' items') + ' left of order ' + os0).trim(), 'Order Info', { timeOut: 6000, closeButton: true, progressBar: true, positionClass: 'toast-top-right' });
    }
    if (highlightOrderId || incompleteOrder || itemsLeft) window.history.replaceState({}, document.title, window.location.pathname);

    /* =================================================================
       Columns
       ================================================================= */
    function sumHtml(st, d) {
        if (!d) return '';
        if (st === 'order') return '<div class="jp-csum3"><span>Total <strong>' + inr(d.total) + '</strong></span><span class="jp-cp">Paid <strong>' + inr(d.paid) + '</strong></span><span class="jp-cr">Remaining <strong>' + inr(d.rem) + '</strong></span></div>';
        if (st === 'karigar') return '<div class="jp-csum3"><span>Issued <strong>' + gm(d.issued) + '</strong></span><span class="jp-ck">Charges <strong>' + inr(d.charges) + '</strong></span><span>Amount <strong>' + inr(d.amount) + '</strong></span></div>';
        return '<div class="jp-csum3"><span>Labour paid <strong>' + inr(d.labour) + '</strong></span><span>Bill amount <strong>' + inr(d.bill) + '</strong></span><span class="jp-cr">To collect <strong>' + inr(d.collect) + '</strong></span></div>';
    }
    function setSum(st, $tfoot) {
        var $td = $tfoot && $tfoot.find('td.jp-sum'), d = null;
        if ($td && $td.length) { d = {}; $.each($td[0].attributes, function (_, a) { if (a.name.indexOf('data-') === 0) d[a.name.slice(5)] = parseFloat(a.value) || 0; }); }
        col(st).find('.jp-col-sum').html(sumHtml(st, d));
    }
    function setCount(st) {
        var $c = col(st), n = $c.find('tr.jp-row').length, more = $c.find('.jp-paging a').length > 0;
        $c.find('.jp-count').text(n + (more ? '+' : ''));
        $('#jp-sdots button').eq(STAGES.indexOf(st)).attr('data-count', n);
        if (!n && !$c.find('tr.jp-empty, tr.jp-state').length) $c.find('tbody').append('<tr class="jp-empty"><td><div class="jp-empty-in">Koi order nahi</div></td></tr>');
    }
    function isTouch() { return isMob() || ('ontouchstart' in window && window.innerWidth < 1024); }
    function prep($c) { $c.find('tr.jp-row .jp-card').each(function () { if (!this.hasAttribute('draggable') && !isTouch()) this.setAttribute('draggable', 'true'); }); }

    var xhr = {};
    function loadCol(st, url) {
        if (st === 'order') return;
        var $c = col(st), $tb = $c.find('tbody');
        if (xhr[st]) xhr[st].abort();
        $tb.html('<tr class="jp-state"><td><div class="jp-loading-in"><i class="fa fa-spinner fa-spin"></i> Loading...</div></td></tr>');
        $c.find('.jp-paging').html('');
        var data = { entries: $('.entries').val(), keyword: $('#keyword').val(), reportrange: $('#reportrange').val() };
        if (st === 'billing') { data.status = 'ready'; data.highlight_order_id = highlightOrderId; }
        xhr[st] = $.ajax({ url: url || URL[st], type: 'GET', data: data,
            success: function (res) {
                if (!(res && res.status === 'success' && res.html !== undefined)) { $tb.html('<tr class="jp-state"><td><div class="jp-empty-in jp-err-in">Data load nahi hua</div></td></tr>'); return; }
                var $tmp = $('<table>').html(res.html);
                $tb.empty().append($tmp.find('tbody').first().children('tr'));
                setSum(st, $tmp.find('tfoot'));
                $c.find('.jp-paging').html(res.paging || '');
                prep($c); setCount(st); searchRes();
                if (st === 'billing' && highlightOrderId) {
                    var el = document.getElementById('order-row-' + highlightOrderId);
                    if (el) { go(2); setTimeout(function () { $(el).find('.jp-card')[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 350); }
                }
            },
            error: function (x, s) { if (s !== 'abort') $tb.html('<tr class="jp-state"><td><div class="jp-empty-in jp-err-in">Data load nahi hua, page refresh karo</div></td></tr>'); }
        });
    }
    function loadOthers() { loadCol('karigar'); loadCol('billing'); }
    function reloadAll() { if (typeof origChange === 'function') origChange(); loadOthers(); }

    var origChange = window.changeEntries, tOthers = null, tDv = null;
    window.changeEntries = function () {
        if (typeof origChange === 'function') origChange.apply(this, arguments);
        clearTimeout(tOthers); tOthers = setTimeout(loadOthers, 320);
        if ($('#jp-dov').hasClass('jp-open')) { clearTimeout(tDv); tDv = setTimeout(function () { dvLoad(); }, 320); }
    };
    $(document).on('change', '.entries', function () { clearTimeout(tOthers); tOthers = setTimeout(loadOthers, 50); });

    /* paging inside Karigar / Billing / popup tables stays there (old handler only knows All orders) */
    $root.on('click', '.jp-paging a', function (e) {
        var $p = $(this).closest('.jp-paging');
        if ($p.is('#paging_area')) return;
        e.preventDefault(); e.stopPropagation();
        var href = $(this).attr('href'); if (!href || href === '#' || href.indexOf('javascript') === 0) return;
        if ($p.hasClass('jp-dv-paging')) dvLoad(href); else loadCol($(this).closest('.jp-col').data('stage'), href);
    });

    var ot = document.getElementById('order_table');
    if (ot) new MutationObserver(function () {
        var $c = col('order'), $tf = $c.find('tfoot');
        if ($tf.length) setSum('order', $tf);
        $c.find('tbody#data_area > tr:not(.jp-row):not(.jp-empty)').addClass('jp-state');
        prep($c); setCount('order'); searchRes();
    }).observe(ot, { childList: true });

    reloadAll();

    /* =================================================================
       Menus, card toggle, card click = View detail
       ================================================================= */
    function closeMenus() { $root.find('.jp-menu.jp-open').removeClass('jp-open'); }
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.jp-menu, .jp-dots, .jp-ad-dots, .jp-itact, .jp-dr-btn2').length) closeMenus();
        if (!$(e.target).closest('.jp-osrch, .jp-os-fab').length) { $('#jp-bhead').removeClass('jp-srch-open'); $('#jp-os-res').prop('hidden', true); }
    });
    $root.on('click', '.jp-dots', function (e) {
        e.stopPropagation();
        var $m = $(this).closest('.jp-card').find('> .jp-menu'), open = $m.hasClass('jp-open');
        closeMenus(); if (!open) $m.addClass('jp-open');
    });
    $root.on('click', '.jp-menu a, .jp-menu button', function () { setTimeout(closeMenus, 0); });
    $root.on('click', '.jp-ad-dots', function (e) { e.stopPropagation(); var $m = $(this).siblings('.jp-ad-menu'), o = $m.hasClass('jp-open'); closeMenus(); if (!o) $m.addClass('jp-open'); });
    $root.on('click', '.jp-adm-show, .jp-adm-show *', function (e) { e.stopPropagation(); });
    $root.on('click', '.jp-dtg', function (e) { e.stopPropagation(); $(this).closest('.jp-card').toggleClass('jp-open'); });
    var noClickUntil = 0;
    $root.on('click', '.jp-card', function (e) {
        if (Date.now() < noClickUntil) return;
        if ($(e.target).closest('a,button,input,label,select,form,.jp-menu').length) return;
        var $v = $(this).find('.btn-view-order').first(); if ($v.length) $v.trigger('click');
    });
    function viewDetail(id) { $('<button type="button" class="btn-view-order" style="display:none">').attr('data-id', id).appendTo($root).trigger('click').remove(); }

    /* =================================================================
       Popups: back button closes the top one (phone)
       ================================================================= */
    var stack = [];
    function pushPop(name, closeFn) { stack.push({ name: name, close: closeFn }); try { history.pushState({ jp: name }, ''); } catch (x) {} }
    function popClosed(name) { for (var i = stack.length - 1; i >= 0; i--) if (stack[i].name === name) { stack.splice(i, 1); try { if (history.state && history.state.jp === name) history.back(); } catch (x) {} return; } }
    window.addEventListener('popstate', function () { var t = stack.pop(); if (t) t.close(true); });

    /* =================================================================
       NEW ADDITION: Create / Edit order popup (old page inside an iframe)
       ================================================================= */
    var $ovO = $('#jp-ov-order'), frame = document.getElementById('jp-order-frame'), frameLoads = 0, frameDraft = null;
    var POPUP = String($root.data('popup')) !== '0';           // board.blade.php: $jpPopup switch
    var frameHref = null, frameOk = false, frameTimer = null;    // frameHref = normal page url (fallback)
    function withEmbed(href, draftId) { var u = href + (href.indexOf('?') > -1 ? '&' : '?') + 'embed=1'; if (draftId) u += '&draft=' + encodeURIComponent(draftId); return u; }
    function plainUrl(href, draftId) { return draftId ? href + (href.indexOf('?') > -1 ? '&' : '?') + 'draft=' + encodeURIComponent(draftId) : href; }
    // Fallback: popup could not show the form -> open the same page normally (old behaviour)
    function openPlain(href, draftId) { if (window.__previewIframeUrl) href = window.__previewIframeUrl(href); window.location.href = plainUrl(href, draftId); }
    window.juOpenOrderPopup = function (href, draftId) {
        if (!href) return;
        if (!POPUP || !frame) return openPlain(href, draftId);
        try {
            frameHref = href;
            if (window.__previewIframeUrl) href = window.__previewIframeUrl(href);
            frameLoads = 0; frameDraft = draftId || null; frameOk = false;
            $ovO.find('.jp-ofr').removeClass('jp-ready jp-print');
            frame.src = withEmbed(href, draftId);
            $ovO.addClass('jp-open'); $('body').css('overflow', 'hidden');
            pushPop('order', function () { closeOrderPopup(true); });
            // form page says "ready" (postMessage) when it loads; no answer in 15s -> open it normally
            clearTimeout(frameTimer);
            frameTimer = setTimeout(function () { if (!frameOk && $ovO.hasClass('jp-open')) frameFail(); }, 15000);
        } catch (x) { openPlain(frameHref || href, draftId); }
    };
    function frameReady() { frameOk = true; clearTimeout(frameTimer); $ovO.find('.jp-ofr').addClass('jp-ready'); }
    function frameFail() {
        var h = frameHref, d = frameDraft;
        clearTimeout(frameTimer); closeOrderPopup(false);
        if (h) openPlain(h, d);
    }
    function closeOrderPopup(fromHistory) {
        clearTimeout(frameTimer);
        if (!$ovO.hasClass('jp-open')) return;
        $ovO.removeClass('jp-open'); $('body').css('overflow', '');
        setTimeout(function () { frame.src = 'about:blank'; }, 200);
        if (!fromHistory) popClosed('order');
    }
    window.juCloseOrderPopup = function () { closeOrderPopup(false); drUI(); };
    if (frame) frame.addEventListener('load', function () {
        if (!$ovO.hasClass('jp-open') || frame.src === 'about:blank') return;
        frameLoads++;
        var href = null; try { href = frame.contentWindow.location.href; } catch (x) {}
        if (frameLoads === 1) {
            if (frameOk) return frameReady();
            // same-origin page loaded but it is not the order form (error page / login page) -> normal page
            var isForm = null; try { isForm = !!frame.contentDocument.querySelector('.ju-order'); } catch (x) {}
            if (isForm === false) return frameFail();
            // cross-origin / blocked frame: give the "ready" message a moment, else normal page
            setTimeout(function () { if (!frameOk && $ovO.hasClass('jp-open')) frameFail(); }, 2500);
            return;
        }
        if (href && href.indexOf('embed=1') > -1) { $ovO.find('.jp-ofr').addClass('jp-ready'); return; }
        // the old save flow navigated away (to the list or to the receipt): order saved
        if (frameDraft) drDel(frameDraft);
        reloadAll();
        if (href && /print/i.test(href)) $ovO.find('.jp-ofr').addClass('jp-ready jp-print');   // "Print Receipt" chosen: receipt stays in the popup
        else { closeOrderPopup(false); toast('Order save ho gaya'); }                          // "Go to Order List" chosen: back to the board
    });
    $root.on('click', 'a.jp-open-order', function (e) {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;   // new tab: normal link
        var href = this.getAttribute('href');
        if (!href || href === '#') return;
        e.preventDefault();
        try { closeMenus(); } catch (x) {}
        if ($(this).closest('#orderDetailModal').length) { $('#orderDetailModal').modal('hide'); setTimeout(function () { juOpenOrderPopup(href); }, 250); }
        else juOpenOrderPopup(href);
    });
    $ovO.on('click', function (e) { if (e.target === this) askFrameClose(); });
    function askFrameClose() { try { if (frame.contentWindow.juEmbedClose) return frame.contentWindow.juEmbedClose(); } catch (x) {} closeOrderPopup(false); }
    window.addEventListener('message', function (e) { if (e.data && e.data.juOrder === 'ready' && e.source === frame.contentWindow) frameReady(); if (e.data && e.data.juOrder === 'close') { closeOrderPopup(false); drUI(); } if (e.data && e.data.juOrder === 'drafts') drUI(); });

    /* =================================================================
       NEW ADDITION: Drafts (browser localStorage, written by the create page)
       ================================================================= */
    var DK = 'juOrderDrafts';
    function drGet() { try { return JSON.parse(localStorage.getItem(DK) || '[]') || []; } catch (x) { return []; } }
    function drSet(L) { try { localStorage.setItem(DK, JSON.stringify(L)); } catch (x) {} }
    function drDel(id) { drSet(drGet().filter(function (d) { return d.id !== id; })); drUI(); }
    function ago(t) { var m = Math.round((Date.now() - t) / 60000); return m < 1 ? 'abhi' : m < 60 ? m + ' min pehle' : m < 1440 ? Math.round(m / 60) + ' ghante pehle' : Math.round(m / 1440) + ' din pehle'; }
    function drUI() {
        var L = drGet(); $('#jp-dr-wrap').prop('hidden', !L.length); $('#jp-dr-c').text(L.length);
        $('#jp-dr-menu').html('<div class="jp-adm-h">' + ic('file') + 'Saved drafts</div>' + (L.length ? L.map(function (d) {
            return '<div class="jp-dr-it" data-id="' + esc(d.id) + '"><span class="jp-dr-tx"><b>' + esc(d.name || 'Bina naam') + '</b><small>' + (d.n || 0) + ' item · ' + ago(d.at) + '</small></span><button type="button" class="jp-dr-del" aria-label="Delete draft">✕</button></div>';
        }).join('') : '<div class="jp-os-empty">Koi draft nahi</div>'));
    }
    drUI(); window.addEventListener('storage', function (e) { if (e.key === DK) drUI(); });
    $root.on('click', '.jp-dr-btn2', function (e) { e.stopPropagation(); var $m = $('#jp-dr-menu'), o = $m.hasClass('jp-open'); closeMenus(); if (!o) { drUI(); $m.addClass('jp-open'); } });
    $root.on('click', '.jp-dr-it', function (e) { if ($(e.target).closest('.jp-dr-del').length) return; closeMenus(); juOpenOrderPopup(URL.create, $(this).data('id')); });
    $root.on('click', '.jp-dr-del', function (e) { e.stopPropagation(); drDel($(this).closest('.jp-dr-it').data('id')); if (!drGet().length) closeMenus(); });

    /* =================================================================
       NEW ADDITION: Change status popup (forward moves use the old actions)
       ================================================================= */
    function cardsOf(id) { return $board.find('tr.jp-row[data-id="' + id + '"] .jp-card'); }
    function stageOf(id) {
        var st = 'order';
        $board.find('tr.jp-row[data-id="' + id + '"]').each(function () { var s = $(this).closest('.jp-col').data('stage'); if (STAGES.indexOf(s) > STAGES.indexOf(st)) st = s; });
        return st;
    }
    function actionFor(id, to) {
        var $c = cardsOf(id);
        if (to === 'karigar') { var $a = $c.find('button[onclick^="openAssignModal"]').first(); return $a.length ? function () { $a.trigger('click'); } : null; }
        if (to === 'billing') { var $f = $c.find('form.jp-act-form').first(); return $f.length ? function () { $f.trigger('submit'); } : null; }
        if (to === 'delivered') { var $s = $c.find('a.jp-act[href*="sale"]').first(); return $s.length ? function () { window.location.href = $s.attr('href'); } : null; }
        return null;
    }
    var $ovCs = $('#jp-ov-cs');
    function openCs(id) {
        var tr = $board.find('tr.jp-row[data-id="' + id + '"]')[0]; if (!tr) return;
        var d = rowData(tr) || {}, cur = stageOf(id);
        var opts = [['order', 'inbox', 'All orders', 'Naya / pending order'], ['karigar', 'tool', 'Karigar list', 'Karigar ko assign'], ['billing', 'box', 'Billing list', 'Karigar se aa gaya, bill banana hai'], ['delivered', 'checkc', 'Sell & deliver', 'Customer ko de diya']];
        var html = '<div class="jp-modal-h"><h3>' + ic('refresh') + 'Change status</h3><button type="button" class="jp-x" aria-label="Close" data-close-cs>✕</button></div>' +
            '<div class="jp-modal-b"><div class="jp-summary-box"><b>#' + esc(d.u) + '</b> · ' + esc(d.cust) + ' · ' + esc(d.prod) + (d.more ? ' +' + d.more + ' more' : '') + '<br>Abhi: <b class="jp-os-st jp-st-' + cur + '">' + STG[cur] + '</b></div><div class="jp-cs-list">' +
            opts.map(function (o) {
                var k = o[0], isCur = k === cur, act = !isCur && actionFor(id, k);
                var sub = isCur ? 'Abhi yahi status hai' : (act ? o[3] : (URL.skip ? o[3] : 'Is order ke liye ye step abhi possible nahi'));
                return '<div class="jp-cs-row"><button type="button" class="jp-cs-opt' + (isCur ? ' jp-cur' : '') + '" data-k="' + k + '"' + (isCur || (!act && !URL.skip) ? ' disabled' : '') + '><span class="jp-cs-ic">' + ic(o[1], k === 'delivered' ? 'jp-t-g' : '') + '</span><span><b>' + o[2] + '</b><small>' + sub + '</small></span></button>' +
                    (isCur ? '' : '<button type="button" class="jp-cs-skip" data-k="' + k + '"' + (URL.skip ? '' : ' disabled') + ' title="Bina popup ke seedha ' + o[2] + ' mein bhejo">' + ic('skip') + 'Skip</button>') + '</div>';
            }).join('') + '</div>' +
            (URL.skip ? '<p class="jp-cs-note">' + ic('skip') + '<b>Skip</b> = beech ke steps (karigar assign / purchase / bill) chhod ke order seedha us stage mein chala jayega.</p>' : '') +
            '</div><div class="jp-modal-f"><button type="button" class="jp-b" data-close-cs>Cancel</button></div>';
        $('#jp-cs-modal').html(html).data('id', id);
        $ovCs.addClass('jp-open'); pushPop('cs', function () { closeCs(true); });
    }
    function closeCs(fromHistory) { if (!$ovCs.hasClass('jp-open')) return; $ovCs.removeClass('jp-open'); if (!fromHistory) popClosed('cs'); }
    $root.on('click', '.jp-cs-open', function (e) { e.stopPropagation(); closeMenus(); openCs($(this).data('id')); });
    $root.on('click', '[data-close-cs]', function () { closeCs(false); });
    $ovCs.on('click', function (e) { if (e.target === this) closeCs(false); });
    $root.on('click', '.jp-cs-opt', function () {
        var id = $('#jp-cs-modal').data('id'), k = $(this).data('k'), act = actionFor(id, k);
        if (act) { closeCs(false); setTimeout(act, 180); }
        else if (URL.skip) skipTo(id, k);
    });
    $root.on('click', '.jp-cs-skip', function () { skipTo($('#jp-cs-modal').data('id'), $(this).data('k')); });
    function skipTo(id, stage) {
        if (!URL.skip) return;
        $.post(URL.skip, { _token: CSRF, order_id: id, stage: stage }, function (res) {
            if (res && res.status === 'success') { closeCs(false); window.toastr && toastr.success(res.message || 'Status badal gaya'); reloadAll(); }
            else window.toastr && toastr.error((res && res.message) || 'Status nahi badla');
        }).fail(function () { window.toastr && toastr.error('Status nahi badla'); });
    }

    /* =================================================================
       NEW ADDITION: search results list (design) — from the cards on the board
       ================================================================= */
    function hl(t, q) { t = esc(t); if (!q) return t; var i = t.toLowerCase().indexOf(q.toLowerCase()); return i < 0 ? t : t.slice(0, i) + '<mark>' + t.slice(i, i + q.length) + '</mark>' + t.slice(i + q.length); }
    function searchRes() {
        var q = ($('#keyword').val() || '').trim(), $r = $('#jp-os-res');
        if (!q || !$('#keyword').is(':focus')) { $r.prop('hidden', true); return; }
        var seen = {}, L = [];
        ['billing', 'karigar', 'order'].forEach(function (st) {
            col(st).find('tr.jp-row').each(function () { var d = rowData(this); if (d && !seen[d.id]) { seen[d.id] = 1; d._st = st; L.push(d); } });
        });
        $r.html(L.length ? L.slice(0, 12).map(function (d) {
            return '<div class="jp-os-it" data-id="' + d.id + '"><span class="jp-os-av jp-st-' + d._st + '">' + esc(ini(d.cust)) + '</span><div class="jp-os-tx"><b>' + hl(d.cust, q) + '</b><small><span class="jp-os-id">#' + hl(d.u, q) + '</span> · ' + hl(d.prod, q) + (d.more ? ' +' + d.more + ' more' : '') + ' · ' + inr(d.total) + '</small>' +
                (d.kar ? '<small>' + ic('tool') + hl(d.kar, q) + '</small>' : '') + '<small><span class="jp-os-st jp-st-' + d._st + '">' + STG[d._st] + '</span>' + (d.urgent ? ' <span class="jp-os-urg">' + ic('bolt', 'jp-t-r') + 'Urgent</span>' : '') + (d.ddate ? ' · ' + ic('truck') + esc(d.ddate) : '') + '</small></div>' +
                '<button type="button" class="jp-os-cs" data-id="' + d.id + '">' + ic('refresh') + 'Change status</button></div>';
        }).join('') : '<div class="jp-os-empty">"' + esc(q) + '" se koi order nahi mila</div>').prop('hidden', false);
    }
    $('#keyword').on('focus input', function () { setTimeout(searchRes, 0); });
    $root.on('click', '.jp-os-it', function (e) { if ($(e.target).closest('.jp-os-cs').length) return; $('#jp-os-res').prop('hidden', true); viewDetail($(this).data('id')); });
    $root.on('click', '.jp-os-cs', function (e) { e.stopPropagation(); $('#jp-os-res').prop('hidden', true); openCs($(this).data('id')); });
    $root.on('click', '.jp-os-fab', function (e) { e.stopPropagation(); var $h = $('#jp-bhead').toggleClass('jp-srch-open'); if ($h.hasClass('jp-srch-open')) setTimeout(function () { $('#keyword').trigger('focus'); }, 50); });

    /* =================================================================
       All orders details popup: Order summary (NEW) · Deleted (NEW) · Delivered
       ================================================================= */
    var $dov = $('#jp-dov'), dvTab = 'summary', dvXhr = [], dvRows = [];
    var TAB = {
        summary: ['Order summary', 'Saare orders ka hisaab: stage, karigar aur metal ke hisaab se'],
        deleted: ['Deleted orders', 'Delete kiye gaye orders, yahan se restore kar sakte ho'],
        delivered: ['Delivered orders', 'Jo orders customer ko sell ho chuke hain']
    };
    function openDov(t) { $dov.addClass('jp-open'); $('body').css('overflow', 'hidden'); pushPop('dov', function () { closeDov(true); }); setTab(t); }
    function closeDov(fromHistory) { if (!$dov.hasClass('jp-open')) return; $dov.removeClass('jp-open'); $('body').css('overflow', ''); $('#jp-dvm').removeClass('jp-open'); if (!fromHistory) popClosed('dov'); }
    function setTab(t) {
        dvTab = t; $dov.find('.jp-tabs button').each(function () { $(this).toggleClass('jp-on', $(this).data('t') === t); });
        $dov.find('.jp-dv-title').text(TAB[t][0]); $dov.find('.jp-dv-sub').text(TAB[t][1]);
        $dov.find('.jp-dv-q').val('').toggle(t !== 'summary'); $dov.find('.jp-dv-showl').toggle(t !== 'summary');
        dvLoad();
    }
    function abortDv() { dvXhr.forEach(function (x) { try { x.abort(); } catch (e) {} }); dvXhr = []; }
    function loadingDv() { $dov.find('.jp-dv-sum').html(''); $dov.find('.jp-dv-th').html(''); $dov.find('.jp-dv-tb').html('<tr><td colspan="12" style="padding:24px;text-align:center;color:var(--muted)"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'); $dov.find('.jp-dv-paging').html(''); }
    function getRows(url, data, done) {
        var x = $.ajax({ url: url, type: 'GET', data: data, success: function (res) {
            var $t = $('<table>').html((res && res.html) || '');
            done($t.find('tr.jp-row').map(function () { return rowData(this); }).get().filter(Boolean), (res && res.paging) || '');
        }, error: function (x, s) { if (s !== 'abort') $dov.find('.jp-dv-tb').html('<tr><td colspan="12" style="padding:24px;text-align:center;color:var(--red)">Data load nahi hua</td></tr>'); } });
        dvXhr.push(x);
    }
    function dvLoad(url) {
        abortDv(); loadingDv();
        var base = { keyword: $('#keyword').val(), reportrange: $('#reportrange').val() };
        if (dvTab === 'summary') {
            var all = null, kar = null, fin = function () { if (all && kar) drawSummary(all, kar); };
            getRows(URL.order, $.extend({}, base, { status: 'all', entries: 500 }), function (r) { all = r; fin(); });
            getRows(URL.karigar, $.extend({}, base, { entries: 500 }), function (r) { kar = r; fin(); });
        } else if (dvTab === 'deleted' && URL.deleted) {
            getRows(url || URL.deleted, $.extend({}, base, { entries: $dov.find('.jp-dv-show').val() }), function (r, p) { dvRows = r; $dov.find('.jp-dv-paging').html(p); drawTable(); });
        } else {
            getRows(url || URL.order, $.extend({}, base, { status: 'delivered', entries: $dov.find('.jp-dv-show').val() }), function (r, p) { dvRows = r; $dov.find('.jp-dv-paging').html(p); drawTable(); });
        }
    }
    function drawSummary(all, kar) {
        var act = all.filter(function (o) { return o.st !== 'delivered'; });
        var urg = act.filter(function (o) { return o.urgent; }).length, soon = act.filter(function (o) { return o.days !== null && o.days !== undefined && o.days <= 3; }).length;
        var gK = 0, sK = 0; kar.forEach(function (k) { if (/silver/i.test(k.metal)) sK += +k.fine || 0; else gK += +k.fine || 0; });
        var T = function (icn, v, l, c) { return '<div class="jp-stat ' + (c || '') + '"><span class="jp-si">' + icn + '</span><div><b>' + v + '</b><span>' + l + '</span></div></div>'; };
        var tot = 0, paid = 0, due = 0; all.forEach(function (o) { tot += o.total; paid += o.paid; due += Math.max(0, o.due); });
        $dov.find('.jp-dv-sum').html('<div class="jp-ds jp-o"><span>Total orders</span><b>' + all.length + '</b></div><div class="jp-ds"><span>Active / Delivered</span><b>' + act.length + ' / ' + (all.length - act.length) + '</b></div><div class="jp-ds"><span>Total value</span><b>' + inr(tot) + '</b></div><div class="jp-ds jp-g"><span>Received</span><b>' + inr(paid) + '</b></div><div class="jp-ds jp-r"><span>Pending due</span><b>' + inr(due) + '</b></div>');
        var grp = { order: [], karigar: [], billing: [], delivered: [] };
        all.forEach(function (o) { var k = o.st === 'delivered' ? 'delivered' : o.st === 'completed' ? 'billing' : o.st === 'in_process' ? 'karigar' : 'order'; grp[k].push(o); });
        var stRows = ['order', 'karigar', 'billing', 'delivered'].map(function (k) {
            var L = grp[k], s = function (f) { return L.reduce(function (a, o) { return a + (+o[f] || 0); }, 0); };
            return '<tr><td><span class="jp-vst jp-' + k + '">' + STG[k] + '</span></td><td>' + L.length + '</td><td>' + s('items') + '</td><td>' + gm(s('gross')) + '</td><td style="color:var(--green)">' + gm(s('fine')) + '</td><td class="jp-amt-k">' + inr(s('total')) + '</td><td class="jp-amt-g">' + inr(s('paid')) + '</td><td class="jp-amt-r">' + inr(L.reduce(function (a, o) { return a + Math.max(0, o.due); }, 0)) + '</td></tr>';
        }).join('');
        var kw = {}; kar.forEach(function (k) { var n = k.kar || '—'; kw[n] = kw[n] || { p: 0, r: 0, f: 0 }; kw[n].p += +k.pend || 0; kw[n].r += +k.recv || 0; kw[n].f += +k.fine || 0; });
        var kRows = Object.keys(kw).map(function (n) { return '<tr><td><b>' + esc(n) + '</b></td><td class="jp-amt-r">' + kw[n].p + '</td><td class="jp-amt-g">' + kw[n].r + '</td><td style="color:var(--orange-dark)">' + gm(kw[n].f) + '</td></tr>'; }).join('') || '<tr><td colspan="4" style="color:var(--muted)">Karigar ke paas koi order nahi</td></tr>';
        var mw = {}; all.forEach(function (o) { var m = /silver/i.test(o.metal) ? 'Silver' : 'Gold'; mw[m] = mw[m] || { n: 0, f: 0, t: 0, d: 0 }; mw[m].n++; mw[m].f += +o.fine || 0; mw[m].t += o.total; mw[m].d += Math.max(0, o.due); });
        var mRows = ['Gold', 'Silver'].filter(function (m) { return mw[m]; }).map(function (m) { return '<tr><td><span class="jp-metal jp-' + m.toLowerCase() + '">' + m + '</span></td><td>' + mw[m].n + '</td><td style="color:var(--green)">' + gm(mw[m].f) + '</td><td class="jp-amt-k">' + inr(mw[m].t) + '</td><td class="jp-amt-r">' + inr(mw[m].d) + '</td></tr>'; }).join('');
        $dov.find('.jp-dv-th').html('');
        $dov.find('.jp-dv-tb').html('<tr><td colspan="8" style="padding:14px;border:0"><div class="jp-osum">' +
            '<div class="jp-osec jp-full"><h5>' + ic('bolt', 'jp-t-r') + 'Live status (abhi ke active orders)</h5><div class="jp-stats jp-ostats">' + T(ic('box'), act.length, 'Active orders') + T(ic('bolt', 'jp-t-r'), urg, 'Urgent orders', urg ? 'jp-red' : '') + T(ic('clock', 'jp-t-r'), soon, '3 din mein delivery') + T(ic('coin'), gm(gK), 'Gold karigar ke paas') + T(ic('award'), gm(sK), 'Silver karigar ke paas') + '</div></div>' +
            '<div class="jp-osec jp-full"><h5>' + ic('clip') + 'Stage-wise summary</h5><table class="jp-dtbl" style="min-width:0"><thead><tr><th>Stage</th><th>Orders</th><th>Items</th><th>Gross</th><th>Fine</th><th>Total</th><th>Advance / Paid</th><th>Balance</th></tr></thead><tbody>' + stRows + '</tbody></table></div>' +
            '<div class="jp-osec"><h5>' + ic('tool') + 'Karigar-wise</h5><table class="jp-dtbl" style="min-width:0"><thead><tr><th>Karigar</th><th>Pending items</th><th>Received</th><th>Fine pending</th></tr></thead><tbody>' + kRows + '</tbody></table></div>' +
            '<div class="jp-osec"><h5>' + ic('coin') + 'Metal-wise</h5><table class="jp-dtbl" style="min-width:0"><thead><tr><th>Metal</th><th>Orders</th><th>Fine</th><th>Total</th><th>Balance</th></tr></thead><tbody>' + mRows + '</tbody></table></div>' +
            '</div></td></tr>');
    }
    function drawTable() {
        var q = ($dov.find('.jp-dv-q').val() || '').trim().toLowerCase(), del = dvTab === 'deleted';
        var L = dvRows.filter(function (o) { return !q || (o.u + ' ' + o.cust + ' ' + o.mob + ' ' + o.prod).toLowerCase().indexOf(q) > -1; });
        var tot = 0, paid = 0, due = 0, gF = 0, sF = 0;
        L.forEach(function (o) { tot += o.total; paid += o.paid; due += Math.max(0, o.due); if (/silver/i.test(o.metal)) sF += o.fine; else gF += o.fine; });
        $dov.find('.jp-dv-sum').html('<div class="jp-ds jp-o"><span>' + (del ? 'Deleted orders' : 'Delivered orders') + '</span><b>' + L.length + '</b></div><div class="jp-ds"><span>' + (del ? 'Total value' : 'Total sale') + '</span><b>' + inr(tot) + '</b></div><div class="jp-ds jp-g"><span>' + (del ? 'Advance liya tha' : 'Received') + '</span><b>' + inr(paid) + '</b></div>' + (del ? '' : '<div class="jp-ds jp-r"><span>Pending due</span><b>' + inr(due) + '</b></div>') + '<div class="jp-ds"><span>Fine ' + (del ? 'weight' : 'sold') + '</span><b><span style="font-size:14px">G ' + gF.toFixed(3) + ' · S ' + sF.toFixed(3) + '</span></b></div>');
        $dov.find('.jp-dv-th').html('<tr><th>S.No</th><th>Order ID</th><th>Delivery</th><th>Customer</th><th>Product</th><th>Karigar</th><th>Fine wt</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th>Action</th></tr>');
        $dov.find('.jp-dv-tb').html(L.length ? L.map(function (o, i) {
            var mc = /silver/i.test(o.metal) ? 'jp-silver' : 'jp-gold', dueC = o.due > 0;
            return '<tr><td>' + (i + 1) + '</td><td><b>#' + esc(o.u) + '</b>' + (o.urgent ? ' <span class="jp-metal jp-urg">' + ic('bolt', 'jp-t-r') + '</span>' : '') + '</td><td>' + esc(o.ddate) + '</td>' +
                '<td class="jp-cs"><b>' + esc(o.cust) + '</b><small>' + esc(o.mob) + '</small></td>' +
                '<td>' + esc(o.prod) + (o.more ? ' +' + o.more : '') + (o.metal ? '<br><span class="jp-metal ' + mc + '">' + esc(o.metal.charAt(0) + o.metal.slice(1).toLowerCase()) + ' ' + esc(o.purity || '') + '</span>' : '') + '</td>' +
                '<td>' + (esc(o.kar) || '—') + '</td><td>' + gm(o.fine) + '</td><td class="jp-amt-k">' + inr(o.total) + '</td><td class="jp-amt-g">' + inr(o.paid) + '</td>' +
                '<td class="' + (dueC ? 'jp-amt-r' : 'jp-amt-g') + '">' + (dueC ? inr(o.due) : '₹0') + '</td><td><span class="jp-pst ' + (del ? 'jp-due' : (dueC ? 'jp-due' : 'jp-paid')) + '">' + (del ? 'Deleted' : (dueC ? 'Due' : 'Paid')) + '</span></td>' +
                '<td class="jp-dv-act"><button type="button" class="jp-itact" aria-label="Order actions" data-i="' + dvRows.indexOf(o) + '">⋮</button></td></tr>';
        }).join('') : '<tr><td colspan="12" style="padding:24px;text-align:center;color:var(--muted)">Koi order nahi mila</td></tr>');
    }
    $root.on('click', '[data-open]', function () { closeMenus(); openDov($(this).data('open')); });
    $root.on('click', '[data-close-dov]', function () { closeDov(false); });
    $dov.on('click', function (e) { if (e.target === this) closeDov(false); });
    $dov.on('click', '.jp-tabs button', function () { setTab($(this).data('t')); });
    $root.on('input', '.jp-dv-q', drawTable);
    $root.on('change', '.jp-dv-show', function () { dvLoad(); });
    $root.on('click', '.jp-itact', function (e) {
        e.stopPropagation();
        var o = dvRows[+$(this).data('i')], $m = $('#jp-dvm'), r = this.getBoundingClientRect(); if (!o) return;
        var h = '<button type="button" class="btn-view-order" data-id="' + o.id + '">' + ic('eye') + 'View detail</button><a href="' + esc(o.print) + '" target="_blank">' + ic('print') + 'Print receipt</a>';
        if (dvTab === 'deleted' && URL.restore) h += '<button type="button" class="jp-rs" data-id="' + o.id + '">' + ic('undo', 'jp-t-g') + 'Restore</button>';
        $m.html(h).addClass('jp-open').css({ left: Math.max(8, r.right - 170) + 'px', top: (r.bottom + 4 + 110 > innerHeight ? r.top - 104 : r.bottom + 4) + 'px' });
    });
    $root.on('click', '#jp-dvm .btn-view-order', function () { $('#jp-dvm').removeClass('jp-open'); });
    $root.on('click', '#jp-dvm .jp-rs', function () {
        var id = $(this).data('id'); $('#jp-dvm').removeClass('jp-open');
        $.post(URL.restore.replace(':id', id), { _token: CSRF }, function (res) {
            if (res && res.status === 'success') { window.toastr && toastr.success(res.message || 'Order restore ho gaya'); dvLoad(); reloadAll(); } else window.toastr && toastr.error((res && res.message) || 'Restore nahi hua');
        }).fail(function () { window.toastr && toastr.error('Restore nahi hua'); });
    });
    $dov.on('scroll', '.jp-dtbl-wrap', function () { $('#jp-dvm').removeClass('jp-open'); });
    $(document).on('keydown', function (e) {
        if (e.key !== 'Escape' || $('.modal.show').length) return;
        if ($ovCs.hasClass('jp-open')) closeCs(false); else if ($ovO.hasClass('jp-open')) askFrameClose(); else if ($dov.hasClass('jp-open')) closeDov(false);
    });

    /* =================================================================
       Drag & drop: move to next stage = the old action of that card
       ================================================================= */
    function moveCard($card, from, to) {
        if (from === to) return;
        var id = $card.closest('tr.jp-row').data('id');
        if ((from === 'order' && to === 'karigar') || (from === 'karigar' && to === 'billing')) {
            var act = actionFor(id, to);
            if (act) return act();
            return toast(to === 'karigar' ? 'Is order ke saare items pehle se karigar ke paas hain' : 'Is karigar ke saare items aa chuke hain');
        }
        if (URL.skip) return openCs(id);       // backend skip exists -> let the user choose in the popup
        toast('Order ek-ek stage aage badhta hai: All orders → Karigar → Billing. Billing se "Sell & deliver" dabao.');
    }
    var drag = null;
    $board.on('dragstart', '.jp-card', function (e) {
        drag = { $c: $(this), from: $(this).closest('.jp-col').data('stage') };
        $(this).addClass('jp-dragging'); $board.addClass('jp-drag-on');
        try { e.originalEvent.dataTransfer.setData('text/plain', 'card'); e.originalEvent.dataTransfer.effectAllowed = 'move'; } catch (x) {}
    });
    $board.on('dragend', '.jp-card', function () { $(this).removeClass('jp-dragging'); $board.removeClass('jp-drag-on'); $board.find('.jp-over').removeClass('jp-over'); stopEdge(); drag = null; });
    $board.on('dragover', '.jp-col', function (e) {
        if (!drag) return; e.preventDefault();
        $board.find('.jp-col.jp-over').not(this).removeClass('jp-over');
        if ($(this).data('stage') !== drag.from) $(this).addClass('jp-over');
        edge(e.originalEvent.clientX);
    });
    $board.on('drop', '.jp-col', function (e) { if (!drag) return; e.preventDefault(); var d = drag, to = $(this).data('stage'); $board.find('.jp-over').removeClass('jp-over'); moveCard(d.$c, d.from, to); });
    var eRaf = null, eV = 0;
    function edge(x) { var r = $board[0].getBoundingClientRect(), z = 90; eV = x < r.left + z ? -Math.ceil((r.left + z - x) / 6) : (x > r.right - z ? Math.ceil((x - (r.right - z)) / 6) : 0); if (eV && !eRaf) { var t = function () { if (!eV) { eRaf = null; return; } $board[0].scrollLeft += eV; eRaf = requestAnimationFrame(t); }; eRaf = requestAnimationFrame(t); } }
    function stopEdge() { eV = 0; if (eRaf) cancelAnimationFrame(eRaf); eRaf = null; }

    /* =================================================================
       Phone: stage buttons, scroll bar, swipe, long-press drag
       ================================================================= */
    function cols() { return $board.children('.jp-col').toArray(); }
    function target(i) { var bd = $board[0], c = cols(); if (!c.length) return 0; var W = bd.clientWidth, max = bd.scrollWidth - W, base = c[i].offsetLeft - c[0].offsetLeft, t = i === 0 ? 0 : (i === c.length - 1 ? max : base - (W - c[i].offsetWidth) / 2); return Math.max(0, Math.min(max, t)); }
    function idxNow() { var bd = $board[0], b = 0, d0 = 1e9; cols().forEach(function (_, i) { var d = Math.abs(bd.scrollLeft - target(i)); if (d < d0) { d0 = d; b = i; } }); return b; }
    function anim(to) { var bd = $board[0]; cancelAnimationFrame(bd._raf); var f = bd.scrollLeft, t0 = performance.now(), D = 460; var s = function (n) { var k = Math.min(1, (n - t0) / D), e = k < .5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2; bd.scrollLeft = f + (to - f) * e; if (k < 1) bd._raf = requestAnimationFrame(s); }; bd._raf = requestAnimationFrame(s); }
    function dots(i) { $('#jp-sdots button').each(function () { $(this).toggleClass('jp-on', +$(this).data('i') === i); }); }
    function go(i) { var n = cols().length; i = Math.max(0, Math.min(n - 1, i)); if (isMob()) $board[0].scrollTo({ left: target(i), behavior: 'smooth' }); dots(i); }
    $('#jp-sdots').on('click', 'button', function () { go(+$(this).data('i')); });
    var bar = document.getElementById('jp-sbar'), th = document.getElementById('jp-sbar-t');
    function barSync() { if (!bar || !isMob()) return; var bd = $board[0], W = bar.clientWidth, max = bd.scrollWidth - bd.clientWidth, tw = Math.max(28, W * bd.clientWidth / Math.max(bd.scrollWidth, 1)); th.style.width = tw + 'px'; th.style.transform = 'translateX(' + ((max > 0 ? bd.scrollLeft / max : 0) * (W - tw)) + 'px)'; }
    var dT = null;
    $board.on('scroll', function () { requestAnimationFrame(barSync); if (isMob()) { clearTimeout(dT); dT = setTimeout(function () { dots(idxNow()); }, 60); } });
    $(window).on('resize', barSync);
    if (bar) {
        var bd0 = null, at = function (cx) { var bd = $board[0], rc = bar.getBoundingClientRect(), tw = th.offsetWidth, max = bd.scrollWidth - bd.clientWidth; return Math.max(0, Math.min(1, (cx - rc.left - tw / 2) / Math.max(rc.width - tw, 1))) * max; };
        bar.addEventListener('pointerdown', function (e) { e.preventDefault(); bd0 = 1; bar.classList.add('jp-act'); $board.css('scroll-snap-type', 'none'); bar.setPointerCapture(e.pointerId); $board[0].scrollLeft = at(e.clientX); });
        bar.addEventListener('pointermove', function (e) { if (bd0) $board[0].scrollLeft = at(e.clientX); });
        var be = function () { if (!bd0) return; bd0 = null; bar.classList.remove('jp-act'); var i = idxNow(); anim(target(i)); dots(i); if (navigator.vibrate) try { navigator.vibrate(10); } catch (x) {} setTimeout(function () { $board.css('scroll-snap-type', ''); }, 480); };
        bar.addEventListener('pointerup', be); bar.addEventListener('pointercancel', be);
    }
    var sw = null;
    $board[0].addEventListener('touchstart', function (e) { if (!isMob() || $(e.target).closest('input,select,textarea,.jp-menu').length) return; var t = e.touches[0]; sw = { x: t.clientX, y: t.clientY, t: Date.now(), skip: false }; }, { passive: true });
    $board[0].addEventListener('touchend', function (e) { if (!sw) return; var s = sw; sw = null; if (s.skip) return; var t = e.changedTouches[0], dx = t.clientX - s.x, dy = t.clientY - s.y; if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.3 && Date.now() - s.t < 800) go(idxNow() + (dx < 0 ? 1 : -1)); }, { passive: true });
    var td = null;
    $board[0].addEventListener('touchstart', function (e) { if (!isMob()) return; var $c = $(e.target).closest('.jp-card'); if (!$c.length || $(e.target).closest('a,button,input,select,label,form,.jp-menu').length) return; var t = e.touches[0]; td = { $c: $c, x: t.clientX, y: t.clientY, on: false, tm: setTimeout(tStart, 380) }; }, { passive: true });
    function tStart() {
        if (!td) return; var c = td.$c[0], r = c.getBoundingClientRect(), g = c.cloneNode(true);
        var w = document.createElement('div'); w.className = 'jp-root jp-tghost'; w.style.width = r.width + 'px'; w.style.left = r.left + 'px'; w.style.top = r.top + 'px'; w.appendChild(g); document.body.appendChild(w);
        $.extend(td, { on: true, g: w, dx: td.x - r.left, dy: td.y - r.top, last: 0, from: td.$c.closest('.jp-col').data('stage'), idx: idxNow(), lx: td.x });
        td.$c.addClass('jp-dragging'); $board.addClass('jp-drag-on').css({ 'scroll-snap-type': 'none', 'overflow-x': 'hidden' }); document.body.classList.add('jp-tdrag');
        if (navigator.vibrate) try { navigator.vibrate(30); } catch (x) {}
        if (sw) sw.skip = true; td.iv = setInterval(tEdge, 120);
    }
    document.addEventListener('touchmove', function (e) {
        if (!td) return; var t = e.touches[0];
        if (!td.on) { if (Math.hypot(t.clientX - td.x, t.clientY - td.y) > 12) { clearTimeout(td.tm); td = null; } return; }
        e.preventDefault(); td.g.style.left = (t.clientX - td.dx) + 'px'; td.g.style.top = (t.clientY - td.dy) + 'px';
        td.g.style.display = 'none'; var u = document.elementFromPoint(t.clientX, t.clientY); td.g.style.display = '';
        var c = u && u.closest('.jp-col'); $board.find('.jp-col.jp-over').each(function () { if (this !== c) $(this).removeClass('jp-over'); });
        if (c && $(c).data('stage') !== td.from) $(c).addClass('jp-over'); td.lx = t.clientX; tEdge();
    }, { passive: false });
    function tEdge() { if (!td || !td.on) return; var now = Date.now(), ed = Math.max(64, innerWidth * .18); if (now - td.last < 650) return; var n = cols().length, i = td.idx, to = null; if (td.lx > innerWidth - ed && i < n - 1) to = i + 1; else if (td.lx < ed && i > 0) to = i - 1; if (to === null) return; td.last = now; td.idx = to; anim(target(to)); dots(to); if (navigator.vibrate) try { navigator.vibrate(12); } catch (x) {} }
    function tEnd(e) {
        if (!td) return; clearTimeout(td.tm); clearInterval(td.iv); var d = td; td = null; if (!d.on) return;
        noClickUntil = Date.now() + 600; document.body.classList.remove('jp-tdrag'); setTimeout(function () { $board.css({ 'overflow-x': '', 'scroll-snap-type': '' }); }, 360);
        d.g.remove(); d.$c.removeClass('jp-dragging'); $board.removeClass('jp-drag-on'); $board.find('.jp-over').removeClass('jp-over');
        var t = e.changedTouches && e.changedTouches[0]; if (!t) return; var u = document.elementFromPoint(t.clientX, t.clientY), c = u && u.closest('.jp-col');
        if (c) moveCard(d.$c, d.from, $(c).data('stage'));
    }
    document.addEventListener('touchend', tEnd); document.addEventListener('touchcancel', tEnd);
    document.addEventListener('contextmenu', function (e) { if (td && td.on) e.preventDefault(); });

    /* board fills the screen below the JU header (columns scroll inside, like the design) */
    function fitBoard() { var w = $root.find('.jp-board-wrap')[0]; if (!w) return; var top = w.getBoundingClientRect().top + window.scrollY; w.style.height = Math.max(480, window.innerHeight - top - 12) + 'px'; }
    fitBoard(); $(window).on('resize orientationchange', fitBoard);

    /* open on this page's stage */
    setTimeout(function () { var i = Math.max(0, STAGES.indexOf(activeStage)); if (isMob() && i) $board[0].scrollLeft = target(i); dots(i); barSync(); }, 60);
});
</script>
