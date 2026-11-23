<!-- Issue Metal to Karigar Pop Modal -->
<div class="modal fade" id="karigarMaterialModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 580px;">
        <form id="karigar_issue_form" class="modal-content border-0 shadow-lg d-flex flex-column" style="border-radius: 20px; overflow: hidden; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); max-height: 100%;">
            <div class="modal-header p-3 border-0 align-items-center" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-bottom: 1px solid #cbd5e1 !important; flex-shrink: 0;">
                <div>
                    <h6 class="modal-title fw-900 text-dark mb-1" style="font-size: 1.1rem;"><i class="fa fa-hammer mr-2 text-primary"></i> Issue Work to <span id="metal_pop_karigar_name" class="text-danger fw-900"></span></h6>
                    <div class="small text-muted fw-800">
                        Order: <span id="pop_order_unique" class="badge badge-light border text-primary px-2" style="font-size: 0.75rem;"></span> 
                        <span class="mx-2">|</span> Product: <span id="pop_product_name" class="fw-900 text-dark"></span>
                    </div>
                </div>
                <button type="button" class="close outline-none" data-dismiss="modal" aria-label="Close" style="color: #64748b; opacity: 1;">
                    <span aria-hidden="true" style="font-size: 24px;">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-3 custom-scrollbar" style="background: #fafafa; overflow-y: auto; flex: 1 1 auto;">
                @csrf
                <input type="hidden" name="order_id" id="issue_order_id">
                <input type="hidden" name="karigar_id" id="issue_karigar_id">

                    <style>
                        /* Premium Metal Category Slider Tabs */
                        .metal-tab-container {
                            display: flex;
                            align-items: center;
                            background: #f1f5f9;
                            border: 1.5px solid #cbd5e1;
                            border-radius: 12px;
                            padding: 3px;
                            gap: 4px;
                            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
                        }
                        .metal-tab-btn {
                            flex: 1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border: none;
                            background: transparent;
                            padding: 6px 12px;
                            font-size: 0.75rem;
                            font-weight: 800;
                            color: #64748b;
                            border-radius: 9px;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            cursor: pointer;
                            outline: none !important;
                            height: 34px;
                        }
                        .metal-tab-icon-wrapper {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 22px;
                            height: 22px;
                            border-radius: 50%;
                            margin-right: 8px;
                            font-size: 0.72rem;
                            transition: all 0.3s;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                        }
                        
                        /* Gold Active Category Style */
                        .metal-tab-btn.gold-active {
                            background: #ffffff;
                            color: #b45309;
                            box-shadow: 0 4px 10px rgba(180, 83, 9, 0.08), 0 1px 3px rgba(180, 83, 9, 0.04);
                        }
                        .metal-tab-btn.gold-active .metal-tab-icon-wrapper {
                            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
                            color: #b45309;
                            border: 1px solid #f59e0b;
                        }
                        .metal-tab-btn:not(.gold-active) .metal-tab-icon-wrapper.gold-icon {
                            background: #fdfdfd;
                            color: #d97706;
                            border: 1px solid #cbd5e1;
                        }
                        
                        /* Silver Active Category Style */
                        .metal-tab-btn.silver-active {
                            background: #ffffff;
                            color: #1e293b;
                            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04);
                        }
                        .metal-tab-btn.silver-active .metal-tab-icon-wrapper {
                            background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
                            color: #334155;
                            border: 1px solid #94a3b8;
                        }
                        .metal-tab-btn:not(.silver-active) .metal-tab-icon-wrapper.silver-icon {
                            background: #fdfdfd;
                            color: #64748b;
                            border: 1px solid #cbd5e1;
                        }
                        
                        .metal-tab-btn:hover:not(.active) {
                            background: rgba(255, 255, 255, 0.5);
                            color: #334155;
                        }

                        .karigar-input { border-radius: 8px; border: 1.5px solid #cbd5e1; height: 36px; text-align: center; font-weight: 700; font-size: 0.85rem; width: 100%; transition: all 0.2s; background: #fff;}
                        .karigar-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); outline: none; }
                        .karigar-input[readonly] { background-color: #f1f5f9; color: #64748b; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.8;}
                        .karigar-input.is-invalid { border-color: #ef4444 !important; background-color: #fef2f2 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important; }
                        
                        /* Vibrant Custom Classes */
                        .input-gold { border-color: #fbbf24; color: #b45309; background-color: #fffbeb; }
                        .input-gold:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
                        
                        .input-silver { border-color: #94a3b8; color: #334155; background-color: #f8fafc; }
                        .input-silver:focus { border-color: #64748b; box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.2); }
                        
                        .input-cash { border-color: #34d399; color: #047857; background-color: #ecfdf5; }
                        .input-cash:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }

                        .input-bank { border-color: #a78bfa; color: #5b21b6; background-color: #f5f3ff; }
                        .input-bank:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); }

                        .karigar-label { font-size: 0.85rem !important; font-weight: 800; color: #475569; margin-bottom: 4px; display: block; letter-spacing: 0.5px;}
                        .karigar-section-title { font-size: 0.85rem; font-weight: 800; color: #1e293b; padding-bottom: 6px; margin-bottom: 12px; margin-top: 16px; display: flex; align-items: center; border-bottom: 1.5px dashed #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px;}
                        .karigar-textarea { border-radius: 8px; border: 1.5px solid #cbd5e1; text-align: left; font-weight: 600; font-size: 0.85rem; width: 100%; padding: 8px 12px; resize: none; height: 36px; background: #fff;}
                        .karigar-textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); outline: none; }
                        
                        /* Custom Scrollbar */
                        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
                        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
                        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

                        /* Ice CSS for tabular sample layout */
                        .ice-table-wrapper {
                            background: rgba(240, 246, 255, 0.6);
                            backdrop-filter: blur(8px);
                            border-radius: 12px;
                            border: 1px solid rgba(191, 219, 254, 0.6);
                            overflow-x: auto;
                            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.04);
                        }
                        .ice-table {
                            width: 100%;
                            margin-bottom: 0;
                        }
                        .ice-table th {
                            background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%) !important;
                            color: #1e3a8a !important;
                            font-weight: 800 !important;
                            font-size: 0.7rem;
                            letter-spacing: 0.5px;
                            text-transform: none !important;
                            padding: 8px 10px !important;
                            border: none !important;
                            border-bottom: 1.5px solid #bfdbfe !important;
                            text-align: center;
                        }
                        .ice-table td {
                            padding: 8px 10px !important;
                            vertical-align: middle !important;
                            font-size: 0.75rem;
                            font-weight: 700;
                            color: #334155;
                            border-bottom: 1px solid rgba(191, 219, 254, 0.3) !important;
                            border-top: none !important;
                            background: rgba(255, 255, 255, 0.8) !important;
                            text-align: center;
                        }
                        .ice-row {
                            transition: all 0.2s ease-in-out;
                        }
                        .ice-row:hover {
                            background: rgba(219, 234, 254, 0.4) !important;
                        }
                        .ice-badge {
                            font-size: 0.6rem !important;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.3px;
                            padding: 2px 8px;
                            border-radius: 50px;
                            border: 1px solid transparent;
                            display: inline-block;
                        }
                        .ice-badge-pending {
                            background: #e0f2fe;
                            color: #0369a1;
                            border-color: #bae6fd;
                        }
                        .ice-badge-process {
                            background: #fffbeb;
                            color: #b45309;
                            border-color: #fde68a;
                        }
                        .ice-btn-cancel {
                            width: 20px;
                            height: 20px;
                            background: #fee2e2;
                            color: #ef4444;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            cursor: pointer;
                            transition: all 0.2s;
                            border: 1px solid #fecaca;
                            padding: 0;
                            font-size: 0.65rem;
                            outline: none;
                        }
                        .ice-btn-cancel:hover {
                            background: #ef4444;
                            color: #fff;
                            transform: scale(1.1);
                            border-color: #ef4444;
                        }
                    </style>

                    <input type="hidden" name="selected_item_ids" id="selected_item_ids_input">

                    <!-- Dynamic Metal Selection Tabs/Buttons for Mixed Orders -->
                    <div id="pop_metal_type_selector_container" class="mb-2" style="display: none;">
                        <div class="metal-tab-container">
                            <button type="button" class="metal-tab-btn" id="btn_select_gold_metal">
                                <span class="metal-tab-icon-wrapper gold-icon"><i class="fa fa-gem"></i></span>
                                GOLD ITEMS (<span id="gold_samples_count">0</span>)
                            </button>
                            <button type="button" class="metal-tab-btn" id="btn_select_silver_metal">
                                <span class="metal-tab-icon-wrapper silver-icon"><i class="fa fa-cube"></i></span>
                                SILVER ITEMS (<span id="silver_samples_count">0</span>)
                            </button>
                        </div>
                    </div>

                    <!-- Sample Selector Option -->
                    <div class="mb-3" id="pop_sample_selector_container">
                        <label class="karigar-label" style="color: #2563eb;"><i class="fa fa-gem mr-1"></i> Select Sample(s)</label>
                        <div class="dropdown custom-sample-dropdown position-relative">
                            <div class="position-relative">
                                <input type="text" id="pop_sample_search_input" class="karigar-input" placeholder="Search & select sample(s)..." aria-haspopup="true" aria-expanded="false" style="padding-right: 35px; background: #fff; cursor: pointer; font-weight: 600;" autocomplete="off">
                                <i class="fa fa-chevron-down text-muted" style="position: absolute; right: 12px; top: 11px; pointer-events: none; font-size: 0.85rem;"></i>
                            </div>
                            <div class="dropdown-menu w-100 shadow-lg border-0 py-1" aria-labelledby="pop_sample_search_input" id="pop_sample_dropdown_menu" style="border-radius: 12px; max-height: 250px; overflow-y: auto; background: #ffffff; border: 1px solid #bfdbfe !important; margin-top: 5px; z-index: 1050;">
                                <!-- Dynamic Options -->
                            </div>
                        </div>
                    </div>

                    <!-- Selected Samples Table -->
                    <div class="mb-3" id="selected_samples_table_container" style="display: none;">
                        <label class="karigar-label text-dark" id="pop_sample_table_label" style="font-size: 0.75rem; font-weight: 800; color: #1e3a8a;"><i class="fa fa-list mr-1"></i> Selected Samples Details</label>
                        <div class="ice-table-wrapper" style="box-shadow: 0 4px 20px rgba(37, 99, 235, 0.08);">
                            <table class="table ice-table" style="margin: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th class="text-left">Product Name</th>
                                        <th>Gross (gm)</th>
                                        <th>Less (gm)</th>
                                        <th>Net (gm)</th>
                                        <th>Wastage (gm)</th>
                                        <th>Fine (gm)</th>
                                        <th>Purity (%)</th>
                                        <th width="30"></th>
                                    </tr>
                                </thead>
                                <tbody id="selected_samples_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="karigar-label">Total Gross(gm)</label>
                            <input type="number" step="0.001" name="approx_weight" id="pop_approx_wt" class="karigar-input" readonly>
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Total Less(gm)</label>
                            <input type="number" step="0.001" name="less_weight" id="pop_less_wt" class="karigar-input" readonly>
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Total Net(gm)</label>
                            <input type="number" step="0.001" name="net_weight" id="pop_net_wt" class="karigar-input" readonly>
                        </div>
                        <input type="hidden" name="metal_touch" id="pop_purity">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="karigar-label">Wastage (%)</label>
                            <input type="text" name="wastage_per" id="pop_wastage_per" class="karigar-input" placeholder="0.00" oninput="let v = sanitizeDecimalInput(this.value, 2); if(this.value !== v) this.value = v; calcKarigarWastage();">
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Wastage (gm)</label>
                            <input type="text" name="wastage_gm" id="pop_wastage_gm" class="karigar-input" placeholder="0.000" oninput="let v = sanitizeDecimalInput(this.value, 3); if(this.value !== v) this.value = v; calcKarigarWastage(true);">
                        </div>
                        <div class="col-4">
                            <label class="karigar-label" style="color: #ff6e26;">Fine (gm)</label>
                            <input type="number" step="0.001" name="approx_fine" id="pop_approx_fine" class="karigar-input" readonly style="border-color: #facc15; background: #fefce8; color: #b45309;">
                            <input type="hidden" id="pop_base_fine" value="0">
                        </div>
                    </div>

                    <div class="karigar-section-title"><i class="fa fa-history mr-2 text-indigo"></i> Old Udhar</div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="karigar-label">Gold (gm)</label>
                            <input type="text" id="old_udhar_gold" class="karigar-input input-gold" placeholder="0.000" readonly>
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Silver (gm)</label>
                            <input type="text" id="old_udhar_silver" class="karigar-input input-silver" placeholder="0.000" readonly>
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Amount (₹)</label>
                            <input type="text" id="old_udhar_cash" class="karigar-input input-cash" placeholder="0" readonly>
                        </div>
                    </div>

                    <div class="karigar-section-title d-flex align-items-center justify-content-between mb-1 pb-1">
                        <span><i class="fa fa-hand-holding-usd mr-2 text-success"></i>  Udhar Issued to Karigar (Fine & Amount)</span>
                        <span class="badge badge-success text-white px-2 py-1" style="font-size: 0.68rem; letter-spacing: 0.3px; border-radius: 6px; font-weight: 800;">UDHAR GIVEN</span>
                    </div>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="karigar-label">Gold  (gm)</label>
                            <input type="text" name="fine_issued_gold" id="fine_issued_gold" class="karigar-input input-gold" placeholder="0.000" oninput="let v = sanitizeDecimalInput(this.value, 3); if(this.value !== v) this.value = v;">
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Silver  (gm)</label>
                            <input type="text" name="fine_issued_silver" id="fine_issued_silver" class="karigar-input input-silver" placeholder="0.000" oninput="let v = sanitizeDecimalInput(this.value, 3); if(this.value !== v) this.value = v;">
                        </div>
                        <div class="col-4">
                            <label class="karigar-label">Cash Amount (₹)</label>
                            <input type="text" name="cash_issued" id="cash_issued" class="karigar-input input-cash" placeholder="0" oninput="let v = sanitizeIntegerInput(this.value); if(this.value !== v) this.value = v;">
                        </div>
                    </div>

                    <div class="karigar-section-title"><i class="fa fa-university mr-2 text-primary"></i> Bank / Online</div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="karigar-label">Bank Amt (₹)</label>
                            <input type="text" name="online_issued" id="online_issued" class="karigar-input input-bank" placeholder="0" oninput="let v = sanitizeIntegerInput(this.value); if(this.value !== v) this.value = v;">
                        </div>
                        <div class="col-8">
                            <label class="karigar-label">Select Bank</label>
                            <select name="bank_id" id="bank_id" class="karigar-input input-bank" style="padding: 0 10px; cursor: pointer;">
                                <option value="">-- Select Bank --</option>
                                @php
                                    $banks = \App\Models\Banking::where('shop_id', auth()->user()->shop_id)->get();
                                @endphp
                                @foreach($banks as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-4">
                            <label class="karigar-label text-danger">Delivery Date</label>
                            <input type="date" name="delivery_date" id="delivery_date" class="karigar-input" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+10 days')) }}" style="cursor: pointer;">
                        </div>
                        <div class="col-8">
                            <label class="karigar-label">Remark</label>
                            <textarea name="remark" id="remark" class="karigar-textarea" placeholder="Enter Remark"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer p-3 border-0 bg-white" style="box-shadow: 0 -4px 10px rgba(0,0,0,0.02); z-index: 10;">
                    <button type="button" class="btn btn-primary w-100 fw-900 text-white shadow-sm" onclick="submitMetalDispatch()" style="height: 42px; background: linear-gradient(135deg, #ff6e26 0%, #ff9d6c 100%); border: none; border-radius: 12px; font-size: 0.95rem; letter-spacing: 0.5px; transition: all 0.3s;">
                        <i class="fa fa-check-circle mr-1"></i> Confirm & Assign Job
                    </button>
                </div>
            </form>
    </div>
</div>
