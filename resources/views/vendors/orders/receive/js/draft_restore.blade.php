{{-- NEW ADDITION (pipeline design): restore a saved draft into the old form fields BEFORE the old script starts,
     so the old code reads them like normal values (items come back through #items_json, same as edit mode). --}}
<script>
(function () {
    var id = new URLSearchParams(location.search).get('draft');
    if (!id || document.getElementById('is_edit_mode').value === '1') return;
    var L = []; try { L = JSON.parse(localStorage.getItem('juOrderDrafts') || '[]') || []; } catch (x) {}
    var d = L.filter(function (x) { return x.id === id; })[0]; if (!d || !d.fields) return;
    var form = document.getElementById('createOrderForm');
    Object.keys(d.fields).forEach(function (n) {
        var el = form.querySelector('[name="' + n + '"]'); if (!el || el.type === 'file') return;
        el.value = d.fields[n];
    });
    window.__juDraftApplied = true;
})();
</script>
