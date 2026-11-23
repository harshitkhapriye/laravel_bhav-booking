<script src="{{ asset('theme/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('theme/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/custom_datatable_96/my_datatable_96.js') }}"></script>
@include('layouts.theme.js.datatable')

<script>
    $(document).ready(function () {
        const list_url = "{{ route('vendor.orders.list') }}";
        let search_timeout = null;

        window.changeEntries = function () {
            if (search_timeout) clearTimeout(search_timeout);
            search_timeout = setTimeout(() => getresult(list_url), 300);
        };

        function getresult(url) {
            // If using a custom datatable script that needs cleanup
            if ($.fn.smartDataTableDestroy) $('.smart_dt').smartDataTableDestroy();

            // Clear totals immediately to avoid stale data
            $(document).find('tfoot').remove();

            $("#data_area").html('<tr><td colspan="8" class="text-center py-5"><div class="d-flex flex-column align-items-center py-4 bg-white border" style="border-radius: 12px;"><i class="fa fa-spinner fa-spin mb-3 text-primary" style="font-size: 2rem;"></i><p class="text-muted fw-800 mb-0">Fetching Data...</p></div></td></tr>');
            $("#paging_area").html('');

            $.ajax({
                url: url,
                type: "GET",
                data: {
                    "entries": $(".entries").val(),
                    "keyword": $("#keyword").val(),
                    "status": $("#status_filter").val(),
                    "reportrange": $("#reportrange").val()
                },
                success: function (data) {
                    if (data.status === 'success' && data.html) {
                        $("#data_area").replaceWith(data.html);
                        $("#paging_area").html(data.paging);
                        
                        // Redraw or re-init if using the custom script
                        if ($.fn.smartDataTableRedraw) $('.smart_dt').smartDataTableRedraw();
                    }
                },
                error: function () {
                    $("#data_area").html('<tr><td colspan="8" class="text-center py-5 text-danger fw-800"><i class="fa fa-exclamation-triangle mr-2"></i> Error loading data.</td></tr>');
                }
            });
        }

        // Initial load if data is missing or empty
        @if(!isset($orders) || $orders->count() == 0)
            getresult(list_url);
        @endif

        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            getresult($(this).attr('href'));
        });

        $(document).on('change', '.entries', function () {
            getresult(list_url);
        });

        $(document).on('change', '#status_filter', function () {
            getresult(list_url);
        });

        $('#daterange-btn').daterangepicker({
            startDate: moment().subtract(10, 'years'),
            endDate: moment().add(10, 'years'),
            ranges: {
                'All Time': [moment().subtract(10, 'years'), moment().add(10, 'years')],
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'left'
        }, function (start, end, label) {
            if (label === 'All Time') {
                $('#reportrange').val('');
                $('#daterange-text').html('All Time');
            } else {
                $('#reportrange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
                $('#daterange-text').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
            }
            changeEntries();
        });

        $(document).on('show.bs.dropdown', '.dropdown', function () {
            $(this).closest('tr').addClass('active-dropdown-row');
        });

        $(document).on('hide.bs.dropdown', '.dropdown', function () {
            $(this).closest('tr').removeClass('active-dropdown-row');
        });

        // Dynamic positioning of tooltips on hover to prevent clipping out of screen or scroll container
        $(document).on('mouseenter', '.custom-tooltip-wrapper', function() {
            const $wrapper = $(this);
            const $tooltip = $wrapper.find('.tooltip-content');
            
            // Raise stacking context of the parent td/tr to prevent overlap
            $wrapper.closest('td').addClass('tooltip-active-td');
            $wrapper.closest('tr').addClass('tooltip-active-tr');

            // Remove previous classes to measure naturally
            $tooltip.removeClass('tooltip-down tooltip-right tooltip-left');
            
            // Get bounding client rects
            const wrapperRect = $wrapper[0].getBoundingClientRect();
            const tooltipHeight = $tooltip.outerHeight();
            const tooltipWidth = $tooltip.outerWidth();
            
            // Find scroll container if any
            const $scrollContainer = $wrapper.closest('.table-responsive, .dataTables_scrollBody');
            let containerTop = 0;
            let containerBottom = window.innerHeight;
            let containerLeft = 0;
            let containerRight = window.innerWidth;
            
            if ($scrollContainer.length > 0) {
                const containerRect = $scrollContainer[0].getBoundingClientRect();
                containerTop = Math.max(containerTop, containerRect.top);
                containerBottom = Math.min(containerBottom, containerRect.bottom);
                containerLeft = Math.max(containerLeft, containerRect.left);
                containerRight = Math.min(containerRight, containerRect.right);
            }
            
            // Check spacing in all four directions relative to the scroll container/viewport
            const spaceAbove = wrapperRect.top - containerTop;
            const spaceBelow = containerBottom - wrapperRect.bottom;
            const spaceRight = containerRight - wrapperRect.right;
            const spaceLeft = wrapperRect.left - containerLeft;
            
            // Decide best placement
            if (spaceAbove < tooltipHeight + 15) {
                // Not enough space above! Let's see if we have space below
                if (spaceBelow >= tooltipHeight + 15) {
                    $tooltip.addClass('tooltip-down');
                } else if (spaceRight >= tooltipWidth + 15) {
                    $tooltip.addClass('tooltip-right');
                } else if (spaceLeft >= tooltipWidth + 15) {
                    $tooltip.addClass('tooltip-left');
                } else {
                    $tooltip.addClass('tooltip-down');
                }
            }
        });

        $(document).on('mouseleave', '.custom-tooltip-wrapper', function() {
            $(this).find('.tooltip-content').removeClass('tooltip-down tooltip-right tooltip-left');
            $(this).closest('td').removeClass('tooltip-active-td');
            $(this).closest('tr').removeClass('tooltip-active-tr');
        });

        let allFetchedSamples = [];
        let selectedSamples = [];
        let activeMetalTab = null;
        let samplesAjaxPromise = null;

        window.openAssignModal = function (orderId, orderUnique, productName, grossWt, lessWt, netWt, wastagePer, wastageGm, fineWt, purity) {
            $('#assignModal').data('order-id', orderId);
            $('#assignModal').data('order-unique', orderUnique);
            $('#assignModal').data('product-name', productName);
            $('#assignModal').data('gross-wt', grossWt);
            $('#assignModal').data('less-wt', lessWt);
            $('#assignModal').data('net-wt', netWt);
            $('#assignModal').data('wastage-per', wastagePer);
            $('#assignModal').data('wastage-gm', wastageGm);
            $('#assignModal').data('fine-wt', fineWt);
            $('#assignModal').data('purity', purity || '');
            
            // Pre-fetch order samples immediately
            let orderIdsStr = Array.isArray(orderId) ? orderId.join(',') : orderId;
            allFetchedSamples = [];
            samplesAjaxPromise = $.ajax({
                url: "{{ route('vendor.orders.karigars.samples') }}",
                type: "GET",
                data: { order_ids: orderIdsStr },
                success: function (res) {
                    if (res.status === 'success') {
                        allFetchedSamples = res.samples.filter(s => s.status === 'pending');
                    }
                }
            });

            $('#assignModal').modal('show');
        };

        window.renderSampleDropdown = function (query = '') {
            let $menu = $('#pop_sample_dropdown_menu');
            $menu.empty();
            
            let selectedIds = selectedSamples.map(s => s.id);
            let unselectedSamples = allFetchedSamples.filter(s => !selectedIds.includes(s.id));
            
            if (activeMetalTab) {
                unselectedSamples = unselectedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === activeMetalTab.toUpperCase());
            } else if (selectedSamples.length > 0) {
                let firstMetal = selectedSamples[0].metal_type.toUpperCase();
                unselectedSamples = unselectedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === firstMetal);
            }
            
            if (unselectedSamples.length === 0) {
                $menu.append('<span class="dropdown-item text-muted text-center py-2" style="font-size: 0.8rem; font-weight: 600;">All samples selected</span>');
                $('#pop_sample_search_input').attr('placeholder', 'All samples selected');
            } else {
                let selectedCount = selectedSamples.length;
                if (selectedCount > 0) {
                    $('#pop_sample_search_input').attr('placeholder', `${selectedCount} Sample(s) Selected`);
                } else {
                    $('#pop_sample_search_input').attr('placeholder', 'Search & select sample(s)...');
                }
                
                let q = query.toLowerCase().trim();
                if (q !== '') {
                    unselectedSamples.sort((a, b) => {
                        let orderA = (a.order ? a.order.order_unique : '').toLowerCase();
                        let nameA = (a.product_name || '').toLowerCase();
                        let wtA = (parseFloat(a.gross_wt) || 0).toString();
                        let touchA = (a.touch || a.purity || '').toLowerCase();
                        let matchesA = orderA.includes(q) || nameA.includes(q) || wtA.includes(q) || touchA.includes(q);
                        
                        let orderB = (b.order ? b.order.order_unique : '').toLowerCase();
                        let nameB = (b.product_name || '').toLowerCase();
                        let wtB = (parseFloat(b.gross_wt) || 0).toString();
                        let touchB = (b.touch || b.purity || '').toLowerCase();
                        let matchesB = orderB.includes(q) || nameB.includes(q) || wtB.includes(q) || touchB.includes(q);
                        
                        if (matchesA && !matchesB) return -1;
                        if (!matchesA && matchesB) return 1;
                        return 0;
                    });
                }
                
                unselectedSamples.forEach(s => {
                    let orderUnique = s.order ? s.order.order_unique : 'N/A';
                    let statusText = s.status === 'in_process' ? 'In Process' : 'Pending';
                    let statusClass = s.status === 'in_process' ? 'ice-badge-process' : 'ice-badge-pending';
                    
                    let isMatch = false;
                    if (q !== '') {
                        let orderStr = orderUnique.toLowerCase();
                        let nameStr = (s.product_name || '').toLowerCase();
                        let wtStr = (parseFloat(s.gross_wt) || 0).toString();
                        let touchStr = (s.touch || s.purity || '').toLowerCase();
                        isMatch = orderStr.includes(q) || nameStr.includes(q) || wtStr.includes(q) || touchStr.includes(q);
                    }
                    
                    let styleAttr = isMatch ? 'background-color: rgba(99, 102, 241, 0.08); font-weight: 800;' : '';
                    
                    $menu.append(`
                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 sample-opt-item" href="#" data-id="${s.id}" style="font-size: 0.8rem; font-weight: 700; color: #334155; border-bottom: 1px solid rgba(226, 232, 240, 0.5); transition: all 0.2s; ${styleAttr}">
                            <span class="d-flex align-items-center"><i class="fa fa-gem text-indigo opacity-75 mr-2"></i> #${orderUnique} | ${s.product_name} (${s.metal_type ? s.metal_type.toUpperCase() : ''} - ${(parseFloat(s.gross_wt || 0) * (s.qty || 1)).toFixed(3)}g)</span>
                            <span class="ice-badge ${statusClass}" style="font-size: 0.6rem !important;">${statusText}</span>
                        </a>
                    `);
                });
            }
        };

        window.renderSampleTable = function () {
            let $tbody = $('#selected_samples_tbody');
            $tbody.empty();
            
            if (selectedSamples.length === 0) {
                $('#selected_samples_table_container').hide();
                return;
            }
            
            $('#selected_samples_table_container').show();
            
            selectedSamples.forEach((s, idx) => {
                let orderUnique = s.order ? s.order.order_unique : 'N/A';
                let metal = s.metal_type ? s.metal_type.charAt(0).toUpperCase() + s.metal_type.slice(1).toLowerCase() : '-';
                let qty = s.qty || 1;
                let touch = s.touch || s.purity || '-';
                let gross = parseFloat(s.gross_wt || 0).toFixed(3);
                let less = parseFloat(s.less_wt || 0).toFixed(3);
                let net = parseFloat(s.net_wt || 0).toFixed(3);
                let wastage = parseFloat(s.wastage_per || 0).toFixed(3);
                let fine = parseFloat(s.fine_wt || 0).toFixed(3);
                
                $tbody.append(`
                    <tr class="ice-row" data-sample-id="${s.id}">
                        <td><span style="color: #2563eb; font-weight: 800;">#${orderUnique}</span></td>
                        <td class="text-left">
                            <div style="font-weight: 800; color: #1e293b; font-size: 0.8rem; margin-bottom: 2px;">
                                ${s.product_name} <small>(${metal})</small>
                            </div>
                            <div style="font-size: 0.65rem; font-weight: 700; color: #64748b;">
                                Qnty: <strong>${qty}</strong>
                            </div>
                        </td>
                        <td><span style="font-weight: 700;">${gross}</span></td>
                        <td><span class="text-muted">${less}</span></td>
                        <td><span class="text-success font-weight-bold">${net}</span></td>
                        <td><span class="text-warning font-weight-bold">${wastage} gm</span></td>
                        <td><span class="text-primary font-weight-bold">${fine}</span></td>
                        <td><span class="badge badge-info px-2 py-1" style="font-size: 0.65rem; font-weight: 800;">${touch}</span></td>
                        <td>
                            <button type="button" class="ice-btn-cancel mx-auto" onclick="removeSelectedSample(${s.id})">&times;</button>
                        </td>
                    </tr>
                `);
            });
        };

        window.removeSelectedSample = function (sampleId) {
            selectedSamples = selectedSamples.filter(s => s.id !== sampleId);
            renderSampleDropdown();
            renderSampleTable();
            updateSums();
        };

        window.updateSums = function () {
            let gross = 0;
            let less = 0;
            let net = 0;
            let fine = 0;
            let totalWastageGm = 0;
            let touchList = [];
            
            selectedSamples.forEach(s => {
                let qty = s.qty || 1;
                gross += (parseFloat(s.gross_wt) || 0) * qty;
                less += (parseFloat(s.less_wt) || 0) * qty;
                net += (parseFloat(s.net_wt) || 0) * qty;
                fine += (parseFloat(s.fine_wt) || 0) * qty;
                
                let wGm = (parseFloat(s.wastage_per) || 0) * qty;
                totalWastageGm += wGm;
                
                let t = s.touch || s.purity;
                if (t && !touchList.includes(t)) {
                    touchList.push(t);
                }
            });
            
            let avgWastagePer = net > 0 ? (totalWastageGm / net) * 100 : 0;
            
            $('#pop_approx_wt').val(gross.toFixed(3));
            $('#pop_less_wt').val(less.toFixed(3));
            $('#pop_net_wt').val(net.toFixed(3));
            $('#pop_purity').val(touchList.join(', '));
            
            if (avgWastagePer > 0) {
                $('#pop_wastage_per').val(avgWastagePer.toFixed(2));
            } else {
                $('#pop_wastage_per').val('');
            }
            
            if (totalWastageGm > 0) {
                $('#pop_wastage_gm').val(totalWastageGm.toFixed(3));
            } else {
                $('#pop_wastage_gm').val('');
            }
            
            $('#pop_approx_fine').val(fine.toFixed(3));
            
            let baseFine = fine - totalWastageGm;
            $('#pop_base_fine').val(baseFine);

            // If more than one sample is selected, make wastage percentage & wastage gm read only
            if (selectedSamples.length > 1) {
                $('#pop_wastage_per').prop('readonly', true);
                $('#pop_wastage_gm').prop('readonly', true);
            } else {
                $('#pop_wastage_per').prop('readonly', false);
                $('#pop_wastage_gm').prop('readonly', false);
            }
            
            // Set hidden inputs
            let ids = selectedSamples.map(s => s.id).join(',');
            $('#selected_item_ids_input').val(ids);
            
            if (selectedSamples.length > 0) {
                $('#issue_order_id').val(selectedSamples[0].jewelry_order_id);
                $('#pop_order_unique').text(selectedSamples.map(s => s.order ? s.order.order_unique : '').filter((v, i, a) => v && a.indexOf(v) === i).join(', '));
                $('#pop_product_name').text(selectedSamples.map(s => s.product_name).filter((v, i, a) => a.indexOf(v) === i).join(', '));
            } else {
                $('#issue_order_id').val('');
                $('#pop_order_unique').text('None');
                $('#pop_product_name').text('None');
            }
        };

        $(document).on('click', '.sample-opt-item', function (e) {
            e.preventDefault();
            e.stopPropagation(); // Stops dropdown from closing when selected!
            
            let sampleId = parseInt($(this).data('id'));
            if (!sampleId) return;
            
            let sample = allFetchedSamples.find(s => s.id === sampleId);
            if (sample && !selectedSamples.some(s => s.id === sampleId)) {
                let selectedIds = selectedSamples.map(s => s.id);
                let unselectedSamples = allFetchedSamples.filter(s => !selectedIds.includes(s.id));
                if (activeMetalTab) {
                    unselectedSamples = unselectedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === activeMetalTab.toUpperCase());
                } else if (selectedSamples.length > 0) {
                    let firstMetal = selectedSamples[0].metal_type.toUpperCase();
                    unselectedSamples = unselectedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === firstMetal);
                }
                
                let wasOnlyOne = (unselectedSamples.length === 1);

                selectedSamples.push(sample);
                renderSampleDropdown();
                renderSampleTable();
                updateSums();

                if (wasOnlyOne) {
                    $('#pop_sample_dropdown_menu').removeClass('show');
                    $('#pop_sample_selector_container .custom-sample-dropdown').removeClass('show');
                }
            }
        });

        $(document).on('input', '#pop_sample_search_input', function () {
            let query = $(this).val();
            renderSampleDropdown(query);
        });

        // Toggle dropdown when clicking anywhere on the custom sample dropdown container (input, wrapper, icon)
        $(document).on('click', '#pop_sample_selector_container .custom-sample-dropdown', function (e) {
            if ($(e.target).closest('#pop_sample_dropdown_menu').length > 0) {
                return;
            }
            e.stopPropagation();
            let $menu = $('#pop_sample_dropdown_menu');
            let $parent = $(this);
            
            if ($menu.hasClass('show')) {
                $menu.removeClass('show');
                $parent.removeClass('show');
            } else {
                // Close other open menus
                $('.dropdown-menu.show').removeClass('show');
                $('.dropdown.show').removeClass('show');
                
                $menu.addClass('show');
                $parent.addClass('show');
                $('#pop_sample_search_input').focus();
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function (e) {
            if ($(e.target).closest('#pop_sample_selector_container .custom-sample-dropdown').length === 0) {
                $('#pop_sample_dropdown_menu').removeClass('show');
                $('#pop_sample_selector_container .custom-sample-dropdown').removeClass('show');
            }
        });

        window.setActiveMetalTab = function (metal) {
            activeMetalTab = metal;
            
            // Empty any currently selected samples if switching tab, so it starts fresh
            selectedSamples = [];
            
            if (metal === 'GOLD') {
                // Gold active
                $('#btn_select_gold_metal').addClass('gold-active active');
                $('#btn_select_silver_metal').removeClass('silver-active active');
                
                // Disable/enable correct inputs
                $('#fine_issued_gold').prop('disabled', false).removeClass('bg-light').css('cursor', 'text');
                $('#fine_issued_silver').prop('disabled', true).addClass('bg-light').css('cursor', 'not-allowed').val('');
            } else if (metal === 'SILVER') {
                // Silver active
                $('#btn_select_silver_metal').addClass('silver-active active');
                $('#btn_select_gold_metal').removeClass('gold-active active');
                
                // Disable/enable correct inputs
                $('#fine_issued_silver').prop('disabled', false).removeClass('bg-light').css('cursor', 'text');
                $('#fine_issued_gold').prop('disabled', true).addClass('bg-light').css('cursor', 'not-allowed').val('');
            }
            
            // Make sure sample selector container is ALWAYS visible!
            $('#pop_sample_selector_container').show();
            $('#pop_sample_table_label').html('<i class="fa fa-list mr-1"></i> Selected Samples Details');
            
            renderSampleDropdown();
            renderSampleTable();
            updateSums();
        };

        $(document).on('click', '#btn_select_gold_metal', function (e) {
            e.preventDefault();
            setActiveMetalTab('GOLD');
        });

        $(document).on('click', '#btn_select_silver_metal', function (e) {
            e.preventDefault();
            setActiveMetalTab('SILVER');
        });

        window.assignToKarigar = function (karigarId, name) {
            let orderIdVal = $('#assignModal').data('order-id');
            let orderIdsStr = Array.isArray(orderIdVal) ? orderIdVal.join(',') : orderIdVal;

            $('#assignModal').modal('hide');
            $('#metal_pop_karigar_name').text(name);
            $('#issue_karigar_id').val(karigarId);
            $('#issue_order_id').val(orderIdsStr);
            
            // Reset fields
            $('#pop_sample_search_input').attr('placeholder', 'Loading samples...').val('');
            $('#pop_sample_dropdown_menu').empty().append('<span class="dropdown-item text-muted text-center py-2">Loading samples...</span>');
            $('#selected_samples_tbody').empty();
            $('#selected_samples_table_container').hide();
            $('#fine_issued_gold, #fine_issued_silver, #cash_issued, #online_issued, #bank_id, #remark').val('');
            $('#old_udhar_gold, #old_udhar_silver').val('0.000').css('color', '');
            $('#old_udhar_cash').val('0').css('color', '');
            
            // Reset submit button state
            let assignBtn = $('#karigarMaterialModal button[onclick="submitMetalDispatch()"]');
            assignBtn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Confirm & Assign Job');

            // Fetch Karigar Old Udhar
            $.ajax({
                url: "{{ url('vendors/orders/karigars/udhar') }}/" + karigarId,
                type: "GET",
                success: function (res) {
                    if (res.status === 'success') {
                        // Gold
                        let goldVal = Math.abs(parseFloat(res.gold) || 0);
                        if (goldVal !== 0) {
                            let sign = (res.gold_status == 1) ? '+' : '-';
                            let color = (res.gold_status == 1) ? '#10b981' : '#ef4444';
                            $('#old_udhar_gold').val(sign + goldVal.toFixed(3)).css('color', color);
                        } else {
                            $('#old_udhar_gold').val('0.000').css('color', '');
                        }

                        // Silver
                        let silverVal = Math.abs(parseFloat(res.silver) || 0);
                        if (silverVal !== 0) {
                            let sign = (res.silver_status == 1) ? '+' : '-';
                            let color = (res.silver_status == 1) ? '#10b981' : '#ef4444';
                            $('#old_udhar_silver').val(sign + silverVal.toFixed(3)).css('color', color);
                        } else {
                            $('#old_udhar_silver').val('0.000').css('color', '');
                        }

                        // Cash
                        let cashVal = Math.abs(parseFloat(res.cash) || 0);
                        if (cashVal !== 0) {
                            let sign = (res.cash_status == 1) ? '+' : '-';
                            let color = (res.cash_status == 1) ? '#10b981' : '#ef4444';
                            $('#old_udhar_cash').val(sign + cashVal.toFixed(0)).css('color', color);
                        } else {
                            $('#old_udhar_cash').val('0').css('color', '');
                        }
                    }
                }
            });

            // Wait for the pre-fetched samples AJAX to complete (it's usually already finished!)
            $.when(samplesAjaxPromise).done(function() {
                // Determine if there are gold and/or silver samples
                let goldSamples = allFetchedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === 'GOLD');
                let silverSamples = allFetchedSamples.filter(s => s.metal_type && s.metal_type.toUpperCase() === 'SILVER');
                
                // Reset/Hide the metal type selector container by default
                $('#pop_metal_type_selector_container').hide();
                $('#pop_sample_selector_container').show();
                $('#pop_sample_table_label').html('<i class="fa fa-list mr-1"></i> Selected Samples Details');
                
                selectedSamples = []; // ALWAYS start empty initially, as user requested!
                activeMetalTab = null;

                if (goldSamples.length > 0 && silverSamples.length > 0) {
                    // MIXED order! Show the metal type selector container
                    $('#pop_metal_type_selector_container').show();
                    $('#gold_samples_count').text(goldSamples.length);
                    $('#silver_samples_count').text(silverSamples.length);
                    
                    // Set active tab to Gold first by default (but do NOT auto-select the samples)
                    setActiveMetalTab('GOLD');
                } else if (goldSamples.length > 0) {
                    // Only Gold samples: show selector and let user choose
                    setActiveMetalTab('GOLD');
                } else if (silverSamples.length > 0) {
                    // Only Silver samples: show selector and let user choose
                    setActiveMetalTab('SILVER');
                } else {
                    // No pending samples found at all
                    selectedSamples = [];
                    activeMetalTab = null;
                    $('#pop_sample_selector_container').hide();
                    $('#fine_issued_gold, #fine_issued_silver').prop('disabled', false).removeClass('bg-light').css('cursor', 'text');
                }
                
                renderSampleDropdown();
                renderSampleTable();
                updateSums();
                
                $('#karigarMaterialModal').modal('show');
            }).fail(function() {
                $('#pop_sample_search_input').attr('placeholder', 'Error loading samples');
                $('#pop_sample_dropdown_menu').empty().append('<span class="dropdown-item text-danger text-center py-2">Error loading samples</span>');
            });
        };

        window.calcKarigarWastage = function (isGmFocus = false) {
            let grossWt = parseFloat($('#pop_approx_wt').val()) || 0;
            let netWt = parseFloat($('#pop_net_wt').val()) || 0;
            let wastagePerStr = $('#pop_wastage_per').val() || "0";
            let wastageGmStr = $('#pop_wastage_gm').val() || "0";
            
            let wastagePer = parseFloat(wastagePerStr);
            let wastageGm = parseFloat(wastageGmStr);
            
            let purity = 100;
            if (typeof selectedSamples !== 'undefined' && selectedSamples && selectedSamples.length > 0) {
                let totalNet = 0;
                let totalWeightedPurity = 0;
                selectedSamples.forEach(s => {
                    let qty = s.qty || 1;
                    let sNet = (parseFloat(s.net_wt) || 0) * qty;
                    let sTouch = parseFloat(s.touch || s.purity || 0);
                    totalNet += sNet;
                    totalWeightedPurity += sNet * sTouch;
                });
                if (totalNet > 0) {
                    purity = totalWeightedPurity / totalNet;
                }
            } else {
                purity = parseFloat($('#pop_purity').val()) || 100;
            }

            if (isGmFocus) {
                if (netWt > 0 && wastageGm > netWt) {
                    toastr.error('Wastage cannot exceed Net weight');
                    wastageGm = netWt;
                    $('#pop_wastage_gm').val(wastageGm.toFixed(3));
                }
                if (netWt > 0) {
                    wastagePer = (wastageGm / netWt) * 100;
                    if (wastagePer > 100) wastagePer = 100;
                    $('#pop_sample_search_input').attr('placeholder', 'Calculating...'); // temporary placeholder
                    $('#pop_wastage_per').val(wastagePer.toFixed(2));
                }
            } else {
                if (wastagePer > 100) {
                    toastr.error('Wastage percentage cannot exceed 100%');
                    wastagePer = 100;
                    $('#pop_wastage_per').val(wastagePer);
                }
                wastageGm = (netWt * wastagePer) / 100;
                if (netWt > 0 && wastageGm > netWt) {
                    wastageGm = netWt;
                    wastagePer = 100;
                    $('#pop_wastage_gm').val(wastageGm.toFixed(3));
                    $('#pop_wastage_per').val(wastagePer.toFixed(2));
                    toastr.error('Wastage cannot exceed Net weight');
                } else {
                    $('#pop_wastage_gm').val(wastageGm.toFixed(3));
                }
            }

            let newFine = (window.fineCalculationMethod === 'tunch_plus_wastage')
                ? ((netWt * (purity / 100)) + wastageGm)
                : ((netWt + wastageGm) * (purity / 100));
            $('#pop_approx_fine').val(newFine.toFixed(3));
        };

        window.clipDecimals = function(val, decimals) {
            if (!val) return val;
            val = val.toString();
            if (val.indexOf('.') !== -1) {
                var parts = val.split('.');
                if (parts[1].length > decimals) {
                    parts[1] = parts[1].substring(0, decimals);
                    val = parts.join('.');
                }
            }
            return val;
        };

        window.sanitizeDecimalInput = function(val, decimals) {
            if (!val && val !== 0 && val !== '0') return '';
            
            // Clean non-numeric characters (allow only digits and one dot)
            let str = val.toString().replace(/[^0-9.]/g, '');
            
            // Format starting with decimal: .25 -> 0.25
            if (str.startsWith('.')) {
                str = '0' + str;
            }
            
            // Format starting with 0 then reset it (unless it has a decimal point)
            if (str === '0') {
                return ''; // reset if it's just '0'
            }
            
            // Strip leading zeros followed by non-decimal digits: 0235 -> 235
            if (str.length > 1 && str.startsWith('0') && str[1] !== '.') {
                str = str.replace(/^0+/, '');
                if (str === '') {
                    return '';
                }
                if (str.startsWith('.')) {
                    str = '0' + str;
                }
            }
            
            let parts = str.split('.');
            if (parts.length > 2) {
                str = parts[0] + '.' + parts.slice(1).join('');
                parts = str.split('.');
            }
            
            // Clip decimals
            if (parts.length > 1 && parts[1].length > decimals) {
                str = parts[0] + '.' + parts[1].substring(0, decimals);
            }
            
            return str;
        };

        window.sanitizeIntegerInput = function(val) {
            if (!val && val !== 0 && val !== '0') return '';
            let str = val.toString().replace(/[^0-9]/g, '');
            
            // Format starting with 0 then reset it
            if (str === '0' || str.replace(/^0+/, '') === '') {
                return ''; // reset if it is just '0' or all zeros
            }
            
            // Strip leading zeros: 0235 -> 235
            if (str.length > 1 && str.startsWith('0')) {
                str = str.replace(/^0+/, '');
            }
            return str;
        };

        window.showInputError = function (field, message) {
            let fieldMap = {
                'karigar_id': '#pop_sample_search_input',
                'bank_id': '#bank_id',
                'wastage_per': '#pop_wastage_per',
                'wastage_gm': '#pop_wastage_gm',
                'fine_issued_gold': '#fine_issued_gold',
                'fine_issued_silver': '#fine_issued_silver',
                'cash_issued': '#cash_issued',
                'online_issued': '#online_issued',
                'remark': '#remark'
            };
            
            let inputSelector = fieldMap[field] || `#${field}`;
            let $el = $(inputSelector);
            
            if ($el.length > 0) {
                $el.addClass('is-invalid');
                $el.parent().find('.karigar-error-msg').remove();
                
                let $errorDiv = $(`
                    <div class="karigar-error-msg mt-1 fw-800 text-danger" style="font-size: 0.65rem; text-transform: none; text-align: left; display: none; line-height: 1.2;">
                        <i class="fa fa-exclamation-circle mr-1"></i> ${message}
                    </div>
                `);
                
                $el.after($errorDiv);
                $errorDiv.slideDown(200);
            }
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            }
        };

        // Clear validation errors on input focus/type
        $(document).on('focus input change', '.karigar-input, select, input', function () {
            $(this).removeClass('is-invalid');
            $(this).parent().find('.karigar-error-msg').slideUp(150, function () {
                $(this).remove();
            });
        });

        window.submitMetalDispatch = function () {
            if (selectedSamples.length === 0) {
                toastr.error('Please select at least one sample.');
                return false;
            }

            // Clear previous errors on submit start
            $('.karigar-error-msg').remove();
            $('.karigar-input').removeClass('is-invalid');

            let formData = $('#karigar_issue_form').serialize();
            let btn = (typeof event !== 'undefined' && event && event.target) ? $(event.target) : $();
            
           
            if(!btn.is('button')) btn = $('#karigarMaterialModal button[onclick="submitMetalDispatch()"]');
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Assigning...');

            $.ajax({
                url: "{{ route('vendor.orders.karigars.issue') }}",
                type: "POST",
                data: formData,
                success: function (res) {
                    if (res.status === 'success') {
                        $('#karigarMaterialModal').modal('hide');
                        
                        let orderIds = selectedSamples.map(s => s.jewelry_order_id).filter((v, i, a) => a.indexOf(v) === i);

                        Swal.fire({
                            title: 'Job Assigned Successfully!',
                            text: "Would you like to print the order receipt(s) now?",
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#b8860b',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: '<i class="fa fa-print"></i> Print Receipt',
                            cancelButtonText: 'Close',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.value || result.isConfirmed) {
                                if (res.issue && res.issue.id) {
                                    let printUrl = '{{ route("vendor.orders.assign.print", ":id") }}'.replace(':id', res.issue.id);
                                    window.open(printUrl, '_blank');
                                }
                            }
                            window.location.href = "{{ route('vendor.orders.karigar_list') }}";
                        });
                    } else {
                        // Clear error messages
                        $('.karigar-error-msg').remove();
                        $('.karigar-input').removeClass('is-invalid');

                        let msg = res.message;
                        if (msg && (msg.toLowerCase().includes('bank') || msg.toLowerCase().includes('online'))) {
                            showInputError('bank_id', msg);
                        } else {
                            toastr.error(msg || 'An error occurred.');
                        }
                        btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Confirm & Assign Job');
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Confirm & Assign Job');
                    
                    // Clear error messages
                    $('.karigar-error-msg').remove();
                    $('.karigar-input').removeClass('is-invalid');

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            showInputError(field, messages[0]);
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        let msg = xhr.responseJSON.message;
                        if (msg.toLowerCase().includes('bank') || msg.toLowerCase().includes('online')) {
                            showInputError('bank_id', msg);
                        } else {
                            toastr.error(msg);
                        }
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                }
            });
        };

        $(document).on('change', '#select_all', function () {
            $('.order_checkbox').prop('checked', $(this).prop('checked'));
            updateBulkBar();
        });

        $(document).on('change', '.order_checkbox', function () {
            if ($('.order_checkbox:checked').length == $('.order_checkbox').length) {
                $('#select_all').prop('checked', true);
            } else {
                $('#select_all').prop('checked', false);
            }
            updateBulkBar();
        });

        function updateBulkBar() {
            var count = $('.order_checkbox:checked').length;
            if (count > 0) {
                $('#selected_count').text(count);
                $('#bulk_actions_bar').css('display', 'flex');
                
                var hasDeliveredForDelete = false;
                var hasNoPendingForAssign = false;
                $('.order_checkbox:checked').each(function () {
                    var status = $(this).closest('tr').attr('data-status');
                    if (status === 'delivered') {
                        hasDeliveredForDelete = true;
                    }
                    if (status === 'in_process' || status === 'completed' || status === 'delivered') {
                        hasNoPendingForAssign = true;
                    }
                });
                
                if (hasNoPendingForAssign) {
                    $('#bulk_assign_btn').hide();
                } else {
                    $('#bulk_assign_btn').show();
                }

                if (hasDeliveredForDelete) {
                    $('#bulk_delete_btn').hide();
                } else {
                    $('#bulk_delete_btn').show();
                }
            } else {
                $('#bulk_actions_bar').hide();
            }
        }

        window.deselectAll = function () {
            $('#select_all').prop('checked', false);
            $('.order_checkbox').prop('checked', false);
            updateBulkBar();
        };

        window.bulkDelete = function () {
            var ids = $('.order_checkbox:checked').map(function () { return $(this).val(); }).get();
            if (ids.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Please select at least one order.');
                return;
            }

            var form = $('#deleteForm');
            form.attr('action', '{{ route("vendor.orders.bulk_delete") }}');
            form.find('input[name="ids"], input[name="ids[]"], input[name="ids_csv"]').remove();
            form.append('<input type="hidden" name="ids_csv" value="' + ids.join(',') + '">');
            ids.forEach(function(id) {
                form.append('<input type="hidden" name="ids[]" value="' + id + '">');
            });

            $('#confirmDeleteModalLabel').text('Confirm Bulk Deletion');
            $('#confirmDeleteModal .mpin-subtitle').text('You are about to delete ' + ids.length + ' selected order(s). Enter 4-digit M-PIN to confirm.');
            $('#confirmDeleteModal').modal('show');
        };

        $(document).on('click', '.btn-delete-order', function(e) {
            var url = $(this).attr('data-delete-url') || $(this).data('delete-url');
            var form = $('#deleteForm');
            if (url) {
                form.attr('action', url);
            }
            form.find('input[name="ids"], input[name="ids[]"], input[name="ids_csv"]').remove();
            $('#confirmDeleteModalLabel').text('Confirm Deletion');
            $('#confirmDeleteModal .mpin-subtitle').text('Are you sure? Enter 4-digit M-PIN to confirm deletion.');
        });

        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var form = $('#deleteForm');
            if (button && button.length) {
                var targetBtn = button.closest('[data-delete-url]');
                var deleteUrl = targetBtn.length ? (targetBtn.attr('data-delete-url') || targetBtn.data('delete-url')) : (button.attr('data-delete-url') || button.data('delete-url'));
                if (deleteUrl) {
                    form.attr('action', deleteUrl);
                    form.find('input[name="ids"], input[name="ids[]"], input[name="ids_csv"]').remove();
                    $('#confirmDeleteModalLabel').text('Confirm Deletion');
                    $('#confirmDeleteModal .mpin-subtitle').text('Are you sure? Enter 4-digit M-PIN to confirm deletion.');
                }
            }
        });

        $('#confirmDeleteModal').on('hidden.bs.modal', function () {
            var form = $('#deleteForm');
            form.find('input[name="ids"], input[name="ids[]"], input[name="ids_csv"]').remove();
            $('#confirmDeleteModalLabel').text('Confirm Deletion');
            $('#confirmDeleteModal .mpin-subtitle').text('Are you sure? Enter 4-digit M-PIN to confirm deletion.');
        });

        window.bulkAssign = function () {
            var ids = $('.order_checkbox:checked').map(function () { return $(this).val(); }).get();
            openAssignModal(ids);
        };

        window.bulkPrint = function () {
            var ids = $('.order_checkbox:checked').map(function () { return $(this).val(); }).get();
            if (ids.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Please select at least one order to print.');
                return;
            }
            
            // Create a dynamic form to submit via POST to hide IDs from URL
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("vendor.orders.bulk_print") }}';
            form.target = '_blank';
            
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);
            
            var idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = ids.join(',');
            form.appendChild(idsInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            deselectAll();
        };

        // View Order Detail
        $(document).on('click', '.btn-view-order', function() {
            const id = $(this).data('id');
            $('#order_detail_area').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin text-primary mb-3" style="font-size: 2rem;"></i><p class="text-muted fw-800">Loading Order Details...</p></div>');
            $('#orderDetailModal').modal('show');

            $.get('{{ route("vendor.orders.detail", ":id") }}'.replace(':id', id), function(html) {
                $('#order_detail_area').html(html);
            });
        });

        // --- Karigar Search & Add Logic ---
        let karigarSearchTimeout = null;

        window.searchKarigars = function() {
            if (karigarSearchTimeout) clearTimeout(karigarSearchTimeout);
            karigarSearchTimeout = setTimeout(() => {
                let keyword = $('#karigar_search_input').val();
                $.ajax({
                    url: "{{ route('vendor.orders.karigars.search') }}",
                    type: "GET",
                    data: { keyword: keyword },
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#karigar_list_area').html(res.html);
                        }
                    }
                });
            }, 300);
        };

        $(document).on('shown.bs.modal', '#assignModal', function () {
            window.searchKarigars();
        });

        $(document).on('change', '#karigar_state', function() {
            var state = $(this).val();
            if (state) {
                $.ajax({
                    url: '/get-districts',
                    type: 'GET',
                    data: { state: state },
                    success: function(response) {
                        $('#karigar_district').empty().append('<option value="">Select District</option>');
                        $.each(response, function(key, district) {
                            $('#karigar_district').append('<option value="' + district.code + '">' + district.name + '</option>');
                        });
                    }
                });
            } else {
                $('#karigar_district').empty().append('<option value="">Select District</option>');
            }
        });

        $(document).on('submit', '#addKarigarForm', function(e) {
            e.preventDefault();
            let btn = $('#saveKarigarBtn');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');
            $('.form-control').removeClass('is-invalid');
            
            $.ajax({
                url: "{{ route('vendor.orders.karigars.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        $('#addKarigarModal').modal('hide');
                        $('#addKarigarForm')[0].reset();
                        window.searchKarigars(); 
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            toastr.error(messages[0]);
                            $('[name="' + field + '"]').addClass('is-invalid');
                        });
                    } else {
                        let error = xhr.responseJSON.message || 'Something went wrong';
                        toastr.error(error);
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save Karigar');
                }
            });
        });
    });
</script>
