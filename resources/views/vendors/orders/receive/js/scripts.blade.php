<style>
    .is-invalid {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 0.15rem rgba(220, 38, 38, 0.15) !important;
    }
</style>
<script>
    let officialGoldRate = 0;
    let officialSilverRate = 0;
    let globalGoldRate = 0;
    let globalSilverRate = 0;
    let isEditingLoading = false;

    // Helper to show validation error underneath the input and optionally in toastr
    function showFieldError(selector, message, showToast = false) {
        let el = $(selector);
        if (el.length === 0) return;
        el.addClass('is-invalid');
        
        let container = el.parent();
        container.find('.validation-error').remove();
        el.siblings('.validation-error').remove();
        
        el.after('<span class="validation-error" style="color: #dc2626; font-size: 0.70rem; font-weight: 700; margin-top: 3px; display: block; text-align: left; line-height: 1.2;"><i class="fa fa-exclamation-circle mr-1"></i> ' + message + '</span>');

        if (showToast && typeof toastr !== 'undefined') {
            toastr.error(message);
        }
    }

    function clearValidationErrors() {
        $('.validation-error').remove();
        $('.is-invalid').removeClass('is-invalid');
    }

    // Auto-clear validation errors when user types or changes value
    $(document).on('input change', '#createOrderForm input, #createOrderForm select, #createOrderForm textarea', function() {
        $(this).removeClass('is-invalid');
        $(this).parent().find('.validation-error').remove();
        $(this).siblings('.validation-error').remove();
    });

    window.toggleFormFields = function () {
        let customerSelected = $('#custo_id').val() !== '';
        
        // Find all inputs, selects, textareas, and buttons in #createOrderForm
        // but exclude the customer search input `#name` itself, and hidden inputs, and token
        $('#createOrderForm').find('input, select, textarea, button').each(function() {
            let $el = $(this);
            // Exclude customer search input, hidden inputs, and token
            if ($el.attr('id') === 'name' || $el.attr('type') === 'hidden' || $el.attr('name') === '_token') {
                return;
            }
            // Exclude elements inside modals (like customer modal)
            if ($el.closest('.modal').length > 0) {
                return;
            }
            // Exclude the plus button next to the customer search input
            if ($el.hasClass('btn-plus-premium') || $el.closest('.btn-plus-premium').length > 0) {
                return;
            }
            
            $el.prop('disabled', !customerSelected);
        });

        // Toggle disabled class on priority badge
        if (customerSelected) {
            $('#urgent_badge').removeClass('disabled');
        } else {
            $('#urgent_badge').addClass('disabled');
        }

        // Wastage dependency on Net
        if (customerSelected) {
            let grossVal = parseIndianNumber($('#item_gross_wt').val());
            let lessVal = parseIndianNumber($('#item_less_wt').val());
            let netVal = grossVal - lessVal;
            if (netVal <= 0) {
                $('#item_wastage_per').prop('readonly', true).css('background-color', '#f1f5f9');
                $('#item_wastage_percent').prop('readonly', true).css('background-color', '#f1f5f9');
            } else {
                $('#item_wastage_per').prop('readonly', false).css('background-color', '');
                $('#item_wastage_percent').prop('readonly', false).css('background-color', '');
            }
        }
    };

    // --- Global Indian Standard Formatting Functions ---
    window.formatIndianNumber = function (val, decimals = null) {
        if (val === null || val === undefined || val === '') return '';
        
        let str = val.toString().replace(/,/g, '');
        let parts = str.split('.');
        let x = parts[0];
        
        let lastThree = x.substring(x.length - 3);
        let otherNumbers = x.substring(0, x.length - 3);
        if (otherNumbers != '') lastThree = ',' + lastThree;
        let formatted = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
        
        if (parts.length > 1) {
            let decimalPart = parts[1];
            if (decimals !== null) decimalPart = decimalPart.substring(0, decimals);
            formatted += "." + decimalPart;
        } else if (str.endsWith('.')) {
            formatted += ".";
        }
        
        return formatted;
    };

    window.parseIndianNumber = function (val) {
        if (!val && val !== 0) return 0;
        let str = val.toString().replace(/,/g, '');
        return parseFloat(str) || 0;
    };

    // Helper to apply formatting to input while preserving cursor position
    function handleInputFormatting(el, decimals = null) {
        let cursorPosition = el.selectionStart;
        let oldVal = el.value;
        let newVal = formatIndianNumber(oldVal, decimals);
        
        if (oldVal !== newVal) {
            el.value = newVal;
            // Adjust cursor position if commas were added/removed
            let diff = newVal.length - oldVal.length;
            el.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
        }
    }

    $(document).ready(function () {
        let orderId = $('#order_unique').val();
        $('#display_order_id').val(orderId);

        // Initialize Select2 with custom searching and redirection options
        $('#item_jewellery_type').select2({
            placeholder: "Choose Type...",
            width: '100%',
            language: {
                noResults: function () {
                    return `<div class="text-center p-2"><span class="text-muted d-block mb-1">Not there</span><a href="{{ route('stock.new.itemgroup') }}" target="_blank" class="btn btn-xs btn-primary text-white px-2 py-1 rounded select2-add-btn" style="font-size: 11px; background-color: var(--premium-indigo); border-color: var(--premium-indigo);"><i class="fa fa-plus-circle mr-1"></i> Add Type</a></div>`;
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        $('#item_product_name').select2({
            placeholder: "Choose Product...",
            width: '100%',
            language: {
                noResults: function () {
                    return `<div class="text-center p-2"><span class="text-muted d-block mb-1">Not there</span><a href="{{ route('stock.new.itemname') }}" target="_blank" class="btn btn-xs btn-primary text-white px-2 py-1 rounded select2-add-btn" style="font-size: 11px; background-color: var(--premium-indigo); border-color: var(--premium-indigo);"><i class="fa fa-plus-circle mr-1"></i> Add Item</a></div>`;
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        // --- EDIT MODE INITIALIZATION ---
        const existingItemsJson = $('#items_json').val();
        if (existingItemsJson) {
            try {
                const parsedItems = JSON.parse(existingItemsJson);
                
                // Initialize global rates from order saved rate
                let savedMetalRate = parseFloat($('#metal_rate_global').val()) || 0;
                if (savedMetalRate > 0) {
                    if (savedMetalRate < 2000) {
                        globalSilverRate = savedMetalRate;
                        globalGoldRate = 0;
                    } else {
                        globalGoldRate = savedMetalRate;
                        globalSilverRate = 0;
                    }
                } else {
                    globalGoldRate = 0;
                    globalSilverRate = 0;
                }

                // Convert DB items to frontend format if necessary
                items = parsedItems.map(it => {
                    // Check if it's already in frontend format or needs conversion
                    if (it.jewelry_order_id) { // Likely DB format
                        let itemMetal = it.metal_type;
                        let itemKarat = it.purity;
                        let itemUnit = window.getMetalRateUnit(itemMetal, itemKarat);
                        let itemUnitLabel = 'gm';
                        let rates = window.getMetalRates(itemMetal, itemKarat);
                        if (rates) {
                            let unitVal = parseFloat(rates.unit) || 1;
                            if (itemMetal.toLowerCase() === 'gold') {
                                itemUnitLabel = unitVal === 1 ? 'gm' : (unitVal + 'gm');
                            } else { // Silver
                                itemUnitLabel = unitVal === 1000 ? 'kg' : (unitVal === 1 ? 'gm' : (unitVal + 'gm'));
                            }
                        } else {
                            itemUnitLabel = (itemMetal.toLowerCase() === 'gold') ? '10gm' : 'kg';
                        }
                        let itemPerGramRate = 0;
                        if (it.rate !== undefined && parseFloat(it.rate) > 0) {
                            itemPerGramRate = parseFloat(it.rate);
                        } else {
                            let activeRate = getActiveRateForMetal(itemMetal.toLowerCase(), itemKarat);
                            itemPerGramRate = activeRate.perGramRate;
                            if (itemPerGramRate <= 0) {
                                itemPerGramRate = (itemMetal.toUpperCase() === 'GOLD') ? globalGoldRate : globalSilverRate;
                            }
                        }

                        return {
                            id: it.id,
                            type: it.jewellery_type,
                            metal: it.metal_type,
                            touch: parseFloat(it.touch),
                            purity: it.purity,
                            name: it.product_name,
                            qty: it.qty,
                            size: it.size,
                            gross: parseFloat(it.gross_wt),
                            less: parseFloat(it.less_wt),
                            net: parseFloat(it.net_wt),
                            wastage: parseFloat(it.wastage_per),
                            wastage_per: (parseFloat(it.net_wt) > 0) ? parseFloat(((parseFloat(it.wastage_per) / parseFloat(it.net_wt)) * 100).toFixed(2)) : 0,
                            fine: parseFloat(it.fine_wt),
                            making: parseFloat(it.making_charge),
                            making_type: it.making_type,
                            labor: parseFloat(it.labor_charge),
                            rate: itemPerGramRate,
                            rate_unit: '/' + itemUnitLabel,
                            status: it.status,
                            karigar_name: it.karigar_name
                        };
                    }
                    return it; // Already in frontend format (from previous save attempt or similar)
                });
                renderItemList();
                calculateFinalTotal();
                
                // Fetch customer balance if ID exists
                const custId = $('#custo_id').val();
                if (custId) fetchCustomerUdhar(custId);

            } catch (e) {
                console.error("Error parsing items JSON:", e);
            }
        }

        const now = new Date();
        const today = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        // Only set today's date if not in edit mode and empty
        if (!$('#items_json').val() && !$('input[name="order_date"]').val()) {
            $('input[name="order_date"]').val(today);
        }

        fetchCurrentRates();
        // Initialize dynamic dropdowns on page load
        let initialMetal = $('#item_metal_type').val() || 'GOLD';
        populateJewelleryTypes(initialMetal);

        toggleFormFields();

        // Customer selection validation on click
        $(document).on('click', '#createOrderForm', function (e) {
            let customerSelected = $('#custo_id').val() !== '';
            if (!customerSelected) {
                if (!$(e.target).is('#name') && !$(e.target).closest('.btn-plus-premium').length && !$(e.target).closest('#custo_modal').length) {
                    e.preventDefault();
                    toastr.warning('Please select a customer first to enable the form.');
                }
            }
        });
    });

    function toggleUrgent() {
        const badge = $('#urgent_badge');
        badge.toggleClass('active');
        $('#is_urgent').val(badge.hasClass('active') ? 1 : 0);
    }


    // Order Specific Customer Search (using UdharController)
    


    var is_new_custo =  true;
    let orderCustomerXHR = null;
    window.searchCustomerOrder = function (input) {
        is_new_custo =  true;
        let keyword = $(input).val().trim();
        
        // If user is editing the customer search input, reset custo_id and disable form fields
        $('#custo_id').val('');
        toggleFormFields();

        if (!keyword) {
            $("#customerlist").removeClass('active').empty();
            
            // Reset balance glass-pills
            $('#cash_label').text('CASH BALANCE');
            $('#gold_label').text('GOLD BALANCE');
            $('#silver_label').text('SILVER BALANCE');
            $('#cust_cash_bal').text('₹ 0.00').removeClass('text-danger').addClass('text-muted');
            $('#cust_gold_bal').text('0.000 g');
            $('#cust_silver_bal').text('0.000 g');
            return;
        }

        if (orderCustomerXHR) orderCustomerXHR.abort();

        $("#customerlist").html('<li class="loading">Searching Parties...</li>').addClass('active');

        orderCustomerXHR = $.get('{{ route("vendor.orders.search_party") }}', {
            keyword: keyword
        }, function (response) {
            let li = '';
            if (response && response.length > 0) {
                response.forEach(function (v) {
                    let name = v.name || '';
                    let mob = v.mobile || '';
                    let num = v.num || '';
                    let stream = num + "/" + name + " - " + mob;

                    li += `
                        <li>
                            <a href="javascript:void(0)"
                               class="select_customer_order"
                               data-id="${v.id}"
                               data-type="${v.type}"
                               data-target="${stream}">
                               ${stream}
                            </a>
                        </li>`;
                });
            } else {
                li = '<li class="no-record text-center p-3">No Party Found</li>';
            }
            $("#customerlist").html(li);
        });
    };

    // Handle Customer Selection for Order
    $(document).on('click', '.select_customer_order', function (e) {
        e.preventDefault();
        is_new_custo =  false;
        const id = $(this).data('id');
        const type = $(this).data('type');
        const target = $(this).data('target');

        $('#name').val(target);
        $('#custo_id').val(id);
        $('#custo_type').val(type);
        $("#customerlist").removeClass('active');

        // Trigger balance fetch
        fetchCustomerUdhar(id);
        
        toggleFormFields();
    });

    function fetchCustomerUdhar(id) {
        $.get('{{ route("vendor.orders.get_udhar", ":id") }}'.replace(':id', id), function (res) {
            // Update labels based on status (1: Jama, 0: Udhar)
            $('#cash_label').text(res.cash_status == 1 ? 'CASH JAMA' : 'CASH UDHAR');
            $('#gold_label').text(res.gold_status == 1 ? 'GOLD JAMA' : 'GOLD UDHAR');
            $('#silver_label').text(res.silver_status == 1 ? 'SILVER JAMA' : 'SILVER UDHAR');

            $('#cust_cash_bal').text('₹ ' + formatIndianNumber(parseFloat(res.cash).toFixed(2)));
            $('#cust_gold_bal').text(formatIndianNumber(parseFloat(res.gold).toFixed(3)) + ' g');
            $('#cust_silver_bal').text(formatIndianNumber(parseFloat(res.silver).toFixed(3)) + ' g');

            // Color coding
            if (res.cash_status == 0 && res.cash > 0) $('#cust_cash_bal').addClass('text-danger').removeClass('text-muted');
            else $('#cust_cash_bal').removeClass('text-danger').addClass('text-muted');
        });
    }

    $(document).on('customerformsubmit', function(e) {
        let data = e.originalEvent.detail;
        $("#custo_plus_form").find("button[type='submit']").prop('disabled', false);
        $("#process_wait").hide();
        if(data.errors) {
            let errors = data.errors;
            $.each(errors, function(i, v) {
                let err = '';
                $.each(v, function(ei, ev) {
                    if(err != '') err += '\n';
                    err += ev;
                });
                $("#" + i).addClass('is-invalid');
                $("#" + i + "_error").html(err);
                toastr.error(err);
            });
        } else if(data.error) {
            toastr.error(data.error);
        } else {
            success_sweettoatr("Customer successfully Added !");
            $("#custo_modal").modal('hide');
            resetcustoform(true);
            let target_str = data.custo.num + '/' + data.custo.name + ' - ' + data.custo.contact;
            $("#custo_id").val(data.custo.id);
            $("#custo_type").val(data.type);
            $("#name").val(target_str);
            $("#customerlist").removeClass('active');
            
            fetchCustomerUdhar(data.custo.id);
            toggleFormFields();
        }
    });

    // Replace the old change handler for 'custo' if any
    $(document).off('change', 'input[name="custo"]');


    let items = [];
    let editingId = null;
    $(document).on('input', '#item_gross_wt, #item_less_wt, #item_wastage_per, #item_wastage_percent, #item_metal_touch', function (e) {
        if (isEditingLoading) return;
        let grossInput = sanitizeNumericInput($('#item_gross_wt').val());
        let lessInput = sanitizeNumericInput($('#item_less_wt').val());
        let wastageInput = sanitizeNumericInput($('#item_wastage_per').val());
        let wastagePercentInput = sanitizeNumericInput($('#item_wastage_percent').val());
        
        // Clip to 3 decimals
        grossInput = clipDecimals(grossInput, 3);
        lessInput = clipDecimals(lessInput, 3);

        $('#item_gross_wt').val(grossInput);
        $('#item_less_wt').val(lessInput);
        
        let gross = parseIndianNumber(grossInput);
        let less = parseIndianNumber(lessInput);

        // Prevent negative Net Weight
        if (less > gross) {
            toastr.error('Less weight cannot exceed Gross weight');
            $('#item_less_wt').val('');
            less = 0;
        }

        let net = gross - less;

        // Wastage dependency on Net
        if (net <= 0) {
            $('#item_wastage_per').val('').prop('readonly', true).css('background-color', '#f1f5f9');
            $('#item_wastage_percent').val('').prop('readonly', true).css('background-color', '#f1f5f9');
            wastageInput = '';
            wastagePercentInput = '';
        } else {
            $('#item_wastage_per').prop('readonly', false).css('background-color', '');
            $('#item_wastage_percent').prop('readonly', false).css('background-color', '');
        }

        // Warning for Zero Net Weight (Warning only during input, Blocked during Add/Save)
        if (gross > 0 && less > 0 && net === 0) {
            toastr.warning('Net weight is 0. Please verify your entries.');
        }

        let targetId = e.target.id;
        let wastage = 0;
        let wastagePercent = 0;

        if (targetId === 'item_wastage_percent') {
            wastagePercentInput = clipDecimals(wastagePercentInput, 2);
            $('#item_wastage_percent').val(wastagePercentInput);
            wastagePercent = parseIndianNumber(wastagePercentInput);
            
            // Calculate wastage in grams
            wastage = net * (wastagePercent / 100);
            
            // Wastage Validation: should not exceed net
            if (net > 0 && wastage > net) {
                toastr.error('Wastage cannot exceed Net weight');
                wastage = net;
                wastagePercent = 100;
                $('#item_wastage_percent').val(clipDecimals(wastagePercent.toString(), 2));
            }
            
            $('#item_wastage_per').val(wastage > 0 ? formatIndianNumber(wastage.toFixed(3)) : '');
        } else {
            // If user typed in wastage_per or other fields, calculate percentage
            wastageInput = clipDecimals(wastageInput, 3);
            if (targetId === 'item_wastage_per') {
                $('#item_wastage_per').val(wastageInput);
            }
            wastage = parseIndianNumber(wastageInput);
            
            // Wastage Validation: should not exceed net
            if (net > 0 && wastage > net) {
                toastr.error('Wastage cannot exceed Net weight');
                $('#item_wastage_per').val(formatIndianNumber(net.toFixed(3)));
                wastage = net;
            }
            
            // Calculate wastage percent
            wastagePercent = net > 0 ? (wastage / net) * 100 : 0;
            
            if (targetId !== 'item_wastage_percent') {
                $('#item_wastage_percent').val(wastagePercent > 0 ? clipDecimals(wastagePercent.toString(), 2) : '');
            }
        }

        let touchInput = sanitizeTouchInput($('#item_metal_touch').val());
        let touch = parseIndianNumber(touchInput);
        if (touch > 100) {
            touch = 100;
            touchInput = '100';
            $('#item_metal_touch').val('100');
            toastr.error('Purity (Touch %) cannot be greater than 100');
        } else {
            $('#item_metal_touch').val(touchInput);
        }

        // Update Purity Karat select based on Touch only if Touch field was directly typed into by user
        if (targetId === 'item_metal_touch' && e.originalEvent) {
            let metalType = $('#item_metal_type').val().toLowerCase();
            let currentKarat = $('#item_purity_karat').val();
            if (metalType === 'gold') {
                let found = (window.goldKaratsData || []).find(item => Math.abs(parseFloat(item.purity_value) - touch) < 0.5);
                if (found) {
                    if (currentKarat !== found.title) $('#item_purity_karat').val(found.title);
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else if (touch >= 72.0 && touch <= 76.0) {
                    if (currentKarat !== '18K') $('#item_purity_karat').val('18K');
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else if (touch >= 82.5 && touch <= 84.5) {
                    if (currentKarat !== '20K') $('#item_purity_karat').val('20K');
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else if (touch >= 91.0 && touch <= 92.5) {
                    if (currentKarat !== '22K') $('#item_purity_karat').val('22K');
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else if (touch >= 99.0 && touch <= 100.0) {
                    if (currentKarat !== '24K') $('#item_purity_karat').val('24K');
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else {
                    $('#item_purity_karat').val('');
                    $('#item_metal_touch').prop('readonly', false).css({
                        'background-color': '',
                        'cursor': ''
                    });
                }
            } else {
                // Silver
                let found = (window.silverKaratsData || []).find(item => Math.abs(parseFloat(item.purity_value) - touch) < 0.5);
                if (found) {
                    if (currentKarat !== found.title) $('#item_purity_karat').val(found.title);
                    $('#item_metal_touch').prop('readonly', true).css({
                        'background-color': '#f1f5f9',
                        'cursor': 'not-allowed'
                    });
                } else {
                    $('#item_purity_karat').val('');
                    $('#item_metal_touch').prop('readonly', false).css({
                        'background-color': '',
                        'cursor': ''
                    });
                }
            }
        }

        $('#item_net_wt').val(formatIndianNumber(net.toFixed(3)));

        let fine = (window.fineCalculationMethod === 'tunch_plus_wastage' || window.fineCalculationMethod === 'wastage_only')
            ? wastage
            : ((net * (touch / 100)) + wastage);
        $('#item_fine_wt').val(formatIndianNumber(fine.toFixed(3)));
    });

    function clipDecimals(val, decimals) {
        if (!val || !val.includes('.')) return val;
        let parts = val.split('.');
        if (parts[1].length > decimals) {
            return parts[0] + '.' + parts[1].substring(0, decimals);
        }
        return val;
    }

    function sanitizeNumericInput(val) {
        if (!val && val !== '0') return '';
        
        // Remove commas and strictly strip any character that is NOT a number or a decimal point
        let str = val.toString().replace(/,/g, '').replace(/[^0-9.]/g, '');
        
        // Prevent multiple decimal points
        if (str.split('.').length > 2) {
            str = str.substring(0, str.lastIndexOf('.'));
        }

        // Handle .255 -> 0.255
        if (str.startsWith('.')) str = '0' + str;
        
        // Handle leading zeros: 0255 -> 255
        if (str.length > 1 && str.startsWith('0') && str[1] !== '.') {
            str = str.replace(/^0+/, '');
            if (str === '') str = '0';
        }
        return str;
    }

    function sanitizeIntegerInput(val) {
        if (!val && val !== '0') return '';
        
        // Strictly strip any character that is NOT a number
        let str = val.toString().replace(/,/g, '').replace(/[^0-9]/g, '');
        
        // Handle leading zeros: 0255 -> 255
        if (str.length > 1 && str.startsWith('0')) {
            str = str.replace(/^0+/, '');
            if (str === '') str = '0';
        }
        return str;
    }

    function sanitizeTouchInput(val) {
        if (!val && val !== '0') return '';
        let str = val.toString().replace(/,/g, '').replace(/[^0-9.]/g, '');
        if (str.split('.').length > 2) str = str.substring(0, str.lastIndexOf('.'));
        if (str.includes('.')) {
            let parts = str.split('.');
            if (parts[1].length > 1) str = parts[0] + '.' + parts[1].substring(0, 1);
        }
        if (str.startsWith('0') && str.length > 1 && str[1] !== '.') {
            str = str.replace(/^0+/, '');
            if (str === '') str = '0';
        }
        return str;
    }

    // Global Metal Rate Update & Live Preview Triggers
    $(document).on('input change', '#item_qty, #metal_rate_global, #misc_charges, #item_gross_wt, #item_less_wt, #item_wastage_per, #item_wastage_percent, #item_metal_touch, #item_making, #item_labor, #item_making_type', function () {
        calculateFinalTotal();
    });


    
    $(document).on('input', '#item_labor', function () {
        let val = sanitizeIntegerInput($(this).val());
        $(this).val(val);
        handleInputFormatting(this, 0);
    });

   
    $(document).on('input', '#item_making', function () {
        // let type = $('#item_making_type').val();
        // if (type === 'fixed') {
            let val = sanitizeIntegerInput($(this).val());
            $(this).val(val);
            handleInputFormatting(this, 0);
        // } else {
        //     let val = sanitizeNumericInput($(this).val());
        //     val = clipDecimals(val, 2);
        //     $(this).val(val);
        //     handleInputFormatting(this, 2);
        // }
    });

    
    $(document).on('change', '#item_making_type', function () {
        if ($(this).val() === 'fixed') {
            let currentVal = $('#item_making').val();
            let intVal = sanitizeIntegerInput(currentVal);
            $('#item_making').val(intVal);
            handleInputFormatting($('#item_making')[0], 0);
        }
        calculateFinalTotal();
    });

   
    $(document).on('input', 'input[name="cash_adv"], input[name="online_adv"], input[name="metal_adv"], input[name="gold_adv_wt_main"], input[name="silver_adv_wt_main"], #metal_rate_global, #misc_charges', function () {
        let name = $(this).attr('name');
        let id = $(this).attr('id');

        // Block advance payments if there are no items and no gross/net weight
        if (name === 'cash_adv' || name === 'online_adv' || name === 'metal_adv' || name === 'gold_adv_wt_main' || name === 'silver_adv_wt_main') {
            let hasItems = items && items.length > 0;
            let currentItemGross = parseFloat($('#item_gross_wt').val()) || 0;
            let currentItemNet = parseFloat($('#item_net_wt').val()) || 0;
            let finalPayable = parseIndianNumber($('#total_payable').val()) || 0;
            if (!hasItems && currentItemGross <= 0 && currentItemNet <= 0 && finalPayable <= 0) {
                $(this).val('');
                if (typeof toastr !== 'undefined') {
                    if (!$(this).data('warning-shown')) {
                        toastr.warning("No payment needed!");
                        $(this).data('warning-shown', true);
                        setTimeout(() => { $(this).removeData('warning-shown'); }, 2000);
                    }
                }
                // showFieldError(this, 'No payment needed! Add order items first');
                calculateFinalTotal();
                return;
            }
        }

        let val = sanitizeNumericInput($(this).val());
        val = val.replace(/[^0-9.]/g, '');
        if (val.split('.').length > 2) val = val.substring(0, val.lastIndexOf('.'));
        
        if (name === 'cash_adv' || name === 'online_adv' || id === 'misc_charges') {
            let intVal = sanitizeIntegerInput($(this).val());
            // If it starts with 0, strip it!
            intVal = intVal.replace(/^0+/, '');
            $(this).val(intVal);
            handleInputFormatting(this, 0);
        } else if (name === 'metal_adv' || name === 'gold_adv_wt_main' || name === 'silver_adv_wt_main') {
            let val = sanitizeNumericInput($(this).val());
            // If it starts with 0 and not 0., strip leading 0s
            if (val.startsWith('0') && val.length > 1 && val[1] !== '.') {
                val = val.replace(/^0+/, '');
            } else if (val === '0') {
                val = '';
            }
            // Strip any starting dot
            if (val.startsWith('.')) {
                val = '0' + val;
            }
            val = clipDecimals(val, 3);
            $(this).val(val);
            handleInputFormatting(this, 3);
        } else {
            if (id === 'metal_rate_global') {
                let rate = parseFloat(val) || 0;
                let metalType = $('#item_metal_type').val().toLowerCase();
                const category = metalType === 'gold' ? 'Gold' : 'Silver';
                const metalKey = category === 'Gold' ? '1' : '2';
                
                let karatVal = $('#item_purity_karat').val();
                if (!karatVal) {
                    karatVal = (category === 'Gold') ? '24K' : '999';
                }

                if (typeof window.checkRateLimit === 'function') {
                    let unit = window.getMetalRateUnit(category.toLowerCase(), karatVal);

                    let typeOfRate = 1;
                    if (category === 'Gold') {
                        if (unit === 1) typeOfRate = 1;
                        else if (unit === 10) typeOfRate = 2;
                        else if (unit > 11 && unit < 12) typeOfRate = 3;
                        else typeOfRate = 2;
                    } else {
                        if (unit === 1) typeOfRate = 1;
                        else if (unit === 1000) typeOfRate = 4;
                        else typeOfRate = 4;
                    }

                    const limitResult = window.checkRateLimit(metalKey, rate, karatVal, typeOfRate);

                    if (!limitResult.valid) {
                        const maxLimit = parseFloat(limitResult.max) || 0;
                        const isOverMax = (maxLimit > 0 && rate > maxLimit);

                        if (isOverMax) {
                            val = String(Math.round(maxLimit));
                            if (typeof toastr !== 'undefined') {
                                toastr.warning("Rate out of limit range! Capped to maximum rate: " + formatIndianNumber(maxLimit));
                            }
                        }
                    }
                }
            }
            $(this).val(val);
            handleInputFormatting(this);
        }

        if ($(this).attr('id') === 'metal_rate_global') {
            $(this).css('border-color', '');
        }
    });

    $(document).on('blur', '#metal_rate_global', function (e) {
        let val = $(this).val();
        let rate = parseFloat((val || '').toString().replace(/[^\d.]/g, '')) || 0;
        let metalType = $('#item_metal_type').val().toLowerCase();
        const category = metalType === 'gold' ? 'Gold' : 'Silver';
        const metalKey = category === 'Gold' ? '1' : '2';
        
        let karatVal = $('#item_purity_karat').val();
        if (!karatVal) {
            karatVal = (category === 'Gold') ? '24K' : '999';
        }

        if (typeof window.checkRateLimit === 'function') {
            let unit = window.getMetalRateUnit(category.toLowerCase(), karatVal);

            let typeOfRate = 1;
            if (category === 'Gold') {
                if (unit === 1) typeOfRate = 1;
                else if (unit === 10) typeOfRate = 2;
                else if (unit > 11 && unit < 12) typeOfRate = 3;
                else typeOfRate = 2;
            } else {
                if (unit === 1) typeOfRate = 1;
                else if (unit === 1000) typeOfRate = 4;
                else typeOfRate = 4;
            }

            const limitResult = window.checkRateLimit(metalKey, rate, karatVal, typeOfRate);

            if (!limitResult.valid) {
                const maxLimit = parseFloat(limitResult.max) || 0;
                const minLimit = parseFloat(limitResult.min) || 0;

                const isOverMax = (maxLimit > 0 && rate > maxLimit);
                const isUnderMin = (minLimit > 0 && rate < minLimit);

                if (isOverMax) {
                    $(this).val(Math.round(maxLimit));
                    $(this).removeClass('is-invalid');
                    $(this).removeData('error-shown');
                    if (typeof toastr !== 'undefined') {
                        toastr.warning("Rate out of limit range! Capped to maximum rate: " + Math.round(maxLimit));
                    }
                    calculateFinalTotal();
                    return;
                } else if (isUnderMin) {
                    let defaultRate = 0;
                    if (typeof window.getMetalRateForPurpose === 'function') {
                        defaultRate = window.getMetalRateForPurpose(category.toLowerCase(), karatVal, 'sell') || 0;
                    }
                    if (defaultRate <= 0) {
                        if (category === 'Gold' && typeof window.metalRates !== 'undefined' && window.metalRates.gold) {
                            defaultRate = window.metalRates.gold * unit;
                        } else if (category === 'Silver' && typeof window.metalRates !== 'undefined' && window.metalRates.silver) {
                            defaultRate = window.metalRates.silver * unit;
                        }
                    }
                    if (defaultRate <= 0) {
                        defaultRate = getRateForKarat(metalType, karatVal);
                    }

                    $(this).val(formatIndianNumber(defaultRate)).trigger('change');
                    $(this).removeClass('is-invalid');
                    $(this).removeData('error-shown');
                    if (typeof toastr !== 'undefined') {
                        toastr.warning("Rate out of limit range! Reset to default " + karatVal + " rate: " + formatIndianNumber(defaultRate));
                    }
                    calculateFinalTotal();
                    return;
                } else {
                    $(this).addClass('is-invalid');
                    if (!$(this).data('error-shown') && typeof toastr !== 'undefined') {
                        toastr.warning(limitResult.title + " rate must be minimum " + Math.round(minLimit) + ".");
                        $(this).data('error-shown', true);
                    }
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).removeData('error-shown');
            }
        }

        $(this).val(formatIndianNumber(rate));
        $(this).css('border-color', '');
        calculateFinalTotal();
    });    

   
    $(document).on('input', '#item_qty', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    
    $(document).on('input', '#item_size', function () {
        let val = $(this).val();
        let str = val.replace(/[^a-zA-Z0-9.\-\/ ]/g, '');
        if (str.split('.').length > 2) {
            str = str.substring(0, str.lastIndexOf('.'));
        }
        if (str.startsWith('.')) {
            str = '0' + str;
        }
        if (str.length > 1 && str.startsWith('0') && str[1] !== '.') {
            str = str.replace(/^0+/, '');
            if (str === '') str = '0';
            if (str.startsWith('.')) str = '0' + str;
        }
        if (str.includes('.')) {
            let parts = str.split('.');
            if (parts[1].length > 2) {
                str = parts[0] + '.' + parts[1].substring(0, 2);
            }
        }
        $(this).val(str);
    });

    // 4. Product Name: Allow alphanumeric and spaces, block special chars
    $(document).on('input', '#item_product_name', function () {
        this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
    });

    $('#add_item_btn').on('click', function () {
        let type = $('#item_jewellery_type').val();
        let name = $('#item_product_name option:selected').text();
        if ($('#item_product_name').val() === '') {
            name = '';
        }
        let gross = parseIndianNumber($('#item_gross_wt').val());
        let net = parseIndianNumber($('#item_net_wt').val());
        let touch = parseIndianNumber($('#item_metal_touch').val());
        let fine = parseIndianNumber($('#item_fine_wt').val());

        clearValidationErrors();
        let hasError = false;
        let firstErrorMsg = '';

        if (!type) {
            let msg = 'Please select a Jewellery Type';
            showFieldError('#item_jewellery_type', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        let karat = $('#item_purity_karat').val();
        if (!karat) {
            let msg = 'Please select a Carat';
            showFieldError('#item_purity_karat', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        let nameVal = $('#item_product_name').val();
        if (!nameVal) {
            let msg = 'Please select a Product';
            showFieldError('#item_product_name', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        if (isNaN(gross) || gross <= 0) {
            let msg = 'Please enter Gross Weight';
            showFieldError('#item_gross_wt', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        if (touch <= 0) {
            let msg = 'Metal Touch % cannot be zero';
            showFieldError('#item_metal_touch', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        let wastageVal = parseIndianNumber($('#item_wastage_per').val());
        let netVal = parseIndianNumber($('#item_net_wt').val());
        if (netVal > 0 && wastageVal > netVal) {
            let msg = 'Wastage cannot exceed Net weight';
            showFieldError('#item_wastage_per', msg, false);
            hasError = true;
            if (!firstErrorMsg) firstErrorMsg = msg;
        }

        if (hasError) {
            if (typeof toastr !== 'undefined' && firstErrorMsg) {
                toastr.error(firstErrorMsg);
            }
            $('.is-invalid').first().focus();
            return;
        }

        let itemData = {
            type: type,
            metal: $('#item_metal_type').val(),
            touch: parseIndianNumber($('#item_metal_touch').val()),
            purity: $('#item_purity_karat').val(),
            name: name,
            qty: $('#item_qty').val(),
            size: $('#item_size').val(),
            gross: gross,
            less: parseIndianNumber($('#item_less_wt').val()),
            net: parseIndianNumber($('#item_net_wt').val()),
            wastage: parseIndianNumber($('#item_wastage_per').val()),
            wastage_per: parseIndianNumber($('#item_wastage_percent').val()),
            fine: parseIndianNumber($('#item_fine_wt').val()),
            making: parseIndianNumber($('#item_making').val()),
            making_type: $('#item_making_type').val(),
            labor: parseIndianNumber($('#item_labor').val()),
            rate: parseIndianNumber($('#metal_rate_global').val()) / window.getMetalRateUnit($('#item_metal_type').val(), $('#item_purity_karat').val()),
            rate_unit: $('#rate_unit_label').text().trim()
        };

        if (editingId) {
            // Update existing item
            let index = items.findIndex(it => it.id === editingId);
            if (index !== -1) {
                items[index] = { ...items[index], ...itemData };
                toastr.success('Item updated successfully');
            }
            editingId = null;
        } else {
            // Add new item
            let newItem = { id: Date.now(), ...itemData };
            items.push(newItem);
        }

        renderItemList();
        resetItemForm();
        calculateFinalTotal();
        $('#item_jewellery_type').focus();
    });

    // ENTER Key support for quick entry
    $(document).on('keypress', '#item_product_name, #item_gross_wt, #item_less_wt, #item_making, #item_labor, #item_size', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#add_item_btn').click();
        }
    });

    function renderItemList() {
        let html = '';
        items.forEach((it, index) => {
            const isEditing = it.id === editingId;
            const isAssigned = it.status === 'in_process';
            
            let rowMaking = 0;
            if (it.making_type === 'per_piece') {
                rowMaking = (it.making * it.qty) + it.labor;
            } else if (it.making_type === 'per_gram') {
                rowMaking = (it.making * it.gross * it.qty) + it.labor;
            } else { // fixed
                rowMaking = it.making + it.labor;
            }
            
            let badgeHtml = '';
            if (isAssigned) {
                const karigarText = it.karigar_name ? `: ${it.karigar_name}` : '';
                badgeHtml = `
                    <div class="mt-1">
                        <span class="badge bg-soft-warning text-warning border px-2 py-0.5 rounded" style="font-size: 0.65rem; font-weight: 800; border-color: #fde68a !important; background-color: #fffbeb !important;">
                            <i class="fa fa-spinner fa-spin mr-1"></i> IN PROCESS (KARIGAR${karigarText})
                        </span>
                    </div>
                `;
            }

            let actionButtonsHtml = '';
            if (isAssigned) {
                const titleText = it.karigar_name ? ` (${it.karigar_name})` : '';
                actionButtonsHtml = `
                    <button type="button" class="btn btn-sm btn-link text-muted mr-2" disabled title="In Process with Karigar${titleText} (Cannot Edit)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted" disabled title="In Process with Karigar${titleText} (Cannot Remove)">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
            } else {
                actionButtonsHtml = `
                    <button type="button" class="btn btn-sm btn-link ${isEditing ? 'text-success' : 'text-primary'} mr-2" onclick="editItem(${it.id})" title="Edit Item">
                        <i class="fa fa-${isEditing ? 'rotate-left' : 'edit'}"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeItem(${it.id})" style="${isEditing ? 'display:none;' : ''}" title="Delete Item">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
            }

            html += `
                    <tr class="align-middle ${isEditing ? 'bg-soft-indigo' : ''}" style="${isEditing ? 'border-left: 4px solid #6366f1;' : ''}">
                        <td class="text-center fw-800 text-muted">${index + 1}</td>
                        <td>
                            <div class="fw-800 text-dark">${it.name} (${it.type})</div>
                            <small class="text-muted">Size: ${it.size} | Qty: ${it.qty}</small>
                            ${badgeHtml}
                        </td>
                        <td class="text-center">
                            <div class="badge ${it.metal === 'GOLD' ? 'bg-warning text-dark' : 'bg-secondary text-white'} px-3 py-1 rounded-pill fw-900">${it.metal}</div>
                             <div class="small fw-900 mt-2 text-indigo">Rate: ₹${formatIndianNumber(it.rate * window.getMetalRateUnit(it.metal, it.purity))} ${it.rate_unit || '/g'}</div>
                            <div class="small fw-800 mt-1 text-muted">${it.touch}% (${it.purity})</div>
                        </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="small text-muted" style="font-size: 0.65rem;">G: ${it.gross.toFixed(3)} | L: ${it.less.toFixed(3)}</div>
                                    <div class="fw-800 text-dark">Net: ${(it.net * it.qty).toFixed(3)}g</div>
                                    <div class="small text-muted" style="font-size: 0.65rem;">Wast: +${(it.wastage * it.qty).toFixed(2)}g</div>
                                    <div class="fw-900 text-success mt-1" style="font-size: 1rem;">Fine: ${(it.fine * it.qty).toFixed(3)}g</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="small text-muted" style="font-size: 0.65rem;">M: ₹${it.making}${it.making_type === 'fixed' ? '(Fixed)' : (it.making_type === 'per_piece' ? '/pc' : '/g')} | L: ₹${it.labor}(Fixed)</div>
                                    <div class="fw-900 text-indigo" style="font-size: 1.1rem;">₹${rowMaking.toLocaleString()}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="small text-muted" style="font-size: 0.65rem;">
                                        Metal: ₹${(it.fine * it.qty * it.rate).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </div>
                                    <div class="fw-900 text-dark" style="font-size: 1.1rem;">
                                        ₹${((it.fine * it.rate * it.qty) + rowMaking).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </div>
                                </div>
                            </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                ${actionButtonsHtml}
                            </div>
                        </td>
                    </tr>
                `;
        });
        $('#item_list_body').html(html);
        $('#item_list_container').toggle(items.length > 0);
        $('#items_json').val(JSON.stringify(items));
    }

    window.removeItem = function (id) {
        let it = items.find(i => i.id === id);
        if (it && it.status === 'in_process') {
            toastr.error('This item is assigned to a Karigar and cannot be removed.');
            return;
        }
        items = items.filter(it => it.id !== id);
        if (editingId === id) {
            resetItemForm();
        }
        renderItemList();
        calculateFinalTotal();
    };

    window.editItem = function (id) {
        let it = items.find(i => i.id === id);
        if (!it) return;
        if (it && it.status === 'in_process') {
            toastr.error('This item is assigned to a Karigar and cannot be edited.');
            return;
        }

        editingId = id;
        isEditingLoading = true;
        
        // Populate dynamic jewellery types (which chains to populateProductNames)
        populateJewelleryTypes(it.metal, it.type, it.name);

        // Load into form
        $('#item_metal_type').val(it.metal).trigger('change.select2');
        let type = it.metal.toLowerCase();
        applyStoredRate(type); // Instant switch
        calculateFinalTotal();

        let normalizedKarat = it.purity || '';
        if (normalizedKarat) {
            if (it.metal === 'GOLD') {
                let match = normalizedKarat.match(/\d+/);
                if (match) {
                    normalizedKarat = match[0] + 'K';
                }
            } else {
                normalizedKarat = String(normalizedKarat).trim();
            }
        }
        let isStandardKarat = false;
        if (it.metal === 'GOLD') {
            isStandardKarat = ['18K', '20K', '22K', '24K'].includes(normalizedKarat);
        } else {
            isStandardKarat = (window.silverKaratsData || []).some(item => String(item.title) === String(normalizedKarat));
        }

        if (isStandardKarat) {
            $('#item_purity_karat').val(normalizedKarat).trigger('change');
            $('#item_metal_touch').val(it.touch).prop('readonly', true).css({
                'background-color': '#f1f5f9',
                'cursor': 'not-allowed'
            }).trigger('input');
        } else {
            $('#item_purity_karat').val('').trigger('change');
            $('#item_metal_touch').val(it.touch).prop('readonly', false).css({
                'background-color': '',
                'cursor': ''
            }).trigger('input');
        }
        // Selected asynchronously by populateProductNames success callback
        
        $('#item_qty').val(it.qty);
        $('#item_size').val(it.size);
        $('#item_gross_wt').val(formatIndianNumber(it.gross)).trigger('input');
        $('#item_fine_wt').val(it.fine.toFixed(3)).trigger('input');
        $('#item_net_wt').val(it.net.toFixed(3)).trigger('input');
        $('#item_less_wt').val(formatIndianNumber(it.less)).trigger('input');
        $('#item_wastage_per').val(formatIndianNumber(it.wastage)).trigger('input');
        $('#item_wastage_percent').val(formatIndianNumber(it.wastage_per)).trigger('input');
        $('#item_making').val(it.making);
        $('#item_making_type').val(it.making_type);
        $('#item_labor').val(it.labor);
        $('#metal_rate_global').val(formatIndianNumber(it.rate * window.getMetalRateUnit(it.metal, it.purity))).trigger('input');

        // Trigger input to update calculations and toggle readonly fields
        $('#item_gross_wt').trigger('input');
        // Update UI Button
        $('#add_item_btn').html('<i class="fa fa-sync-alt mr-1"></i> EDIT ITEM').addClass('btn-success').removeClass('btn-premium-indigo');

        // Refresh list to highlight row
        renderItemList();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $(".order-module").offset().top - 20
        }, 500);
        
        toastr.info('Item loaded for editing');
    };

    function resetItemForm() {
        editingId = null;
        $('#add_item_btn').html('<i class="fa fa-plus-circle mr-1"></i> ADD ITEM').removeClass('btn-success').addClass('btn-premium-indigo');
        
        clearValidationErrors();
        
        $('#item_product_name').val('').trigger('change');
        $('#item_gross_wt, #item_less_wt, #item_net_wt, #item_size, #item_remarks').val('');
        $('#item_qty').val(1);
        $('#item_jewellery_type').val('').trigger('change');
        $('#item_wastage_per').val('');
        $('#item_wastage_percent').val('');
        $('#item_fine_wt').val('0.000');
        $('#item_making').val('0');
        $('#item_making_type').val('per_gram');
        $('#item_labor').val('0');
        
        // Reset Carat & Touch
        $('#item_purity_karat').val('22K').trigger('change');
        
        renderItemList();
        toggleFormFields();
    }

    function resetFullForm() {
        // 1. Clear Items Array
        items = [];
        renderItemList();

        // 2. Reset Item Entry Form
        resetItemForm();

        // 3. Clear Advance Payments & Banks
        $('input[name="cash_adv"], input[name="online_adv"], input[name="metal_adv"]').val('');
        $('select[name="bank_id"]').val('');
        $('select[name="metal_adv_type"]').val('gold');

        // 4. Reset Customer Balance Displays
        $('#cust_cash_bal').text('₹ 0').removeClass('text-danger').addClass('text-muted');
        $('#cust_gold_bal').text('0.000 g');
        $('#cust_silver_bal').text('0.000 g');
        $('#cash_label').text('BALANCE');
        $('#gold_label').text('GOLD');
        $('#silver_label').text('SILVER');

        // 5. Clear Misc Charges & Remarks
        $('#misc_charges').val(0);
        $('input[name="remarks"]').val('');

        // 6. Clear Sketch
        clearSketch();

        // 7. Reset Urgent Status
        $('#is_urgent').val(0);
        $('#urgent_badge').removeClass('active');

        // 8. Regenerate Order ID
        let newOrderId = 'ORD-' + Math.floor(100000 + Math.random() * 900000);
        $('#order_unique').val(newOrderId);
        $('#display_order_id').val(newOrderId);

        // 9. Recalculate Totals
        calculateFinalTotal();

        // Disable form fields since customer is cleared
        toggleFormFields();
    }

    $(document).on('change', '#item_metal_type', function () {
        // Dynamically populate karat dropdown for the new metal
        updateKaratDropdown($(this).val());
        
        populateJewelleryTypes($(this).val());
        
        calculateFinalTotal();
    });

    $(document).on('change', '#item_purity_karat', function () {
        let karat = $(this).val();
        let metalType = $('#item_metal_type').val().toLowerCase();
        let touchField = $('#item_metal_touch');
        
        // Update metal rate label and unit label dynamically based on active unit
        let rates = window.getMetalRates(metalType, karat);
        let unit = 1;
        let unitLabel = 'gm';
        if (rates) {
            unit = parseFloat(rates.unit) || 1;
            if (metalType === 'gold') {
                unitLabel = unit === 1 ? 'gm' : (unit + 'gm');
            } else { // Silver
                unitLabel = unit === 1000 ? 'kg' : (unit === 1 ? 'gm' : (unit + 'gm'));
            }
        } else {
            // Fallback to default
            unit = metalType === 'gold' ? 10 : 1000;
            unitLabel = metalType === 'gold' ? '10gm' : 'kg';
        }
        
        $('#main_metal_label').text(metalType.toUpperCase() + ' RATE (' + unitLabel + ')');
        $('#rate_unit_label').text('/' + unitLabel);
        
        if (!karat) {
            touchField.prop('readonly', false).css({
                'background-color': '',
                'cursor': ''
            });
            return;
        }

        if (metalType === 'gold') {
            let found = (window.goldKaratsData || []).find(item => String(item.title).toUpperCase() === String(karat).toUpperCase() || String(item.karat_code) === String(karat));
            if (found && found.purity_value) {
                touchField.val(parseFloat(found.purity_value)).prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else if (karat === '18K' || karat === '18') {
                touchField.val('72.99').prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else if (karat === '20K' || karat === '20') {
                touchField.val('83.33').prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else if (karat === '22K' || karat === '22') {
                touchField.val('91.67').prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else if (karat === '24K' || karat === '24') {
                touchField.val('100').prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else {
                touchField.prop('readonly', false).css({
                    'background-color': '',
                    'cursor': ''
                });
            }
        } else {
            // Silver
            let found = (window.silverKaratsData || []).find(item => String(item.title) === String(karat));
            if (found && found.purity_value) {
                touchField.val(parseFloat(found.purity_value)).prop('readonly', true).css({
                    'background-color': '#f1f5f9',
                    'cursor': 'not-allowed'
                });
            } else {
                touchField.prop('readonly', false).css({
                    'background-color': '',
                    'cursor': ''
                });
            }
        }

        // Carat-wise rate loading
        if (!isEditingLoading) {
            let rate = getRateForKarat(metalType, karat);
            if (rate > 0) {
                $('#metal_rate_global').val(formatIndianNumber(rate));
            }
        }

        // Recalculate fine weight & totals without cyclic input triggers
        let net = parseIndianNumber($('#item_net_wt').val());
        let wastage = parseIndianNumber($('#item_wastage_per').val());
        let touch = parseIndianNumber(touchField.val());
        let fine = (window.fineCalculationMethod === 'tunch_plus_wastage' || window.fineCalculationMethod === 'wastage_only')
            ? wastage
            : ((net * (touch / 100)) + wastage);
        $('#item_fine_wt').val(fine > 0 ? fine.toFixed(3) : '0.000');
        calculateFinalTotal();
    });

    window.shopMetalDataCache = {};

    $(document).on('change', '#item_jewellery_type', function () {
        let metal = ($('#item_metal_type').val() || 'gold').toLowerCase();
        let jewelleryType = $(this).val();
        if (!isEditingLoading) {
            populateProductNames(metal, jewelleryType);
        }
    });

    $(document).on('change', '#item_product_name', function () {
        if (isEditingLoading) return;
        let val = $(this).val();
        if (!val) return;
        
        // Auto-fill attributes from selected option
        let selectedOpt = $(this).find('option:selected');
        let touch = selectedOpt.data('touch');
        let karat = selectedOpt.data('karat');
        let wastage = selectedOpt.data('wastage');
        let labor = selectedOpt.data('labor');
        
        if (karat !== undefined && karat !== null && String(karat).trim() !== '') {
            let normalizedKarat = karat;
            if (typeof karat === 'string') {
                let match = String(karat).match(/\d+/);
                if (match) {
                    normalizedKarat = match[0] + 'K';
                }
            }
            if (['18K', '20K', '22K', '24K'].includes(normalizedKarat)) {
                $('#item_purity_karat').val(normalizedKarat).trigger('change');
            }
        } else if (touch !== undefined && touch !== null && touch !== '') {
            let numTouch = parseFloat(touch);
            if (numTouch >= 91.0 && numTouch <= 92.5) {
                $('#item_purity_karat').val('22K').trigger('change');
            } else if (numTouch >= 82.5 && numTouch <= 84.5) {
                $('#item_purity_karat').val('20K').trigger('change');
            } else if (numTouch >= 72.0 && numTouch <= 76.0) {
                $('#item_purity_karat').val('18K').trigger('change');
            } else if (numTouch >= 99.0 && numTouch <= 100.0) {
                $('#item_purity_karat').val('24K').trigger('change');
            } else if (!$('#item_purity_karat').val()) {
                $('#item_metal_touch').val(touch);
            }
        }
        if (wastage !== undefined && wastage !== null && wastage !== '') {
            $('#item_wastage_per').val(wastage).trigger('input');
        }
        if (labor !== undefined && labor !== null && labor !== '') {
            $('#item_labor').val(labor).trigger('input');
        }
    });

    function renderProductOptions(products, preselectedValue = null) {
        let select = $('#item_product_name');
        select.empty().append('<option value="">Choose Product...</option>');
        
        if (products && products.length > 0) {
            products.forEach(function(item) {
                let opt = $('<option></option>').val(item.id).text(item.item_name);
                opt.data('touch', item.tounch);
                opt.data('karat', item.karet);
                opt.data('wastage', item.wastage);
                opt.data('labor', item.labour_value);
                select.append(opt);
            });
            
            if (preselectedValue) {
                let matchingOpt = select.find('option').filter(function() {
                    return $(this).text().trim() === String(preselectedValue).trim();
                });
                if (matchingOpt.length > 0) {
                    select.val(matchingOpt.val());
                }
            }
        }
        select.trigger('change.select2');
        isEditingLoading = false;
        
        // Trigger input on gross wt to run all calculations and toggle readonly states
        $('#item_gross_wt').trigger('input');
        toggleFormFields();
    }

    function populateProductNames(metal, jewelleryType, preselectedValue = null) {
        let normalizedMetal = (metal || 'gold').toLowerCase();
        let select = $('#item_product_name');
        
        if (!jewelleryType) {
            select.empty().append('<option value="">Choose Product...</option>').trigger('change.select2');
            return;
        }

        // Instant synchronous loading from in-memory cache if available!
        if (window.shopMetalDataCache[normalizedMetal] && window.shopMetalDataCache[normalizedMetal].products) {
            let products = window.shopMetalDataCache[normalizedMetal].products[jewelleryType] || [];
            renderProductOptions(products, preselectedValue);
            return;
        }

        // Fallback AJAX if not yet cached
        $.ajax({
            url: '{{ route("vendor.orders.create") }}',
            type: 'GET',
            data: {
                metal: normalizedMetal,
                jewellery_type: jewelleryType
            },
            success: function(response) {
                let products = Array.isArray(response) ? response : (response.products ? (response.products[jewelleryType] || []) : []);
                renderProductOptions(products, preselectedValue);
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch products via AJAX:', error);
                isEditingLoading = false;
            }
        });
    }

    function populateJewelleryTypes(metal, preselectedValue = null, preselectedProductValue = null) {
        let normalizedMetal = (metal || 'gold').toLowerCase();
        let select = $('#item_jewellery_type');

        function renderTypes(types, preselectedType, preselectedProduct) {
            select.empty().append('<option value="">Choose Type...</option>');
            if (types && types.length > 0) {
                types.forEach(function(type) {
                    let opt = $('<option></option>').val(type).text(type);
                    select.append(opt);
                });
            }
            select.trigger('change.select2');
            
            if (preselectedType) {
                select.val(preselectedType).trigger('change.select2');
                populateProductNames(normalizedMetal, preselectedType, preselectedProduct);
            } else {
                select.val('').trigger('change.select2');
                $('#item_product_name').empty().append('<option value="">Choose Product...</option>').trigger('change.select2');
            }
        }

        // If cached in memory, render immediately with 0ms delay!
        if (window.shopMetalDataCache[normalizedMetal]) {
            renderTypes(window.shopMetalDataCache[normalizedMetal].types, preselectedValue, preselectedProductValue);
            return;
        }

        $.ajax({
            url: '{{ route("vendor.orders.create") }}',
            type: 'GET',
            data: {
                metal: normalizedMetal
            },
            success: function(response) {
                let types = [];
                let products = {};
                if (response && response.status === 'success') {
                    types = response.types || [];
                    products = response.products || {};
                } else if (Array.isArray(response)) {
                    types = response;
                }
                window.shopMetalDataCache[normalizedMetal] = {
                    types: types,
                    products: products
                };
                renderTypes(types, preselectedValue, preselectedProductValue);
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch jewellery types via AJAX:', error);
                isEditingLoading = false;
            }
        });
    }

    function fetchCurrentRates() {
        $.get('{{ route("vendor.rates.current") }}', function (res) {
            if (res.status === 'success') {
                let goldRate = 0;
                let silverRate = 0;
                if (typeof window.getMetalRateForPurpose === 'function') {
                    let rawGold = window.getMetalRateForPurpose('gold', 24, 'sell');
                    let rawSilver = window.getMetalRateForPurpose('silver', 999, 'sell');
                    if (rawGold > 0 && typeof window.convertMetalRate === 'function') {
                        goldRate = window.convertMetalRate('gold', 24, rawGold);
                    } else {
                        goldRate = rawGold;
                    }
                    if (rawSilver > 0 && typeof window.convertMetalRate === 'function') {
                        silverRate = window.convertMetalRate('silver', 999, rawSilver);
                    } else {
                        silverRate = rawSilver;
                    }
                }
                
                officialGoldRate = (goldRate > 0) ? goldRate : (parseFloat(res.rates.gold) / 10);
                officialSilverRate = (silverRate > 0) ? silverRate : (parseFloat(res.rates.silver) / 1000);

                // Sync initial globals with official rates intelligently
                let savedMetalRate = parseFloat($('#metal_rate_global').val()) || 0;
                if (savedMetalRate > 0) {
                    if (savedMetalRate < 2000) {
                        globalSilverRate = savedMetalRate;
                        globalGoldRate = officialGoldRate;
                    } else {
                        globalGoldRate = savedMetalRate;
                        globalSilverRate = officialSilverRate;
                    }
                } else {
                    globalGoldRate = officialGoldRate;
                    globalSilverRate = officialSilverRate;
                }

                applyStoredRate();

                // Initialize dynamic karat dropdown and fetch appropriate rate
                let initialMetal = $('#item_metal_type').val() || 'GOLD';
                let initialKarat = null;
                const isEdit = $('#is_edit_mode').val() === '1';
                if (isEdit && typeof items !== 'undefined' && items.length > 0) {
                    initialMetal = items[0].metal;
                    initialKarat = items[0].purity;
                    $('#item_metal_type').val(initialMetal.toUpperCase());
                }
                updateKaratDropdown(initialMetal, initialKarat);
            }
        });
    }

    function getActiveRateForMetal(metalType, karat) {
        const metal = metalType.toLowerCase();
        let karatStr = karat ? String(karat).toUpperCase().trim() : '';
        if (!karatStr) {
            karatStr = (metal === 'gold') ? '24K' : '999';
        }

        let unitVal = 1;
        let rate = 0;

     
        if (typeof window.getMetalRates === 'function') {
            const rates = window.getMetalRates(metal, karatStr);
            if (rates) {
                unitVal = parseFloat(rates.unit) || (metal === 'gold' ? 10 : 1);
                rate = parseFloat(rates.sell_rate) || 0;
            }
        }

        // 2. If not found or 0, fallback to default rate from window.metalRates
        if (rate <= 0) {
            if (metal === 'gold') {
                rate = (window.metalRates && window.metalRates.gold) ? window.metalRates.gold : 0;
                unitVal = 10;
            } else {
                rate = (window.metalRates && window.metalRates.silver) ? window.metalRates.silver : 0;
                unitVal = 1;
            }
        }

        return {
            rate: rate,
            unit: unitVal,
            perGramRate: rate / unitVal
        };
    }

    function getRateForKarat(metalType, karat) {
        const activeRateObj = getActiveRateForMetal(metalType, karat);
        return activeRateObj.rate;
    }

    function updateKaratDropdown(metalType, selectedKaratVal = null) {
        let select = $('#item_purity_karat');
        let currentSelected = selectedKaratVal || select.val();
        select.empty().append('<option value="">-</option>');
        
        let type = String(metalType).toLowerCase();
        if (type === 'gold') {
            let data = (window.goldKaratsData && window.goldKaratsData.length > 0) ? window.goldKaratsData : [
                { title: '24K', karat_code: 24, purity_value: '100.00', is_default: 0 },
                { title: '22K', karat_code: 22, purity_value: '91.67', is_default: 1 },
                { title: '20K', karat_code: 20, purity_value: '83.33', is_default: 0 },
                { title: '18K', karat_code: 18, purity_value: '72.99', is_default: 0 }
            ];
            let defaultKarat = '';
            data.forEach(function(item) {
                let opt = $('<option></option>').val(item.title).text(item.title);
                opt.data('purity', item.purity_value);
                if (item.is_default == 1) {
                    defaultKarat = item.title;
                }
                select.append(opt);
            });
            let valToSet = currentSelected || defaultKarat || '22K';
            if (!select.find(`option[value="${valToSet}"]`).length) {
                valToSet = defaultKarat || '22K';
            }
            select.val(valToSet).trigger('change');
        } else {
            let data = (window.silverKaratsData && window.silverKaratsData.length > 0) ? window.silverKaratsData : [
                { title: '999', karat_code: 999, purity_value: '100.00', is_default: 1 },
                { title: '925', karat_code: 925, purity_value: '92.50', is_default: 0 },
                { title: '800', karat_code: 800, purity_value: '80.00', is_default: 0 }
            ];
            let defaultKarat = '';
            data.forEach(function(item) {
                let purity = parseFloat(item.purity_value) || 0;
                let caratVal = (purity * 24) / 100;
                let caratStr = (caratVal % 1 === 0) ? caratVal.toFixed(0) : caratVal.toFixed(1);
                let displayText = item.title + ' (' + caratStr + ' Carat)';
                
                let opt = $('<option></option>').val(item.title).text(displayText);
                opt.data('purity', item.purity_value);
                if (item.is_default == 1) {
                    defaultKarat = item.title;
                }
                select.append(opt);
            });
            let valToSet = currentSelected || defaultKarat || '999';
            if (!select.find(`option[value="${valToSet}"]`).length) {
                valToSet = defaultKarat || '999';
            }
            select.val(valToSet).trigger('change');
        }
    }

    function applyStoredRate(type = null) {
        if (!type) type = $('#item_metal_type').val().toLowerCase();
        let karat = $('#item_purity_karat').val();
        let unit = window.getMetalRateUnit(type, karat);
        
        let rate = (type === 'gold') ? (globalGoldRate * unit) : (globalSilverRate * unit);

        // Glitch Protection: If current input is exactly the wrong metal's rate, reset to official
        let currentVal = parseIndianNumber($('#metal_rate_global').val());
        let wrongType = (type === 'gold') ? 'silver' : 'gold';
        let wrongKarat = (type === 'gold') ? '999' : '22K';
        let wrongUnit = window.getMetalRateUnit(wrongType, wrongKarat);
        let wrongOfficial = (type === 'gold') ? (officialSilverRate * wrongUnit) : (officialGoldRate * wrongUnit);

        if (currentVal === 0 || currentVal === wrongOfficial || !rate) {
            let fetchedRate = getRateForKarat(type, karat);
            rate = fetchedRate;
            if (type === 'gold') globalGoldRate = rate / unit;
            else globalSilverRate = rate / unit;
        }

        $('#metal_rate_global').val(formatIndianNumber(rate));
        calculateFinalTotal();
    }

    function calculateFinalTotal() {
        let currentRate = parseIndianNumber($('#metal_rate_global').val());
        let metalType = $('#item_metal_type').val().toLowerCase();
        let karat = $('#item_purity_karat').val();
        let unit = window.getMetalRateUnit(metalType, karat);
        let perGramRate = currentRate / unit;

        if (metalType === 'gold') globalGoldRate = perGramRate;
        else globalSilverRate = perGramRate;

        let totalFine = 0;
        let totalMakingAndMetalValue = 0;
        let totalMakingOnly = 0;
        let totalQty = 0;

        items.forEach(it => {
            if (editingId && it.id === editingId) return; // Exclude current editing item to prevent double counting
            
            let qty = parseInt(it.qty) || 1;
            totalFine += it.fine * qty;
            let itemMetalValue = (it.fine * (it.rate || 0));
            let itemMakingTotal = 0;
            if (it.making_type === 'per_piece') {
                itemMakingTotal = (it.making * qty) + it.labor;
            } else if (it.making_type === 'per_gram') {
                itemMakingTotal = (it.making * it.gross * qty) + it.labor;
            } else { // fixed
                itemMakingTotal = it.making + it.labor;
            }
            totalMakingAndMetalValue += (itemMetalValue * qty) + itemMakingTotal;
            totalMakingOnly += itemMakingTotal;
            totalQty += qty;
        });

        let currentGross = parseIndianNumber($('#item_gross_wt').val());
        let currentNet = parseIndianNumber($('#item_net_wt').val());
        let currentMakingTotal = 0;
        let currentMetalValue = 0;

        if (currentGross > 0) {
            let currentFine = parseIndianNumber($('#item_fine_wt').val());
            let currentMakingVal = parseIndianNumber($('#item_making').val());
            let currentMakingType = $('#item_making_type').val();
            let currentLabor = parseIndianNumber($('#item_labor').val());
            let currentQty = parseInt($('#item_qty').val()) || 1;

            totalFine += currentFine * currentQty;
            currentMetalValue = currentFine * perGramRate * currentQty;
            
            if (currentMakingType === 'per_piece') {
                currentMakingTotal = (currentMakingVal * currentQty) + (currentLabor * currentQty);
            } else if (currentMakingType === 'per_gram') {
                currentMakingTotal = (currentMakingVal * currentGross * currentQty) + (currentLabor * currentQty);
            } else { // fixed
                currentMakingTotal = currentMakingVal + (currentLabor * currentQty);
            }
            totalQty += currentQty;
        }

        $('#total_items_display').val(formatIndianNumber(totalQty, 0));
        $('#total_fine_display').val(formatIndianNumber(totalFine.toFixed(3)));
        $('#total_making_charges').val(formatIndianNumber((totalMakingOnly + currentMakingTotal).toFixed(2)));

        let finalPayable = totalMakingAndMetalValue + currentMetalValue + currentMakingTotal;
        let misc = parseIndianNumber($('#misc_charges').val());
        finalPayable += misc;

        $('#total_payable').val(finalPayable.toFixed(2));
        $('#total_payable_display').text('₹ ' + finalPayable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        let cashAdv = parseIndianNumber($('input[name="cash_adv"]').val());
        let onlineAdv = parseIndianNumber($('input[name="online_adv"]').val());
        let metalAdvType = $('#metal_adv_type').val() || 'gold';

        let goldAdvValue = parseIndianNumber($('#gold_adv_amount').val());
        let goldAdvWt = parseIndianNumber($('#gold_adv_fine').val()) || parseIndianNumber($('#gold_adv_wt_main').val()) || (metalAdvType === 'gold' ? parseIndianNumber($('#metal_adv').val()) : 0);
        
        let silverAdvValue = parseIndianNumber($('#silver_adv_amount').val());
        let silverAdvWt = parseIndianNumber($('#silver_adv_fine').val()) || parseIndianNumber($('#silver_adv_wt_main').val()) || (metalAdvType === 'silver' ? parseIndianNumber($('#metal_adv').val()) : 0);

        let advGoldRateGram = 0;
        let advSilverRateGram = 0;
        
        if (typeof window.getMetalRates === 'function') {
            const goldRatesObj = window.getMetalRates('gold', '24K');
            if (goldRatesObj) {
                advGoldRateGram = (parseFloat(goldRatesObj.purchase_rate) || parseFloat(goldRatesObj.sell_rate) || 0) / (parseFloat(goldRatesObj.unit) || 10);
            }
            const silverRatesObj = window.getMetalRates('silver', '999');
            if (silverRatesObj) {
                advSilverRateGram = (parseFloat(silverRatesObj.purchase_rate) || parseFloat(silverRatesObj.sell_rate) || 0) / (parseFloat(silverRatesObj.unit) || 1);
            }
        }
        
        if (advGoldRateGram <= 0) {
            const goldRate10gm = (typeof window.metalRates !== 'undefined' && window.metalRates.gold) ? window.metalRates.gold : 0;
            advGoldRateGram = goldRate10gm > 0 ? (goldRate10gm / 10) : globalGoldRate;
        }
        if (advSilverRateGram <= 0) {
            advSilverRateGram = (typeof window.metalRates !== 'undefined' && window.metalRates.silver) ? window.metalRates.silver : globalSilverRate;
        }

        if (goldAdvValue <= 0 && goldAdvWt > 0) {
            goldAdvValue = Math.round(goldAdvWt * advGoldRateGram);
        }
        if (silverAdvValue <= 0 && silverAdvWt > 0) {
            silverAdvValue = Math.round(silverAdvWt * advSilverRateGram);
        }

        if (goldAdvWt > 0 && silverAdvWt > 0 && metalAdvType !== 'both') {
            metalAdvType = 'both';
            $('#metal_adv_type').val('both');
            $('#metal_adv').addClass('d-none');
            $('#metal_adv_dual_inputs').removeClass('d-none');
            $('#metal_adv_tabs_nav').removeClass('d-none');
            $('.metal-both-nav-btn-wrapper').removeClass('d-none');
        }

        let totalMetalAdvValue = 0;
        if (metalAdvType === 'gold') {
            totalMetalAdvValue = goldAdvValue;
            if (goldAdvWt > 0 && !$('#metal_adv').is(':focus')) {
                $('#metal_adv').val(goldAdvWt.toFixed(3));
            }
        } else if (metalAdvType === 'silver') {
            totalMetalAdvValue = silverAdvValue;
            if (silverAdvWt > 0 && !$('#metal_adv').is(':focus')) {
                $('#metal_adv').val(silverAdvWt.toFixed(3));
            }
        } else { // both
            totalMetalAdvValue = goldAdvValue + silverAdvValue;
            if (goldAdvWt > 0 && !$('#gold_adv_wt_main').is(':focus')) {
                $('#gold_adv_wt_main').val(goldAdvWt.toFixed(3));
            }
            if (silverAdvWt > 0 && !$('#silver_adv_wt_main').is(':focus')) {
                $('#silver_adv_wt_main').val(silverAdvWt.toFixed(3));
            }
            let combinedWt = goldAdvWt + silverAdvWt;
            if (combinedWt > 0 && !$('#metal_adv').is(':focus')) {
                $('#metal_adv').val(combinedWt.toFixed(3));
            }
        }

        $('#total_metal_adv_amount_summary').text('₹ ' + totalMetalAdvValue.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

        let totalAdvance = cashAdv + onlineAdv + totalMetalAdvValue;
        let balanceDue = finalPayable - totalAdvance;

        $('#advance_total_text').text('₹ ' + totalAdvance.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        
        let displayDue = balanceDue;
        if (balanceDue < 0) {
            displayDue = Math.abs(balanceDue);
            $('#balance_due_label').text('Advance').removeClass('text-danger').addClass('text-success');
            $('#balance_due_display').removeClass('text-danger').addClass('text-success');
        } else {
            $('#balance_due_label').text('Balance Due').removeClass('text-success').addClass('text-danger');
            $('#balance_due_display').removeClass('text-success').addClass('text-danger');
        }

        $('#balance_due_display').text('₹ ' + displayDue.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        $('#balance_due').val(balanceDue.toFixed(2));

        // Re-render item list to keep table rates and values dynamically updated
        if (typeof renderItemList === 'function') {
            renderItemList();
        }

        if (typeof updateMetalAdvTabBadges === 'function') {
            updateMetalAdvTabBadges();
        }
    }

    $(document).on('input', 'input[name="cash_adv"], input[name="online_adv"], input[name="metal_adv"], input[name="gold_adv_wt_main"], input[name="silver_adv_wt_main"], #metal_rate_global', calculateFinalTotal);
    $(document).on('change', 'select[name="metal_adv_type"]', calculateFinalTotal);
    $(document).on('input', '#misc_charges', calculateFinalTotal);

    // Validate that order items exist before allowing advance payments
    $(document).on('focus input click', 'input[name="cash_adv"], input[name="online_adv"], input[name="metal_adv"], input[name="gold_adv_wt_main"], input[name="silver_adv_wt_main"]', function(e) {
        let hasItems = items && items.length > 0;
        let currentItemGross = parseFloat($('#item_gross_wt').val()) || 0;
        let currentItemNet = parseFloat($('#item_net_wt').val()) || 0;
        let finalPayable = parseIndianNumber($('#total_payable').val()) || 0;

        if (!hasItems && currentItemGross <= 0 && currentItemNet <= 0 && finalPayable <= 0) {
            let val = $(this).val();
            if (val && parseFloat(val) > 0) {
                $(this).val('');
                calculateFinalTotal();
            }
            if (e.type === 'focus' || e.type === 'click' || (e.type === 'input' && val)) {
                if (typeof toastr !== 'undefined') {
                    if (!$(this).data('adv-warn-shown')) {
                        toastr.warning("No payment needed!");
                        $(this).data('adv-warn-shown', true);
                        setTimeout(() => { $(this).removeData('adv-warn-shown'); }, 2000);
                    }
                }
                // showFieldError(this, 'No payment needed! Add order items first');
            }
        }
    });

    $('#design_file').on('change', function () {
        const file = this.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                Swal.fire('Error', 'Please upload only JPG or PNG images', 'error');
                $(this).val('');
                $('#image_preview_container').fadeOut();
                return;
            }

            let reader = new FileReader();
            reader.onload = function (e) {
                const canvas = document.getElementById('sketch_canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = function () {
                    const size = 100;
                    canvas.width = size;
                    canvas.height = size;
                    const scale = Math.min(size / img.width, size / img.height);
                    const x = (size / 2) - (img.width / 2) * scale;
                    const y = (size / 2) - (img.height / 2) * scale;
                    ctx.clearRect(0, 0, size, size);
                    ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
                    $('#image_preview_container').fadeIn();
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    function clearSketch() {
        $('#design_file').val('');
        $('#image_preview_container').fadeOut();
        const canvas = document.getElementById('sketch_canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    function resetSubmitButton() {
        const isEdit = $('#is_edit_mode').val() === '1';
        const btnText = isEdit ? 'Update Order' : 'Create Order';
        const btnIcon = isEdit ? 'save' : 'check';
        $('.btn-premium-confirm').prop('disabled', false).html('<i class="fa fa-' + btnIcon + ' mr-2"></i> ' + btnText);
        $("#full_page_loader").fadeOut(200);
    }

    // Form Submission
    $('#createOrderForm').on('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent global loader and button disabling

        // Disable button immediately to prevent double click
        $('.btn-premium-confirm').prop('disabled', true);

        clearValidationErrors();

        // Smart Save: If there's an item in the form fields, try to add it before submitting
        let name = $('#item_product_name').val();
        let gross = parseIndianNumber($('#item_gross_wt').val());

        if (name || gross > 0) {
            // Manually trigger the add item logic which handles local validation errors
            $('#add_item_btn').click();
            if ($('.is-invalid').length > 0) {
                resetSubmitButton();
                return;
            }
        }

        // Re-check after potential auto-add
        if (items.length === 0) {
            toastr.error('Please add at least one item to the list before saving.');
            showFieldError('#item_gross_wt', 'Item gross weight required');
            resetSubmitButton();
            $('#item_gross_wt').focus();
            return;
        }

        let totalPayableVal = parseIndianNumber($('#total_payable').val());
        if (totalPayableVal <= 0) {
            toastr.error('Total Payable amount cannot be zero.');
            resetSubmitButton();
            return;
        }

        if (!$('#custo_id').val()) {
            toastr.error('Please select a customer');
            showFieldError('#name', 'Please select a customer', false);
            resetSubmitButton();
            $('#name').focus();
            return;
        }

       
        let deliveryDateInput = $('input[name="delivery_date"]');
        let deliveryDateVal = deliveryDateInput.val();
        if (deliveryDateVal) {
            let parts = deliveryDateVal.split('-');
            if (parts.length === 3) {
                let y = parseInt(parts[0], 10);
                if (parts[0].length !== 4 || isNaN(y) || y < 2000 || y > 2099) {
                    toastr.error('Delivery date year must be a valid 4-digit year (e.g. 2026).');
                    showFieldError('input[name="delivery_date"]', 'Year must be 4 digits', false);
                    resetSubmitButton();
                    return;
                }
            }
        }

       
        let orderDateInput = $('input[name="order_date"]');
        let orderDateVal = orderDateInput.val();
        if (orderDateVal) {
            let parts = orderDateVal.split('-');
            if (parts.length === 3) {
                let y = parseInt(parts[0], 10);
                if (parts[0].length !== 4 || isNaN(y) || y < 2000 || y > 2099) {
                    toastr.error('Order date year must be a valid 4-digit year (e.g. 2026).');
                    showFieldError('input[name="order_date"]', 'Year must be 4 digits', false);
                    resetSubmitButton();
                    return;
                }
            }
        }

        // Bank Selection Validation
        let onlineAdv = parseIndianNumber($('input[name="online_adv"]').val());
        let bankId = $('select[name="bank_id"]').val();
        if (onlineAdv > 0 && !bankId) {
            toastr.error('Please select a bank for online advance payment');
            showFieldError('select[name="bank_id"]', 'Please select a bank for the online advance payment.', false);
            resetSubmitButton();
            $('select[name="bank_id"]').focus();
            return;
        }

        // Metal Advance Validation
        const currentMetalAdvType = $('#metal_adv_type').val() || 'gold';
        if (currentMetalAdvType === 'both') {
            if (!validateCompleteMetalAdv('gold', true)) {
                resetSubmitButton();
                $('#metal_adv_details_dropdown').show();
                $('.metal-adv-tab-btn.gold-tab').trigger('click');
                return;
            }
            if (!validateCompleteMetalAdv('silver', true)) {
                resetSubmitButton();
                $('#metal_adv_details_dropdown').show();
                $('.metal-adv-tab-btn.silver-tab').trigger('click');
                return;
            }
        } else if (currentMetalAdvType === 'gold') {
            const hasGoldAdv = (parseFloat($('#gold_adv_gross').val()) || 0) > 0 || (parseFloat($('#metal_adv').val()) || 0) > 0 || (parseFloat($('#gold_adv_amount').val()) || 0) > 0;
            if (hasGoldAdv) {
                if (!validateCompleteMetalAdv('gold', true)) {
                    resetSubmitButton();
                    $('#metal_adv_details_dropdown').show();
                    return;
                }
            }
        } else if (currentMetalAdvType === 'silver') {
            const hasSilverAdv = (parseFloat($('#silver_adv_gross').val()) || 0) > 0 || (parseFloat($('#metal_adv').val()) || 0) > 0 || (parseFloat($('#silver_adv_amount').val()) || 0) > 0;
            if (hasSilverAdv) {
                if (!validateCompleteMetalAdv('silver', true)) {
                    resetSubmitButton();
                    $('#metal_adv_details_dropdown').show();
                    return;
                }
            }
        }

        // Check if any item in list has net weight 0
        let zeroNetItem = items.find(it => it.net === 0);
        if (zeroNetItem) {
            toastr.error('One or more items in the list have zero net weight. Please edit or remove them before saving.');
            resetSubmitButton();
            return;
        }

        // Final Metal Rate Range Check
        let globalRate = parseIndianNumber($('#metal_rate_global').val());
        let metalType = $('#item_metal_type').val().toLowerCase();
        const category = metalType === 'gold' ? 'Gold' : 'Silver';
        const metalKey = category === 'Gold' ? '1' : '2';
        let karatVal = $('#item_purity_karat').val();
        if (!karatVal) {
            karatVal = (category === 'Gold') ? '24K' : '999';
        }
        let unit = window.getMetalRateUnit(category.toLowerCase(), karatVal);
        let perGramRate = globalRate / unit;

        if (typeof window.checkRateLimit === 'function') {
            let typeOfRate = 1;
            if (category === 'Gold') {
                if (unit === 1) typeOfRate = 1;
                else if (unit === 10) typeOfRate = 2;
                else if (unit > 11 && unit < 12) typeOfRate = 3;
                else typeOfRate = 2;
            } else {
                if (unit === 1) typeOfRate = 1;
                else if (unit === 1000) typeOfRate = 4;
                else typeOfRate = 4;
            }

            const rateValidation = window.checkRateLimit(metalKey, globalRate, karatVal, typeOfRate);
            if (!rateValidation.valid) {
                let displayMin = parseFloat(rateValidation.min) || 0;
                let displayMax = parseFloat(rateValidation.max) || 0;
                let rateMsg = 'Global Metal Rate is out of range! (' + formatIndianNumber(displayMin) + ' - ' + formatIndianNumber(displayMax) + ')';
                toastr.error(rateMsg);
                showFieldError('#metal_rate_global', rateMsg, false);
                resetSubmitButton();
                return;
            }
        }

        let formData = new FormData(this);
        formData.set('metal_rate_global', perGramRate);
        formData.append('items_json', JSON.stringify(items));

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                const isEdit = $('#is_edit_mode').val() === '1';
                $("#full_page_loader").fadeIn(200);
                $('.btn-premium-confirm').prop('disabled', true);
            },
            success: function (res) {
                if (res.status === 'success') {
                    $("#full_page_loader").fadeOut(200);
                    toastr.success(res.message);
                    
                    Swal.fire({
                        title: 'Order Saved Successfully!',
                        text: "Would you like to print the order receipt now?",
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#b8860b',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa fa-print"></i> Print Receipt',
                        cancelButtonText: 'Go to Order List',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed || result.value) {
                            let printUrl = '{{ route("vendor.orders.print", ":id") }}'.replace(':id', res.order_id);
                            window.location.href = printUrl;
                        } else {
                            window.location.href = '{{ route("vendor.orders.list") }}';
                        }
                    });
                } else {
                    resetSubmitButton();
                    toastr.error(res.message || 'Something went wrong');
                }
            },
            error: function (xhr) {
                resetSubmitButton();
                let msg = 'Something went wrong! Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                }
                toastr.error(msg);
            }
        });
    });

    $(document).on('click', '.select2-add-btn', function (e) {
        e.stopPropagation();
        window.open($(this).attr('href'), '_blank');
    });

    // --- Detailed Metal Advance Calculations and Handlers (Gold + Silver Dual Support) ---
    function normalizeLeadingDot(val) {
        if (typeof val === 'string' && val.startsWith('.')) {
            return '0' + val;
        }
        return val;
    }

    function formatWeightDecimal(val) {
        val = normalizeLeadingDot(val);
        if (val.includes('.')) {
            const parts = val.split('.');
            return parts[0] + '.' + parts[1].slice(0, 3);
        }
        return val;
    }

    function formatTunchDecimal(val) {
        val = normalizeLeadingDot(val);
        if (val.includes('.')) {
            const parts = val.split('.');
            val = parts[0] + '.' + parts[1].slice(0, 2);
        }
        if (parseFloat(val) > 100) {
            if (typeof toastr !== 'undefined') {
                toastr.error("Touch cannot be greater than 100");
            }
            val = val.slice(0, -1);
        }
        return val;
    }

    // --- GOLD ADVANCE FUNCTIONS ---
    function toggleGoldAdvCategory() {
        const cat = $('#gold_adv_category').val() || 'kacha';
        if ($('#metal_adv_type').val() === 'gold') {
            $('#metal_adv_category').val(cat);
        }
    }

    function getActiveGoldAdvRateUnit() {
        const storedUnit = parseFloat($('#gold_adv_rate').data('rate-unit'));
        if (storedUnit > 0) {
            return storedUnit;
        }

        const tunchInput = $('#gold_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) 
            ? parseFloat(tunchInput) 
            : 100;
        
        let matchedKarat = null;
        const karatsData = window.goldKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                matchedKarat = karatsData[i].title;
                break;
            }
        }
        let karatToLookup = matchedKarat || '24K';

        if (typeof window.getMetalRates === 'function') {
            const rates = window.getMetalRates('gold', karatToLookup);
            if (rates && rates.unit) {
                return parseFloat(rates.unit) || 10;
            }
        }
        if (typeof window.getMetalRateUnit === 'function') {
            return parseFloat(window.getMetalRateUnit('gold', karatToLookup)) || 10;
        }
        return 10;
    }

    function getGoldAdvUnitLabel() {
        const unit = getActiveGoldAdvRateUnit();
        if (unit === 1) return '/1gm';
        if (unit === 10) return '/10gm';
        if (unit > 11 && unit < 12) return '/tola';
        return '/' + unit + 'gm';
    }

    function updateGoldAdvFloatingLabels() {
        const unitLabel = getGoldAdvUnitLabel();
        const rateLabelText = 'Rate (' + unitLabel + ')';
        $('#label_gold_adv_rate').text(rateLabelText);
        $('#gold_adv_rate').attr('placeholder', rateLabelText);

        const fields = [
            { id: '#gold_adv_gross', labelId: '#label_gold_adv_gross' },
            { id: '#gold_adv_net', labelId: '#label_gold_adv_net' },
            { id: '#gold_adv_tunch', labelId: '#label_gold_adv_tunch' },
            { id: '#gold_adv_fine', labelId: '#label_gold_adv_fine' },
            { id: '#gold_adv_rate', labelId: '#label_gold_adv_rate' },
            { id: '#gold_adv_amount', labelId: '#label_gold_adv_amount' }
        ];

        fields.forEach(f => {
            const val = ($(f.id).val() || '').toString().trim();
            const num = parseFloat(val);
            if (val !== '' && !isNaN(num) && num > 0) {
                $(f.labelId).addClass('active');
            } else {
                $(f.labelId).removeClass('active');
            }
        });
    }

    function validateGoldAdvRateLimit() {
        const rateVal = parseInt($('#gold_adv_rate').val(), 10) || 0;
        if (rateVal <= 0) return true;

        const metalCode = '1';
        const unitVal = getActiveGoldAdvRateUnit();
        let rateTypeCode = 2;
        if (unitVal === 1) {
            rateTypeCode = 1;
        } else if (unitVal === 10) {
            rateTypeCode = 2;
        } else if (unitVal > 11 && unitVal < 12) {
            rateTypeCode = 3;
        }

        const tunchInput = $('#gold_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) ? parseFloat(tunchInput) : 100;
        let karatVal = '24K';
        const karatsData = window.goldKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                karatVal = karatsData[i].title;
                break;
            }
        }

        if (typeof window.checkRateLimit === 'function') {
            const limitResult = window.checkRateLimit(metalCode, rateVal, karatVal, rateTypeCode);
            if (!limitResult.valid) {
                const maxLimit = parseFloat(limitResult.max) || 0;
                const minLimit = parseFloat(limitResult.min) || 0;

                if (maxLimit > 0 && rateVal > maxLimit) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(`Gold Rate exceeds maximum limit (₹${maxLimit})! Rate has been reset.`);
                    }
                    $('#gold_adv_rate').val('').addClass('is-invalid');
                    $('#gold_adv_amount').val('');
                    updateGoldAdvFloatingLabels();
                    return false;
                }

                if (minLimit > 0 && rateVal < minLimit) {
                    $('#gold_adv_rate').addClass('is-invalid');
                    return false;
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error(limitResult.msg || `Rate must be between ${limitResult.min} and ${limitResult.max}`);
                }
                $('#gold_adv_rate').addClass('is-invalid');
                return false;
            }
        }
        $('#gold_adv_rate').removeClass('is-invalid');
        return true;
    }

    function calculateGoldAdvFine() {
        const gross = parseFloat($("#gold_adv_gross").val()) || 0;
        let net = parseFloat($("#gold_adv_net").val()) || 0;
        if (net <= 0) net = gross;
        const tunch = parseFloat($("#gold_adv_tunch").val()) || 0;

        let fine = 0;
        if (net > 0) {
            fine = (tunch > 0) ? ((net * tunch) / 100) : net;
        }
        
        const fineValStr = fine > 0 ? fine.toFixed(3) : '';
        $("#gold_adv_fine").val(fineValStr);
        if ($('#metal_adv_type').val() === 'gold') {
            $("#metal_adv").val(fineValStr);
        } else if ($('#metal_adv_type').val() === 'both') {
            $("#gold_adv_wt_main").val(fineValStr);
        }
        calculateGoldAdvCost();
        updateGoldAdvFloatingLabels();
    }

    function calculateGoldAdvCost() {
        const gross = parseFloat($("#gold_adv_gross").val()) || 0;
        let net = parseFloat($("#gold_adv_net").val()) || 0;
        let fine = parseFloat($("#gold_adv_fine").val()) || parseFloat($("#gold_adv_wt_main").val()) || 0;
        if (net <= 0) net = gross;
        
        const rate = parseFloat($("#gold_adv_rate").val()) || 0;
        let unitVal = parseFloat($("#gold_adv_rate").data('rate-unit'));
        if (!unitVal || isNaN(unitVal) || unitVal <= 0) {
            unitVal = getActiveGoldAdvRateUnit();
            $("#gold_adv_rate").data('rate-unit', unitVal);
        }

        const amount = unitVal > 0 ? Math.round(fine * (rate / unitVal)) : 0;
        $("#gold_adv_amount").val(amount > 0 ? amount.toFixed(2) : '');
        calculateFinalTotal();
        if (typeof updateMetalAdvTabBadges === 'function') {
            updateMetalAdvTabBadges();
        }
        updateGoldAdvFloatingLabels();
    }

    function updateGoldAdvRateAndLabel(skipRateOverwrite = false) {
        const tunchInput = $('#gold_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) 
            ? parseFloat(tunchInput) 
            : 100;
        
        let matchedKarat = null;
        const karatsData = window.goldKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                matchedKarat = karatsData[i].title;
                break;
            }
        }

        let activeUnit = 10;
        let activePurRate = 0;
        let activePerGramRate = 0;
        
        let karatToLookup = matchedKarat || '24K';
        if (typeof window.getMetalRates === 'function') {
            const rates = window.getMetalRates('gold', karatToLookup);
            if (rates) {
                activeUnit = parseFloat(rates.unit) || 10;
                activePurRate = parseFloat(rates.purchase_rate) || parseFloat(rates.sell_rate) || 0;
                activePerGramRate = activePurRate / activeUnit;
            }
        }
        if (activePurRate <= 0 && typeof window.getMetalRateForPurpose === 'function') {
            activePurRate = window.getMetalRateForPurpose('gold', karatToLookup, 'purchase') || 0;
            activeUnit = (typeof window.getMetalRateUnit === 'function') ? window.getMetalRateUnit('gold', karatToLookup) : 10;
            activePerGramRate = activePurRate / activeUnit;
        }

        let displayedRate;
        if (matchedKarat) {
            displayedRate = activePurRate;
        } else {
            const tunchAdjustedPerGramRate = activePerGramRate * (tunch / 100);
            displayedRate = tunchAdjustedPerGramRate * activeUnit;
        }
        
        if (!skipRateOverwrite) {
            if (displayedRate > 0) {
                $("#gold_adv_rate").val(Math.round(displayedRate));
            } else {
                $("#gold_adv_rate").val('');
            }
        }

        $("#gold_adv_rate").data('rate-unit', activeUnit);
        $('#gold_adv_rate_amount_container').removeClass('gold silver unit-1 unit-10 unit-1000');
        $('#gold_adv_rate_amount_container').addClass('gold unit-' + (activeUnit === 1 ? '1' : (activeUnit === 10 ? '10' : activeUnit)) + ' active');

        calculateGoldAdvCost();
        updateGoldAdvFloatingLabels();
    }

    // --- SILVER ADVANCE FUNCTIONS ---
    function toggleSilverAdvCategory() {
        const cat = $('#silver_adv_category').val() || 'kacha';
        if ($('#metal_adv_type').val() === 'silver') {
            $('#metal_adv_category').val(cat);
        }
    }

    function getActiveSilverAdvRateUnit() {
        const storedUnit = parseFloat($('#silver_adv_rate').data('rate-unit'));
        if (storedUnit > 0) {
            return storedUnit;
        }

        const tunchInput = $('#silver_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) 
            ? parseFloat(tunchInput) 
            : 100;
        
        let matchedKarat = null;
        const karatsData = window.silverKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                matchedKarat = karatsData[i].title;
                break;
            }
        }
        let karatToLookup = matchedKarat || '999';

        if (typeof window.getMetalRates === 'function') {
            const rates = window.getMetalRates('silver', karatToLookup);
            if (rates && rates.unit) {
                return parseFloat(rates.unit) || 1;
            }
        }
        if (typeof window.getMetalRateUnit === 'function') {
            return parseFloat(window.getMetalRateUnit('silver', karatToLookup)) || 1;
        }
        return 1;
    }

    function getSilverAdvUnitLabel() {
        const unit = getActiveSilverAdvRateUnit();
        if (unit === 1) return '/1gm';
        if (unit === 10) return '/10gm';
        if (unit === 1000) return '/kg';
        return '/' + unit + 'gm';
    }

    function updateSilverAdvFloatingLabels() {
        const unitLabel = getSilverAdvUnitLabel();
        const rateLabelText = 'Rate (' + unitLabel + ')';
        $('#label_silver_adv_rate').text(rateLabelText);
        $('#silver_adv_rate').attr('placeholder', rateLabelText);

        const fields = [
            { id: '#silver_adv_gross', labelId: '#label_silver_adv_gross' },
            { id: '#silver_adv_net', labelId: '#label_silver_adv_net' },
            { id: '#silver_adv_tunch', labelId: '#label_silver_adv_tunch' },
            { id: '#silver_adv_fine', labelId: '#label_silver_adv_fine' },
            { id: '#silver_adv_rate', labelId: '#label_silver_adv_rate' },
            { id: '#silver_adv_amount', labelId: '#label_silver_adv_amount' }
        ];

        fields.forEach(f => {
            const val = ($(f.id).val() || '').toString().trim();
            const num = parseFloat(val);
            if (val !== '' && !isNaN(num) && num > 0) {
                $(f.labelId).addClass('active');
            } else {
                $(f.labelId).removeClass('active');
            }
        });
    }

    function validateSilverAdvRateLimit() {
        const rateVal = parseInt($('#silver_adv_rate').val(), 10) || 0;
        if (rateVal <= 0) return true;

        const metalCode = '2';
        const unitVal = getActiveSilverAdvRateUnit();
        let rateTypeCode = 4;
        if (unitVal === 1) {
            rateTypeCode = 1;
        } else if (unitVal === 1000) {
            rateTypeCode = 4;
        }

        const tunchInput = $('#silver_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) ? parseFloat(tunchInput) : 100;
        let karatVal = '999';
        const karatsData = window.silverKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                karatVal = karatsData[i].title;
                break;
            }
        }

        if (typeof window.checkRateLimit === 'function') {
            const limitResult = window.checkRateLimit(metalCode, rateVal, karatVal, rateTypeCode);
            if (!limitResult.valid) {
                const maxLimit = parseFloat(limitResult.max) || 0;
                const minLimit = parseFloat(limitResult.min) || 0;

                if (maxLimit > 0 && rateVal > maxLimit) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(`Silver Rate exceeds maximum limit (₹${maxLimit})! Rate has been reset.`);
                    }
                    $('#silver_adv_rate').val('').addClass('is-invalid');
                    $('#silver_adv_amount').val('');
                    updateSilverAdvFloatingLabels();
                    return false;
                }

                if (minLimit > 0 && rateVal < minLimit) {
                    $('#silver_adv_rate').addClass('is-invalid');
                    return false;
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error(limitResult.msg || `Rate must be between ${limitResult.min} and ${limitResult.max}`);
                }
                $('#silver_adv_rate').addClass('is-invalid');
                return false;
            }
        }
        $('#silver_adv_rate').removeClass('is-invalid');
        return true;
    }

    function calculateSilverAdvFine() {
        const gross = parseFloat($("#silver_adv_gross").val()) || 0;
        let net = parseFloat($("#silver_adv_net").val()) || 0;
        if (net <= 0) net = gross;
        const tunch = parseFloat($("#silver_adv_tunch").val()) || 0;

        let fine = 0;
        if (net > 0) {
            fine = (tunch > 0) ? ((net * tunch) / 100) : net;
        }
        
        const fineValStr = fine > 0 ? fine.toFixed(3) : '';
        $("#silver_adv_fine").val(fineValStr);
        if ($('#metal_adv_type').val() === 'silver') {
            $("#metal_adv").val(fineValStr);
        } else if ($('#metal_adv_type').val() === 'both') {
            $("#silver_adv_wt_main").val(fineValStr);
        }
        calculateSilverAdvCost();
        updateSilverAdvFloatingLabels();
    }

    function calculateSilverAdvCost() {
        const gross = parseFloat($("#silver_adv_gross").val()) || 0;
        let net = parseFloat($("#silver_adv_net").val()) || 0;
        let fine = parseFloat($("#silver_adv_fine").val()) || parseFloat($("#silver_adv_wt_main").val()) || 0;
        if (net <= 0) net = gross;
        
        const rate = parseFloat($("#silver_adv_rate").val()) || 0;
        let unitVal = parseFloat($("#silver_adv_rate").data('rate-unit'));
        if (!unitVal || isNaN(unitVal) || unitVal <= 0) {
            unitVal = getActiveSilverAdvRateUnit();
            $("#silver_adv_rate").data('rate-unit', unitVal);
        }

        const amount = unitVal > 0 ? Math.round(fine * (rate / unitVal)) : 0;
        $("#silver_adv_amount").val(amount > 0 ? amount.toFixed(2) : '');
        calculateFinalTotal();
        if (typeof updateMetalAdvTabBadges === 'function') {
            updateMetalAdvTabBadges();
        }
        updateSilverAdvFloatingLabels();
    }

    function updateSilverAdvRateAndLabel(skipRateOverwrite = false) {
        const tunchInput = $('#silver_adv_tunch').val();
        const tunch = (tunchInput !== undefined && tunchInput !== '' && parseFloat(tunchInput) > 0) 
            ? parseFloat(tunchInput) 
            : 100;
        
        let matchedKarat = null;
        const karatsData = window.silverKaratsData || [];
        for (let i = 0; i < karatsData.length; i++) {
            if (parseFloat(karatsData[i].purity_value) === tunch) {
                matchedKarat = karatsData[i].title;
                break;
            }
        }

        let activeUnit = 1;
        let activePurRate = 0;
        let activePerGramRate = 0;
        
        let karatToLookup = matchedKarat || '999';
        if (typeof window.getMetalRates === 'function') {
            const rates = window.getMetalRates('silver', karatToLookup);
            if (rates) {
                activeUnit = parseFloat(rates.unit) || 1;
                activePurRate = parseFloat(rates.purchase_rate) || parseFloat(rates.sell_rate) || 0;
                activePerGramRate = activePurRate / activeUnit;
            }
        }
        if (activePurRate <= 0 && typeof window.getMetalRateForPurpose === 'function') {
            activePurRate = window.getMetalRateForPurpose('silver', karatToLookup, 'purchase') || 0;
            activeUnit = (typeof window.getMetalRateUnit === 'function') ? window.getMetalRateUnit('silver', karatToLookup) : 1;
            activePerGramRate = activePurRate / activeUnit;
        }

        let displayedRate;
        if (matchedKarat) {
            displayedRate = activePurRate;
        } else {
            const tunchAdjustedPerGramRate = activePerGramRate * (tunch / 100);
            displayedRate = tunchAdjustedPerGramRate * activeUnit;
        }
        
        if (!skipRateOverwrite) {
            if (displayedRate > 0) {
                $("#silver_adv_rate").val(Math.round(displayedRate));
            } else {
                $("#silver_adv_rate").val('');
            }
        }

        $("#silver_adv_rate").data('rate-unit', activeUnit);
        $('#silver_adv_rate_amount_container').removeClass('gold silver unit-1 unit-10 unit-1000');
        $('#silver_adv_rate_amount_container').addClass('silver unit-' + activeUnit + ' active');

        calculateSilverAdvCost();
        updateSilverAdvFloatingLabels();
    }

    function updateMetalAdvRateAndLabel(skipRateOverwrite = false) {
        const type = $('#metal_adv_type').val() || 'gold';
        if (type === 'gold' || type === 'both') {
            updateGoldAdvRateAndLabel(skipRateOverwrite);
        }
        if (type === 'silver' || type === 'both') {
            updateSilverAdvRateAndLabel(skipRateOverwrite);
        }
    }

    function updateMetalAdvTabBadges() {
        const goldGross = parseFloat($('#gold_adv_gross').val()) || 0;
        const goldFine = parseFloat($('#gold_adv_fine').val()) || parseFloat($('#gold_adv_wt_main').val()) || 0;
        const goldRate = parseFloat($('#gold_adv_rate').val()) || 0;
        const goldTunch = parseFloat($('#gold_adv_tunch').val()) || 0;

        const isGoldActive = !$('#gold_adv_panel').hasClass('d-none');
        if (goldGross > 0 && goldTunch > 0 && goldRate > 0) {
            $('#gold_tab_badge').text('✓ ' + goldFine.toFixed(3) + 'g').removeClass('badge-empty badge-active-gold').addClass('badge-filled');
            $('#gold_panel_status_badge').text('✓ READY').css({'border-color': '#86efac', 'color': '#15803d'});
        } else if (goldGross > 0) {
            $('#gold_tab_badge').text('Incomplete').removeClass('badge-filled badge-active-gold').addClass('badge-empty');
            $('#gold_panel_status_badge').text('INCOMPLETE').css({'border-color': '#fde047', 'color': '#b45309'});
        } else {
            $('#gold_tab_badge').text('0.000g').removeClass('badge-filled').addClass(isGoldActive ? 'badge-active-gold' : 'badge-empty');
            $('#gold_panel_status_badge').text(isGoldActive ? 'ACTIVE' : 'EMPTY').css({'border-color': '#fde047', 'color': '#b45309'});
        }

        const silverGross = parseFloat($('#silver_adv_gross').val()) || 0;
        const silverFine = parseFloat($('#silver_adv_fine').val()) || parseFloat($('#silver_adv_wt_main').val()) || 0;
        const silverRate = parseFloat($('#silver_adv_rate').val()) || 0;
        const silverTunch = parseFloat($('#silver_adv_tunch').val()) || 0;

        const isSilverActive = !$('#silver_adv_panel').hasClass('d-none');
        if (silverGross > 0 && silverTunch > 0 && silverRate > 0) {
            $('#silver_tab_badge').text('✓ ' + silverFine.toFixed(3) + 'g').removeClass('badge-empty badge-active-silver').addClass('badge-filled');
            $('#silver_panel_status_badge').text('✓ READY').css({'border-color': '#86efac', 'color': '#15803d'});
        } else if (silverGross > 0) {
            $('#silver_tab_badge').text('Incomplete').removeClass('badge-filled badge-active-silver').addClass('badge-empty');
            $('#silver_panel_status_badge').text('INCOMPLETE').css({'border-color': '#cbd5e1', 'color': '#64748b'});
        } else {
            $('#silver_tab_badge').text('0.000g').removeClass('badge-filled').addClass(isSilverActive ? 'badge-active-silver' : 'badge-empty');
            $('#silver_panel_status_badge').text(isSilverActive ? 'ACTIVE' : 'EMPTY').css({'border-color': '#cbd5e1', 'color': '#64748b'});
        }

        // Main input visual sync
        if ($('#metal_adv_type').val() === 'both') {
            if (isGoldActive) {
                $('#gold_adv_wt_main').addClass('active-metal-input');
                $('#silver_adv_wt_main').removeClass('active-metal-input');
            } else if (isSilverActive) {
                $('#silver_adv_wt_main').addClass('active-metal-input');
                $('#gold_adv_wt_main').removeClass('active-metal-input');
            }
        }
    }

    function validateCompleteMetalAdv(metalType, showAlert = true) {
        clearValidationErrors();
        if (metalType === 'gold') {
            const gross = parseFloat($('#gold_adv_gross').val()) || 0;
            const tunch = parseFloat($('#gold_adv_tunch').val()) || 0;
            const rate = parseFloat($('#gold_adv_rate').val()) || 0;

            if (gross <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Gold Gross Weight.");
                showFieldError('#gold_adv_gross', 'Gold Gross Weight required');
                $('#gold_adv_gross').focus();
                return false;
            }
            if (tunch <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Gold Touch / Purity %.");
                showFieldError('#gold_adv_tunch', 'Gold Touch % required');
                $('#gold_adv_tunch').focus();
                return false;
            }
            if (rate <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Gold Rate.");
                showFieldError('#gold_adv_rate', 'Gold Rate required');
                $('#gold_adv_rate').focus();
                return false;
            }
            return true;
        } else if (metalType === 'silver') {
            const gross = parseFloat($('#silver_adv_gross').val()) || 0;
            const tunch = parseFloat($('#silver_adv_tunch').val()) || 0;
            const rate = parseFloat($('#silver_adv_rate').val()) || 0;

            if (gross <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Silver Gross Weight.");
                showFieldError('#silver_adv_gross', 'Silver Gross Weight required');
                $('#silver_adv_gross').focus();
                return false;
            }
            if (tunch <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Silver Touch / Purity %.");
                showFieldError('#silver_adv_tunch', 'Silver Touch % required');
                $('#silver_adv_tunch').focus();
                return false;
            }
            if (rate <= 0) {
                if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Silver Rate.");
                showFieldError('#silver_adv_rate', 'Silver Rate required');
                $('#silver_adv_rate').focus();
                return false;
            }
            return true;
        }
        return true;
    }

    function validateMetalAdvSection(metalType, showAlert = true) {
        clearValidationErrors();
        if (metalType === 'gold') {
            const gross = parseFloat($('#gold_adv_gross').val()) || 0;
            const tunch = parseFloat($('#gold_adv_tunch').val()) || 0;
            const rate = parseFloat($('#gold_adv_rate').val()) || 0;

            if (gross > 0) {
                if (tunch <= 0) {
                    if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Gold Touch / Purity %.");
                    showFieldError('#gold_adv_tunch', 'Gold Touch % required');
                    $('#gold_adv_tunch').focus();
                    return false;
                }
                if (rate <= 0) {
                    if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Gold Rate.");
                    showFieldError('#gold_adv_rate', 'Gold Rate required');
                    $('#gold_adv_rate').focus();
                    return false;
                }
            }
            return true;
        } else if (metalType === 'silver') {
            const gross = parseFloat($('#silver_adv_gross').val()) || 0;
            const tunch = parseFloat($('#silver_adv_tunch').val()) || 0;
            const rate = parseFloat($('#silver_adv_rate').val()) || 0;

            if (gross > 0) {
                if (tunch <= 0) {
                    if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Silver Touch / Purity %.");
                    showFieldError('#silver_adv_tunch', 'Silver Touch % required');
                    $('#silver_adv_tunch').focus();
                    return false;
                }
                if (rate <= 0) {
                    if (showAlert && typeof toastr !== 'undefined') toastr.warning("Please enter Silver Rate.");
                    showFieldError('#silver_adv_rate', 'Silver Rate required');
                    $('#silver_adv_rate').focus();
                    return false;
                }
            }
            return true;
        }
        return true;
    }

    // Tab Navigation for BOTH metal mode
    $(document).on('click', '.metal-adv-tab-btn', function(e) {
        e.preventDefault();
        $('#metal_adv_details_dropdown').show();
        const $btn = $(this);
        const targetPanel = $btn.attr('data-tab');

        $('.metal-adv-tab-btn').removeClass('active');
        $btn.addClass('active');
        $('.metal-adv-panel').addClass('d-none');
        $('#' + targetPanel).removeClass('d-none');
        
        if (targetPanel === 'gold_adv_panel') {
            updateGoldAdvRateAndLabel(true);
        } else if (targetPanel === 'silver_adv_panel') {
            updateSilverAdvRateAndLabel(true);
        }
        updateMetalAdvTabBadges();
    });

    $(document).on('click', '.switch-to-silver-btn', function(e) {
        e.preventDefault();
        $('#metal_adv_details_dropdown').show();
        $('.metal-adv-tab-btn.silver-tab').trigger('click');
        setTimeout(() => { $('#silver_adv_gross').focus(); }, 100);
    });

    $(document).on('click', '.switch-to-gold-btn', function(e) {
        e.preventDefault();
        $('#metal_adv_details_dropdown').show();
        $('.metal-adv-tab-btn.gold-tab').trigger('click');
        setTimeout(() => { $('#gold_adv_gross').focus(); }, 100);
    });

    $(document).on('click', '.done-metal-adv-btn', function(e) {
        e.preventDefault();
        const currentMetalType = $('#metal_adv_type').val() || 'gold';
        if (currentMetalType === 'both') {
            if (!validateCompleteMetalAdv('gold', true)) {
                $('.metal-adv-tab-btn.gold-tab').trigger('click');
                return;
            }
            if (!validateCompleteMetalAdv('silver', true)) {
                $('.metal-adv-tab-btn.silver-tab').trigger('click');
                return;
            }
        } else if (currentMetalType === 'gold') {
            if (!validateCompleteMetalAdv('gold', true)) {
                return;
            }
        } else if (currentMetalType === 'silver') {
            if (!validateCompleteMetalAdv('silver', true)) {
                return;
            }
        }
        $('#metal_adv_details_dropdown').hide();
        calculateFinalTotal();
        if (typeof toastr !== 'undefined') {
            toastr.success("Metal advance details recorded!");
        }
    });

    // Sample autocomplete for Gold Jewellery Type
    $('#gold_adv_jewellery_type').on('focus input', function() {
        const query = $(this).val();
        $.get("{{ route('billing.metal.samples') }}", { metal: 'gold', query: query }, function(res) {
            const list = $('#gold_adv_jewellery_sample_list');
            list.empty();
            if (res && res.length > 0) {
                res.forEach((item, index) => {
                    const activeClass = (index === 0) ? 'hover bg-light' : '';
                    list.append(`<li class="dropdown-item gold-adv-sample-item ${activeClass}" data-title="${item.title}" style="cursor: pointer; padding: 6px 16px;">${item.title}</li>`);
                });
                list.show();
            } else {
                list.hide();
            }
        });
    });

    $('#metal_adv_details_dropdown').on('mouseenter', '#gold_adv_jewellery_sample_list li', function() {
        $('#gold_adv_jewellery_sample_list li').removeClass('hover bg-light');
        $(this).addClass('hover bg-light');
    });

    $('#metal_adv_details_dropdown').on('click', '.gold-adv-sample-item', function(e) {
        e.stopPropagation();
        $('#gold_adv_jewellery_type').val($(this).data('title'));
        $('#gold_adv_jewellery_sample_list').hide();
    });

    // Sample autocomplete for Silver Jewellery Type
    $('#silver_adv_jewellery_type').on('focus input', function() {
        const query = $(this).val();
        $.get("{{ route('billing.metal.samples') }}", { metal: 'silver', query: query }, function(res) {
            const list = $('#silver_adv_jewellery_sample_list');
            list.empty();
            if (res && res.length > 0) {
                res.forEach((item, index) => {
                    const activeClass = (index === 0) ? 'hover bg-light' : '';
                    list.append(`<li class="dropdown-item silver-adv-sample-item ${activeClass}" data-title="${item.title}" style="cursor: pointer; padding: 6px 16px;">${item.title}</li>`);
                });
                list.show();
            } else {
                list.hide();
            }
        });
    });

    $('#metal_adv_details_dropdown').on('mouseenter', '#silver_adv_jewellery_sample_list li', function() {
        $('#silver_adv_jewellery_sample_list li').removeClass('hover bg-light');
        $(this).addClass('hover bg-light');
    });

    $('#metal_adv_details_dropdown').on('click', '.silver-adv-sample-item', function(e) {
        e.stopPropagation();
        $('#silver_adv_jewellery_type').val($(this).data('title'));
        $('#silver_adv_jewellery_sample_list').hide();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#gold_adv_jewellery_type, #gold_adv_jewellery_sample_list, #silver_adv_jewellery_type, #silver_adv_jewellery_sample_list').length) {
            $('#gold_adv_jewellery_sample_list, #silver_adv_jewellery_sample_list').hide();
        }
    });

    // Metal Advance Category Change Handlers
    $('#gold_adv_category').on('change', function() {
        toggleGoldAdvCategory();
    });

    $('#silver_adv_category').on('change', function() {
        toggleSilverAdvCategory();
    });

    // Metal Advance Type Change Handler
    $("#metal_adv_type").change(function(e) {
        const type = $(this).val();
        if (type === 'both') {
            $('#metal_adv').addClass('d-none');
            $('#metal_adv_dual_inputs').removeClass('d-none');
            $('#metal_adv_tabs_nav').removeClass('d-none');
            $('.metal-both-nav-btn-wrapper').removeClass('d-none');
            $('.metal-adv-tab-btn.gold-tab').trigger('click');
            toggleGoldAdvCategory();
            toggleSilverAdvCategory();
            updateGoldAdvRateAndLabel(false);
            updateSilverAdvRateAndLabel(false);
        } else if (type === 'gold') {
            $('#metal_adv_dual_inputs').addClass('d-none');
            $('#metal_adv').removeClass('d-none');
            $('#metal_adv_tabs_nav').addClass('d-none');
            $('.metal-both-nav-btn-wrapper').addClass('d-none');
            $('#gold_adv_panel').removeClass('d-none');
            $('#silver_adv_panel').addClass('d-none');
            $('#metal_adv_category').val($('#gold_adv_category').val() || 'kacha');
            // Clear silver
            $('#silver_adv_gross, #silver_adv_net, #silver_adv_tunch, #silver_adv_fine, #silver_adv_wt_main, #silver_adv_amount').val('');
            toggleGoldAdvCategory();
            updateGoldAdvRateAndLabel(false);
        } else if (type === 'silver') {
            $('#metal_adv_dual_inputs').addClass('d-none');
            $('#metal_adv').removeClass('d-none');
            $('#metal_adv_tabs_nav').addClass('d-none');
            $('.metal-both-nav-btn-wrapper').addClass('d-none');
            $('#silver_adv_panel').removeClass('d-none');
            $('#gold_adv_panel').addClass('d-none');
            $('#metal_adv_category').val($('#silver_adv_category').val() || 'kacha');
            // Clear gold
            $('#gold_adv_gross, #gold_adv_net, #gold_adv_tunch, #gold_adv_fine, #gold_adv_wt_main, #gold_adv_amount').val('');
            toggleSilverAdvCategory();
            updateSilverAdvRateAndLabel(false);
        }
        calculateFinalTotal();
    });

    // --- GOLD INPUT EVENTS ---
    $("#gold_adv_tunch").on('input', function(){
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        rawVal = rawVal.replace(/^0+/, '');
        $(this).val(formatTunchDecimal(rawVal));
        calculateGoldAdvFine();
        updateGoldAdvRateAndLabel(false);
    });

    $("#gold_adv_gross").on('input', function(){
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        if (rawVal.startsWith('0') && rawVal.length > 1 && rawVal[1] !== '.') {
            rawVal = rawVal.replace(/^0+/, '');
        } else if (rawVal === '0') {
            rawVal = '';
        }
        const grossValStr = formatWeightDecimal(rawVal);
        $(this).val(grossValStr);
        $("#gold_adv_net").val(grossValStr);
        calculateGoldAdvFine();
    });

    $("#gold_adv_net").on('input', function(){
        const grossStr = $("#gold_adv_gross").val() || false;
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        if (rawVal.startsWith('0') && rawVal.length > 1 && rawVal[1] !== '.') {
            rawVal = rawVal.replace(/^0+/, '');
        } else if (rawVal === '0') {
            rawVal = '';
        }
        let grossVal = parseFloat(grossStr) || 0;
        let netStr = formatWeightDecimal(rawVal);
        let netVal = parseFloat(netStr) || 0;
        if (grossVal > 0 && netVal > grossVal) {
            netStr = grossVal.toString();
        }
        $(this).val(netStr);
        calculateGoldAdvFine();
    });

    $('#gold_adv_rate').on('input change blur', function(){
        let val = $(this).val().replace(/[^0-9]/g, '').replace(/^0+/, '');
        $(this).val(val);
        const effectiveUnit = getActiveGoldAdvRateUnit();
        $(this).data('rate-unit', effectiveUnit);
        $('#gold_adv_rate_amount_container').removeClass('gold silver unit-1 unit-10 unit-1000').addClass('gold unit-' + (effectiveUnit === 1 ? '1' : (effectiveUnit === 10 ? '10' : effectiveUnit)) + ' active');
        validateGoldAdvRateLimit();
        calculateGoldAdvCost();
    });

    $('#gold_adv_wt_main').on('input change', function(){
        let val = $(this).val().replace(/[^0-9.]/g, '');
        if (val.split('.').length > 2) val = val.substring(0, val.lastIndexOf('.'));
        val = formatWeightDecimal(val);
        $(this).val(val);
        $('#gold_adv_gross, #gold_adv_net, #gold_adv_fine').val(val);
        calculateGoldAdvCost();
    });

    // --- SILVER INPUT EVENTS ---
    $("#silver_adv_tunch").on('input', function(){
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        rawVal = rawVal.replace(/^0+/, '');
        $(this).val(formatTunchDecimal(rawVal));
        calculateSilverAdvFine();
        updateSilverAdvRateAndLabel(false);
    });

    $("#silver_adv_gross").on('input', function(){
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        if (rawVal.startsWith('0') && rawVal.length > 1 && rawVal[1] !== '.') {
            rawVal = rawVal.replace(/^0+/, '');
        } else if (rawVal === '0') {
            rawVal = '';
        }
        const grossValStr = formatWeightDecimal(rawVal);
        $(this).val(grossValStr);
        $("#silver_adv_net").val(grossValStr);
        calculateSilverAdvFine();
    });

    $("#silver_adv_net").on('input', function(){
        const grossStr = $("#silver_adv_gross").val() || false;
        let rawVal = $(this).val().replace(/[^0-9.]/g, '');
        if (rawVal.split('.').length > 2) rawVal = rawVal.substring(0, rawVal.lastIndexOf('.'));
        if (rawVal.startsWith('0') && rawVal.length > 1 && rawVal[1] !== '.') {
            rawVal = rawVal.replace(/^0+/, '');
        } else if (rawVal === '0') {
            rawVal = '';
        }
        let grossVal = parseFloat(grossStr) || 0;
        let netStr = formatWeightDecimal(rawVal);
        let netVal = parseFloat(netStr) || 0;
        if (grossVal > 0 && netVal > grossVal) {
            netStr = grossVal.toString();
        }
        $(this).val(netStr);
        calculateSilverAdvFine();
    });

    $('#silver_adv_rate').on('input change blur', function(){
        let val = $(this).val().replace(/[^0-9]/g, '').replace(/^0+/, '');
        $(this).val(val);
        const effectiveUnit = getActiveSilverAdvRateUnit();
        $(this).data('rate-unit', effectiveUnit);
        $('#silver_adv_rate_amount_container').removeClass('gold silver unit-1 unit-10 unit-1000').addClass('silver unit-' + (effectiveUnit === 1 ? '1' : (effectiveUnit === 10 ? '10' : (effectiveUnit === 1000 ? '1000' : effectiveUnit))) + ' active');
        validateSilverAdvRateLimit();
        calculateSilverAdvCost();
        updateSilverAdvFloatingLabels();
    });

    $('#silver_adv_wt_main').on('input change', function(){
        let val = $(this).val().replace(/[^0-9.]/g, '');
        if (val.split('.').length > 2) val = val.substring(0, val.lastIndexOf('.'));
        val = formatWeightDecimal(val);
        $(this).val(val);
        $('#silver_adv_gross, #silver_adv_net, #silver_adv_fine').val(val);
        calculateSilverAdvCost();
        updateSilverAdvFloatingLabels();
    });

    // Single input #metal_adv event handler (when in Gold or Silver mode)
    $('#metal_adv').on('input change', function(){
        const metalType = $('#metal_adv_type').val() || 'gold';
        let val = $(this).val().replace(/[^0-9.]/g, '');
        if (val.split('.').length > 2) val = val.substring(0, val.lastIndexOf('.'));
        val = formatWeightDecimal(val);
        $(this).val(val);
        if (metalType === 'gold') {
            $('#gold_adv_gross, #gold_adv_net, #gold_adv_fine').val(val);
            calculateGoldAdvCost();
            updateGoldAdvFloatingLabels();
        } else if (metalType === 'silver') {
            $('#silver_adv_gross, #silver_adv_net, #silver_adv_fine').val(val);
            calculateSilverAdvCost();
            updateSilverAdvFloatingLabels();
        }
    });

    // Auto-load default rates on page load
    $(document).ready(function() {
        toggleGoldAdvCategory();
        toggleSilverAdvCategory();
        const isEdit = $('#is_edit_mode').val() === '1';
        if (!isEdit) {
            $("#metal_adv_type").trigger('change');
        } else {
            const currentType = $('#metal_adv_type').val();
            if (currentType === 'both') {
                $('#metal_adv').addClass('d-none');
                $('#metal_adv_dual_inputs').removeClass('d-none');
                $('#metal_adv_tabs_nav').removeClass('d-none');
            }
            updateGoldAdvRateAndLabel(true);
            updateSilverAdvRateAndLabel(true);
        }
        if (typeof updateMetalAdvTabBadges === 'function') {
            updateMetalAdvTabBadges();
        }
        updateGoldAdvFloatingLabels();
        updateSilverAdvFloatingLabels();
    });

    // Toggle metal details dropdown
    $('#toggle_metal_adv_details, #metal_adv, #gold_adv_wt_main, #silver_adv_wt_main').on('click focus', function(e) {
        e.stopPropagation();

        let hasItems = items && items.length > 0;
        let currentItemGross = parseFloat($('#item_gross_wt').val()) || 0;
        let currentItemNet = parseFloat($('#item_net_wt').val()) || 0;
        let finalPayable = parseIndianNumber($('#total_payable').val()) || 0;

        if (!hasItems && currentItemGross <= 0 && currentItemNet <= 0 && finalPayable <= 0) {
            $('#metal_adv_details_dropdown').hide();
            $(this).blur();
            if (typeof toastr !== 'undefined') {
                if (!$(this).data('adv-warn-shown')) {
                    toastr.warning("No payment needed!");
                    $(this).data('adv-warn-shown', true);
                    setTimeout(() => { $(this).removeData('adv-warn-shown'); }, 2000);
                }
            }
            // showFieldError(this, 'No payment needed! Add order items first');
            return;
        }

        $('#metal_adv_details_dropdown').show();

        const currentMetalType = $('#metal_adv_type').val() || 'gold';
        if (currentMetalType === 'both') {
            if ($(this).attr('id') === 'silver_adv_wt_main') {
                $('.metal-adv-tab-btn.silver-tab').trigger('click');
            } else if ($(this).attr('id') === 'gold_adv_wt_main') {
                $('.metal-adv-tab-btn.gold-tab').trigger('click');
            } else {
                updateMetalAdvRateAndLabel(true);
            }
        } else {
            updateMetalAdvRateAndLabel(true);
        }
        if (typeof updateMetalAdvTabBadges === 'function') {
            updateMetalAdvTabBadges();
        }
        updateGoldAdvFloatingLabels();
        updateSilverAdvFloatingLabels();
    });

    // Enter key on Gold Rate moves to Silver section in BOTH mode
    $('#gold_adv_rate').on('keydown', function(e) {
        if (e.key === 'Enter' && $('#metal_adv_type').val() === 'both') {
            e.preventDefault();
            $('.switch-to-silver-btn').trigger('click');
        }
    });

    $(document).on('click', '#close_metal_adv_details', function(e) {
        e.preventDefault();
        $('#metal_adv_details_dropdown').hide();
    });

    // Close details dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#metal_adv_details_dropdown, #toggle_metal_adv_details, #metal_adv, #gold_adv_wt_main, #silver_adv_wt_main').length) {
            $('#metal_adv_details_dropdown').hide();
        }
    });

    // Restrict date input year to 4 characters only
    $(document).on('input change blur keyup', 'input[type="date"]', function () {
        let val = $(this).val();
        if (!val) return;

        let parts = val.split('-');
        if (parts.length === 3) {
            let year = parts[0];
            let month = parts[1];
            let day = parts[2];

            if (year.length > 4) {
                year = year.substring(0, 4);
                let corrected = year + '-' + month + '-' + day;
                $(this).val(corrected);
            }

            let numYear = parseInt(year, 10);
            if (numYear > 2099) {
                $(this).val('2099-' + month + '-' + day);
            }
        }
    });

    // Initial floating labels check
    updateGoldAdvFloatingLabels();
    updateSilverAdvFloatingLabels();
</script>