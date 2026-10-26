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

    // Track last saved booking ID for the form PRINT button (Task C3)
    let lastSavedBookingId = null;

    // Track current booking being edited (null when in create mode) (Task C4)
    let editingBookingId = null;
    let editingBookingNo = null;

    // Map of currently loaded bookings on the page by ID for action menu operations
    let lastLoadedBookingsMap = {};

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

    /**
     * Resolve dynamic route URL from window.APP_ROUTES
     * Replaces '__ID__' with the provided model ID
     * Why: Eliminates hardcoded URLs in JavaScript and relies on Laravel route naming.
     */
    function getRoute(routeName, id = null) {
        if (!window.APP_ROUTES || !window.APP_ROUTES[routeName]) {
            return id !== null ? `/bhav-bookings/${id}` : '/bhav-bookings';
        }
        const url = window.APP_ROUTES[routeName];
        return id !== null ? url.replace('__ID__', encodeURIComponent(id)) : url;
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

    /**
     * Lock customer selection in Edit Mode:
     * - Make input readOnly with light grey look
     * - Replace user icon with lock icon
     * - Hide clear "×" button
     * - Disable add-customer button
     * - Close and prevent opening customer dropdown
     */
    function lockCustomerField() {
        if (customerSearchInput) {
            customerSearchInput.readOnly = true;
            customerSearchInput.classList.add('customer-locked');
        }
        const customerFieldIcon = document.getElementById('customerFieldIcon');
        if (customerFieldIcon) {
            customerFieldIcon.classList.remove('fa-user');
            customerFieldIcon.classList.add('fa-lock', 'text-secondary');
        }
        if (customerClearBtn) {
            customerClearBtn.style.display = 'none';
        }
        if (btnOpenAddCustomerModal) {
            btnOpenAddCustomerModal.disabled = true;
            btnOpenAddCustomerModal.classList.add('disabled');
        }
        hideCustomerDropdown();
    }

    /**
     * Unlock customer selection on exit from Edit Mode
     */
    function unlockCustomerField() {
        if (customerSearchInput) {
            customerSearchInput.readOnly = false;
            customerSearchInput.classList.remove('customer-locked');
        }
        const customerFieldIcon = document.getElementById('customerFieldIcon');
        if (customerFieldIcon) {
            customerFieldIcon.classList.remove('fa-lock', 'text-secondary');
            customerFieldIcon.classList.add('fa-user');
        }
        if (btnOpenAddCustomerModal) {
            btnOpenAddCustomerModal.disabled = false;
            btnOpenAddCustomerModal.classList.remove('disabled');
        }
        if (selectedCustomer && customerClearBtn) {
            customerClearBtn.style.display = 'block';
        }
    }

    // In-memory cache for recent customers and active in-flight request promise
    let cachedRecentCustomers = null;
    let recentCustomersPromise = null;

    /**
     * Fetch recent customers ordered by their latest Bhav Booking.
     * Caches results in memory; forceRefresh=true forces a new backend call.
     * Multiple concurrent calls share the same in-flight promise to prevent duplicate requests.
     */
    function fetchRecentCustomers(forceRefresh = false) {
        if (cachedRecentCustomers !== null && !forceRefresh) {
            return Promise.resolve(cachedRecentCustomers);
        }

        if (recentCustomersPromise !== null) {
            return recentCustomersPromise;
        }

        const recentUrl = (window.APP_ROUTES && window.APP_ROUTES.customerRecent)
            ? window.APP_ROUTES.customerRecent
            : '/customers/recent';

        recentCustomersPromise = fetch(recentUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load recent customers');
            return response.json();
        })
        .then(customers => {
            cachedRecentCustomers = Array.isArray(customers) ? customers : [];

            // If the dropdown is currently open AND customer input is empty or showing recent customers,
            // re-render it with the fresh list.
            if (customerSearchResults && customerSearchResults.style.display === 'block') {
                const inputVal = customerSearchInput ? customerSearchInput.value.trim() : '';
                const hasRecentHeader = customerSearchResults.querySelector('.customer-dropdown-header') !== null;
                const isShowingRecent = hasRecentHeader || !inputVal || selectedCustomer !== null;

                if (isShowingRecent) {
                    renderRecentCustomersList(cachedRecentCustomers);
                }
            }

            return cachedRecentCustomers;
        })
        .catch(err => {
            console.warn('Recent customers fetch error:', err);
            return [];
        })
        .finally(() => {
            recentCustomersPromise = null;
        });

        return recentCustomersPromise;
    }

    /**
     * Show the recent customers dropdown list under customer search input.
     * Reuses in-flight promise if a fetch or refresh is already in progress.
     */
    function showRecentCustomersDropdown() {
        if (!customerSearchResults || editingBookingId !== null) return;

        // Abort any active search query fetch
        if (currentSearchAbortController) {
            currentSearchAbortController.abort();
            currentSearchAbortController = null;
        }

        // If a request is in progress OR cache is empty, ensure loading UI and wait for in-flight promise
        if (recentCustomersPromise !== null || cachedRecentCustomers === null) {
            if (customerSearchResults.style.display !== 'block' || !cachedRecentCustomers) {
                customerSearchResults.innerHTML = `
                    <div class="customer-dropdown-header">
                        <i class="fa-solid fa-clock-rotate-left text-primary"></i> Recent customers
                    </div>
                    <div class="p-3 text-center text-muted small">
                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading recent customers...
                    </div>
                `;
                customerSearchResults.style.display = 'block';
            }

            fetchRecentCustomers().then(customers => {
                if (customerSearchResults && customerSearchResults.style.display === 'block') {
                    const inputVal = customerSearchInput ? customerSearchInput.value.trim() : '';
                    const hasRecentHeader = customerSearchResults.querySelector('.customer-dropdown-header') !== null;
                    if (!inputVal || selectedCustomer !== null || hasRecentHeader) {
                        renderRecentCustomersList(customers);
                    }
                }
            });
            return;
        }

        renderRecentCustomersList(cachedRecentCustomers);
    }

    /**
     * Render recent customers list into dropdown container
     */
    function renderRecentCustomersList(customers) {
        if (!customerSearchResults) return;

        if (!customers || customers.length === 0) {
            customerSearchResults.innerHTML = `
                <div class="customer-dropdown-header">
                    <i class="fa-solid fa-clock-rotate-left text-primary"></i> Recent customers
                </div>
                <div class="p-3 text-center text-muted small">
                    No recent customers found &nbsp;
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
                    openAddCustomerModal();
                });
            }
            customerSearchResults.style.display = 'block';
            currentSearchResults = [];
            activeCustomerIndex = -1;
            return;
        }

        currentSearchResults = customers;
        activeCustomerIndex = -1;

        let listHtml = `
            <div class="customer-dropdown-header">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i> Recent customers
            </div>
        `;

        customers.forEach((c, index) => {
            const escapedName = escapeHtml(c.name);
            const escapedNo = escapeHtml(c.customer_no);
            const escapedMobile = c.mobile ? escapeHtml(c.mobile) : '<span class="text-muted fst-italic">No mobile</span>';

            const avatarHtml = c.profile_image_url
                ? `<img src="${escapeHtml(c.profile_image_url)}" alt="${escapedName}" class="dropdown-party-avatar me-2">`
                : `<div class="dropdown-party-avatar-default me-2"><i class="fa-solid fa-user"></i></div>`;

            const supplierBadge = (c.party_type === 'SUPPLIER')
                ? `<span class="party-supplier-badge ms-2"><i class="fa-solid fa-truck me-1"></i>Supplier</span>`
                : '';

            listHtml += `
                <div class="customer-search-item d-flex align-items-center" data-index="${index}">
                    ${avatarHtml}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-dark customer-item-name d-flex align-items-center">
                            <span class="text-truncate">${escapedName}</span>
                            ${supplierBadge}
                        </div>
                        <div class="small text-muted d-flex justify-content-between mt-1">
                            <span><i class="fa-solid fa-id-badge me-1"></i>${escapedNo}</span>
                            <span><i class="fa-solid fa-phone me-1"></i>${escapedMobile}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        customerSearchResults.innerHTML = listHtml;
        customerSearchResults.style.display = 'block';

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
    // In-memory Customer Search Cache: query -> results (max 50 entries)
    const customerSearchCache = new Map();

    function setCustomerSearchCache(key, data) {
        if (customerSearchCache.size >= 50) {
            const firstKey = customerSearchCache.keys().next().value;
            customerSearchCache.delete(firstKey);
        }
        customerSearchCache.set(key, data);
    }

    function renderCustomerSearchResults(customers, query) {
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
            customerSearchResults.style.display = 'block';
            return;
        }

        let listHtml = '';
        customers.forEach((c, index) => {
            const highlightedName = highlightMatch(c.name, query);
            const highlightedNo = highlightMatch(c.customer_no, query);
            const highlightedMobile = c.mobile ? highlightMatch(c.mobile, query) : '<span class="text-muted fst-italic">No mobile</span>';

            const avatarHtml = c.profile_image_url
                ? `<img src="${escapeHtml(c.profile_image_url)}" alt="${escapeHtml(c.name)}" class="dropdown-party-avatar me-2">`
                : `<div class="dropdown-party-avatar-default me-2"><i class="fa-solid fa-user"></i></div>`;

            const supplierBadge = (c.party_type === 'SUPPLIER')
                ? `<span class="party-supplier-badge ms-2"><i class="fa-solid fa-truck me-1"></i>Supplier</span>`
                : '';

            listHtml += `
                <div class="customer-search-item d-flex align-items-center" data-index="${index}">
                    ${avatarHtml}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-dark customer-item-name d-flex align-items-center">
                            <span class="text-truncate">${highlightedName}</span>
                            ${supplierBadge}
                        </div>
                        <div class="small text-muted d-flex justify-content-between mt-1">
                            <span><i class="fa-solid fa-id-badge me-1"></i>${highlightedNo}</span>
                            <span><i class="fa-solid fa-phone me-1"></i>${highlightedMobile}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        customerSearchResults.innerHTML = listHtml;
        customerSearchResults.style.display = 'block';

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
    }

    function performCustomerSearch(query) {
        if (editingBookingId !== null) return;
        const searchUrl = window.APP_ROUTES ? window.APP_ROUTES.customerSearch : '/customers/search';
        const cacheKey = query.toLowerCase().trim();

        // If in cache, show cached results instantly
        if (customerSearchCache.has(cacheKey)) {
            renderCustomerSearchResults(customerSearchCache.get(cacheKey), query);
        } else {
            customerSearchResults.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Searching customers...
                </div>
            `;
            customerSearchResults.style.display = 'block';
        }

        if (currentSearchAbortController) {
            currentSearchAbortController.abort();
        }
        currentSearchAbortController = new AbortController();

        // Still refresh in background once
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
            setCustomerSearchCache(cacheKey, customers);
            renderCustomerSearchResults(customers, query);
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            if (!customerSearchCache.has(cacheKey)) {
                customerSearchResults.innerHTML = `
                    <div class="p-3 text-center text-danger small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Failed to search. Please try again.
                    </div>
                `;
            }
        });
    }

    if (customerSearchInput) {
        // Show recent customers on focus or click when input is empty OR a customer is already selected
        customerSearchInput.addEventListener('focus', function () {
            if (editingBookingId !== null) return;
            if (!this.value.trim() || selectedCustomer) {
                showRecentCustomersDropdown();
            }
        });

        customerSearchInput.addEventListener('click', function () {
            if (editingBookingId !== null) return;
            if (!this.value.trim() || selectedCustomer) {
                showRecentCustomersDropdown();
            }
        });

        customerSearchInput.addEventListener('input', function () {
            if (editingBookingId !== null) return;
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
                customerClearBtn.style.display = 'none';
                showRecentCustomersDropdown();
                return;
            }

            customerClearBtn.style.display = 'block';

            searchDebounceTimer = setTimeout(() => {
                performCustomerSearch(query);
            }, 200);
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
                    if (activeCustomerIndex >= 0 && currentSearchResults[activeCustomerIndex]) {
                        selectCustomer(currentSearchResults[activeCustomerIndex]);
                    } else if (!selectedCustomer && currentSearchResults[0]) {
                        selectCustomer(currentSearchResults[0]);
                    } else {
                        hideCustomerDropdown();
                    }
                    const typeSelect = document.getElementById('typeSelect');
                    if (typeSelect) typeSelect.focus();
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
            if (editingBookingId !== null) return;
            clearCustomerSelection();
            if (customerSearchInput) {
                customerSearchInput.focus();
                showRecentCustomersDropdown();
            }
        });
    }

    document.addEventListener('click', function (e) {
        // Prevent closing dropdown if user clicked on trade toggle buttons
        const tradeToggle = document.getElementById('tradeToggle') || document.getElementById('tradeToggleContainer');
        if (tradeToggle && tradeToggle.contains(e.target)) {
            return;
        }

        if (customerSearchWrapper && !customerSearchWrapper.contains(e.target)) {
            hideCustomerDropdown();
        }
    });

    // =========================================================================
    // 5. ADD CUSTOMER / NEW PARTY DETAIL MODAL
    // Multipart form submission, image previews, family subprofile, dynamic locations
    // =========================================================================

    const addCustomerModalEl = document.getElementById('addCustomerModal');
    const addCustomerForm = document.getElementById('addCustomerForm');
    const btnOpenAddCustomerModal = document.getElementById('btnOpenAddCustomerModal');
    const btnSaveNewCustomer = document.getElementById('btnSaveNewCustomer');

    // Primary Core Inputs
    const newCustomerName = document.getElementById('newCustomerName');
    const newCustomerMobile = document.getElementById('newCustomerMobile');
    const newCustomerEmail = document.getElementById('newCustomerEmail');
    const newCustomerAddress = document.getElementById('newCustomerAddress');

    // Party Type Toggle
    const partySegmentedToggle = document.getElementById('partySegmentedToggle');
    const btnToggleCustomer = document.getElementById('btnToggleCustomer');
    const btnToggleSupplier = document.getElementById('btnToggleSupplier');
    const newPartyTypeInput = document.getElementById('newPartyTypeInput');

    // Profile Image Elements
    const partyProfileCameraInput = document.getElementById('partyProfileCameraInput');
    const partyProfileFolderInput = document.getElementById('partyProfileFolderInput');
    const btnProfileCamera = document.getElementById('btnProfileCamera');
    const btnProfileFolder = document.getElementById('btnProfileFolder');
    const partyProfilePreview = document.getElementById('partyProfilePreview');
    const partyProfilePlaceholder = document.getElementById('partyProfilePlaceholder');
    const btnRemoveProfileImage = document.getElementById('btnRemoveProfileImage');

    // Family Sub-Profile Elements
    const checkFamilySubProfile = document.getElementById('checkFamilySubProfile');
    const familySubProfileDrawer = document.getElementById('familySubProfileDrawer');
    const parentCustomerSearchInput = document.getElementById('parentCustomerSearchInput');
    const hiddenParentCustomerId = document.getElementById('hiddenParentCustomerId');
    const parentCustomerSearchResults = document.getElementById('parentCustomerSearchResults');
    const parentCustomerClearBtn = document.getElementById('parentCustomerClearBtn');
    const parentCustomerSelectedBadge = document.getElementById('parentCustomerSelectedBadge');

    // Additional Information Section Elements
    const btnToggleAdditionalInfo = document.getElementById('btnToggleAdditionalInfo');
    const iconToggleAdditional = document.getElementById('iconToggleAdditional');
    const additionalInfoSection = document.getElementById('additionalInfoSection');
    const newPartyBirthday = document.getElementById('newPartyBirthday');
    const newPartyAnniversary = document.getElementById('newPartyAnniversary');
    const newPartyAadhaar = document.getElementById('newPartyAadhaar');
    const newPartyPan = document.getElementById('newPartyPan');
    const newPartyGst = document.getElementById('newPartyGst');
    const newPartyLicNo = document.getElementById('newPartyLicNo');
    const newPartyRegNo = document.getElementById('newPartyRegNo');
    const newPartyLogoTitle = document.getElementById('newPartyLogoTitle');
    const newPartyShopName = document.getElementById('newPartyShopName');
    const newPartyState = document.getElementById('newPartyState');
    const newPartyArea = document.getElementById('newPartyArea');
    const newPartyShopAddress = document.getElementById('newPartyShopAddress');

    // Shop Logo Elements
    const partyShopLogoCameraInput = document.getElementById('partyShopLogoCameraInput');
    const partyShopLogoFolderInput = document.getElementById('partyShopLogoFolderInput');
    const btnShopLogoCamera = document.getElementById('btnShopLogoCamera');
    const btnShopLogoFolder = document.getElementById('btnShopLogoFolder');
    const shopLogoPreview = document.getElementById('shopLogoPreview');
    const shopLogoPlaceholder = document.getElementById('shopLogoPlaceholder');
    const btnRemoveShopLogo = document.getElementById('btnRemoveShopLogo');

    // State Tracking
    let selectedProfileImageFile = null;
    let selectedShopLogoFile = null;
    let isAdditionalSectionExpanded = false;
    let selectedParentCustomer = null;
    let parentSearchDebounceTimer = null;
    let parentSearchAbortController = null;

    /**
     * Set active party type (CUSTOMER or SUPPLIER)
     */
    function setPartyType(type) {
        if (newPartyTypeInput) newPartyTypeInput.value = type;
        if (partySegmentedToggle) {
            partySegmentedToggle.setAttribute('data-type', type);
        }
        if (btnToggleCustomer && btnToggleSupplier) {
            if (type === 'CUSTOMER') {
                btnToggleCustomer.classList.add('active');
                btnToggleSupplier.classList.remove('active');
            } else {
                btnToggleSupplier.classList.add('active');
                btnToggleCustomer.classList.remove('active');
            }
        }
    }

    if (btnToggleCustomer) {
        btnToggleCustomer.addEventListener('click', function () {
            setPartyType('CUSTOMER');
        });
    }

    if (btnToggleSupplier) {
        btnToggleSupplier.addEventListener('click', function () {
            setPartyType('SUPPLIER');
        });
    }

    /**
     * Profile Image Selection & Removal
     */
    function handleProfileImageSelected(file) {
        if (!file) return;
        selectedProfileImageFile = file;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (partyProfilePreview && partyProfilePlaceholder && btnRemoveProfileImage) {
                partyProfilePreview.src = e.target.result;
                partyProfilePreview.style.display = 'block';
                partyProfilePlaceholder.style.display = 'none';
                btnRemoveProfileImage.style.display = 'inline-flex';
            }
        };
        reader.readAsDataURL(file);
    }

    function clearProfileImageSelection() {
        selectedProfileImageFile = null;
        if (partyProfileCameraInput) partyProfileCameraInput.value = '';
        if (partyProfileFolderInput) partyProfileFolderInput.value = '';
        if (partyProfilePreview) {
            partyProfilePreview.src = '';
            partyProfilePreview.style.display = 'none';
        }
        if (partyProfilePlaceholder) partyProfilePlaceholder.style.display = 'block';
        if (btnRemoveProfileImage) btnRemoveProfileImage.style.display = 'none';
    }

    if (btnProfileCamera && partyProfileCameraInput) {
        btnProfileCamera.addEventListener('click', () => partyProfileCameraInput.click());
        partyProfileCameraInput.addEventListener('change', function () {
            if (this.files && this.files[0]) handleProfileImageSelected(this.files[0]);
        });
    }

    if (btnProfileFolder && partyProfileFolderInput) {
        btnProfileFolder.addEventListener('click', () => partyProfileFolderInput.click());
        partyProfileFolderInput.addEventListener('change', function () {
            if (this.files && this.files[0]) handleProfileImageSelected(this.files[0]);
        });
    }

    if (btnRemoveProfileImage) {
        btnRemoveProfileImage.addEventListener('click', clearProfileImageSelection);
    }

    /**
     * Shop Logo Selection & Removal
     */
    function handleShopLogoSelected(file) {
        if (!file) return;
        selectedShopLogoFile = file;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (shopLogoPreview && shopLogoPlaceholder && btnRemoveShopLogo) {
                shopLogoPreview.src = e.target.result;
                shopLogoPreview.style.display = 'block';
                shopLogoPlaceholder.style.display = 'none';
                btnRemoveShopLogo.style.display = 'inline-flex';
            }
        };
        reader.readAsDataURL(file);
    }

    function clearShopLogoSelection() {
        selectedShopLogoFile = null;
        if (partyShopLogoCameraInput) partyShopLogoCameraInput.value = '';
        if (partyShopLogoFolderInput) partyShopLogoFolderInput.value = '';
        if (shopLogoPreview) {
            shopLogoPreview.src = '';
            shopLogoPreview.style.display = 'none';
        }
        if (shopLogoPlaceholder) shopLogoPlaceholder.style.display = 'block';
        if (btnRemoveShopLogo) btnRemoveShopLogo.style.display = 'none';
    }

    if (btnShopLogoCamera && partyShopLogoCameraInput) {
        btnShopLogoCamera.addEventListener('click', () => partyShopLogoCameraInput.click());
        partyShopLogoCameraInput.addEventListener('change', function () {
            if (this.files && this.files[0]) handleShopLogoSelected(this.files[0]);
        });
    }

    if (btnShopLogoFolder && partyShopLogoFolderInput) {
        btnShopLogoFolder.addEventListener('click', () => partyShopLogoFolderInput.click());
        partyShopLogoFolderInput.addEventListener('change', function () {
            if (this.files && this.files[0]) handleShopLogoSelected(this.files[0]);
        });
    }

    if (btnRemoveShopLogo) {
        btnRemoveShopLogo.addEventListener('click', clearShopLogoSelection);
    }

    /**
     * Family Sub-Profile Drawer & Parent Customer Selection
     */
    if (checkFamilySubProfile) {
        checkFamilySubProfile.addEventListener('change', function () {
            if (this.checked) {
                if (familySubProfileDrawer) familySubProfileDrawer.style.display = 'block';
                if (parentCustomerSearchInput) parentCustomerSearchInput.focus();
            } else {
                if (familySubProfileDrawer) familySubProfileDrawer.style.display = 'none';
                clearParentCustomerSelection();
            }
        });
    }

    function clearParentCustomerSelection() {
        selectedParentCustomer = null;
        if (hiddenParentCustomerId) hiddenParentCustomerId.value = '';
        if (parentCustomerSearchInput) parentCustomerSearchInput.value = '';
        if (parentCustomerClearBtn) parentCustomerClearBtn.style.display = 'none';
        if (parentCustomerSelectedBadge) {
            parentCustomerSelectedBadge.style.display = 'none';
            parentCustomerSelectedBadge.innerHTML = '';
        }
        if (parentCustomerSearchResults) {
            parentCustomerSearchResults.style.display = 'none';
            parentCustomerSearchResults.innerHTML = '';
        }
    }

    if (parentCustomerClearBtn) {
        parentCustomerClearBtn.addEventListener('click', function () {
            clearParentCustomerSelection();
            if (parentCustomerSearchInput) parentCustomerSearchInput.focus();
        });
    }

    if (parentCustomerSearchInput) {
        parentCustomerSearchInput.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(parentSearchDebounceTimer);

            if (selectedParentCustomer) {
                selectedParentCustomer = null;
                if (hiddenParentCustomerId) hiddenParentCustomerId.value = '';
                if (parentCustomerSelectedBadge) parentCustomerSelectedBadge.style.display = 'none';
            }

            if (query.length < 1) {
                if (parentCustomerClearBtn) parentCustomerClearBtn.style.display = 'none';
                if (parentCustomerSearchResults) parentCustomerSearchResults.style.display = 'none';
                return;
            }

            if (parentCustomerClearBtn) parentCustomerClearBtn.style.display = 'block';

            parentSearchDebounceTimer = setTimeout(() => {
                performParentCustomerSearch(query);
            }, 200);
        });
    }

    function performParentCustomerSearch(query) {
        if (!parentCustomerSearchResults) return;
        const searchUrl = window.APP_ROUTES ? window.APP_ROUTES.customerSearch : '/customers/search';

        if (parentSearchAbortController) {
            parentSearchAbortController.abort();
        }
        parentSearchAbortController = new AbortController();

        parentCustomerSearchResults.innerHTML = `
            <div class="p-2 text-center text-muted small">
                <i class="fa-solid fa-spinner fa-spin me-2"></i> Searching...
            </div>
        `;
        parentCustomerSearchResults.style.display = 'block';

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            signal: parentSearchAbortController.signal
        })
        .then(res => res.json())
        .then(customers => {
            if (!customers || customers.length === 0) {
                parentCustomerSearchResults.innerHTML = `
                    <div class="p-2 text-center text-muted small">No customer found</div>
                `;
                return;
            }

            let html = '';
            customers.forEach((c, idx) => {
                const escapedName = escapeHtml(c.name);
                const escapedNo = escapeHtml(c.customer_no);
                const escapedMobile = c.mobile ? escapeHtml(c.mobile) : 'No mobile';

                html += `
                    <div class="customer-search-item py-1 px-2" data-parent-index="${idx}">
                        <div class="fw-bold small text-dark">${escapedName} (${escapedNo})</div>
                        <div class="text-muted" style="font-size: 11px;"><i class="fa-solid fa-phone me-1"></i>${escapedMobile}</div>
                    </div>
                `;
            });

            parentCustomerSearchResults.innerHTML = html;

            parentCustomerSearchResults.querySelectorAll('.customer-search-item').forEach(el => {
                el.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-parent-index'), 10);
                    const chosen = customers[idx];
                    if (chosen) {
                        selectedParentCustomer = chosen;
                        if (hiddenParentCustomerId) hiddenParentCustomerId.value = chosen.id;
                        if (parentCustomerSearchInput) parentCustomerSearchInput.value = `${chosen.name} (${chosen.customer_no})`;
                        if (parentCustomerClearBtn) parentCustomerClearBtn.style.display = 'block';
                        if (parentCustomerSearchResults) parentCustomerSearchResults.style.display = 'none';

                        if (parentCustomerSelectedBadge) {
                            parentCustomerSelectedBadge.innerHTML = `
                                <i class="fa-solid fa-check-circle text-success me-1"></i> Linked Main Customer: <strong>${escapeHtml(chosen.name)}</strong> (Mobile: ${escapeHtml(chosen.mobile || 'N/A')})
                            `;
                            parentCustomerSelectedBadge.style.display = 'block';
                        }

                        // Auto-fill mobile if currently empty
                        if (newCustomerMobile && !newCustomerMobile.value.trim() && chosen.mobile) {
                            newCustomerMobile.value = chosen.mobile;
                        }
                    }
                });
            });
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            parentCustomerSearchResults.innerHTML = `
                <div class="p-2 text-center text-danger small">Search failed</div>
            `;
        });
    }

    /**
     * State & Dynamic District Population
     */
    function initStatesDropdown() {
        if (!newPartyState || newPartyState.options.length > 1) return;
        const locations = window.INDIA_LOCATIONS;
        if (locations && Array.isArray(locations.states)) {
            locations.states.forEach(state => {
                const opt = document.createElement('option');
                opt.value = state;
                opt.textContent = state;
                newPartyState.appendChild(opt);
            });
        }
    }

    function renderDistrictField(stateName) {
        const districtContainer = document.getElementById('districtContainer');
        if (!districtContainer) return;

        const locations = window.INDIA_LOCATIONS;
        const districtList = (locations && locations.districts && locations.districts[stateName])
            ? locations.districts[stateName]
            : null;

        if (districtList && Array.isArray(districtList) && districtList.length > 0) {
            let optionsHtml = '<option value="">Select District</option>';
            districtList.forEach(d => {
                optionsHtml += `<option value="${escapeHtml(d)}">${escapeHtml(d)}</option>`;
            });
            districtContainer.innerHTML = `
                <select class="form-select party-input-pill" id="newPartyDistrict" name="district">
                    ${optionsHtml}
                </select>
            `;
        } else {
            districtContainer.innerHTML = `
                <input type="text" class="form-control party-input-pill" id="newPartyDistrict" name="district" placeholder="Enter District" maxlength="60">
            `;
        }
    }

    if (newPartyState) {
        newPartyState.addEventListener('change', function () {
            renderDistrictField(this.value);
        });
    }

    /**
     * Set min and max on birthday and anniversary date inputs (min: 1900-01-01, max: today)
     */
    function initPartyDateInputs() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const todayStr = `${y}-${m}-${d}`;

        if (newPartyBirthday) {
            newPartyBirthday.min = '1900-01-01';
            newPartyBirthday.max = todayStr;
        }
        if (newPartyAnniversary) {
            newPartyAnniversary.min = '1900-01-01';
            newPartyAnniversary.max = todayStr;
        }
    }

    /**
     * Validate a date input field (year 1900 to current year / not future)
     * Returns true if valid or empty; false if invalid
     */
    function validatePartyDateInput(input, errorElId) {
        if (!input) return true;
        const errorEl = document.getElementById(errorElId);

        const showError = () => {
            input.classList.add('is-invalid');
            if (errorEl) {
                errorEl.textContent = 'Enter a valid date (year 1900 to current year)';
                errorEl.style.display = 'block';
            }
        };

        const clearError = () => {
            input.classList.remove('is-invalid');
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.style.display = 'none';
            }
        };

        // If field has invalid input (e.g. 5-digit year or malformed input)
        if (input.validity && input.validity.badInput) {
            showError();
            return false;
        }

        const val = input.value ? input.value.trim() : '';
        if (!val) {
            clearError();
            return true;
        }

        const now = new Date();
        const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        const parts = val.split('-');
        const year = parseInt(parts[0], 10);

        if (isNaN(year) || year < 1900 || val > todayStr || (input.validity && (input.validity.rangeUnderflow || input.validity.rangeOverflow))) {
            showError();
            return false;
        }

        clearError();
        return true;
    }

    /**
     * Expand / Collapse Additional Information Section with smooth animation (~250ms)
     * Does not leave a fixed max-height or overflow: hidden after fully opened.
     */
    function expandAdditionalSection() {
        if (!additionalInfoSection) return;
        isAdditionalSectionExpanded = true;
        if (btnToggleAdditionalInfo) {
            btnToggleAdditionalInfo.classList.add('expanded');
        }
        if (iconToggleAdditional) {
            iconToggleAdditional.classList.remove('fa-plus');
            iconToggleAdditional.classList.add('fa-minus');
        }

        additionalInfoSection.style.overflow = 'hidden';
        additionalInfoSection.style.maxHeight = '';
        additionalInfoSection.classList.add('expanded');

        const onTransitionEnd = function (e) {
            if (e && e.target !== additionalInfoSection) return;
            additionalInfoSection.removeEventListener('transitionend', onTransitionEnd);
            if (isAdditionalSectionExpanded) {
                additionalInfoSection.style.maxHeight = 'none';
                additionalInfoSection.style.overflow = 'visible';
            }
        };
        additionalInfoSection.addEventListener('transitionend', onTransitionEnd);
        setTimeout(() => {
            if (isAdditionalSectionExpanded && additionalInfoSection.style.maxHeight !== 'none') {
                onTransitionEnd();
            }
        }, 300);

        // Smoothly scroll modal body so the heading comes into view
        const headingEl = additionalInfoSection.querySelector('.additional-divider-wrapper') || additionalInfoSection;
        if (headingEl) {
            setTimeout(() => {
                headingEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 60);
        }
    }

    function collapseAdditionalSection(immediate = false) {
        if (!additionalInfoSection) return;
        isAdditionalSectionExpanded = false;
        if (btnToggleAdditionalInfo) {
            btnToggleAdditionalInfo.classList.remove('expanded');
        }
        if (iconToggleAdditional) {
            iconToggleAdditional.classList.remove('fa-minus');
            iconToggleAdditional.classList.add('fa-plus');
        }

        if (immediate) {
            additionalInfoSection.classList.remove('expanded');
            additionalInfoSection.style.maxHeight = '';
            additionalInfoSection.style.overflow = '';
            return;
        }

        additionalInfoSection.style.overflow = 'hidden';
        additionalInfoSection.style.maxHeight = additionalInfoSection.scrollHeight + 'px';
        void additionalInfoSection.offsetHeight;

        additionalInfoSection.classList.remove('expanded');
        additionalInfoSection.style.maxHeight = '0px';

        const onCollapseEnd = function (e) {
            if (e && e.target !== additionalInfoSection) return;
            additionalInfoSection.removeEventListener('transitionend', onCollapseEnd);
            if (!isAdditionalSectionExpanded) {
                additionalInfoSection.style.maxHeight = '';
                additionalInfoSection.style.overflow = '';
            }
        };
        additionalInfoSection.addEventListener('transitionend', onCollapseEnd);
        setTimeout(() => {
            if (!isAdditionalSectionExpanded) {
                onCollapseEnd();
            }
        }, 300);
    }

    if (btnToggleAdditionalInfo) {
        btnToggleAdditionalInfo.addEventListener('click', function () {
            if (isAdditionalSectionExpanded) {
                collapseAdditionalSection();
            } else {
                expandAdditionalSection();
            }
        });
    }

    /**
     * Open & Reset Add Customer / Party Modal
     */
    function openAddCustomerModal(prefillName = '') {
        if (editingBookingId !== null) return;
        resetAddCustomerModalErrors();
        initStatesDropdown();
        initPartyDateInputs();

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
        const errorEls = document.querySelectorAll('#addCustomerForm .field-error-text');
        errorEls.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        const invalidInputs = document.querySelectorAll('#addCustomerForm .is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
    }

    function resetAddCustomerModalComplete() {
        if (addCustomerForm) addCustomerForm.reset();
        resetAddCustomerModalErrors();
        clearProfileImageSelection();
        clearShopLogoSelection();
        clearParentCustomerSelection();
        setPartyType('CUSTOMER');
        collapseAdditionalSection(true);
        renderDistrictField('');
        initPartyDateInputs();
    }

    if (btnOpenAddCustomerModal) {
        btnOpenAddCustomerModal.addEventListener('click', function () {
            if (editingBookingId !== null) return;
            const queryToPrefill = !selectedCustomer && customerSearchInput ? customerSearchInput.value.trim() : '';
            openAddCustomerModal(queryToPrefill);
        });
    }

    if (addCustomerModalEl) {
        addCustomerModalEl.addEventListener('shown.bs.modal', function () {
            initStatesDropdown();
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

    // Input formatters
    if (newCustomerMobile) {
        newCustomerMobile.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    if (newPartyAadhaar) {
        newPartyAadhaar.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });
    }

    if (newPartyPan) {
        newPartyPan.addEventListener('input', function () {
            this.value = this.value.toUpperCase().slice(0, 10);
        });
    }

    if (newPartyGst) {
        newPartyGst.addEventListener('input', function () {
            this.value = this.value.toUpperCase().slice(0, 15);
        });
    }

    // Birthday and anniversary date inputs change and blur validation
    if (newPartyBirthday) {
        newPartyBirthday.addEventListener('change', function () {
            validatePartyDateInput(newPartyBirthday, 'errorNewPartyBirthday');
        });
        newPartyBirthday.addEventListener('blur', function () {
            validatePartyDateInput(newPartyBirthday, 'errorNewPartyBirthday');
        });
    }

    if (newPartyAnniversary) {
        newPartyAnniversary.addEventListener('change', function () {
            validatePartyDateInput(newPartyAnniversary, 'errorNewPartyAnniversary');
        });
        newPartyAnniversary.addEventListener('blur', function () {
            validatePartyDateInput(newPartyAnniversary, 'errorNewPartyAnniversary');
        });
    }

    /**
     * Submit New Party Detail via AJAX (multipart/form-data)
     */
    if (btnSaveNewCustomer) {
        btnSaveNewCustomer.addEventListener('click', function () {
            resetAddCustomerModalErrors();

            const nameVal = newCustomerName ? newCustomerName.value.trim() : '';
            const mobileVal = newCustomerMobile ? newCustomerMobile.value.trim() : '';
            const isFamily = checkFamilySubProfile && checkFamilySubProfile.checked;
            const parentIdVal = hiddenParentCustomerId ? hiddenParentCustomerId.value.trim() : '';
            const aadhaarVal = newPartyAadhaar ? newPartyAadhaar.value.trim() : '';
            const panVal = newPartyPan ? newPartyPan.value.trim().toUpperCase() : '';

            // Frontend validation checks before sending
            if (!nameVal) {
                const err = document.getElementById('errorNewCustomerName');
                if (err) {
                    err.textContent = 'Party name is required';
                    err.style.display = 'block';
                }
                newCustomerName.classList.add('is-invalid');
                newCustomerName.focus();
                return;
            }

            if (!mobileVal) {
                const err = document.getElementById('errorNewCustomerMobile');
                if (err) {
                    err.textContent = 'Mobile number is required';
                    err.style.display = 'block';
                }
                newCustomerMobile.classList.add('is-invalid');
                newCustomerMobile.focus();
                return;
            }

            if (!/^\d{10}$/.test(mobileVal)) {
                const err = document.getElementById('errorNewCustomerMobile');
                if (err) {
                    err.textContent = 'Mobile number must be 10 digits';
                    err.style.display = 'block';
                }
                newCustomerMobile.classList.add('is-invalid');
                newCustomerMobile.focus();
                return;
            }

            if (isFamily && !parentIdVal) {
                const err = document.getElementById('errorParentCustomerId');
                if (err) {
                    err.textContent = 'Please select the main customer for this family sub-profile';
                    err.style.display = 'block';
                }
                if (parentCustomerSearchInput) {
                    parentCustomerSearchInput.classList.add('is-invalid');
                    parentCustomerSearchInput.focus();
                }
                return;
            }

            // Validate birthday and anniversary dates
            const isBirthdayValid = validatePartyDateInput(newPartyBirthday, 'errorNewPartyBirthday');
            const isAnniversaryValid = validatePartyDateInput(newPartyAnniversary, 'errorNewPartyAnniversary');

            if (!isBirthdayValid || !isAnniversaryValid) {
                expandAdditionalSection();
                if (!isBirthdayValid && newPartyBirthday) {
                    newPartyBirthday.focus();
                } else if (!isAnniversaryValid && newPartyAnniversary) {
                    newPartyAnniversary.focus();
                }
                return;
            }

            if (aadhaarVal && !/^\d{12}$/.test(aadhaarVal)) {
                expandAdditionalSection();
                const err = document.getElementById('errorNewPartyAadhaar');
                if (err) {
                    err.textContent = 'Aadhaar number must be 12 digits';
                    err.style.display = 'block';
                }
                newPartyAadhaar.classList.add('is-invalid');
                newPartyAadhaar.focus();
                return;
            }

            if (panVal && !/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(panVal)) {
                expandAdditionalSection();
                const err = document.getElementById('errorNewPartyPan');
                if (err) {
                    err.textContent = 'Invalid PAN format (e.g. ABCDE1234F)';
                    err.style.display = 'block';
                }
                newPartyPan.classList.add('is-invalid');
                newPartyPan.focus();
                return;
            }

            // Construct FormData payload
            const formData = new FormData();
            formData.append('name', nameVal);
            formData.append('mobile', mobileVal);
            formData.append('party_type', newPartyTypeInput ? newPartyTypeInput.value : 'CUSTOMER');
            formData.append('is_family_sub_profile', isFamily ? '1' : '0');

            if (isFamily && parentIdVal) {
                formData.append('parent_customer_id', parentIdVal);
            }

            const emailVal = newCustomerEmail ? newCustomerEmail.value.trim() : '';
            if (emailVal) formData.append('email', emailVal);

            const addressVal = newCustomerAddress ? newCustomerAddress.value.trim() : '';
            if (addressVal) formData.append('address', addressVal);

            // Additional section fields
            const birthdayVal = newPartyBirthday ? newPartyBirthday.value : '';
            if (birthdayVal) formData.append('birthday', birthdayVal);

            const anniversaryVal = newPartyAnniversary ? newPartyAnniversary.value : '';
            if (anniversaryVal) formData.append('anniversary', anniversaryVal);

            if (aadhaarVal) formData.append('aadhaar_no', aadhaarVal);
            if (panVal) formData.append('pan_no', panVal);

            const gstVal = newPartyGst ? newPartyGst.value.trim().toUpperCase() : '';
            if (gstVal) formData.append('gst_no', gstVal);

            const licVal = newPartyLicNo ? newPartyLicNo.value.trim() : '';
            if (licVal) formData.append('lic_no', licVal);

            const regVal = newPartyRegNo ? newPartyRegNo.value.trim() : '';
            if (regVal) formData.append('reg_no', regVal);

            const logoTitleVal = newPartyLogoTitle ? newPartyLogoTitle.value.trim() : '';
            if (logoTitleVal) formData.append('logo_title', logoTitleVal);

            const shopNameVal = newPartyShopName ? newPartyShopName.value.trim() : '';
            if (shopNameVal) formData.append('shop_name', shopNameVal);

            const stateVal = newPartyState ? newPartyState.value : '';
            if (stateVal) formData.append('state', stateVal);

            const districtInputEl = document.getElementById('newPartyDistrict');
            const districtVal = districtInputEl ? districtInputEl.value.trim() : '';
            if (districtVal) formData.append('district', districtVal);

            const areaVal = newPartyArea ? newPartyArea.value.trim() : '';
            if (areaVal) formData.append('area', areaVal);

            const shopAddressVal = newPartyShopAddress ? newPartyShopAddress.value.trim() : '';
            if (shopAddressVal) formData.append('shop_address', shopAddressVal);

            // Append image files if selected
            if (selectedProfileImageFile) {
                formData.append('profile_image', selectedProfileImageFile);
            }
            if (selectedShopLogoFile) {
                formData.append('shop_logo', selectedShopLogoFile);
            }

            // Loading state
            btnSaveNewCustomer.disabled = true;
            const originalButtonHtml = btnSaveNewCustomer.innerHTML;
            btnSaveNewCustomer.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';

            const storeUrl = window.APP_ROUTES ? window.APP_ROUTES.customerStore : '/customers';

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        let hasAdditionalError = false;
                        let firstErrorField = null;

                        const errorTargetMap = {
                            name: { id: 'errorNewCustomerName', input: newCustomerName, inAdditional: false },
                            mobile: { id: 'errorNewCustomerMobile', input: newCustomerMobile, inAdditional: false },
                            email: { id: 'errorNewCustomerEmail', input: newCustomerEmail, inAdditional: false },
                            address: { id: 'errorNewCustomerAddress', input: newCustomerAddress, inAdditional: false },
                            parent_customer_id: { id: 'errorParentCustomerId', input: parentCustomerSearchInput, inAdditional: false },
                            is_family_sub_profile: { id: 'errorParentCustomerId', input: parentCustomerSearchInput, inAdditional: false },
                            profile_image: { id: 'errorPartyProfileImage', input: null, inAdditional: false },
                            birthday: { id: 'errorNewPartyBirthday', input: newPartyBirthday, inAdditional: true },
                            anniversary: { id: 'errorNewPartyAnniversary', input: newPartyAnniversary, inAdditional: true },
                            aadhaar_no: { id: 'errorNewPartyAadhaar', input: newPartyAadhaar, inAdditional: true },
                            pan_no: { id: 'errorNewPartyPan', input: newPartyPan, inAdditional: true },
                            gst_no: { id: 'errorNewPartyGst', input: newPartyGst, inAdditional: true },
                            lic_no: { id: 'errorNewPartyLicNo', input: newPartyLicNo, inAdditional: true },
                            reg_no: { id: 'errorNewPartyRegNo', input: newPartyRegNo, inAdditional: true },
                            logo_title: { id: 'errorNewPartyLogoTitle', input: newPartyLogoTitle, inAdditional: true },
                            shop_name: { id: 'errorNewPartyShopName', input: newPartyShopName, inAdditional: true },
                            state: { id: 'errorNewPartyState', input: newPartyState, inAdditional: true },
                            district: { id: 'errorNewPartyDistrict', input: document.getElementById('newPartyDistrict'), inAdditional: true },
                            area: { id: 'errorNewPartyArea', input: newPartyArea, inAdditional: true },
                            shop_address: { id: 'errorNewPartyShopAddress', input: newPartyShopAddress, inAdditional: true },
                            shop_logo: { id: 'errorPartyShopLogo', input: null, inAdditional: true },
                        };

                        Object.keys(data.errors).forEach(fieldKey => {
                            const mapping = errorTargetMap[fieldKey];
                            if (mapping) {
                                const errEl = document.getElementById(mapping.id);
                                if (errEl) {
                                    errEl.textContent = data.errors[fieldKey][0];
                                    errEl.style.display = 'block';
                                }
                                if (mapping.input) {
                                    mapping.input.classList.add('is-invalid');
                                }
                                if (mapping.inAdditional) {
                                    hasAdditionalError = true;
                                }
                                if (!firstErrorField && mapping.input) {
                                    firstErrorField = mapping.input;
                                }
                            }
                        });

                        // If error is in a collapsed field, auto-expand the section and scroll to it
                        if (hasAdditionalError && !isAdditionalSectionExpanded) {
                            expandAdditionalSection();
                        }

                        if (firstErrorField) {
                            setTimeout(() => {
                                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstErrorField.focus();
                            }, 100);
                        }
                    } else {
                        showToast(data.message || 'Failed to create party.', 'error');
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

                resetAddCustomerModalComplete();
                selectCustomer(newCustomer);
                showToast('Party added', 'success');

                // Refresh recent customers cache after creating a new party
                fetchRecentCustomers(true);
            })
            .catch(err => {
                console.warn('Party store response:', err);
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
    const tradeToggleContainer = document.getElementById('tradeToggle') || document.getElementById('tradeToggleContainer');
    const hiddenTradeType = document.getElementById('hiddenTradeType');
    const labelWeight = document.getElementById('labelWeight');
    const labelRate = document.getElementById('labelRate');

    /**
     * Return rate unit label from config: 10 -> "(/10GM)", 1 -> "(/GM)", any other n -> "(/nGM)"
     */
    function getRateUnitLabel() {
        const unit = (window.BHAV_CONFIG && window.BHAV_CONFIG.rate_unit_grams) ? window.BHAV_CONFIG.rate_unit_grams : 10;
        return unit == 1 ? '(/GM)' : `(/${unit}GM)`;
    }

    /**
     * Set active trade mode in UI and form hidden input.
     * Updates sliding pill container data-mode/classes (green for BUY, coral/red for SELL).
     * Why: Safe to call programmatically from edit mode without triggering auto-focus or dropdown.
     */
    function setTradeMode(mode) {
        if (hiddenTradeType) hiddenTradeType.value = mode;

        if (tradeToggleContainer) {
            tradeToggleContainer.setAttribute('data-mode', mode);
            if (mode === 'BUY') {
                tradeToggleContainer.classList.add('mode-buy');
                tradeToggleContainer.classList.remove('mode-sell');
            } else {
                tradeToggleContainer.classList.add('mode-sell');
                tradeToggleContainer.classList.remove('mode-buy');
            }
        }

        const unitLabel = getRateUnitLabel();
        if (mode === 'BUY') {
            btnBuy.classList.add('active');
            btnSell.classList.remove('active');
            if (labelWeight) labelWeight.textContent = 'BUY WEIGHT :';
            if (labelRate) labelRate.textContent = `BUY RATE ${unitLabel} :`;
            if (labelPayment) labelPayment.textContent = 'GIVEN :';
        } else {
            btnSell.classList.add('active');
            btnBuy.classList.remove('active');
            if (labelWeight) labelWeight.textContent = 'SELL WEIGHT :';
            if (labelRate) labelRate.textContent = `SELL RATE ${unitLabel} :`;
            if (labelPayment) labelPayment.textContent = 'RECEIVED :';
        }
    }

    /**
     * User clicks trade toggle: switches mode, focuses customer input, selects text,
     * and opens recent customers dropdown while keeping existing customer selection.
     */
    function handleTradeToggleSwitch(mode) {
        setTradeMode(mode);

        if (editingBookingId !== null) {
            return;
        }

        if (customerSearchInput) {
            customerSearchInput.focus();
            customerSearchInput.select();
            showRecentCustomersDropdown();
        }
    }

    if (btnBuy && btnSell) {
        btnBuy.addEventListener('click', function () {
            handleTradeToggleSwitch('BUY');
        });

        btnSell.addEventListener('click', function () {
            handleTradeToggleSwitch('SELL');
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

    // Payment Section State & DOM Elements
    let bookingPayments = [];
    let nextPaymentId = 1;
    let currentPaymentMode = 'CASH';

    const labelPayment = document.getElementById('labelPayment');
    const paymentModeToggle = document.getElementById('paymentModeToggle');
    const paymentModeTogglePill = document.getElementById('paymentModeTogglePill');
    const btnPaymentCash = document.getElementById('btnPaymentCash');
    const btnPaymentOnline = document.getElementById('btnPaymentOnline');
    const paymentOnlineFields = document.getElementById('paymentOnlineFields');
    const paymentOnlineMethod = document.getElementById('paymentOnlineMethod');
    const paymentRefNo = document.getElementById('paymentRefNo');
    const inputPaymentAmount = document.getElementById('inputPaymentAmount');
    const btnAddPayment = document.getElementById('btnAddPayment');
    const paymentError = document.getElementById('paymentError');

    const paymentEntriesRowWrapper = document.getElementById('paymentEntriesRowWrapper');
    const paymentEntriesTableBody = document.getElementById('paymentEntriesTableBody');
    const paymentEntriesCountBadge = document.getElementById('paymentEntriesCountBadge');

    const paymentSummaryRow = document.getElementById('paymentSummaryRow');
    const paymentSummaryBox = document.getElementById('paymentSummaryBox');
    const paymentSummaryPaid = document.getElementById('paymentSummaryPaid');
    const paymentSummaryBalance = document.getElementById('paymentSummaryBalance');

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

        // Update Payment Summary & Validation
        if (typeof updatePaymentSummary === 'function') {
            updatePaymentSummary(totals.grandAmount);
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

    // =========================================================================
    // 7B. PAYMENT SECTION & ENTRIES
    // Cash / Online mode toggle, multi-payment entries, inline edit, summary line
    // =========================================================================

    /**
     * Compute total payments and balance:
     * - Paid: sum of all confirmed rows in bookingPayments + currently typed payment amount (if > 0)
     * - Balance: booking grand total - total paid
     */
    function calculatePaymentTotals(bookingGrandTotal) {
        let confirmedPaid = 0;
        for (const p of bookingPayments) {
            confirmedPaid += parseFloat(p.amount) || 0;
        }
        confirmedPaid = Math.round(confirmedPaid * 100) / 100;

        const curPayAmt = parseFloat(inputPaymentAmount ? inputPaymentAmount.value : 0) || 0;
        const totalPaid = Math.round((confirmedPaid + curPayAmt) * 100) / 100;
        const balance = Math.round((bookingGrandTotal - totalPaid) * 100) / 100;

        return {
            confirmedPaid,
            curPayAmt,
            totalPaid,
            balance
        };
    }

    /**
     * Update summary line: "Paid: ₹x  |  Balance: ₹y"
     * Balance in red if > 0, green if 0.
     * Also checks if total drops below paid (e.g. when weights/rates change).
     */
    function updatePaymentSummary(bookingGrandTotal) {
        const payTotals = calculatePaymentTotals(bookingGrandTotal);

        if (paymentSummaryPaid) {
            paymentSummaryPaid.textContent = `₹${formatIndianCurrency(payTotals.totalPaid)}`;
        }

        if (paymentSummaryBalance) {
            paymentSummaryBalance.textContent = `₹${formatIndianCurrency(Math.max(0, payTotals.balance))}`;
            if (payTotals.balance > 0) {
                paymentSummaryBalance.className = 'text-danger';
            } else {
                paymentSummaryBalance.className = 'text-success';
            }
        }

        // Frontend check: total paid must not exceed booking total amount
        if (payTotals.totalPaid > bookingGrandTotal && bookingGrandTotal >= 0 && payTotals.totalPaid > 0) {
            if (paymentError) {
                paymentError.textContent = `Payment cannot be more than booking amount ₹${formatIndianCurrency(bookingGrandTotal)}`;
                paymentError.style.display = 'block';
            }
            if (inputPaymentAmount && payTotals.curPayAmt > 0) {
                inputPaymentAmount.classList.add('is-invalid');
            }
        } else {
            if (paymentError && paymentError.textContent.startsWith('Payment cannot be more than')) {
                paymentError.textContent = '';
                paymentError.style.display = 'none';
                if (inputPaymentAmount) inputPaymentAmount.classList.remove('is-invalid');
            }
        }
    }

    /**
     * Switch payment mode between CASH and ONLINE.
     * Cash = green, Online = blue. Shows/hides method & ref inputs.
     */
    function setPaymentMode(mode) {
        currentPaymentMode = mode;
        if (paymentModeToggle) {
            paymentModeToggle.setAttribute('data-mode', mode);
            if (mode === 'CASH') {
                paymentModeToggle.classList.add('mode-cash');
                paymentModeToggle.classList.remove('mode-online');
            } else {
                paymentModeToggle.classList.add('mode-online');
                paymentModeToggle.classList.remove('mode-cash');
            }
        }

        if (btnPaymentCash && btnPaymentOnline) {
            if (mode === 'CASH') {
                btnPaymentCash.classList.add('active');
                btnPaymentOnline.classList.remove('active');
            } else {
                btnPaymentOnline.classList.add('active');
                btnPaymentCash.classList.remove('active');
            }
        }

        if (paymentOnlineFields) {
            if (mode === 'ONLINE') {
                paymentOnlineFields.classList.remove('d-none');
                paymentOnlineFields.classList.add('d-flex');
            } else {
                paymentOnlineFields.classList.remove('d-flex');
                paymentOnlineFields.classList.add('d-none');
            }
        }
    }

    if (btnPaymentCash) {
        btnPaymentCash.addEventListener('click', function (e) {
            e.preventDefault();
            setPaymentMode('CASH');
        });
    }

    if (btnPaymentOnline) {
        btnPaymentOnline.addEventListener('click', function (e) {
            e.preventDefault();
            setPaymentMode('ONLINE');
        });
    }

    /**
     * Add current payment line as an entry in bookingPayments
     */
    function addPaymentEntryRow() {
        const amtVal = parseFloat(inputPaymentAmount ? inputPaymentAmount.value : 0);
        const bookingTotals = calculateTotals();
        const bookingGrandTotal = bookingTotals.grandAmount;

        let confirmedPaid = 0;
        for (const p of bookingPayments) {
            confirmedPaid += parseFloat(p.amount) || 0;
        }

        if (!amtVal || isNaN(amtVal) || amtVal <= 0) {
            if (paymentError) {
                paymentError.textContent = 'Payment amount must be greater than 0.';
                paymentError.style.display = 'block';
            }
            if (inputPaymentAmount) {
                inputPaymentAmount.classList.add('is-invalid');
                inputPaymentAmount.focus();
            }
            return false;
        }

        const roundedAmt = Math.round(amtVal * 100) / 100;
        if (Math.round((confirmedPaid + roundedAmt) * 100) / 100 > bookingGrandTotal) {
            if (paymentError) {
                paymentError.textContent = `Payment cannot be more than booking amount ₹${formatIndianCurrency(bookingGrandTotal)}`;
                paymentError.style.display = 'block';
            }
            if (inputPaymentAmount) {
                inputPaymentAmount.classList.add('is-invalid');
                inputPaymentAmount.focus();
            }
            return false;
        }

        if (paymentError) {
            paymentError.textContent = '';
            paymentError.style.display = 'none';
        }
        if (inputPaymentAmount) inputPaymentAmount.classList.remove('is-invalid');

        const mode = currentPaymentMode;
        const onlineMethod = mode === 'ONLINE' ? (paymentOnlineMethod ? paymentOnlineMethod.value : 'UPI') : null;
        const refNo = mode === 'ONLINE' ? (paymentRefNo ? paymentRefNo.value.trim() : null) : null;

        bookingPayments.push({
            id: nextPaymentId++,
            mode: mode,
            online_method: onlineMethod,
            reference_no: refNo,
            amount: roundedAmt
        });

        // Clears the amount, keeps the selected mode, focuses the amount again
        if (inputPaymentAmount) {
            inputPaymentAmount.value = '';
            inputPaymentAmount.focus();
        }

        renderPaymentEntriesTable();
        updateAllTotals();
        return true;
    }

    // Add Payment Plus button click
    if (btnAddPayment) {
        btnAddPayment.addEventListener('click', function (e) {
            e.preventDefault();
            addPaymentEntryRow();
        });
    }

    // Payment Amount input input/blur/enter handlers
    if (inputPaymentAmount) {
        inputPaymentAmount.addEventListener('input', function () {
            let val = this.value;
            val = val.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            if (parts.length === 2 && parts[1].length > 2) val = parts[0] + '.' + parts[1].slice(0, 2);
            this.value = val;

            if (paymentError && parseFloat(val) > 0 && !paymentError.textContent.startsWith('Payment cannot be more than')) {
                paymentError.style.display = 'none';
                this.classList.remove('is-invalid');
            }

            updateAllTotals();
        });

        inputPaymentAmount.addEventListener('blur', function () {
            const val = this.value.trim();
            if (val !== '' && !isNaN(val)) {
                const num = parseFloat(val);
                if (num >= 0) this.value = num.toFixed(2);
            }
            updateAllTotals();
        });

        inputPaymentAmount.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const curVal = parseFloat(this.value) || 0;
                if (curVal > 0) {
                    addPaymentEntryRow();
                } else if (inputNarration) {
                    inputNarration.focus();
                }
            }
        });
    }

    /**
     * Render Payment Entries mini-table from single source of truth (bookingPayments)
     */
    function renderPaymentEntriesTable() {
        if (!paymentEntriesTableBody || !paymentEntriesRowWrapper) return;

        if (bookingPayments.length === 0) {
            paymentEntriesRowWrapper.style.display = 'none';
            paymentEntriesTableBody.innerHTML = '';
            updateAllTotals();
            return;
        }

        paymentEntriesRowWrapper.style.display = 'flex';
        if (paymentEntriesCountBadge) {
            paymentEntriesCountBadge.textContent = bookingPayments.length;
        }

        let rowsHtml = '';
        bookingPayments.forEach((p, index) => {
            const aVal = parseFloat(p.amount).toFixed(2);
            const isCash = (p.mode || '').toUpperCase() === 'CASH';
            const modeBadge = isCash
                ? '<span class="badge-payment-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>'
                : `<span class="badge-payment-online"><i class="fa-solid fa-globe"></i> ${escapeHtml(p.online_method || 'Online')}${p.reference_no ? ' • ' + escapeHtml(p.reference_no) : ''}</span>`;

            rowsHtml += `
                <tr class="payment-entry-row" data-id="${p.id}">
                    <td class="text-center text-muted fw-bold col-entry-sn" style="width: 35px;">${index + 1}</td>
                    <td class="text-start py-1">${modeBadge}</td>
                    <td class="py-1" style="width: 140px;">
                        <input type="text"
                               inputmode="decimal"
                               class="entry-cell-input row-input-payment-amount text-end fw-bold"
                               data-id="${p.id}"
                               value="${aVal}"
                               title="Edit amount">
                    </td>
                    <td class="text-center col-entry-del py-1" style="width: 35px;">
                        <button type="button"
                                class="btn-delete-entry-row btn-delete-payment-row"
                                data-id="${p.id}"
                                title="Delete payment"
                                aria-label="Delete payment">
                            <i class="fa-solid fa-trash-can text-danger"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        paymentEntriesTableBody.innerHTML = rowsHtml;
        attachPaymentRowListeners();
        updateAllTotals();
    }

    function attachPaymentRowListeners() {
        if (!paymentEntriesTableBody) return;

        // Inline amount editing
        const amtInputs = paymentEntriesTableBody.querySelectorAll('.row-input-payment-amount');
        amtInputs.forEach(input => {
            input.addEventListener('input', function () {
                const payId = parseInt(this.getAttribute('data-id'), 10);
                const payment = bookingPayments.find(p => p.id === payId);
                if (!payment) return;

                let val = this.value.replace(/[^0-9.]/g, '');
                const parts = val.split('.');
                if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
                if (parts.length === 2 && parts[1].length > 2) val = parts[0] + '.' + parts[1].slice(0, 2);
                this.value = val;

                const parsed = parseFloat(val) || 0;
                payment.amount = parsed;
                this.classList.remove('is-invalid');
                updateAllTotals();
            });

            input.addEventListener('blur', function () {
                const payId = parseInt(this.getAttribute('data-id'), 10);
                const payment = bookingPayments.find(p => p.id === payId);
                if (payment) {
                    this.value = parseFloat(payment.amount || 0).toFixed(2);
                }
                updateAllTotals();
            });
        });

        // Delete button for payment rows
        const deleteButtons = paymentEntriesTableBody.querySelectorAll('.btn-delete-payment-row');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const payId = parseInt(this.getAttribute('data-id'), 10);
                const pIndex = bookingPayments.findIndex(p => p.id === payId);
                if (pIndex !== -1) {
                    bookingPayments.splice(pIndex, 1);
                    renderPaymentEntriesTable();
                }
            });
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
    const linkCancelEdit = document.getElementById('linkCancelEdit');
    const linkCancelEditTop = document.getElementById('linkCancelEditTop');
    const editModeIndicator = document.getElementById('editModeIndicator');
    const editingBookingNoLabel = document.getElementById('editingBookingNoLabel');

    // Form PRINT button: prints the last saved booking in this session
    // If none, displays toast: "Save the booking first"
    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            if (!lastSavedBookingId) {
                showToast('Save the booking first', 'warning');
                return;
            }
            window.open(getRoute('bhavBookingPrint', lastSavedBookingId), '_blank');
        });
    }

    // Cancel edit click handlers (both top banner link and bottom button link)
    if (linkCancelEdit) {
        linkCancelEdit.addEventListener('click', function (e) {
            e.preventDefault();
            exitEditMode();
        });
    }

    if (linkCancelEditTop) {
        linkCancelEditTop.addEventListener('click', function (e) {
            e.preventDefault();
            exitEditMode();
        });
    }

    /**
     * Enter Edit Mode in UI:
     * - Shows "Editing BB-000001" banner
     * - Changes SAVE button text to "UPDATE"
     * - Shows "Cancel edit" link
     */
    function enterEditMode(booking) {
        editingBookingId = booking.id;
        editingBookingNo = booking.booking_no;

        if (editingBookingNoLabel) editingBookingNoLabel.textContent = booking.booking_no;
        if (editModeIndicator) {
            editModeIndicator.classList.remove('d-none');
            editModeIndicator.classList.add('d-flex');
        }
        if (linkCancelEdit) linkCancelEdit.style.display = 'inline-block';

        if (btnSave) {
            btnSave.innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i> UPDATE';
        }

        // Lock customer field and prevent changing in Edit Mode
        lockCustomerField();
    }

    /**
     * Exit Edit Mode in UI:
     * - Hides "Editing BB-000001" banner
     * - Restores SAVE button text
     * - Hides "Cancel edit" link
     * - Unlocks customer field
     * - Resets form state
     */
    function exitEditMode(skipFocus = false) {
        editingBookingId = null;
        editingBookingNo = null;

        if (editModeIndicator) {
            editModeIndicator.classList.remove('d-flex');
            editModeIndicator.classList.add('d-none');
        }
        if (linkCancelEdit) linkCancelEdit.style.display = 'none';

        if (btnSave) {
            btnSave.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> SAVE';
        }

        unlockCustomerField();
        resetFormAfterSave(skipFocus);
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

        if (paymentError) {
            paymentError.textContent = '';
            paymentError.style.display = 'none';
        }
        if (inputPaymentAmount) inputPaymentAmount.classList.remove('is-invalid');

        const tableInputs = entriesTableBody ? entriesTableBody.querySelectorAll('.entry-cell-input') : [];
        tableInputs.forEach(input => input.classList.remove('is-invalid'));
    }

    /**
     * Reset form after a successful booking save:
     * - Clear rows
     * - Clear weight
     * - Clear narration
     * - WhatsApp off
     * - Reset payments
     * - KEEP BUY/SELL mode, metal and rate
     * - Generate new request_token
     * - Focus customer input
     */
    function resetFormAfterSave(skipFocus = false) {
        clearCustomerSelection();
        bookingItems = [];
        renderEntriesTable();

        bookingPayments = [];
        nextPaymentId = 1;
        renderPaymentEntriesTable();
        setPaymentMode('CASH');
        if (inputPaymentAmount) inputPaymentAmount.value = '';
        if (paymentRefNo) paymentRefNo.value = '';
        if (paymentOnlineMethod) paymentOnlineMethod.value = 'UPI';

        if (inputWeight) inputWeight.value = '';
        if (inputNarration) inputNarration.value = '';
        if (narrationCounter) narrationCounter.textContent = '0/255';
        if (whatsappSwitch) whatsappSwitch.checked = false;

        // Generate fresh request_token for the next save
        currentRequestToken = generateRequestToken();

        // Update totals (will show 0.00)
        updateAllTotals();

        if (!skipFocus) {
            // Focus customer search and auto-open recent customers dropdown
            if (customerSearchInput) {
                customerSearchInput.focus();
                showRecentCustomersDropdown();
            }
        }
    }

    /**
     * Display the Save / Update Success Centered Popup Modal
     */
    function showSaveSuccessModal(savedBooking, isEditMode) {
        const modalEl = document.getElementById('saveSuccessModal');
        if (!modalEl) {
            showToast(`Booking ${savedBooking.booking_no || ''} ${isEditMode ? 'updated' : 'saved'}`, 'success');
            return;
        }

        const titleEl = document.getElementById('saveSuccessModalTitle');
        const bNoEl = document.getElementById('successBookingNo');
        const custEl = document.getElementById('successCustomerName');
        const tradeBadge = document.getElementById('successTradeBadge');
        const metalBadge = document.getElementById('successMetalBadge');
        const wtEl = document.getElementById('successTotalWeight');
        const amtEl = document.getElementById('successTotalAmount');
        const btnPrint = document.getElementById('btnSuccessPrint');
        const btnOk = document.getElementById('btnSuccessOk');

        if (titleEl) {
            titleEl.textContent = isEditMode ? 'Transaction Updated Successfully!' : 'Transaction Saved Successfully!';
        }

        if (bNoEl) {
            bNoEl.textContent = savedBooking.booking_no || (isEditMode ? editingBookingNo : '-');
        }

        const customerName = (savedBooking.customer && savedBooking.customer.name)
            ? savedBooking.customer.name
            : (selectedCustomer ? selectedCustomer.name : '-');
        if (custEl) custEl.textContent = customerName;

        const isBuy = (savedBooking.trade_type || '').toUpperCase() === 'BUY';
        if (tradeBadge) {
            tradeBadge.textContent = isBuy ? 'BUY' : 'SELL';
            tradeBadge.className = `badge rounded-pill px-2 py-1 fw-bold ${isBuy ? 'bg-success text-white' : 'bg-danger text-white'}`;
        }

        const isGold = (savedBooking.metal || '').toUpperCase() === 'GOLD';
        if (metalBadge) {
            metalBadge.textContent = isGold ? 'GOLD' : 'SILVER';
            metalBadge.className = `badge rounded-pill px-2 py-1 fw-bold ${isGold ? 'badge-metal-gold' : 'badge-metal-silver'}`;
        }

        if (wtEl) {
            wtEl.textContent = `${(parseFloat(savedBooking.total_weight) || 0).toFixed(3)} gm`;
        }

        if (amtEl) {
            amtEl.textContent = `₹${formatIndianCurrency(savedBooking.total_amount || 0)}`;
        }

        const payRowEl = document.getElementById('successPaymentRow');
        const payTextEl = document.getElementById('successPaymentText');
        const payments = Array.isArray(savedBooking.payments) ? savedBooking.payments : [];
        let cashAmt = 0;
        let onlineAmt = 0;
        payments.forEach(p => {
            const amt = parseFloat(p.amount) || 0;
            if ((p.mode || '').toUpperCase() === 'CASH') {
                cashAmt += amt;
            } else {
                onlineAmt += amt;
            }
        });
        const totalPaid = Math.round((cashAmt + onlineAmt) * 100) / 100;

        if (payRowEl && payTextEl) {
            if (totalPaid > 0) {
                payRowEl.style.removeProperty('display');
                payRowEl.classList.remove('d-none');
                payRowEl.style.display = 'flex';
                payTextEl.textContent = `Paid: ₹${formatIndianCurrency(totalPaid)} (Cash ₹${formatIndianCurrency(cashAmt)} + Online ₹${formatIndianCurrency(onlineAmt)})`;
            } else {
                payRowEl.style.display = 'none';
            }
        }

        if (btnPrint) {
            btnPrint.onclick = function () {
                window.open(getRoute('bhavBookingPrint', savedBooking.id), '_blank');
            };
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

            const onModalShown = function () {
                if (btnOk) btnOk.focus();
                modalEl.removeEventListener('shown.bs.modal', onModalShown);
            };
            modalEl.addEventListener('shown.bs.modal', onModalShown);

            const onModalKeydown = function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    modalInstance.hide();
                }
            };
            modalEl.addEventListener('keydown', onModalKeydown);

            const onModalHidden = function () {
                modalEl.removeEventListener('keydown', onModalKeydown);
                modalEl.removeEventListener('hidden.bs.modal', onModalHidden);
                if (payRowEl) payRowEl.style.display = 'none';

                // Focus customer search and open recent customers dropdown after modal closes
                if (customerSearchInput) {
                    customerSearchInput.focus();
                    showRecentCustomersDropdown();
                }
            };
            modalEl.addEventListener('hidden.bs.modal', onModalHidden);

            modalInstance.show();
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

            // Auto-treat typed payment amount as one entry with current mode if "+" was not clicked
            const typedPayAmt = parseFloat(inputPaymentAmount ? inputPaymentAmount.value : 0) || 0;
            if (typedPayAmt > 0) {
                const valStr = inputPaymentAmount.value.trim();
                const decMatch = valStr.match(/\.(\d+)$/);
                if (decMatch && decMatch[1].length > 2) {
                    if (paymentError) {
                        paymentError.textContent = 'Payment amount can have at most 2 decimals.';
                        paymentError.style.display = 'block';
                    }
                    if (inputPaymentAmount) inputPaymentAmount.classList.add('is-invalid');
                    inputPaymentAmount.focus();
                    return;
                }
                bookingPayments.push({
                    id: nextPaymentId++,
                    mode: currentPaymentMode,
                    online_method: currentPaymentMode === 'ONLINE' ? (paymentOnlineMethod ? paymentOnlineMethod.value : 'UPI') : null,
                    reference_no: currentPaymentMode === 'ONLINE' && paymentRefNo && paymentRefNo.value.trim() ? paymentRefNo.value.trim() : null,
                    amount: typedPayAmt
                });
                inputPaymentAmount.value = '';
                if (paymentRefNo) paymentRefNo.value = '';
                renderPaymentEntriesTable();
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

            // 4. Validate total payments do not exceed booking total amount
            let totalConfirmedPaid = 0;
            for (const p of bookingPayments) {
                totalConfirmedPaid += parseFloat(p.amount) || 0;
            }
            totalConfirmedPaid = Math.round(totalConfirmedPaid * 100) / 100;

            const itemTotals = calculateCurrentTotals();
            const grandTotalAmount = itemTotals.grandAmount;

            if (totalConfirmedPaid > grandTotalAmount && grandTotalAmount >= 0) {
                const formattedBookingAmt = formatIndianCurrency(grandTotalAmount);
                if (paymentError) {
                    paymentError.textContent = `Payment cannot be more than booking amount ₹${formattedBookingAmt}`;
                    paymentError.style.display = 'block';
                }
                if (inputPaymentAmount) inputPaymentAmount.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = inputPaymentAmount;
            }

            if (!isValid) {
                if (firstInvalidField) firstInvalidField.focus();
                return;
            }

            const isEditMode = Boolean(editingBookingId);

            // Disable save button + show spinner to prevent double submission
            btnSave.disabled = true;
            btnSave.innerHTML = isEditMode
                ? '<i class="fa-solid fa-spinner fa-spin me-2"></i> Updating...'
                : '<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';

            const payload = {
                trade_type: hiddenTradeType ? hiddenTradeType.value : 'SELL',
                entry_type: typeSelect ? typeSelect.value : 'ONLY_ENTRY',
                metal: categorySelect ? categorySelect.value : 'GOLD',
                items: bookingItems.map(item => ({
                    weight: parseFloat(item.weight),
                    rate: parseFloat(item.rate)
                })),
                payments: bookingPayments.map(p => ({
                    mode: p.mode,
                    online_method: p.mode === 'ONLINE' ? (p.online_method || 'UPI') : null,
                    reference_no: p.mode === 'ONLINE' && p.reference_no ? p.reference_no : null,
                    amount: parseFloat(p.amount)
                })),
                narration: inputNarration ? inputNarration.value.trim() : null,
                send_whatsapp: whatsappSwitch ? whatsappSwitch.checked : false
            };

            // Only send customer_id and request_token on CREATE (store). Updates do not change customer or request_token.
            if (!isEditMode) {
                payload.customer_id = parseInt(hiddenCustomerId.value, 10);
                payload.request_token = currentRequestToken;
            }

            const targetUrl = isEditMode
                ? getRoute('bhavBookingUpdate', editingBookingId)
                : (window.APP_ROUTES && window.APP_ROUTES.bhavBookingStore ? window.APP_ROUTES.bhavBookingStore : '/bhav-bookings');

            const targetMethod = isEditMode ? 'PUT' : 'POST';

            fetch(targetUrl, {
                method: targetMethod,
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
                    // Handle Laravel 422 validation errors or business rule rejection
                    if (response.status === 422 && data.errors) {
                        handleValidationErrors(data.errors);
                    } else {
                        showToast(data.message || (isEditMode ? 'Failed to update booking.' : 'Failed to save booking.'), 'error');
                    }
                    throw new Error(isEditMode ? 'Update failed' : 'Save failed');
                }

                return data;
            })
            .then(savedBooking => {
                const bookingNo = savedBooking.booking_no || (isEditMode ? editingBookingNo : 'Booking');

                // Remember last saved booking ID for the form PRINT button
                lastSavedBookingId = savedBooking.id;

                // Refresh recent customers cache after booking save
                fetchRecentCustomers(true);

                if (isEditMode) {
                    exitEditMode(true);
                    // Stay on current page when editing
                    fetchBookingList(currentListPage);
                    showSaveSuccessModal(savedBooking, true);
                } else {
                    resetFormAfterSave(true);
                    // Reload list from page 1 after new creation
                    fetchBookingList(1);
                    showSaveSuccessModal(savedBooking, false);
                }
            })
            .catch(err => {
                console.warn(isEditMode ? 'Booking update error:' : 'Booking save error:', err);
            })
            .finally(() => {
                btnSave.disabled = false;
                if (editingBookingId) {
                    btnSave.innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i> UPDATE';
                } else {
                    btnSave.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> SAVE';
                }
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

        if (errors.payments && paymentError) {
            paymentError.textContent = errors.payments[0];
            paymentError.style.display = 'block';
            if (inputPaymentAmount) inputPaymentAmount.classList.add('is-invalid');
        }

        Object.keys(errors).forEach(key => {
            if (key.startsWith('payments.') && paymentError) {
                if (!paymentError.textContent) {
                    paymentError.textContent = errors[key][0];
                    paymentError.style.display = 'block';
                }
                if (inputPaymentAmount) inputPaymentAmount.classList.add('is-invalid');
            }
        });
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
    // 10. BOOKING LIST, FILTERS & PAGINATION (Task B2)
    // Server-side filtered & paginated booking table with dynamic aggregates
    // =========================================================================

    // List State
    let currentListView = 'current'; // 'current' or 'old'
    let currentListPage = 1;
    let listAbortController = null;
    let partySearchDebounceTimer = null;

    // DOM Elements: Header List Menu
    const menuCurrentList = document.getElementById('menuCurrentList');
    const menuOldBooking = document.getElementById('menuOldBooking');
    const iconCurrentCheck = document.getElementById('iconCurrentCheck');
    const iconOldCheck = document.getElementById('iconOldCheck');
    const listHeadingText = document.getElementById('listHeadingText');
    const listHeadingIcon = document.getElementById('listHeadingIcon');

    // DOM Elements: Filters
    const filterPartyInput = document.getElementById('filterPartyInput');
    const filterTradeSelect = document.getElementById('filterTradeSelect');
    const filterMetalSelect = document.getElementById('filterMetalSelect');
    const filterTypeSelect = document.getElementById('filterTypeSelect');
    const pageSizeSelect = document.getElementById('pageSizeSelect');

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

    // DOM Elements: Table & Totals & Pagination
    const bhavBookingTable = document.getElementById('bhavBookingTable');
    const bhavTableBody = document.getElementById('bhavTableBody');
    const tableLoadingBar = document.getElementById('tableLoadingBar');
    let isFirstBookingListLoad = true;
    const listPaginationInfo = document.getElementById('listPaginationInfo');
    const listPaginationLinks = document.getElementById('listPaginationLinks');

    const totalBuyWt = document.getElementById('totalBuyWt');
    const totalBuyAmt = document.getElementById('totalBuyAmt');
    const totalSellWt = document.getElementById('totalSellWt');
    const totalSellAmt = document.getElementById('totalSellAmt');
    const totalGiven = document.getElementById('totalGiven');
    const totalReceived = document.getElementById('totalReceived');

    /**
     * Format server ISO/SQL datetime string as DD-MM-YYYY hh:mm AM/PM
     * Why: Consistent 12-hour Indian date-time format for readability
     */
    function formatDateTimeDisplay(dateString) {
        if (!dateString) return '-';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return escapeHtml(dateString);

        const day = pad2(d.getDate());
        const month = pad2(d.getMonth() + 1);
        const year = d.getFullYear();

        let hours = d.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 hour converts to 12
        const minutes = pad2(d.getMinutes());

        return `${day}-${month}-${year} ${pad2(hours)}:${minutes} ${ampm}`;
    }

    /**
     * Get visible column count based on active trade filter (8 when Buy or Sell, 11 when All)
     */
    function getVisibleColumnCount() {
        const tradeVal = filterTradeSelect ? filterTradeSelect.value : 'ALL';
        return (tradeVal === 'BUY' || tradeVal === 'SELL') ? 8 : 11;
    }

    /**
     * Apply trade column visibility classes to the table
     */
    function applyTradeColumnVisibility(tradeType) {
        if (!bhavBookingTable) return;
        bhavBookingTable.classList.remove('show-buy-only', 'show-sell-only');
        if (tradeType === 'BUY') {
            bhavBookingTable.classList.add('show-buy-only');
        } else if (tradeType === 'SELL') {
            bhavBookingTable.classList.add('show-sell-only');
        }
    }

    /**
     * Format server ISO/SQL datetime string into two compact lines:
     * Line 1: DD-MM-YYYY (12px)
     * Line 2: hh:mm AM/PM (10px grey)
     */
    function formatDateTimeTwoLines(dateString) {
        if (!dateString) return '<span class="text-muted opacity-50">–</span>';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return `<div>${escapeHtml(dateString)}</div>`;

        const day = pad2(d.getDate());
        const month = pad2(d.getMonth() + 1);
        const year = d.getFullYear();

        let hours = d.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const minutes = pad2(d.getMinutes());

        return `<div class="fw-semibold text-dark text-nowrap" style="font-size: 12px; line-height: 1.2;">${day}-${month}-${year}</div>` +
               `<div class="text-muted text-nowrap" style="font-size: 10px; line-height: 1.2;">${pad2(hours)}:${minutes} ${ampm}</div>`;
    }

    /**
     * Format metal weights for totals footer:
     * Never add Gold and Silver weights together!
     * Format as two lines: "G: x gm" and "S: y gm" (nowrap)
     */
    function formatMetalWeightTotal(goldWeight, silverWeight) {
        const g = parseFloat(goldWeight) || 0;
        const s = parseFloat(silverWeight) || 0;

        if (g > 0 && s > 0) {
            return `<div class="text-nowrap" style="line-height: 1.25;">G: ${g.toFixed(3)} gm</div><div class="text-nowrap" style="line-height: 1.25;">S: ${s.toFixed(3)} gm</div>`;
        } else if (g > 0) {
            return `<div class="text-nowrap" style="line-height: 1.25;">G: ${g.toFixed(3)} gm</div>`;
        } else if (s > 0) {
            return `<div class="text-nowrap" style="line-height: 1.25;">S: ${s.toFixed(3)} gm</div>`;
        } else {
            return `<div class="text-nowrap" style="line-height: 1.25;">0.000 gm</div>`;
        }
    }

    /**
     * Reset table footer aggregate totals to zero
     */
    function resetTotalsToZero() {
        if (totalBuyWt) totalBuyWt.innerHTML = '<div class="text-nowrap" style="line-height: 1.25;">0.000 gm</div>';
        if (totalBuyAmt) totalBuyAmt.textContent = '₹0.00';
        if (totalSellWt) totalSellWt.innerHTML = '<div class="text-nowrap" style="line-height: 1.25;">0.000 gm</div>';
        if (totalSellAmt) totalSellAmt.textContent = '₹0.00';
        if (totalGiven) totalGiven.innerHTML = '<div class="text-danger fw-bold text-nowrap" style="line-height: 1.25;">G: ₹0.00</div>';
        if (totalReceived) totalReceived.innerHTML = '<div class="text-success fw-bold text-nowrap" style="line-height: 1.25;">R: ₹0.00</div>';
    }

    /**
     * Render footer aggregate totals using strictly allowed keys
     * Keys: buy_weight_gold, buy_weight_silver, buy_amount, sell_weight_gold,
     *       sell_weight_silver, sell_amount, given_total, received_total
     */
    function renderTotals(totals) {
        if (!totals) {
            resetTotalsToZero();
            return;
        }

        if (totalBuyWt) {
            totalBuyWt.innerHTML = formatMetalWeightTotal(totals.buy_weight_gold, totals.buy_weight_silver);
        }
        if (totalBuyAmt) {
            totalBuyAmt.textContent = '₹' + formatIndianCurrency(totals.buy_amount || 0);
        }
        if (totalSellWt) {
            totalSellWt.innerHTML = formatMetalWeightTotal(totals.sell_weight_gold, totals.sell_weight_silver);
        }
        if (totalSellAmt) {
            totalSellAmt.textContent = '₹' + formatIndianCurrency(totals.sell_amount || 0);
        }
        if (totalGiven) {
            totalGiven.innerHTML = `<div class="text-danger fw-bold text-nowrap" style="line-height: 1.25;">G: ₹${formatIndianCurrency(totals.given_total || 0)}</div>`;
        }
        if (totalReceived) {
            totalReceived.innerHTML = `<div class="text-success fw-bold text-nowrap" style="line-height: 1.25;">R: ₹${formatIndianCurrency(totals.received_total || 0)}</div>`;
        }
    }

    /**
     * Render spinner row while request is in flight
     */
    function renderLoadingState() {
        if (!bhavTableBody) return;
        const colCount = getVisibleColumnCount();
        bhavTableBody.innerHTML = `
            <tr class="table-loading-row">
                <td colspan="${colCount}" class="py-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="fw-semibold">Loading bookings...</span>
                </td>
            </tr>
        `;
    }

    /**
     * Render friendly error row with retry button
     */
    function renderErrorState(message) {
        if (!bhavTableBody) return;
        const colCount = getVisibleColumnCount();
        resetTotalsToZero();
        renderPagination({ current_page: 1, per_page: 10, total: 0, last_page: 1, from: 0, to: 0 });

        bhavTableBody.innerHTML = `
            <tr class="table-error-row">
                <td colspan="${colCount}" class="py-4 text-center text-danger">
                    <i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block opacity-75"></i>
                    <div class="fw-semibold mb-2">${escapeHtml(message || 'Failed to load bookings.')}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" id="btnRetryList">
                        <i class="fa-solid fa-rotate-right me-1"></i> Retry
                    </button>
                </td>
            </tr>
        `;

        const btnRetry = document.getElementById('btnRetryList');
        if (btnRetry) {
            btnRetry.addEventListener('click', function () {
                fetchBookingList(currentListPage);
            });
        }
    }

    /**
     * Render empty state matching existing design when rows is empty
     */
    function renderEmptyState() {
        if (!bhavTableBody) return;
        const colCount = getVisibleColumnCount();
        resetTotalsToZero();
        bhavTableBody.innerHTML = `
            <tr class="table-empty-row">
                <td colspan="${colCount}" class="py-5 text-center text-muted">
                    <i class="fa-regular fa-folder-open fa-3x text-secondary opacity-50 mb-2 d-block"></i>
                    <span class="fw-semibold">No Records Found</span>
                </td>
            </tr>
        `;
    }

    /**
     * Render booking table rows using strictly allowed response keys
     */
    function renderTableRows(rows, pagination) {
        if (!bhavTableBody) return;

        if (!rows || rows.length === 0) {
            renderEmptyState();
            return;
        }

        const startFrom = (pagination && typeof pagination.from === 'number') ? pagination.from : 1;
        lastLoadedBookingsMap = {};

        let html = '';
        rows.forEach((booking, index) => {
            lastLoadedBookingsMap[booking.id] = booking;
            const sn = startFrom + index;
            const dateTimeHtml = formatDateTimeTwoLines(booking.booked_at);

            // Customer details & badges
            const customer = booking.customer || {};
            const customerName = customer.name || 'Unknown Customer';
            const customerNo = customer.customer_no || '-';
            const bookingNo = booking.booking_no || '-';

            const isGold = (booking.metal || '').toUpperCase() === 'GOLD';
            const metalBadge = isGold
                ? `<span class="badge-metal-gold">Gold</span>`
                : `<span class="badge-metal-silver">Silver</span>`;

            const isOnlyEntry = (booking.entry_type || '').toUpperCase() === 'ONLY_ENTRY';
            const typeBadge = isOnlyEntry
                ? `<span class="badge-type-entry">Only Entry</span>`
                : `<span class="badge-type-ledger">Bhav+Ledger</span>`;

            const statusUpper = (booking.status || 'PENDING').toUpperCase();
            let statusBadgeClass = 'badge-status-pending';
            let statusText = 'Pending';
            if (statusUpper === 'PARTIAL') {
                statusBadgeClass = 'badge-status-partial';
                statusText = 'Partial';
            } else if (statusUpper === 'DELIVERED') {
                statusBadgeClass = 'badge-status-delivered';
                statusText = 'Delivered';
            } else if (statusUpper === 'CANCELLED') {
                statusBadgeClass = 'badge-status-cancelled';
                statusText = 'Cancelled';
            }

            const statusBadge = `<span class="${statusBadgeClass}">${statusText}</span>`;

            // Rate cell: single rate or "Multiple" with breakdown tooltip
            const items = Array.isArray(booking.items) ? booking.items : [];
            const emptyCell = '<span class="text-muted opacity-50">–</span>';
            let rateDisplay = emptyCell;

            if (items.length === 1) {
                const singleRate = parseFloat(items[0].rate) || 0;
                rateDisplay = `₹${formatIndianCurrency(singleRate)}`;
            } else if (items.length > 1) {
                const lines = items.map((it, i) => {
                    const w = (parseFloat(it.weight) || 0).toFixed(3);
                    const r = formatIndianCurrency(it.rate || 0);
                    const a = formatIndianCurrency(it.amount || 0);
                    return `#${i + 1}: ${w} gm × ₹${r} = ₹${a}`;
                });
                const tooltipContent = lines.join('\n');
                rateDisplay = `<span class="badge bg-light text-dark border cursor-help text-nowrap" title="${escapeHtml(tooltipContent)}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${escapeHtml(tooltipContent)}"><i class="fa-solid fa-layer-group text-primary me-1"></i>Multiple</span>`;
            }

            // BUY booking fills BUY DETAILS, SELL booking fills SELL DETAILS
            const isBuy = (booking.trade_type || '').toUpperCase() === 'BUY';
            const wtFormatted = (parseFloat(booking.total_weight) || 0).toFixed(3);
            const amtFormatted = '₹' + formatIndianCurrency(booking.total_amount || 0);

            let buyWt = emptyCell, buyRate = emptyCell, buyAmt = emptyCell;
            let sellWt = emptyCell, sellRate = emptyCell, sellAmt = emptyCell;

            if (isBuy) {
                buyWt = `<span class="text-success fw-bold text-nowrap">${wtFormatted} <span class="unit-text text-muted" style="font-size: 10px; font-weight: normal;">gm</span></span>`;
                buyRate = `<span class="text-success fw-semibold text-nowrap">${rateDisplay}</span>`;
                buyAmt = `<span class="text-success fw-bold text-nowrap">${amtFormatted}</span>`;
            } else {
                sellWt = `<span class="text-danger fw-bold text-nowrap">${wtFormatted} <span class="unit-text text-muted" style="font-size: 10px; font-weight: normal;">gm</span></span>`;
                sellRate = `<span class="text-danger fw-semibold text-nowrap">${rateDisplay}</span>`;
                sellAmt = `<span class="text-danger fw-bold text-nowrap">${amtFormatted}</span>`;
            }

            // Given / Received
            const givenAmt = parseFloat(booking.given_amount) || 0;
            const receivedAmt = parseFloat(booking.received_amount) || 0;
            const givenHtml = `<div class="text-danger fw-bold text-nowrap" style="font-size: 11px; line-height: 1.25;">G: ₹${formatIndianCurrency(givenAmt)}</div>`;
            const receivedHtml = `<div class="text-success fw-bold text-nowrap" style="font-size: 11px; line-height: 1.25;">R: ₹${formatIndianCurrency(receivedAmt)}</div>`;

            // Action column: 3-dot Kebab menu (View, Edit, Print, Share, WhatsApp, Delete, Silent Delete)
            const actionHtml = renderActionKebabMenu(booking);

            html += `
                <tr class="bhav-row" data-id="${booking.id}">
                    <td class="bhav-col-sn fw-semibold text-muted text-center">${sn}</td>
                    <td class="bhav-col-datetime text-center">${dateTimeHtml}</td>
                    <td class="bhav-col-customer text-start">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 12px; line-height: 1.25; max-width: 190px;" title="${escapeHtml(customerName)}">${escapeHtml(customerName)}</div>
                        <div class="text-muted text-nowrap" style="font-size: 10.5px; line-height: 1.25;">
                            <span class="text-secondary">${escapeHtml(customerNo)}</span> • <span class="fw-semibold text-dark">${escapeHtml(bookingNo)}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-1 text-nowrap" style="line-height: 1;">
                            ${metalBadge}
                            ${typeBadge}
                            ${statusBadge}
                        </div>
                    </td>
                    <td class="bhav-col-buy-wt col-buy text-end">${buyWt}</td>
                    <td class="bhav-col-buy-rate col-buy text-end">${buyRate}</td>
                    <td class="bhav-col-buy-amt col-buy group-end text-end">${buyAmt}</td>
                    <td class="bhav-col-sell-wt col-sell text-end">${sellWt}</td>
                    <td class="bhav-col-sell-rate col-sell text-end">${sellRate}</td>
                    <td class="bhav-col-sell-amt col-sell group-end text-end">${sellAmt}</td>
                    <td class="bhav-col-given-received text-center">${givenHtml}${receivedHtml}</td>
                    <td class="bhav-col-action text-center">${actionHtml}</td>
                </tr>
            `;
        });

        // Dispose existing tooltips before re-rendering to prevent memory build-up
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const oldTooltips = bhavTableBody.querySelectorAll('[data-bs-toggle="tooltip"]');
            oldTooltips.forEach(el => {
                const inst = bootstrap.Tooltip.getInstance(el);
                if (inst) inst.dispose();
            });
        }

        bhavTableBody.innerHTML = html;

        // Initialize tooltips if Bootstrap Tooltip is loaded (disposing existing first)
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipElements = bhavTableBody.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipElements.forEach(el => {
                const inst = bootstrap.Tooltip.getInstance(el);
                if (inst) inst.dispose();
                new bootstrap.Tooltip(el);
            });
        }
    }

    /**
     * Render pagination info and page links (max 5 visible page numbers)
     * Keys: current_page, per_page, total, last_page, from, to
     */
    function renderPagination(pagination) {
        if (!listPaginationInfo || !listPaginationLinks) return;

        if (!pagination || pagination.total === 0 || !pagination.from) {
            listPaginationInfo.textContent = 'Showing 0–0 of 0';
            listPaginationLinks.innerHTML = '';
            return;
        }

        const total = pagination.total || 0;
        const from = pagination.from || 0;
        const to = pagination.to || 0;
        const currentPage = pagination.current_page || 1;
        const lastPage = Math.max(1, pagination.last_page || 1);

        listPaginationInfo.textContent = `Showing ${from}–${to} of ${total}`;

        // Build window of at most 5 page numbers centered on currentPage
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        let linksHtml = '';

        // Previous button
        const isPrevDisabled = currentPage <= 1;
        linksHtml += `
            <li class="page-item ${isPrevDisabled ? 'disabled' : ''}">
                <button type="button" class="page-link bhav-pagination-btn px-2" data-page="${currentPage - 1}" ${isPrevDisabled ? 'disabled tabindex="-1" aria-disabled="true"' : ''}>
                    <i class="fa-solid fa-chevron-left small"></i> Prev
                </button>
            </li>
        `;

        // Numbered page buttons
        for (let p = startPage; p <= endPage; p++) {
            const isActive = p === currentPage;
            linksHtml += `
                <li class="page-item ${isActive ? 'active' : ''}">
                    <button type="button" class="page-link bhav-pagination-btn" data-page="${p}" ${isActive ? 'aria-current="page"' : ''}>
                        ${p}
                    </button>
                </li>
            `;
        }

        // Next button
        const isNextDisabled = currentPage >= lastPage;
        linksHtml += `
            <li class="page-item ${isNextDisabled ? 'disabled' : ''}">
                <button type="button" class="page-link bhav-pagination-btn px-2" data-page="${currentPage + 1}" ${isNextDisabled ? 'disabled tabindex="-1" aria-disabled="true"' : ''}>
                    Next <i class="fa-solid fa-chevron-right small"></i>
                </button>
            </li>
        `;

        listPaginationLinks.innerHTML = linksHtml;

        // Attach click handlers to pagination buttons
        const pageButtons = listPaginationLinks.querySelectorAll('button[data-page]');
        pageButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (this.disabled) return;
                const targetPage = parseInt(this.getAttribute('data-page'), 10);
                if (targetPage && targetPage !== currentPage && targetPage >= 1 && targetPage <= lastPage) {
                    fetchBookingList(targetPage);
                }
            });
        });
    }

    /**
     * Fetch booking list from backend GET /bhav-bookings/list
     * Handles AbortController cancellation of prior requests
     */
    function fetchBookingList(page = 1) {
        currentListPage = page;

        // Cancel previous request if still running
        if (listAbortController) {
            listAbortController.abort();
        }
        listAbortController = new AbortController();
        const signal = listAbortController.signal;

        if (isFirstBookingListLoad) {
            renderLoadingState();
        } else {
            // Keep current rows visible with reduced opacity (0.5) and thin orange loading bar
            if (bhavTableBody) {
                bhavTableBody.style.opacity = '0.5';
                bhavTableBody.style.pointerEvents = 'none';
            }
            if (tableLoadingBar) {
                tableLoadingBar.classList.add('active');
            }
        }

        const baseUrl = (window.APP_ROUTES && window.APP_ROUTES.bhavBookingList)
            ? window.APP_ROUTES.bhavBookingList
            : '/bhav-bookings/list';

        const params = new URLSearchParams();
        params.set('view', currentListView);
        params.set('page', page);

        const perPageVal = pageSizeSelect ? pageSizeSelect.value : '10';
        params.set('per_page', perPageVal);

        if (filterPartyInput && filterPartyInput.value.trim() !== '') {
            params.set('party', filterPartyInput.value.trim());
        }

        if (dateFilterFrom && dateFilterFrom.value) {
            params.set('date_from', dateFilterFrom.value);
        }

        if (dateFilterTo && dateFilterTo.value) {
            params.set('date_to', dateFilterTo.value);
        }

        if (filterTradeSelect && filterTradeSelect.value) {
            const tradeVal = filterTradeSelect.value;
            if (tradeVal !== 'ALL') {
                params.set('trade_type', tradeVal);
            }
            applyTradeColumnVisibility(tradeVal);
        }

        if (filterMetalSelect && filterMetalSelect.value) {
            params.set('metal', filterMetalSelect.value);
        }

        if (filterTypeSelect && filterTypeSelect.value) {
            params.set('entry_type', filterTypeSelect.value);
        }

        const fullUrl = `${baseUrl}?${params.toString()}`;

        fetch(fullUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            signal: signal
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to load booking list.');
            }
            return data;
        })
        .then(data => {
            if (tableLoadingBar) tableLoadingBar.classList.remove('active');
            if (bhavTableBody) {
                bhavTableBody.style.opacity = '1';
                bhavTableBody.style.pointerEvents = 'auto';
            }
            isFirstBookingListLoad = false;

            // Use strictly allowed keys: rows, pagination, totals
            const rows = data.rows || [];
            const pagination = data.pagination || null;
            const totals = data.totals || null;

            renderTableRows(rows, pagination);
            renderPagination(pagination);
            renderTotals(totals);
        })
        .catch(error => {
            if (tableLoadingBar) tableLoadingBar.classList.remove('active');
            if (bhavTableBody) {
                bhavTableBody.style.opacity = '1';
                bhavTableBody.style.pointerEvents = 'auto';
            }

            if (error.name === 'AbortError') {
                // Ignore aborted requests
                return;
            }
            console.warn('Booking list fetch error:', error);
            renderErrorState(error.message);
        });
    }

    // Party Search Input: 250ms debounce
    if (filterPartyInput) {
        filterPartyInput.addEventListener('input', function () {
            clearTimeout(partySearchDebounceTimer);
            partySearchDebounceTimer = setTimeout(() => {
                fetchBookingList(1);
            }, 250);
        });
    }

    // Trade Filter Change
    if (filterTradeSelect) {
        filterTradeSelect.addEventListener('change', function () {
            applyTradeColumnVisibility(this.value);
            fetchBookingList(1);
        });
    }

    // Metal Filter Change
    if (filterMetalSelect) {
        filterMetalSelect.addEventListener('change', function () {
            fetchBookingList(1);
        });
    }

    // Type Filter Change
    if (filterTypeSelect) {
        filterTypeSelect.addEventListener('change', function () {
            fetchBookingList(1);
        });
    }

    // Page Size Selector Change
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function () {
            fetchBookingList(1);
        });
    }

    // Date Range Helpers & Events
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

        // Trigger reload from page 1 on date change
        fetchBookingList(1);
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

    // =========================================================================
    // 11. ACTION COLUMN HANDLERS: VIEW, PRINT, EDIT, DELETE (Task C)
    // =========================================================================

    /**
     * View Booking Details in Bootstrap Modal:
     * Fetches fresh booking with items and customer from GET /bhav-bookings/{bhavBooking}
     */
    function viewBookingDetails(bookingId) {
        const showUrl = getRoute('bhavBookingShow', bookingId);

        fetch(showUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                showToast(data.message || 'Booking not found', 'error');
                throw new Error(data.message || 'View failed');
            }
            return data;
        })
        .then(booking => {
            // Populate Modal Fields
            const viewBookingNo = document.getElementById('viewBookingNo');
            const viewBookingDateTime = document.getElementById('viewBookingDateTime');
            const viewTradeTypeBadge = document.getElementById('viewTradeTypeBadge');
            const viewMetalBadge = document.getElementById('viewMetalBadge');
            const viewTypeBadge = document.getElementById('viewTypeBadge');
            const viewStatusBadge = document.getElementById('viewStatusBadge');

            const viewCustomerName = document.getElementById('viewCustomerName');
            const viewCustomerNo = document.getElementById('viewCustomerNo');
            const viewCustomerMobile = document.getElementById('viewCustomerMobile');
            const viewCustomerCity = document.getElementById('viewCustomerCity');

            const viewItemsTableBody = document.getElementById('viewItemsTableBody');
            const viewTotalWeight = document.getElementById('viewTotalWeight');
            const viewTotalAmount = document.getElementById('viewTotalAmount');
            const viewDeliveredWeight = document.getElementById('viewDeliveredWeight');
            const viewPendingWeight = document.getElementById('viewPendingWeight');
            const viewNarration = document.getElementById('viewNarration');
            const btnViewModalPrint = document.getElementById('btnViewModalPrint');

            if (viewBookingNo) viewBookingNo.textContent = booking.booking_no || '-';
            if (viewBookingDateTime) viewBookingDateTime.textContent = formatDateTimeDisplay(booking.booked_at);

            // Trade Type Badge
            if (viewTradeTypeBadge) {
                const isBuy = (booking.trade_type || '').toUpperCase() === 'BUY';
                viewTradeTypeBadge.textContent = booking.trade_type;
                viewTradeTypeBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isBuy ? 'bg-success text-white' : 'bg-danger text-white'}`;
            }

            // Metal Badge
            if (viewMetalBadge) {
                const isGold = (booking.metal || '').toUpperCase() === 'GOLD';
                viewMetalBadge.textContent = booking.metal;
                viewMetalBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isGold ? 'badge-metal-gold' : 'badge-metal-silver'}`;
            }

            // Type Badge
            if (viewTypeBadge) {
                const isEntry = (booking.entry_type || '').toUpperCase() === 'ONLY_ENTRY';
                viewTypeBadge.textContent = isEntry ? 'Only Entry' : 'Bhav Cut + Ledger';
                viewTypeBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isEntry ? 'badge-type-entry' : 'badge-type-ledger'}`;
            }

            // Status Badge
            if (viewStatusBadge) {
                const st = (booking.status || 'PENDING').toUpperCase();
                viewStatusBadge.textContent = st;
                let stClass = 'badge-status-pending';
                if (st === 'PARTIAL') stClass = 'badge-status-partial';
                else if (st === 'DELIVERED') stClass = 'badge-status-delivered';
                else if (st === 'CANCELLED') stClass = 'badge-status-cancelled';
                viewStatusBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${stClass}`;
            }

            // Customer Details
            const customer = booking.customer || {};
            if (viewCustomerName) viewCustomerName.textContent = customer.name || '-';
            if (viewCustomerNo) viewCustomerNo.textContent = customer.customer_no || '-';
            if (viewCustomerMobile) viewCustomerMobile.textContent = customer.mobile || 'N/A';
            if (viewCustomerCity) viewCustomerCity.textContent = customer.city || 'N/A';

            // Items Table
            if (viewItemsTableBody) {
                const items = Array.isArray(booking.items) ? booking.items : [];
                if (items.length === 0) {
                    viewItemsTableBody.innerHTML = '<tr><td colspan="4" class="py-3 text-muted text-center">No items recorded</td></tr>';
                } else {
                    let itemsHtml = '';
                    items.forEach((it, idx) => {
                        const sn = it.sort_order || (idx + 1);
                        const w = (parseFloat(it.weight) || 0).toFixed(3);
                        const r = formatIndianCurrency(it.rate || 0);
                        const a = formatIndianCurrency(it.amount || 0);
                        itemsHtml += `
                            <tr>
                                <td class="text-center fw-bold text-muted">${sn}</td>
                                <td class="text-end fw-semibold">${w} gm</td>
                                <td class="text-end">₹${r}</td>
                                <td class="text-end fw-bold text-dark">₹${a}</td>
                            </tr>
                        `;
                    });
                    viewItemsTableBody.innerHTML = itemsHtml;
                }
            }

            // Totals
            if (viewTotalWeight) viewTotalWeight.textContent = `${(parseFloat(booking.total_weight) || 0).toFixed(3)} gm`;
            if (viewTotalAmount) viewTotalAmount.textContent = `₹${formatIndianCurrency(booking.total_amount || 0)}`;
            if (viewDeliveredWeight) viewDeliveredWeight.textContent = `${(parseFloat(booking.delivered_weight) || 0).toFixed(3)} gm`;
            if (viewPendingWeight) viewPendingWeight.textContent = `${(parseFloat(booking.pending_weight) || 0).toFixed(3)} gm`;

            // Narration
            if (viewNarration) viewNarration.textContent = booking.narration || 'None';

            // Payments Section
            const viewPaymentsCard = document.getElementById('viewPaymentsCard');
            const viewPaymentsTableBody = document.getElementById('viewPaymentsTableBody');
            const viewTotalPaidAmount = document.getElementById('viewTotalPaidAmount');
            const viewPaymentBalanceAmount = document.getElementById('viewPaymentBalanceAmount');

            const payments = Array.isArray(booking.payments) ? booking.payments : [];
            let totalPaid = 0;
            if (viewPaymentsCard) {
                if (payments.length > 0) {
                    viewPaymentsCard.style.display = 'block';
                    let payHtml = '';
                    payments.forEach((p, idx) => {
                        const sn = p.sort_order || (idx + 1);
                        const isCash = (p.mode || '').toUpperCase() === 'CASH';
                        const badgeClass = isCash ? 'badge-payment-cash' : 'badge-payment-online';
                        let modeText = isCash ? 'CASH' : 'ONLINE';
                        if (!isCash && p.online_method) {
                            modeText += ` (${p.online_method})`;
                        }
                        const ref = p.reference_no ? `<span class="text-muted ms-1 small">Ref: ${escapeHtml(p.reference_no)}</span>` : '<span class="text-muted small">-</span>';
                        const amt = parseFloat(p.amount) || 0;
                        totalPaid += amt;
                        payHtml += `
                            <tr>
                                <td class="text-center fw-bold text-muted">${sn}</td>
                                <td>
                                    <span class="badge ${badgeClass}">${modeText}</span>
                                </td>
                                <td>${ref}</td>
                                <td class="text-end fw-bold text-dark">₹${formatIndianCurrency(amt)}</td>
                            </tr>
                        `;
                    });
                    if (viewPaymentsTableBody) viewPaymentsTableBody.innerHTML = payHtml;
                } else {
                    viewPaymentsCard.style.display = 'none';
                    if (viewPaymentsTableBody) viewPaymentsTableBody.innerHTML = '';
                }
            }

            const bookingTotalAmt = parseFloat(booking.total_amount) || 0;
            const balanceAmt = Math.max(0, bookingTotalAmt - totalPaid);
            if (viewTotalPaidAmount) viewTotalPaidAmount.textContent = `₹${formatIndianCurrency(totalPaid)}`;
            if (viewPaymentBalanceAmount) {
                viewPaymentBalanceAmount.textContent = `₹${formatIndianCurrency(balanceAmt)}`;
                viewPaymentBalanceAmount.className = balanceAmt > 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
            }

            // Wire print button inside view modal
            if (btnViewModalPrint) {
                btnViewModalPrint.onclick = function () {
                    window.open(getRoute('bhavBookingPrint', booking.id), '_blank');
                };
            }

            // Open Bootstrap Modal
            const modalEl = document.getElementById('viewBookingModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.show();
            }
        })
        .catch(err => {
            console.warn('View details error:', err);
        });
    }

    /**
     * Check if user has unsaved work in the left form:
     * - Rows added
     * - Weight typed
     * - Customer selected
     * - Payment entries added or payment typed
     */
    function hasUnsavedEntries() {
        const hasRows = Array.isArray(bookingItems) && bookingItems.length > 0;
        const hasWeight = Boolean(inputWeight && inputWeight.value && inputWeight.value.trim() !== '');
        const hasCustomer = Boolean(selectedCustomer || (hiddenCustomerId && hiddenCustomerId.value));
        const hasPayments = Array.isArray(bookingPayments) && bookingPayments.length > 0;
        const hasPaymentAmount = Boolean(inputPaymentAmount && inputPaymentAmount.value && parseFloat(inputPaymentAmount.value) > 0);
        return hasRows || hasWeight || hasCustomer || hasPayments || hasPaymentAmount;
    }

    /**
     * Load an existing booking into the left form for editing (Task C4)
     * Fetches booking with customer + items from GET /bhav-bookings/{bhavBooking}
     */
    function loadBookingForEdit(bookingId, knownBookingNo = null) {
        // If clicking Edit on the booking that is already being edited, do nothing
        if (editingBookingId === bookingId) {
            return;
        }

        // If target booking number is already known and user has unsaved entries, confirm before fetching
        if (knownBookingNo && hasUnsavedEntries()) {
            const confirmed = window.confirm(`You have unsaved entries. Discard them and edit ${knownBookingNo}?`);
            if (!confirmed) {
                return;
            }
        }

        const showUrl = getRoute('bhavBookingShow', bookingId);

        fetch(showUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                showToast(data.message || 'Booking not found', 'error');
                throw new Error(data.message || 'Edit load failed');
            }
            return data;
        })
        .then(booking => {
            // If target booking number was not known upfront (e.g. from ?edit=) and user has unsaved entries, confirm now
            if (!knownBookingNo && hasUnsavedEntries()) {
                const bNo = booking.booking_no || `BB-${String(booking.id).padStart(6, '0')}`;
                const confirmed = window.confirm(`You have unsaved entries. Discard them and edit ${bNo}?`);
                if (!confirmed) {
                    return;
                }
            }
            // Restriction check: allowed if status = PENDING, delivered_weight = 0, and NO payments with source BUY_SELL
            const statusUpper = (booking.status || '').toUpperCase();
            const isModifiable = statusUpper === 'PENDING'
                && (parseFloat(booking.delivered_weight) || 0) === 0
                && !booking.has_external_payments;

            if (!isModifiable) {
                showToast('This booking has delivery/payment and cannot be edited', 'warning');
                return;
            }

            // Clear any lingering validation errors
            resetValidationErrors();

            // 1. Customer
            if (booking.customer) {
                selectCustomer(booking.customer);
            }

            // 2. Trade mode (BUY / SELL)
            setTradeMode(booking.trade_type || 'SELL');

            // 3. Type
            if (typeSelect) {
                typeSelect.value = booking.entry_type || 'ONLY_ENTRY';
            }

            // 4. Metal (Update currentMetal before select to avoid triggering confirmation prompt)
            currentMetal = booking.metal || 'GOLD';
            if (categorySelect) {
                categorySelect.value = currentMetal;
            }

            // 5. Multi-weight Items
            bookingItems = [];
            if (Array.isArray(booking.items) && booking.items.length > 0) {
                booking.items.forEach(it => {
                    const w = parseFloat(it.weight) || 0;
                    const r = parseFloat(it.rate) || 0;
                    const a = parseFloat(it.amount) || calculateRowAmount(w, r);
                    bookingItems.push({
                        id: nextItemId++,
                        weight: w,
                        rate: r,
                        amount: a
                    });
                });
            }
            renderEntriesTable();

            // 5b. Multi-entry Payments (load booking's BOOKING source payments)
            bookingPayments = [];
            nextPaymentId = 1;
            if (Array.isArray(booking.payments) && booking.payments.length > 0) {
                booking.payments.forEach(p => {
                    if (!p.source || p.source === 'BOOKING') {
                        bookingPayments.push({
                            id: nextPaymentId++,
                            mode: (p.mode || 'CASH').toUpperCase(),
                            online_method: p.online_method || 'UPI',
                            reference_no: p.reference_no || '',
                            amount: parseFloat(p.amount) || 0
                        });
                    }
                });
            }
            renderPaymentEntriesTable();
            setPaymentMode('CASH');
            if (inputPaymentAmount) inputPaymentAmount.value = '';
            if (paymentRefNo) paymentRefNo.value = '';
            if (paymentOnlineMethod) paymentOnlineMethod.value = 'UPI';

            // 6. Current entry line inputs
            if (inputWeight) inputWeight.value = '';
            if (inputRate) {
                if (booking.items && booking.items.length > 0) {
                    inputRate.value = parseFloat(booking.items[0].rate).toFixed(2);
                } else if (BHAV_CONFIG.default_rates && BHAV_CONFIG.default_rates[currentMetal]) {
                    inputRate.value = BHAV_CONFIG.default_rates[currentMetal];
                }
            }

            // 7. Narration
            if (inputNarration) {
                inputNarration.value = booking.narration || '';
            }
            if (narrationCounter) {
                narrationCounter.textContent = `${(booking.narration || '').length}/255`;
            }

            // 8. WhatsApp switch
            if (whatsappSwitch) {
                whatsappSwitch.checked = Boolean(booking.send_whatsapp);
            }

            // 9. Update grand totals
            updateAllTotals();

            // 10. Switch UI to Edit Mode
            enterEditMode(booking);

            // Smoothly scroll to the form and focus weight input
            const formEl = document.getElementById('bhavBookingForm');
            if (formEl) {
                formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            if (inputWeight) {
                inputWeight.focus();
            }
        })
        .catch(err => {
            console.warn('Booking edit load error:', err);
        });
    }

    /**
     * Delete an existing booking (Task C5)
     * Soft deletes via DELETE /bhav-bookings/{bhavBooking}
     */
    function deleteBooking(bookingId, bookingNo, amount) {
        const formattedAmt = formatIndianCurrency(amount || 0);
        const confirmed = window.confirm(`Are you sure you want to delete booking ${bookingNo} for ₹${formattedAmt}?`);
        if (!confirmed) return;

        const deleteUrl = getRoute('bhavBookingDelete', bookingId);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                showToast(data.message || 'Failed to delete booking.', 'error');
                throw new Error(data.message || 'Delete failed');
            }
            return data;
        })
        .then(() => {
            showToast(`Booking ${bookingNo} deleted`, 'success');

            // If the deleted booking was currently being edited, exit edit mode immediately
            if (editingBookingId === bookingId) {
                exitEditMode();
            }

            // Reload the list staying on the current page
            fetchBookingList(currentListPage);
        })
        .catch(err => {
            console.warn('Booking delete error:', err);
        });
    }

    // Attach Event Delegation to Table Body for Action 3-Dot Kebab Menu
    if (bhavTableBody) {
        bhavTableBody.addEventListener('click', function (e) {
            handleActionKebabMenuClick(e, lastLoadedBookingsMap, {
                onEdit: function (booking) {
                    loadBookingForEdit(booking.id, booking.booking_no);
                },
                onDeleted: function (bookingId, bookingNo) {
                    if (editingBookingId === bookingId) {
                        exitEditMode();
                    }
                    fetchBookingList(currentListPage);
                }
            });
        });
    }

    // =========================================================================
    // INITIALIZATION: Load Booking List & Auto-open Customer Field on Page Load
    // =========================================================================
    initPartyDateInputs();
    fetchBookingList(1);

    // Auto-load booking for edit if ?edit={id} query parameter is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    const editIdParam = urlParams.get('edit');
    if (editIdParam) {
        const editId = parseInt(editIdParam, 10);
        if (editId) {
            window.history.replaceState({}, '', window.location.pathname);
            loadBookingForEdit(editId);
        }
    } else {
        // Auto-focus customer input and open recent customers dropdown on page load
        if (customerSearchInput) {
            customerSearchInput.focus();
            if (!customerSearchResults || customerSearchResults.style.display !== 'block') {
                showRecentCustomersDropdown();
            }
        }
    }

});
