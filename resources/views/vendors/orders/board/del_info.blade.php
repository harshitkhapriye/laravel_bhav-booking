{{-- =====================================================================
     NEW ADDITION (pipeline design): delete impact data for one order.
     Used inside the card ⋮ "Delete" link:
        <a class="jp-del btn-delete-order" ... @include('vendors.orders.board.del_info', ['jpDelOrder' => $order])>
     It only prints a data-jpdel='{...}' attribute. board/js.blade.php reads it and shows the
     "Delete order?" popup (what goes back where) BEFORE the old M-PIN delete popup (#confirmDeleteModal).
     If nothing was given / taken on the order, the popup is skipped and the old M-PIN popup opens directly.

     Values follow ReceiveController@destroy (no backend change):
       - customer cash / online / metal advance  -> OrderUdharService::convertOrderAdvancesToJama (customer udhaar jama)
       - karigar issues of items still with karigar -> issue reversed (metal / cash given to karigar comes back)
       - items received from karigar (purchase done) -> InventoryStock unlinked, item stays in stock
     Read-only: nothing here changes data.
     ===================================================================== --}}
@php
    $jdO = $jpDelOrder;
    $jdItems = $jdO->items ?? collect();
    $jdWithIds = $jdItems->where('status', 'in_process')->pluck('id')->all();
    $jdKar = [];
    foreach (($jdO->karigarIssues ?? collect()) as $jdIs) {
        $jdIds = is_array($jdIs->order_item_id) ? $jdIs->order_item_id : [];
        $jdMine = $jdIds ? array_values(array_intersect($jdIds, $jdWithIds)) : $jdWithIds;
        if (!$jdMine) continue;
        // an issue can also cover items already received -> count only the share of items still with karigar (per metal)
        $jdF = fn ($ids, $m = null) => (float) $jdItems->whereIn('id', $ids)->filter(fn ($it) => !$m || strtolower($it->metal_type ?? '') === $m)->sum('fine_wt');
        $jdPart = fn ($m = null) => $jdIds && $jdF($jdIds, $m) > 0 ? min(1, $jdF($jdMine, $m) / $jdF($jdIds, $m)) : ($jdIds ? 0 : 1);
        $jdShare = $jdPart();
        $jdK = $jdIs->karigar_id ?? 0;
        if (!isset($jdKar[$jdK])) $jdKar[$jdK] = ['name' => $jdIs->karigar->name ?? 'Karigar', 'ids' => [], 'gold' => 0, 'silver' => 0, 'cash' => 0];
        $jdKar[$jdK]['ids'] = array_values(array_unique(array_merge($jdKar[$jdK]['ids'], $jdMine)));
        $jdKar[$jdK]['gold'] += (float) ($jdIs->fine_issued_gm ?? 0) * $jdPart('gold');
        $jdKar[$jdK]['silver'] += (float) ($jdIs->fine_issued_silver ?? 0) * $jdPart('silver');
        $jdKar[$jdK]['cash'] += ((float) ($jdIs->cash_issued ?? 0) + (float) ($jdIs->online_issued ?? 0)) * $jdShare;
    }
    $jdRcv = $jdItems->whereIn('status', ['received', 'completed']);
    $jdGold = is_callable([$jdO, 'getGoldAdvWeight']) ? (float) $jdO->getGoldAdvWeight() : (float) ($jdO->gold_adv_wt ?? 0);
    $jdSilver = is_callable([$jdO, 'getSilverAdvWeight']) ? (float) $jdO->getSilverAdvWeight() : (float) ($jdO->silver_adv_wt ?? 0);
    $jdData = [
        'u' => $jdO->order_unique ?? '',
        'cust' => $jdO->custo_name ?: ($jdO->party->custo_full_name ?? ($jdO->party->name ?? '')),
        'cash' => round((float) ($jdO->cash_adv ?? 0), 2),
        'online' => round((float) ($jdO->online_adv ?? 0), 2),
        'bank' => $jdO->bank->name ?? '',
        'gold' => round($jdGold, 3),
        'silver' => round($jdSilver, 3),
        'kar' => array_values(array_map(fn ($k) => ['name' => $k['name'], 'n' => count($k['ids']), 'gold' => round($k['gold'], 3), 'silver' => round($k['silver'], 3), 'cash' => round($k['cash'], 2)], $jdKar)),
        'rcv' => $jdRcv->count(),
        'rcvWt' => round((float) $jdRcv->sum(fn ($it) => (float) ($it->inventoryStock->gross ?? $it->gross_wt ?? 0)), 3),
    ];
@endphp
data-jpdel='@json($jdData)'
