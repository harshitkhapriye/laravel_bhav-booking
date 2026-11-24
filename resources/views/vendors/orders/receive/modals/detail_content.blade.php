{{--
    Order detail popup content (loaded by list_scripts into #order_detail_area).
    New UI: same data as before, shown in the pipeline "details" design.
--}}
@php
    $dName = $order->customer->custo_full_name ?? $order->custo_name ?? 'Walk-in Customer';
    $dPhone = $order->customer->custo_fone ?? $order->custo_phone ?? 'N/A';
    $dIni = collect(preg_split('/\s+/', trim($dName)))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $dStatus = $order->status == 2 ? 'Completed' : ($order->status == 3 ? 'Delivered' : 'In process');
    $dStCls = $order->status == 2 ? 'billing' : ($order->status == 3 ? 'delivered' : 'karigar');
    $dItems = $order->items;
    $dGross = $dItems->sum(fn($i) => (float) $i->gross_wt * ($i->qty ?: 1));
    $dFine = $dItems->sum(fn($i) => (float) $i->fine_wt * ($i->qty ?: 1));
    $dAdvCash = (float) $order->cash_adv + (float) $order->online_adv;
    $goldAdvWt = $order->getGoldAdvWeight();
    $silverAdvWt = $order->getSilverAdvWeight();
    $goldAdvCat = $order->gold_adv_category ?: ($order->metal_adv_type == 'gold' ? $order->metal_adv_category : 'kacha');
    $silverAdvCat = $order->silver_adv_category ?: ($order->metal_adv_type == 'silver' ? $order->metal_adv_category : 'kacha');
    $metalAdvCat = $order->metal_adv_category ?: 'kacha';
    $dDays = $order->delivery_date ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($order->delivery_date)->startOfDay(), false) : null;
    $dBal = (float) $order->balance_due;
@endphp
<div class="jp-modal jp-vw">
    <div class="jp-vh">
        @if($order->design_image)
            <img class="jp-vimg" src="{{ asset($order->design_image) }}" alt="Design" onclick="window.open(this.src)">
        @else
            <span class="jp-av">{{ $dIni }}</span>
        @endif
        <div style="flex:1;min-width:0">
            <div class="jp-vnm"><h3>{{ $dName }}</h3><small><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-phone"></use></svg></span>{{ $dPhone }}</small></div>
            <div class="jp-vtags">
                <span class="jp-sno">#{{ $order->order_unique }}</span>
                <span class="jp-vst jp-{{ $dStCls }}">{{ $dStatus }}</span>
                @if($order->is_urgent)<span class="jp-metal jp-urg jp-sm"><span class="jp-ic jp-t-r"><svg viewBox="0 0 24 24"><use href="#ji-bolt"></use></svg></span>Urgent</span>@endif
                @if($order->status != 3 && $dDays !== null)
                    @if($dDays < 0)
                        <span class="jp-due-c jp-late">{{ abs($dDays) }} din overdue</span>
                    @elseif($dDays <= 3)
                        <span class="jp-due-c jp-soon">{{ $dDays == 0 ? 'Aaj delivery' : $dDays . ' din baaki' }}</span>
                    @endif
                @endif
            </div>
            <div class="jp-vdates">
                <span><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-inbox"></use></svg></span>Order <b>{{ date('d M Y', strtotime($order->order_date)) }}</b></span>
                <span><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-truck"></use></svg></span>Delivery <b>{{ $order->delivery_date ? date('d M Y', strtotime($order->delivery_date)) : 'Not set' }}</b></span>
            </div>
        </div>
        <button type="button" class="jp-x" data-dismiss="modal" aria-label="Close">✕</button>
    </div>

    <div class="jp-vbody">
        <div class="jp-vamt">
            <div class="jp-va jp-k"><span>Total amount</span><b>₹{{ number_format($order->total_payable, 0) }}</b></div>
            <div class="jp-va jp-g"><span>Advance</span><b>{{ $dAdvCash > 0 ? '₹' . number_format($dAdvCash, 0) : '—' }}</b></div>
            <div class="jp-va {{ $dBal > 0 ? 'jp-r' : 'jp-g' }}"><span>{{ $dBal < 0 ? 'Advance extra' : 'Balance' }}</span><b>{{ $dBal != 0 ? '₹' . number_format(abs($dBal), 0) : '—' }}</b></div>
        </div>

        {{-- Design mein nahi hai: Customer balance (comment out)
        @if($udhari)
            <div class="jp-vsec">
                <h5><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-wallet"></use></svg></span>Customer balance</h5>
                <div class="jp-vgrid">
                    <div class="jp-vf {{ $udhari->custo_amount_status == 1 ? 'jp-r' : 'jp-g' }}"><span>Total udhari</span><b>₹{{ number_format($udhari->custo_amount, 2) }} {{ $udhari->custo_amount_status == 1 ? 'Dr' : 'Cr' }}</b></div>
                    @if($udhari->custo_gold > 0)
                        <div class="jp-vf {{ $udhari->custo_gold_status == 1 ? 'jp-r' : 'jp-g' }}"><span>Gold</span><b>{{ number_format($udhari->custo_gold, 3) }} g {{ $udhari->custo_gold_status == 1 ? 'Dr' : 'Cr' }}</b></div>
                    @endif
                </div>
            </div>
        @endif
        --}}

        <div class="jp-vsec">
            <h5><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-gem"></use></svg></span>Product &amp; weight</h5>
            <div class="jp-vgrid">
                @php $dFirst = $dItems->first(); @endphp
                <div class="jp-vf"><span>Product</span><b>{{ $dFirst->product_name ?? '—' }}@if($dItems->count() > 1) +{{ $dItems->count() - 1 }} more @endif</b></div>
                <div class="jp-vf"><span>Qty</span><b>{{ $dItems->sum('qty') }}</b></div>
                <div class="jp-vf"><span>Metal</span><b>{{ $dFirst ? ucfirst(strtolower($dFirst->metal_type)) . ' ' . $dFirst->purity : '—' }}</b></div>
                <div class="jp-vf jp-o"><span>Gross wt</span><b>{{ number_format($dGross, 3) }} g</b></div>
                <div class="jp-vf jp-g"><span>Fine wt</span><b>{{ number_format($dFine, 3) }} g</b></div>
                <div class="jp-vf"><span>Total items</span><b>{{ $dItems->count() }}</b></div>
                {{-- Design mein nahi hai: Rate (comment out)
                <div class="jp-vf"><span>Rate</span><b>₹{{ number_format($order->metal_rate, 0) }} · {{ $order->rate_type == 'fixed' ? 'Fixed' : 'Market' }}</b></div>
                --}}
            </div>
        </div>

        <div class="jp-vsec">
            <h5><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-box"></use></svg></span>Items ({{ $dItems->count() }})</h5>
            <div class="jp-vitems">
                @foreach($order->items as $item)
                    <div class="jp-vitem">
                        <span class="jp-in"><b>{{ $item->product_name }}</b><small>Qty {{ $item->qty }} · {{ ucfirst(strtolower($item->metal_type)) }} {{ $item->touch }}% · {{ strtoupper($item->jewellery_type) }}@if($item->size) · Size {{ $item->size }}@endif</small></span>
                        <span class="jp-iw" style="text-align:right;font-weight:700">{{ number_format($item->gross_wt, 3) }} g<small>Net {{ number_format($item->net_wt, 3) }} · Fine {{ number_format($item->fine_wt, 3) }} g</small></span>
                        <b class="jp-amt-k">₹{{ number_format($item->making_charge, 2) }}<small style="display:block;font-size:10.5px;color:var(--muted);font-weight:600">{{ $item->making_type == 'fixed' ? 'Making fixed' : ($item->making_type == 'per_piece' ? 'Making /pc' : 'Making /g') }}@if($item->labor_charge > 0) · Labour ₹{{ number_format($item->labor_charge, 2) }}@endif</small></b>
                        @if($item->status == 'in_process')
                            <span class="jp-pc jp-p">With karigar</span>
                        @elseif($item->status == 'completed' || $item->status == 'received')
                            <span class="jp-pc jp-g">Ready</span>
                        @elseif($item->status == 'delivered')
                            <span class="jp-pc jp-g">Delivered</span>
                        @else
                            <span class="jp-pc jp-w">Pending</span>
                        @endif
                    </div>
                @endforeach
            </div>
            @if($order->remarks)
                <div class="jp-vrem"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-msg"></use></svg></span>{{ $order->remarks }}</div>
            @endif
        </div>

        {{-- Design mein nahi hai: Payment summary (upar Total / Advance / Balance mein already hai) (comment out)
        <div class="jp-vsec">
            <h5><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-wallet"></use></svg></span>Payment summary</h5>
            <div class="jp-vgrid">
                <div class="jp-vf jp-k"><span>Total bill value</span><b>₹{{ number_format($order->total_payable, 2) }}</b></div>
                @if($order->misc_charges > 0)
                    <div class="jp-vf"><span>Misc. charges</span><b>+₹{{ number_format($order->misc_charges, 2) }}</b></div>
                @endif
                <div class="jp-vf jp-g"><span>Cash advance</span><b>₹{{ number_format($order->cash_adv, 2) }}</b></div>
                @if($order->online_adv > 0)
                    <div class="jp-vf jp-g"><span>Online advance @if($order->bank)({{ $order->bank->name }})@endif</span><b>₹{{ number_format($order->online_adv, 2) }}</b></div>
                @endif
                @if($goldAdvWt > 0)
                    <div class="jp-vf jp-o"><span>Gold advance ({{ ucfirst($goldAdvCat) }})</span><b>{{ number_format($goldAdvWt, 3) }} g @if($order->gold_adv_amount > 0)(₹{{ number_format($order->gold_adv_amount, 2) }})@endif</b></div>
                @endif
                @if($silverAdvWt > 0)
                    <div class="jp-vf"><span>Silver advance ({{ ucfirst($silverAdvCat) }})</span><b>{{ number_format($silverAdvWt, 3) }} g @if($order->silver_adv_amount > 0)(₹{{ number_format($order->silver_adv_amount, 2) }})@endif</b></div>
                @endif
                @if(!$goldAdvWt && !$silverAdvWt && $order->metal_adv_wt > 0)
                    <div class="jp-vf jp-o"><span>Metal ({{ strtoupper($order->metal_adv_type) }}, {{ ucfirst($metalAdvCat) }})</span><b>{{ number_format($order->metal_adv_wt, 3) }} g</b></div>
                @endif
                <div class="jp-vf {{ $dBal < 0 ? 'jp-g' : 'jp-r' }}"><span>{{ $dBal < 0 ? 'Final advance' : 'Final balance due' }}</span><b>₹{{ number_format(abs($dBal), 2) }}</b></div>
            </div>
        </div>
        --}}
    </div>

    <div class="jp-modal-f">
        <button type="button" class="jp-b" data-dismiss="modal">Close</button>
        <a class="jp-b" href="{{ route('vendor.orders.print', $order->id) }}" target="_blank"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-print"></use></svg></span>Print receipt</a>
        @if($order->status != 3 && !$order->items->where('status', 'delivered')->isNotEmpty())
            <a class="jp-b jp-pri jp-open-order" href="{{ route('vendor.orders.edit', $order->id) }}"><span class="jp-ic"><svg viewBox="0 0 24 24"><use href="#ji-pencil"></use></svg></span>Edit order</a>
        @endif
    </div>
</div>
