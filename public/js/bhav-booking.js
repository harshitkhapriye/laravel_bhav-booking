/**
 * Bhav Booking UI Logic & Interactivity
 * Application: Just Udhari - Buy-Sell Module
 *
 * Organized in clearly commented sections:
 * 1. CONFIG & STATE
 * 2. HELPERS
 * 3. TOAST
 * 4. CUSTOMER SEARCH
 * 5. ADD CUSTOMER MODAL
 * 6. TRADE TOGGLE (BUY/SELL)
 * 7. MULTI-WEIGHT ENTRIES & CALCULATIONS
 * 8. SAVE & FORM SUBMISSION
 * 9. KEYBOARD NAVIGATION
 * 10. DATE RANGE FILTER & LIST MENU
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // 1. CONFIG & STATE
    // Configuration from backend (window.BHAV_CONFIG) and reactive state
    // =========================================================================

    // Configuration passed from config/bhav.php via Blade view
    const BHAV_CONFIG = window.BHAV_CONFIG || {
        rate_unit_grams: 10,
        default_rates: { GOLD: 165000, SILVER: 1800 },
        max_weight: 99999.999,
        max_rate: 9999999.99
    };

    // Single source of truth for multi-weight entries: array of item objects
    // Each item: { id: number, weight: number, rate: number, amount: number }
    let bookingItems = [];
    let nextItemId = 1;

    // Track current metal to detect changes and prompt confirmation
    let currentMetal = 'GOLD';

    // Unique request token for duplicate-save protection (idempotency)
    let currentRequestToken = generateRequestToken();

    // =========================================================================
    // 2. HELPERS
    // Small utility functions used throughout the application
    // =========================================================================

    /**
     * Generate a UUID v4 string for duplicate save protection
     * Why: Prevents double-creation if user double clicks or network retries
     */
    function generateRequestToken() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        // Fallback for older browsers
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * Escape special HTML characters to prevent Cross-Site Scripting (XSS)
     * Why: Untrusted server data should never be injected directly into DOM
     */
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Safely highlight matched text for search suggestions
     */
    function highlightMatch(sourceText, query) {
        if (!sourceText) return '';
        const escapedSource = escapeHtml(sourceText);
        if (!query || query.trim() === '') return escapedSource;

        const escapedQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedSource.replace(regex, '<span class="customer-highlight">$1</span>');
    }

    /**
     * Format a numeric amount in the Indian numbering system (e.g. 1,45,000.00)
     * Why: Gold and silver transactions in India traditionally use Indian comma separation
     */
    function formatIndianCurrency(amount) {
        const num = parseFloat(amount);
        if (isNaN(num)) return '0.00';
        return num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Calculate row amount based on bullion formula:
     * Amount = round(weight * rate / rate_unit_grams, 2)
     * Why: In Indian bullion trade, rates are quoted per 10 grams (1 tola)
     */
    function calculateRowAmount(weight, rate) {
        const rateUnit = (BHAV_CONFIG && BHAV_CONFIG.rate_unit_grams) ? BHAV_CONFIG.rate_unit_grams : 10;
        const w = parseFloat(weight) || 0;
        const r = parseFloat(rate) || 0;
        return Math.round(((w * r) / rateUnit) * 100) / 100;
    }

    /**
     * Helper to zero-pad single-digit numbers for date strings
     */
    function pad2(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    /**
     * Format Date object as DD-MM-YYYY
     */
    function formatDateDisplay(d) {
        return `${pad2(d.getDate())}-${pad2(d.getMonth() + 1)}-${d.getFullYear()}`;
    }

    /**
     * Format Date object as YYYY-MM-DD
     */
    function formatDateISO(d) {
        return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
    }

    /**
     * Get the CSRF token from the HTML meta tag
     * Why: Laravel requires this token on POST requests to protect against forgery
     */
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    // =========================================================================
    // 3. TOAST
    // Reusable Bootstrap toast notification system
    // =========================================================================

    /**
     * Display a temporary toast notification at the top-right of the screen
     */
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        let bgClass = 'bg-white text-dark';
        let borderClass = 'border-primary';
        let iconHtml = '<i class="fa-solid fa-circle-info text-primary me-2"></i>';

        if (type === 'success') {
            borderClass = 'border-success';
            iconHtml = '<i class="fa-solid fa-circle-check text-success me-2"></i>';
        } else if (type === 'warning') {
            borderClass = 'border-warning';
            iconHtml = '<i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>';
        } else if (type === 'error' || type === 'danger') {
            borderClass = 'border-danger';
            iconHtml = '<i class="fa-solid fa-circle-xmark text-danger me-2"></i>';
        }

        const toastId = 'toast_' + Date.now();
        const toastEl = document.createElement('div');
        toastEl.className = `toast app-toast border-start border-4 ${borderClass} ${bgClass} shadow-lg`;
        toastEl.id = toastId;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex align-items-center p-3">
                <div class="toast-body p-0 d-flex align-items-center flex-grow-1">
                    <span class="fs-5">${iconHtml}</span>
                    <span class="fw-semibold">${escapeHtml(message)}</span>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
            bsToast.show();
        } else {
            setTimeout(() => toastEl.remove(), 3500);
        }
    }

    window.showToast = showToast;

    // =========================================================================
    // 4. CUSTOMER SEARCH
    // Live customer search, debouncing, keyboard navigation, and selection
    // =========================================================================

    const customerSearchInput = document.getElementById('customerSearchInput');
    const customerSearchResults = document.getElementById('customerSearchResults');
    const customerClearBtn = document.getElementById('customerClearBtn');
    const hiddenCustomerId = document.getElementById('hiddenCustomerId');
    const customerSelectedInfo = document.getElementById('customerSelectedInfo');
    const customerError = document.getElementById('customerError');
    const customerSearchWrapper = document.getElementById('customerSearchWrapper');

    let selectedCustomer = null;
    let searchDebounceTimer = null;
    let activeCustomerIndex = -1;
    let currentSearchResults = [];
    let currentSearchAbortController = null;

    /**
     * Select a customer and update all related UI fields
     */
    function selectCustomer(customer) {
        selectedCustomer = customer;
        hiddenCustomerId.value = customer.id;
        customerSearchInput.value = `${customer.name} (${customer.customer_no})`;

        const mobileText = customer.mobile ? escapeHtml(customer.mobile) : 'N/A';
        const cityText = customer.city ? escapeHtml(customer.city) : 'N/A';
        customerSelectedInfo.innerHTML = `
            <i class="fa-solid fa-phone me-1 text-success"></i> <strong>Mobile:</strong> ${mobileText}
            <span class="mx-2 text-muted">|</span>
            <i class="fa-solid fa-location-dot me-1 text-primary"></i> <strong>City:</strong> ${cityText}
        `;
        customerSelectedInfo.style.display = 'block';

        customerClearBtn.style.display = 'block';
        hideCustomerError();
        hideCustomerDropdown();
    }

    /**
     * Clear customer selection and reset search state
     */
    function clearCustomerSelection() {
        selectedCustomer = null;
        hiddenCustomerId.value = '';
        customerSearchInput.value = '';
        customerClearBtn.style.display = 'none';
        customerSelectedInfo.style.display = 'none';
        customerSelectedInfo.innerHTML = '';
        hideCustomerDropdown();
        hideCustomerError();

        const whatsappSwitch = document.getElementById('whatsappSwitch');
        if (whatsappSwitch && whatsappSwitch.checked) {
            whatsappSwitch.checked = false;
        }

        customerSearchInput.focus();
    }

    function showCustomerError(message) {
        if (!customerError) return;
        customerError.textContent = message;
        customerError.style.display = 'block';
        customerSearchInput.classList.add('is-invalid');
    }

    function hideCustomerError() {
        if (!customerError) return;
        customerError.textContent = '';
        customerError.style.display = 'none';
        customerSearchInput.classList.remove('is-invalid');
    }

    function hideCustomerDropdown() {
        if (!customerSearchResults) return;
        customerSearchResults.style.display = 'none';
        customerSearchResults.innerHTML = '';
        activeCustomerIndex = -1;
        currentSearchResults = [];
    }

    function updateActiveDropdownItem() {
        const items = customerSearchResults.querySelectorAll('.customer-search-item');
        items.forEach((item, idx) => {
            if (idx === activeCustomerIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    function performCustomerSearch(query) {
        const searchUrl = window.APP_ROUTES ? window.APP_ROUTES.customerSearch : '/customers/search';

        if (currentSearchAbortController) {
            currentSearchAbortController.abort();
        }
        currentSearchAbortController = new AbortController();

        customerSearchResults.innerHTML = `
            <div class="p-3 text-center text-muted small">
                <i class="fa-solid fa-spinner fa-spin me-2"></i> Searching customers...
            </div>
        `;
        customerSearchResults.style.display = 'block';

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            signal: currentSearchAbortController.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Search failed');
            return response.json();
        })
        .then(customers => {
            currentSearchResults = customers;
            activeCustomerIndex = -1;

            if (!customers || customers.length === 0) {
                customerSearchResults.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        No customer found &nbsp;
                        <a href="#" id="linkAddNewCustomer" class="text-primary fw-bold text-decoration-none">
                            + Add New Customer
                        </a>
                    </div>
                `;
                const linkAddNew = document.getElementById('linkAddNewCustomer');
                if (linkAddNew) {
                    linkAddNew.addEventListener('click', function (e) {
                        e.preventDefault();
                        hideCustomerDropdown();
                        openAddCustomerModal(query);
                    });
                }
                return;
            }

            let listHtml = '';
            customers.forEach((c, index) => {
                const highlightedName = highlightMatch(c.name, query);
                const highlightedNo = highlightMatch(c.customer_no, query);
                const highlightedMobile = c.mobile ? highlightMatch(c.mobile, query) : '<span class="text-muted fst-italic">No mobile</span>';

                listHtml += `
                    <div class="customer-search-item" data-index="${index}">
                        <div class="fw-bold text-dark customer-item-name">${highlightedName}</div>
                        <div class="small text-muted d-flex justify-content-between mt-1">
                            <span><i class="fa-solid fa-id-badge me-1"></i>${highlightedNo}</span>
                            <span><i class="fa-solid fa-phone me-1"></i>${highlightedMobile}</span>
                        </div>
                    </div>
                `;
            });

            customerSearchResults.innerHTML = listHtml;

            const itemEls = customerSearchResults.querySelectorAll('.customer-search-item');
            itemEls.forEach(itemEl => {
                itemEl.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-index'), 10);
                    if (currentSearchResults[idx]) {
                        selectCustomer(currentSearchResults[idx]);
                        const typeSelect = document.getElementById('typeSelect');
                        if (typeSelect) typeSelect.focus();
                    }
                });
            });
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            customerSearchResults.innerHTML = `
                <div class="p-3 text-center text-danger small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Failed to search. Please try again.
                </div>
            `;
        });
    }

    if (customerSearchInput) {
        customerSearchInput.addEventListener('input', function () {
            const query = this.value.trim();

            if (selectedCustomer) {
                selectedCustomer = null;
                hiddenCustomerId.value = '';
                customerSelectedInfo.style.display = 'none';
                customerClearBtn.style.display = 'none';
            }

            hideCustomerError();
            clearTimeout(searchDebounceTimer);

            if (query.length < 1) {
                hideCustomerDropdown();
                customerClearBtn.style.display = 'none';
                return;
            }

            customerClearBtn.style.display = 'block';

            searchDebounceTimer = setTimeout(() => {
                performCustomerSearch(query);
            }, 300);
        });

        customerSearchInput.addEventListener('keydown', function (e) {
            const items = customerSearchResults.querySelectorAll('.customer-search-item');
            const isDropdownVisible = customerSearchResults.style.display === 'block' && items.length > 0;

            if (e.key === 'ArrowDown') {
                if (isDropdownVisible) {
                    e.preventDefault();
                    activeCustomerIndex = (activeCustomerIndex + 1) % items.length;
                    updateActiveDropdownItem();
                }
            } else if (e.key === 'ArrowUp') {
                if (isDropdownVisible) {
                    e.preventDefault();
                    activeCustomerIndex = (activeCustomerIndex - 1 + items.length) % items.length;
                    updateActiveDropdownItem();
                }
            } else if (e.key === 'Enter') {
                if (isDropdownVisible) {
                    e.preventDefault();
                    const targetIndex = activeCustomerIndex >= 0 ? activeCustomerIndex : 0;
                    if (currentSearchResults[targetIndex]) {
                        selectCustomer(currentSearchResults[targetIndex]);
                        const typeSelect = document.getElementById('typeSelect');
                        if (typeSelect) typeSelect.focus();
                    }
                } else {
                    e.preventDefault();
                    const typeSelect = document.getElementById('typeSelect');
                    if (typeSelect) typeSelect.focus();
                }
            } else if (e.key === 'Escape') {
                hideCustomerDropdown();
            }
        });
    }

    if (customerClearBtn) {
        customerClearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearCustomerSelection();
        });
    }

    document.addEventListener('click', function (e) {
        if (customerSearchWrapper && !customerSearchWrapper.contains(e.target)) {
            hideCustomerDropdown();
        }
    });

    // =========================================================================
    // 5. ADD CUSTOMER MODAL
    // Open modal, validation error rendering, saving via AJAX, and auto-select
    // =========================================================================

    const addCustomerModalEl = document.getElementById('addCustomerModal');
    const addCustomerForm = document.getElementById('addCustomerForm');
    const newCustomerName = document.getElementById('newCustomerName');
    const newCustomerMobile = document.getElementById('newCustomerMobile');
    const newCustomerCity = document.getElementById('newCustomerCity');
    const btnSaveNewCustomer = document.getElementById('btnSaveNewCustomer');
    const btnOpenAddCustomerModal = document.getElementById('btnOpenAddCustomerModal');

    const errorNewCustomerName = document.getElementById('errorNewCustomerName');
    const errorNewCustomerMobile = document.getElementById('errorNewCustomerMobile');
    const errorNewCustomerCity = document.getElementById('errorNewCustomerCity');

    function openAddCustomerModal(prefillName = '') {
        resetAddCustomerModalErrors();

        if (!selectedCustomer) {
            if (prefillName && typeof prefillName === 'string') {
                newCustomerName.value = prefillName;
            } else if (customerSearchInput) {
                newCustomerName.value = customerSearchInput.value.trim();
            }
        } else {
            newCustomerName.value = '';
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(addCustomerModalEl);
            modalInstance.show();
        }
    }

    function resetAddCustomerModalErrors() {
        [errorNewCustomerName, errorNewCustomerMobile, errorNewCustomerCity].forEach(el => {
            if (el) {
                el.textContent = '';
                el.style.display = 'none';
            }
        });
        [newCustomerName, newCustomerMobile, newCustomerCity].forEach(input => {
            if (input) input.classList.remove('is-invalid');
        });
    }

    if (btnOpenAddCustomerModal) {
        btnOpenAddCustomerModal.addEventListener('click', function () {
            const queryToPrefill = !selectedCustomer && customerSearchInput ? customerSearchInput.value.trim() : '';
            openAddCustomerModal(queryToPrefill);
        });
    }

    if (addCustomerModalEl) {
        addCustomerModalEl.addEventListener('shown.bs.modal', function () {
            if (selectedCustomer) {
                newCustomerName.value = '';
            }
            if (newCustomerName) {
                newCustomerName.focus();
                const val = newCustomerName.value;
                newCustomerName.value = '';
                newCustomerName.value = val;
            }
        });
    }

    if (newCustomerMobile) {
        newCustomerMobile.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    if (btnSaveNewCustomer) {
        btnSaveNewCustomer.addEventListener('click', function () {
            resetAddCustomerModalErrors();

            const nameVal = newCustomerName ? newCustomerName.value.trim() : '';
            const mobileVal = newCustomerMobile ? newCustomerMobile.value.trim() : '';
            const cityVal = newCustomerCity ? newCustomerCity.value.trim() : '';

            if (!nameVal) {
                if (errorNewCustomerName) {
                    errorNewCustomerName.textContent = 'Customer name is required.';
                    errorNewCustomerName.style.display = 'block';
                }
                newCustomerName.classList.add('is-invalid');
                newCustomerName.focus();
                return;
            }

            btnSaveNewCustomer.disabled = true;
            const originalButtonHtml = btnSaveNewCustomer.innerHTML;
            btnSaveNewCustomer.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';

            const storeUrl = window.APP_ROUTES ? window.APP_ROUTES.customerStore : '/customers';

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    name: nameVal,
                    mobile: mobileVal || null,
                    city: cityVal || null
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        if (data.errors.name && errorNewCustomerName) {
                            errorNewCustomerName.textContent = data.errors.name[0];
                            errorNewCustomerName.style.display = 'block';
                            newCustomerName.classList.add('is-invalid');
                        }
                        if (data.errors.mobile && errorNewCustomerMobile) {
                            errorNewCustomerMobile.textContent = data.errors.mobile[0];
                            errorNewCustomerMobile.style.display = 'block';
                            newCustomerMobile.classList.add('is-invalid');
                        }
                        if (data.errors.city && errorNewCustomerCity) {
                            errorNewCustomerCity.textContent = data.errors.city[0];
                            errorNewCustomerCity.style.display = 'block';
                            newCustomerCity.classList.add('is-invalid');
                        }
                    } else {
                        showToast(data.message || 'Failed to create customer.', 'error');
                    }
                    throw new Error('Validation failed');
                }
                return data;
            })
            .then(newCustomer => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(addCustomerModalEl);
                    if (modalInstance) modalInstance.hide();
                }
                if (addCustomerForm) addCustomerForm.reset();

                selectCustomer(newCustomer);
                showToast('Customer added', 'success');
            })
            .catch(err => {
                console.warn('Customer store response:', err);
            })
            .finally(() => {
                btnSaveNewCustomer.disabled = false;
                btnSaveNewCustomer.innerHTML = originalButtonHtml;
            });
        });
    }

    // =========================================================================
    // 6. TRADE TOGGLE (BUY/SELL)
    // Switch between BUY and SELL modes and sync with hidden input & labels
    // =========================================================================

    const btnBuy = document.getElementById('btnBuy');
    const btnSell = document.getElementById('btnSell');
    const hiddenTradeType = document.getElementById('hiddenTradeType');
    const labelWeight = document.getElementById('labelWeight');
    const labelRate = document.getElementById('labelRate');

    function setTradeMode(mode) {
        if (hiddenTradeType) hiddenTradeType.value = mode;

        if (mode === 'BUY') {
            btnBuy.classList.add('active');
            btnSell.classList.remove('active');
            if (labelWeight) labelWeight.textContent = 'BUY WEIGHT :';
            if (labelRate) labelRate.textContent = 'BUY RATE (/10GM) :';
        } else {
            btnSell.classList.add('active');
            btnBuy.classList.remove('active');
            if (labelWeight) labelWeight.textContent = 'SELL WEIGHT :';
            if (labelRate) labelRate.textContent = 'SELL RATE (/10GM) :';
        }
    }

    if (btnBuy && btnSell) {
        btnBuy.addEventListener('click', function () {
            setTradeMode('BUY');
        });

        btnSell.addEventListener('click', function () {
            setTradeMode('SELL');
        });
    }

    // =========================================================================
    // 7. MULTI-WEIGHT ENTRIES & CALCULATIONS
    // Single source of truth array, inline edit, delete, grand total, and metal rule
    // =========================================================================

    const categorySelect = document.getElementById('categorySelect');
    const typeSelect = document.getElementById('typeSelect');
    const inputWeight = document.getElementById('inputWeight');
    const inputRate = document.getElementById('inputRate');
    const inputAmount = document.getElementById('inputAmount');
    const hiddenAmount = document.getElementById('hiddenAmount');
    const btnAddWeight = document.getElementById('btnAddWeight');
    const entriesRowWrapper = document.getElementById('entriesRowWrapper');
    const entriesTableBody = document.getElementById('entriesTableBody');
    const entriesCountBadge = document.getElementById('entriesCountBadge');
    const entriesTotalWeight = document.getElementById('entriesTotalWeight');
    const entriesTotalAmount = document.getElementById('entriesTotalAmount');
    const entriesGlobalError = document.getElementById('entriesGlobalError');
    const inputNarration = document.getElementById('inputNarration');
    const narrationCounter = document.getElementById('narrationCounter');
    const whatsappSwitch = document.getElementById('whatsappSwitch');

    const weightError = document.getElementById('weightError');
    const rateError = document.getElementById('rateError');

    // Initialize metal and default rate from BHAV_CONFIG
    if (categorySelect) {
        currentMetal = categorySelect.value || 'GOLD';
    }
    if (inputRate && BHAV_CONFIG.default_rates && BHAV_CONFIG.default_rates[currentMetal]) {
        inputRate.value = BHAV_CONFIG.default_rates[currentMetal];
    }

    /**
     * Compute all totals:
     * - Table totals: sum of all confirmed rows in bookingItems
     * - Grand total: table totals + current entry line (if weight > 0 and rate > 0)
     * Why: AMOUNT field must always show the grand total (all rows + current line)
     */
    function calculateTotals() {
        let tableWeight = 0;
        let tableAmount = 0;

        for (const item of bookingItems) {
            const w = parseFloat(item.weight) || 0;
            const a = parseFloat(item.amount) || 0;
            tableWeight += w;
            tableAmount += a;
        }

        // Avoid floating-point errors (e.g. 2.125 + 0.875 = 3.000)
        tableWeight = Math.round(tableWeight * 1000) / 1000;
        tableAmount = Math.round(tableAmount * 100) / 100;

        // Current entry line
        const curWeight = parseFloat(inputWeight ? inputWeight.value : 0) || 0;
        const curRate = parseFloat(inputRate ? inputRate.value : 0) || 0;
        let curLineAmount = 0;

        if (curWeight > 0 && curRate > 0) {
            curLineAmount = calculateRowAmount(curWeight, curRate);
        }

        const grandWeight = Math.round((tableWeight + curWeight) * 1000) / 1000;
        const grandAmount = Math.round((tableAmount + curLineAmount) * 100) / 100;

        return {
            tableWeight,
            tableAmount,
            grandWeight,
            grandAmount,
            curWeight,
            curRate,
            curLineAmount
        };
    }

    /**
     * Update all total displays on the page:
     * 1. Mini-table footer (shows table totals)
     * 2. Main AMOUNT field (shows grand total)
     */
    function updateAllTotals() {
        const totals = calculateTotals();

        // Update mini-table footer
        if (entriesTotalWeight) {
            entriesTotalWeight.textContent = `${totals.tableWeight.toFixed(3)} gm`;
        }
        if (entriesTotalAmount) {
            entriesTotalAmount.textContent = `₹${formatIndianCurrency(totals.tableAmount)}`;
        }
        if (entriesCountBadge) {
            entriesCountBadge.textContent = bookingItems.length;
        }

        // Update main AMOUNT field with grand total (all rows + current line)
        if (inputAmount && hiddenAmount) {
            hiddenAmount.value = totals.grandAmount.toFixed(2);
            inputAmount.value = formatIndianCurrency(totals.grandAmount);
        }
    }

    /**
     * Re-render the Entries mini-table from the single source of truth (bookingItems)
     * Why: Renders cleanly whenever rows are added, deleted, or cleared.
     */
    function renderEntriesTable() {
        if (!entriesTableBody || !entriesRowWrapper) return;

        if (bookingItems.length === 0) {
            entriesRowWrapper.style.display = 'none';
            entriesTableBody.innerHTML = '';
            if (entriesGlobalError) entriesGlobalError.style.display = 'none';
            updateAllTotals();
            return;
        }

        entriesRowWrapper.style.display = 'flex';

        let rowsHtml = '';
        bookingItems.forEach((item, index) => {
            const wVal = parseFloat(item.weight).toFixed(3);
            const rVal = parseFloat(item.rate).toFixed(2);
            const aVal = formatIndianCurrency(item.amount);

            rowsHtml += `
                <tr class="entry-row" data-id="${item.id}" data-index="${index}">
                    <td class="text-center text-muted fw-bold col-entry-sn">${index + 1}</td>
                    <td class="col-entry-wt">
                        <input type="text"
                               inputmode="decimal"
                               class="entry-cell-input row-input-weight"
                               data-id="${item.id}"
                               value="${wVal}"
                               title="Edit weight">
                    </td>
                    <td class="col-entry-rt">
                        <input type="text"
                               inputmode="decimal"
                               class="entry-cell-input row-input-rate"
                               data-id="${item.id}"
                               value="${rVal}"
                               title="Edit rate">
                    </td>
                    <td class="text-end fw-bold text-dark col-entry-amt row-amount-text" data-id="${item.id}">
                        ₹${aVal}
                    </td>
                    <td class="text-center col-entry-del">
                        <button type="button"
                                class="btn-delete-entry-row"
                                data-id="${item.id}"
                                title="Delete entry"
                                aria-label="Delete entry">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        entriesTableBody.innerHTML = rowsHtml;
        attachEntryRowListeners();
        updateAllTotals();
    }

    /**
     * Attach event listeners for inline editing and row deletion
     * Why: Inline editing updates only that row and the totals without re-rendering the whole table,
     * ensuring cursor focus is never lost while typing.
     */
    function attachEntryRowListeners() {
        if (!entriesTableBody) return;

        // Weight inputs inside table rows
        const weightInputs = entriesTableBody.querySelectorAll('.row-input-weight');
        weightInputs.forEach(input => {
            input.addEventListener('input', function () {
                const itemId = parseInt(this.getAttribute('data-id'), 10);
                const item = bookingItems.find(it => it.id === itemId);
                if (!item) return;

                // Sanitize input characters
                let val = this.value.replace(/[^0-9.]/g, '');
                const parts = val.split('.');
                if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
                if (parts.length === 2 && parts[1].length > 3) val = parts[0] + '.' + parts[1].slice(0, 3);
                this.value = val;

                const parsedWeight = parseFloat(val) || 0;
                item.weight = parsedWeight;
                item.amount = calculateRowAmount(item.weight, item.rate);

                // Update amount display in DOM for this row
                const amtCell = entriesTableBody.querySelector(`.row-amount-text[data-id="${itemId}"]`);
                if (amtCell) {
                    amtCell.textContent = `₹${formatIndianCurrency(item.amount)}`;
                }

                this.classList.remove('is-invalid');
                updateAllTotals();
            });

            input.addEventListener('blur', function () {
                const itemId = parseInt(this.getAttribute('data-id'), 10);
                const item = bookingItems.find(it => it.id === itemId);
                if (item) {
                    this.value = parseFloat(item.weight || 0).toFixed(3);
                }
                updateAllTotals();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const itemId = parseInt(this.getAttribute('data-id'), 10);
                    const rateInput = entriesTableBody.querySelector(`.row-input-rate[data-id="${itemId}"]`);
                    if (rateInput) rateInput.focus();
                }
            });
        });

        // Rate inputs inside table rows
        const rateInputs = entriesTableBody.querySelectorAll('.row-input-rate');
        rateInputs.forEach(input => {
            input.addEventListener('input', function () {
                const itemId = parseInt(this.getAttribute('data-id'), 10);
                const item = bookingItems.find(it => it.id === itemId);
                if (!item) return;

                let val = this.value.replace(/[^0-9.]/g, '');
                const parts = val.split('.');
                if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
                if (parts.length === 2 && parts[1].length > 2) val = parts[0] + '.' + parts[1].slice(0, 2);
                this.value = val;

                const parsedRate = parseFloat(val) || 0;
                item.rate = parsedRate;
                item.amount = calculateRowAmount(item.weight, item.rate);

                const amtCell = entriesTableBody.querySelector(`.row-amount-text[data-id="${itemId}"]`);
                if (amtCell) {
                    amtCell.textContent = `₹${formatIndianCurrency(item.amount)}`;
                }

                this.classList.remove('is-invalid');
                updateAllTotals();
            });

            input.addEventListener('blur', function () {
                const itemId = parseInt(this.getAttribute('data-id'), 10);
                const item = bookingItems.find(it => it.id === itemId);
                if (item) {
                    this.value = parseFloat(item.rate || 0).toFixed(2);
                }
                updateAllTotals();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (inputWeight) inputWeight.focus();
                }
            });
        });

        // Delete button for each row
        const deleteButtons = entriesTableBody.querySelectorAll('.btn-delete-entry-row');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const itemId = parseInt(this.getAttribute('data-id'), 10);
                const itemIndex = bookingItems.findIndex(it => it.id === itemId);
                if (itemIndex !== -1) {
                    bookingItems.splice(itemIndex, 1);
                    renderEntriesTable();
                }
            });
        });
    }

    /**
     * Add current entry line to bookingItems array
     * Why: Validates weight > 0 and rate > 0, then pushes row, clears weight, keeps rate, and focuses weight.
     * @returns {boolean} Whether entry was successfully added
     */
    function addEntryLineRow() {
        const weightVal = parseFloat(inputWeight ? inputWeight.value : 0);
        const rateVal = parseFloat(inputRate ? inputRate.value : 0);

        let valid = true;

        if (!weightVal || isNaN(weightVal) || weightVal <= 0) {
            if (weightError) {
                weightError.textContent = 'Weight must be greater than 0.';
                weightError.style.display = 'block';
            }
            if (inputWeight) inputWeight.classList.add('is-invalid');
            valid = false;
        } else {
            if (weightError) weightError.style.display = 'none';
            if (inputWeight) inputWeight.classList.remove('is-invalid');
        }

        if (!rateVal || isNaN(rateVal) || rateVal <= 0) {
            if (rateError) {
                rateError.textContent = 'Rate must be greater than 0.';
                rateError.style.display = 'block';
            }
            if (inputRate) inputRate.classList.add('is-invalid');
            valid = false;
        } else {
            if (rateError) rateError.style.display = 'none';
            if (inputRate) inputRate.classList.remove('is-invalid');
        }

        if (!valid) return false;

        const rowAmount = calculateRowAmount(weightVal, rateVal);

        bookingItems.push({
            id: nextItemId++,
            weight: weightVal,
            rate: rateVal,
            amount: rowAmount
        });

        // Clear weight, keep rate, re-render table, focus weight
        inputWeight.value = '';
        renderEntriesTable();
        inputWeight.focus();
        return true;
    }

    // Plus button click adds entry line
    if (btnAddWeight) {
        btnAddWeight.addEventListener('click', function (e) {
            e.preventDefault();
            addEntryLineRow();
        });
    }

    /**
     * Metal change rule:
     * Changing metal resets rate to that metal's default.
     * If rows already exist, prompt confirmation: "Changing metal will clear entries. Continue?"
     * One booking must never mix Gold and Silver!
     */
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const newMetal = this.value;

            if (bookingItems.length > 0) {
                const confirmed = window.confirm('Changing metal will clear entries. Continue?');
                if (!confirmed) {
                    // Revert selection back to previous metal
                    this.value = currentMetal;
                    return;
                }
                // Cleared entries
                bookingItems = [];
                renderEntriesTable();
            }

            currentMetal = newMetal;

            // Reset rate to new metal's default
            if (BHAV_CONFIG.default_rates && BHAV_CONFIG.default_rates[newMetal] !== undefined && inputRate) {
                inputRate.value = BHAV_CONFIG.default_rates[newMetal];
                if (rateError) {
                    rateError.style.display = 'none';
                    inputRate.classList.remove('is-invalid');
                }
            }

            updateAllTotals();
        });
    }

    // Weight input characters & formatting
    if (inputWeight) {
        inputWeight.addEventListener('input', function () {
            let val = this.value;
            val = val.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            if (parts.length === 2 && parts[1].length > 3) val = parts[0] + '.' + parts[1].slice(0, 3);
            this.value = val;

            if (weightError && parseFloat(val) > 0) {
                weightError.style.display = 'none';
                this.classList.remove('is-invalid');
            }

            updateAllTotals();
        });

        inputWeight.addEventListener('blur', function () {
            const val = this.value.trim();
            if (val !== '' && !isNaN(val)) {
                const num = parseFloat(val);
                if (num >= 0) this.value = num.toFixed(3);
            }
            updateAllTotals();
        });
    }

    // Rate input characters & formatting
    if (inputRate) {
        inputRate.addEventListener('input', function () {
            let val = this.value;
            val = val.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            if (parts.length === 2 && parts[1].length > 2) val = parts[0] + '.' + parts[1].slice(0, 2);
            this.value = val;

            if (rateError && parseFloat(val) > 0) {
                rateError.style.display = 'none';
                this.classList.remove('is-invalid');
            }

            updateAllTotals();
        });

        inputRate.addEventListener('blur', function () {
            const val = this.value.trim();
            if (val !== '' && !isNaN(val)) {
                const num = parseFloat(val);
                if (num >= 0) this.value = num.toFixed(2);
            }
            updateAllTotals();
        });
    }

    // Remarks / Narration counter
    if (inputNarration && narrationCounter) {
        inputNarration.addEventListener('input', function () {
            narrationCounter.textContent = `${this.value.length}/255`;
        });
    }

    // WhatsApp switch check
    if (whatsappSwitch) {
        whatsappSwitch.addEventListener('change', function () {
            if (this.checked) {
                if (!selectedCustomer || !selectedCustomer.mobile || selectedCustomer.mobile.trim() === '') {
                    this.checked = false;
                    showToast('Customer mobile number required for WhatsApp', 'warning');
                }
            }
        });
    }

    // Initial total calculation on load
    updateAllTotals();

    // =========================================================================
    // 8. SAVE & FORM SUBMISSION
    // POST /bhav-bookings, request_token, double-click protection, 422 error display
    // =========================================================================

    const btnSave = document.getElementById('btnSave');
    const btnPrint = document.getElementById('btnPrint');

    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            showToast('Coming soon', 'info');
        });
    }

    /**
     * Clear all field validation errors
     */
    function resetValidationErrors() {
        hideCustomerError();
        if (weightError) {
            weightError.textContent = '';
            weightError.style.display = 'none';
        }
        if (inputWeight) inputWeight.classList.remove('is-invalid');

        if (rateError) {
            rateError.textContent = '';
            rateError.style.display = 'none';
        }
        if (inputRate) inputRate.classList.remove('is-invalid');

        if (entriesGlobalError) {
            entriesGlobalError.textContent = '';
            entriesGlobalError.style.display = 'none';
        }

        const tableInputs = entriesTableBody ? entriesTableBody.querySelectorAll('.entry-cell-input') : [];
        tableInputs.forEach(input => input.classList.remove('is-invalid'));
    }

    /**
     * Reset form after a successful booking save:
     * - Clear rows
     * - Clear weight
     * - Clear narration
     * - WhatsApp off
     * - KEEP BUY/SELL mode, metal and rate
     * - Generate new request_token
     * - Focus customer input
     */
    function resetFormAfterSave() {
        clearCustomerSelection();
        bookingItems = [];
        renderEntriesTable();

        if (inputWeight) inputWeight.value = '';
        if (inputNarration) inputNarration.value = '';
        if (narrationCounter) narrationCounter.textContent = '0/255';
        if (whatsappSwitch) whatsappSwitch.checked = false;

        // Generate fresh request_token for the next save
        currentRequestToken = generateRequestToken();

        // Update totals (will show 0.00)
        updateAllTotals();

        // Focus customer search
        if (customerSearchInput) {
            customerSearchInput.focus();
        }
    }

    if (btnSave) {
        btnSave.addEventListener('click', function (e) {
            e.preventDefault();
            resetValidationErrors();

            // 1. Check if current entry line is filled: auto-treat as a row if "+" was not clicked
            const currentWeight = parseFloat(inputWeight ? inputWeight.value : 0) || 0;
            const currentRate = parseFloat(inputRate ? inputRate.value : 0) || 0;

            if (currentWeight > 0 && currentRate > 0) {
                const rowAmount = calculateRowAmount(currentWeight, currentRate);
                bookingItems.push({
                    id: nextItemId++,
                    weight: currentWeight,
                    rate: currentRate,
                    amount: rowAmount
                });
                inputWeight.value = '';
                renderEntriesTable();
            }

            let isValid = true;
            let firstInvalidField = null;

            // 2. Validate Customer
            if (!hiddenCustomerId.value || !selectedCustomer) {
                showCustomerError('Customer must be selected.');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = customerSearchInput;
            }

            // 3. Validate at least 1 row exists
            if (bookingItems.length === 0) {
                // Highlight weight & rate inputs
                if (!currentWeight || currentWeight <= 0) {
                    if (weightError) {
                        weightError.textContent = 'Weight must be greater than 0.';
                        weightError.style.display = 'block';
                    }
                    if (inputWeight) inputWeight.classList.add('is-invalid');
                    if (!firstInvalidField) firstInvalidField = inputWeight;
                }
                if (!currentRate || currentRate <= 0) {
                    if (rateError) {
                        rateError.textContent = 'Rate must be greater than 0.';
                        rateError.style.display = 'block';
                    }
                    if (inputRate) inputRate.classList.add('is-invalid');
                    if (!firstInvalidField) firstInvalidField = inputRate;
                }
                isValid = false;
            }

            if (!isValid) {
                if (firstInvalidField) firstInvalidField.focus();
                return;
            }

            // Disable save button + show spinner to prevent double submission
            btnSave.disabled = true;
            const originalBtnHtml = btnSave.innerHTML;
            btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';

            const payload = {
                customer_id: parseInt(hiddenCustomerId.value, 10),
                trade_type: hiddenTradeType ? hiddenTradeType.value : 'SELL',
                entry_type: typeSelect ? typeSelect.value : 'ONLY_ENTRY',
                metal: categorySelect ? categorySelect.value : 'GOLD',
                items: bookingItems.map(item => ({
                    weight: parseFloat(item.weight),
                    rate: parseFloat(item.rate)
                })),
                narration: inputNarration ? inputNarration.value.trim() : null,
                send_whatsapp: whatsappSwitch ? whatsappSwitch.checked : false,
                request_token: currentRequestToken
            };

            const storeUrl = window.APP_ROUTES && window.APP_ROUTES.bhavBookingStore
                ? window.APP_ROUTES.bhavBookingStore
                : '/bhav-bookings';

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    // Handle Laravel 422 validation errors
                    if (response.status === 422 && data.errors) {
                        handleValidationErrors(data.errors);
                    } else {
                        showToast(data.message || 'Failed to save booking.', 'error');
                    }
                    throw new Error('Save failed');
                }

                return data;
            })
            .then(savedBooking => {
                // Success: Toast "Booking BB-000001 saved"
                const bookingNo = savedBooking.booking_no || 'Booking';
                showToast(`Booking ${bookingNo} saved`, 'success');

                // Reset form state keeping mode, metal and rate
                resetFormAfterSave();
            })
            .catch(err => {
                console.warn('Booking save error:', err);
            })
            .finally(() => {
                btnSave.disabled = false;
                btnSave.innerHTML = originalBtnHtml;
            });
        });
    }

    /**
     * Map server 422 validation errors to corresponding fields and row inputs
     */
    function handleValidationErrors(errors) {
        if (errors.customer_id) {
            showCustomerError(errors.customer_id[0]);
        }
        if (errors.items && entriesGlobalError) {
            entriesGlobalError.textContent = errors.items[0];
            entriesGlobalError.style.display = 'block';
        }

        // Check for row item errors like items.0.weight or items.1.rate
        Object.keys(errors).forEach(key => {
            const match = key.match(/^items\.(\d+)\.(weight|rate)$/);
            if (match && entriesTableBody) {
                const rowIndex = parseInt(match[1], 10);
                const fieldName = match[2];
                const item = bookingItems[rowIndex];
                if (item) {
                    const rowInput = entriesTableBody.querySelector(
                        fieldName === 'weight'
                            ? `.row-input-weight[data-id="${item.id}"]`
                            : `.row-input-rate[data-id="${item.id}"]`
                    );
                    if (rowInput) {
                        rowInput.classList.add('is-invalid');
                        rowInput.title = errors[key][0];
                    }
                }
            }
        });

        if (errors.narration && inputNarration) {
            inputNarration.classList.add('is-invalid');
        }
    }

    // =========================================================================
    // 9. KEYBOARD NAVIGATION
    // Fast navigation between inputs: Enter moves sequentially
    // =========================================================================

    if (typeSelect) {
        typeSelect.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (categorySelect) categorySelect.focus();
            }
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (inputWeight) inputWeight.focus();
            }
        });
    }

    if (inputWeight) {
        inputWeight.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (inputRate) inputRate.focus();
            }
        });
    }

    if (inputRate) {
        inputRate.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const curW = parseFloat(inputWeight ? inputWeight.value : 0) || 0;
                const curR = parseFloat(this.value) || 0;

                if (curW > 0 && curR > 0) {
                    // Enter in Rate with weight filled: adds entry row, clears weight, keeps rate, focuses weight
                    addEntryLineRow();
                } else if (bookingItems.length > 0) {
                    // Enter with empty weight moves to Narration
                    if (inputNarration) inputNarration.focus();
                } else {
                    // Try to add and display weight error
                    addEntryLineRow();
                }
            }
        });
    }

    if (inputNarration) {
        inputNarration.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (btnSave) btnSave.click();
            }
        });
    }

    // =========================================================================
    // 10. DATE RANGE FILTER & LIST MENU
    // Dropdown with presets (Today, Yesterday, Last 7/30 days, Month, Custom)
    // =========================================================================

    const menuOldBooking = document.getElementById('menuOldBooking');
    if (menuOldBooking) {
        menuOldBooking.addEventListener('click', function (e) {
            e.preventDefault();
            showToast('Coming soon', 'info');
        });
    }

    const dateRangePickerWrapper = document.getElementById('dateRangePickerWrapper');
    const dateRangeInput = document.getElementById('dateRangeInput');
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    const dateFilterFrom = document.getElementById('dateFilterFrom');
    const dateFilterTo = document.getElementById('dateFilterTo');
    const customDatePanel = document.getElementById('customDatePanel');
    const customDateFrom = document.getElementById('customDateFrom');
    const customDateTo = document.getElementById('customDateTo');
    const btnApplyCustomDate = document.getElementById('btnApplyCustomDate');
    const customDateError = document.getElementById('customDateError');

    function applyDateRange(from, to, presetName = '') {
        if (!dateRangeInput || !dateFilterFrom || !dateFilterTo) return;

        dateFilterFrom.value = formatDateISO(from);
        dateFilterTo.value = formatDateISO(to);
        dateRangeInput.value = `${formatDateDisplay(from)} - ${formatDateDisplay(to)}`;

        if (dateRangeDropdown) {
            const buttons = dateRangeDropdown.querySelectorAll('.date-preset-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('data-preset') === presetName) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
    }

    if (dateRangeInput && dateRangeDropdown) {
        dateRangeInput.addEventListener('click', function (e) {
            e.stopPropagation();
            const isVisible = dateRangeDropdown.style.display === 'block';
            dateRangeDropdown.style.display = isVisible ? 'none' : 'block';
        });
    }

    if (dateRangeDropdown) {
        const presetButtons = dateRangeDropdown.querySelectorAll('.date-preset-btn');
        presetButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation();
                const preset = this.getAttribute('data-preset');
                const today = new Date();

                if (preset === 'custom') {
                    if (customDatePanel) {
                        customDatePanel.style.display = 'block';
                        if (customDateError) customDateError.style.display = 'none';
                        if (customDateFrom && dateFilterFrom) customDateFrom.value = dateFilterFrom.value;
                        if (customDateTo && dateFilterTo) customDateTo.value = dateFilterTo.value;
                    }
                    presetButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    return;
                }

                if (customDatePanel) customDatePanel.style.display = 'none';

                let fromDate = new Date(today);
                let toDate = new Date(today);

                if (preset === 'today') {
                    fromDate = new Date(today);
                    toDate = new Date(today);
                } else if (preset === 'yesterday') {
                    fromDate.setDate(today.getDate() - 1);
                    toDate = new Date(fromDate);
                } else if (preset === 'last7') {
                    fromDate.setDate(today.getDate() - 6);
                    toDate = new Date(today);
                } else if (preset === 'last30') {
                    fromDate.setDate(today.getDate() - 29);
                    toDate = new Date(today);
                } else if (preset === 'thisMonth') {
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = new Date(today);
                } else if (preset === 'lastMonth') {
                    fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                }

                applyDateRange(fromDate, toDate, preset);
                dateRangeDropdown.style.display = 'none';
            });
        });
    }

    if (btnApplyCustomDate) {
        btnApplyCustomDate.addEventListener('click', function (e) {
            e.stopPropagation();
            const fromVal = customDateFrom ? customDateFrom.value : '';
            const toVal = customDateTo ? customDateTo.value : '';

            if (!fromVal || !toVal) {
                if (customDateError) {
                    customDateError.textContent = 'Please select both dates.';
                    customDateError.style.display = 'block';
                }
                return;
            }

            if (fromVal > toVal) {
                if (customDateError) {
                    customDateError.textContent = 'From date cannot be after To date.';
                    customDateError.style.display = 'block';
                }
                return;
            }

            if (customDateError) customDateError.style.display = 'none';

            const [fromY, fromM, fromD] = fromVal.split('-').map(Number);
            const [toY, toM, toD] = toVal.split('-').map(Number);

            const fromDate = new Date(fromY, fromM - 1, fromD);
            const toDate = new Date(toY, toM - 1, toD);

            applyDateRange(fromDate, toDate, 'custom');
            if (dateRangeDropdown) dateRangeDropdown.style.display = 'none';
        });
    }

    document.addEventListener('click', function (e) {
        if (dateRangePickerWrapper && !dateRangePickerWrapper.contains(e.target)) {
            if (dateRangeDropdown) dateRangeDropdown.style.display = 'none';
        }
    });

});
