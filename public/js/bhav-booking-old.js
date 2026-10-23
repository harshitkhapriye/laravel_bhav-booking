/**
 * Old Bhav Booking List - Page JavaScript Logic
 * Application: Just Udhari - Buy-Sell Module
 *
 * Handles:
 * - Fetching old (DELIVERED / CANCELLED) bookings via GET /bhav-bookings/list?view=old
 * - Full-width table rendering with lavender header cells (#e0e3f7)
 * - 3-Dot Kebab Action menu delegation (View, Print, Share, WhatsApp, Delete, Silent Delete)
 * - Redirecting to main page with ?edit={id} on Edit click
 * - Select all / row-level checkbox selection
 * - Date Range Filter with presets & custom range
 * - Debounced customer search (400ms) & page size selector
 * - History back navigation
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // 1. STATE & DOM ELEMENTS
    // =========================================================================
    let currentOldPage = 1;
    let oldListAbortController = null;
    let partySearchTimer = null;
    let lastLoadedBookingsMap = {};

    // DOM Elements
    const oldTableBody = document.getElementById('oldBhavTableBody');
    const selectAllCheckbox = document.getElementById('selectAllRowsCheckbox');
    const oldPaginationInfo = document.getElementById('oldPaginationInfo');
    const oldPaginationLinks = document.getElementById('oldPaginationLinks');

    const filterPartyInput = document.getElementById('filterPartyInput');
    const pageSizeSelect = document.getElementById('pageSizeSelect');

    const dateRangePickerWrapper = document.getElementById('dateRangePickerWrapper');
    const dateRangeInput = document.getElementById('dateRangeInput');
    const dateFilterFrom = document.getElementById('dateFilterFrom');
    const dateFilterTo = document.getElementById('dateFilterTo');
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    const customDatePanel = document.getElementById('customDatePanel');
    const customDateFrom = document.getElementById('customDateFrom');
    const customDateTo = document.getElementById('customDateTo');
    const customDateError = document.getElementById('customDateError');
    const btnApplyCustomDate = document.getElementById('btnApplyCustomDate');

    const btnBackNavigation = document.getElementById('btnBackNavigation');

    // =========================================================================
    // 2. BACK NAVIGATION HANDLER
    // =========================================================================
    if (btnBackNavigation) {
        btnBackNavigation.addEventListener('click', function (e) {
            e.preventDefault();
            // If previous history exists, go back; otherwise navigate to Bhav Booking index
            if (window.history.length > 1 && document.referrer) {
                window.history.back();
            } else {
                const indexUrl = getRoute('bhavBookingIndex');
                window.location.href = indexUrl !== '#' ? indexUrl : '/buy-sell/bhav-booking';
            }
        });
    }

    // =========================================================================
    // 3. TABLE RENDERING FUNCTIONS
    // =========================================================================

    /**
     * Render table loading spinner state
     */
    function renderLoadingState() {
        if (!oldTableBody) return;
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;
        }
        oldTableBody.innerHTML = `
            <tr class="table-loading-row">
                <td colspan="9" class="py-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="fw-semibold">Loading old bookings...</span>
                </td>
            </tr>
        `;
    }

    /**
     * Render empty state with info icon and "No records found"
     */
    function renderEmptyState() {
        if (!oldTableBody) return;
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;
        }
        renderPagination({ current_page: 1, per_page: 10, total: 0, last_page: 1, from: 0, to: 0 });

        oldTableBody.innerHTML = `
            <tr class="table-empty-row">
                <td colspan="9" class="py-5 text-center text-muted">
                    <i class="fa-solid fa-circle-info fa-2x text-secondary opacity-50 mb-2 d-block"></i>
                    <span class="fw-semibold">No records found</span>
                </td>
            </tr>
        `;
    }

    /**
     * Render error row with retry button
     */
    function renderErrorState(message) {
        if (!oldTableBody) return;
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;
        }
        renderPagination({ current_page: 1, per_page: 10, total: 0, last_page: 1, from: 0, to: 0 });

        oldTableBody.innerHTML = `
            <tr class="table-error-row">
                <td colspan="9" class="py-4 text-center text-danger">
                    <i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block opacity-75"></i>
                    <div class="fw-semibold mb-2">${escapeHtml(message || 'Failed to load old bookings.')}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" id="btnRetryOldList">
                        <i class="fa-solid fa-rotate-right me-1"></i> Retry
                    </button>
                </td>
            </tr>
        `;

        const btnRetry = document.getElementById('btnRetryOldList');
        if (btnRetry) {
            btnRetry.addEventListener('click', function () {
                fetchOldBookings(currentOldPage);
            });
        }
    }

    /**
     * Render table rows matching Old Booking specifications:
     * Columns: [checkbox] SN | Date/Time | C. No/C. Name | Narration Remark | Sell Weight (Gm) | Sell Rate (₹) | Buy Weight (Gm) | Buy Rate (₹) | Action
     */
    function renderTableRows(rows, pagination) {
        if (!oldTableBody) return;

        if (!rows || rows.length === 0) {
            renderEmptyState();
            return;
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = false;
        }

        const startFrom = (pagination && typeof pagination.from === 'number') ? pagination.from : 1;
        lastLoadedBookingsMap = {};

        let html = '';
        rows.forEach((booking, index) => {
            lastLoadedBookingsMap[booking.id] = booking;

            const sn = startFrom + index;
            const dateTimeStr = formatDateTimeDisplay(booking.booked_at);

            // Customer Details
            const customer = booking.customer || {};
            const customerName = customer.name || 'Unknown';
            const customerNo = customer.customer_no || '-';

            // Narration: truncate with ellipsis, full text in tooltip
            const narrationText = booking.narration ? booking.narration.trim() : '';
            const narrationDisplay = narrationText
                ? `<div class="text-truncate text-secondary" style="max-width: 220px;" title="${escapeHtml(narrationText)}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${escapeHtml(narrationText)}">${escapeHtml(narrationText)}</div>`
                : `<span class="text-muted">---</span>`;

            // Rate Display (Single rate or "Multiple" with breakdown tooltip)
            const items = Array.isArray(booking.items) ? booking.items : [];
            let rateDisplay = '---';

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
                rateDisplay = `<span class="badge bg-light text-dark border cursor-help" title="${escapeHtml(tooltipContent)}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${escapeHtml(tooltipContent)}"><i class="fa-solid fa-layer-group text-primary me-1"></i>Multiple</span>`;
            }

            // BUY / SELL Columns logic:
            // SELL booking fills Sell Weight/Rate and shows "---" in Buy columns; BUY the opposite.
            const isBuy = (booking.trade_type || '').toUpperCase() === 'BUY';
            const wtFormatted = (parseFloat(booking.total_weight) || 0).toFixed(3);

            let sellWeightHtml = '<span class="text-muted">---</span>';
            let sellRateHtml = '<span class="text-muted">---</span>';
            let buyWeightHtml = '<span class="text-muted">---</span>';
            let buyRateHtml = '<span class="text-muted">---</span>';

            if (isBuy) {
                buyWeightHtml = `<span class="text-success fw-bold">${wtFormatted}</span>`;
                buyRateHtml = `<span class="text-success fw-semibold">${rateDisplay}</span>`;
            } else {
                sellWeightHtml = `<span class="text-danger fw-bold">${wtFormatted}</span>`;
                sellRateHtml = `<span class="text-danger fw-semibold">${rateDisplay}</span>`;
            }

            // Action column 3-dot dropdown menu
            const actionHtml = renderActionKebabMenu(booking);

            html += `
                <tr data-id="${booking.id}">
                    <td class="text-center py-2">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <input type="checkbox" class="form-check-input row-select-checkbox m-0 cursor-pointer" data-id="${booking.id}">
                            <span class="text-muted fw-semibold small ms-1">${sn}</span>
                        </div>
                    </td>
                    <td class="text-center py-2 text-nowrap" style="font-size: 11px;">
                        ${dateTimeStr}
                    </td>
                    <td class="text-start py-2">
                        <div class="fw-bold text-dark text-truncate" style="max-width: 190px;" title="${escapeHtml(customerName)}">${escapeHtml(customerName)}</div>
                        <div class="text-muted" style="font-size: 11px;">
                            ${escapeHtml(customerNo)} • <span class="fw-semibold text-dark">${escapeHtml(booking.booking_no || '')}</span>
                        </div>
                    </td>
                    <td class="text-start py-2">
                        ${narrationDisplay}
                    </td>
                    <td class="text-end py-2">
                        ${sellWeightHtml}
                    </td>
                    <td class="text-end py-2">
                        ${sellRateHtml}
                    </td>
                    <td class="text-end py-2">
                        ${buyWeightHtml}
                    </td>
                    <td class="text-end py-2">
                        ${buyRateHtml}
                    </td>
                    <td class="text-center py-2">
                        ${actionHtml}
                    </td>
                </tr>
            `;
        });

        oldTableBody.innerHTML = html;

        // Initialize tooltips if Bootstrap Tooltip is loaded
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipElements = oldTableBody.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipElements.forEach(el => new bootstrap.Tooltip(el));
        }

        // Attach row checkbox change listeners to update header checkbox state
        const rowCheckboxes = oldTableBody.querySelectorAll('.row-select-checkbox');
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectAllCheckboxState);
        });
    }

    /**
     * Update header checkbox state based on row checkbox selections
     */
    function updateSelectAllCheckboxState() {
        if (!selectAllCheckbox || !oldTableBody) return;
        const rowCheckboxes = oldTableBody.querySelectorAll('.row-select-checkbox');
        if (rowCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            return;
        }
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    }

    // Header checkbox selects/unselects all rows on the current page
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            if (!oldTableBody) return;
            const rowCheckboxes = oldTableBody.querySelectorAll('.row-select-checkbox');
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
        });
    }

    /**
     * Render pagination info and page links matching main page style:
     * "Showing 1 to 3 of 3 entries" + prev / page numbers / next
     */
    function renderPagination(pagination) {
        if (!oldPaginationInfo || !oldPaginationLinks) return;

        if (!pagination || pagination.total === 0 || !pagination.from) {
            oldPaginationInfo.textContent = 'Showing 0 to 0 of 0 entries';
            oldPaginationLinks.innerHTML = '';
            return;
        }

        const total = pagination.total || 0;
        const from = pagination.from || 0;
        const to = pagination.to || 0;
        const currentPage = pagination.current_page || 1;
        const lastPage = Math.max(1, pagination.last_page || 1);

        oldPaginationInfo.textContent = `Showing ${from} to ${to} of ${total} entries`;

        // At most 5 visible page numbers centered on currentPage
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

        // Numbered buttons
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

        oldPaginationLinks.innerHTML = linksHtml;

        // Wire page buttons
        const pageButtons = oldPaginationLinks.querySelectorAll('button[data-page]');
        pageButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (this.disabled) return;
                const targetPage = parseInt(this.getAttribute('data-page'), 10);
                if (targetPage && targetPage !== currentPage && targetPage >= 1 && targetPage <= lastPage) {
                    fetchOldBookings(targetPage);
                }
            });
        });
    }

    // =========================================================================
    // 4. DATA FETCHING (GET /bhav-bookings/list?view=old)
    // =========================================================================

    /**
     * Fetch old bookings from backend API with view=old
     */
    function fetchOldBookings(page = 1) {
        currentOldPage = page;

        if (oldListAbortController) {
            oldListAbortController.abort();
        }
        oldListAbortController = new AbortController();
        const signal = oldListAbortController.signal;

        renderLoadingState();

        const baseUrl = (window.APP_ROUTES && window.APP_ROUTES.bhavBookingList)
            ? window.APP_ROUTES.bhavBookingList
            : '/bhav-bookings/list';

        const params = new URLSearchParams();
        params.set('view', 'old');
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
                throw new Error(data.message || 'Failed to load old bookings.');
            }
            return data;
        })
        .then(data => {
            const rows = data.rows || [];
            const pagination = data.pagination || null;
            renderTableRows(rows, pagination);
            renderPagination(pagination);
        })
        .catch(error => {
            if (error.name === 'AbortError') return;
            console.warn('Old bookings fetch error:', error);
            renderErrorState(error.message);
        });
    }

    // =========================================================================
    // 5. ACTION MENU EVENT DELEGATION
    // =========================================================================
    if (oldTableBody) {
        oldTableBody.addEventListener('click', function (e) {
            handleActionKebabMenuClick(e, lastLoadedBookingsMap, {
                // On Edit: redirect to main page with ?edit={id}
                onEdit: function (booking) {
                    const indexUrl = getRoute('bhavBookingIndex') || '/buy-sell/bhav-booking';
                    window.location.href = `${indexUrl}?edit=${booking.id}`;
                },
                // On Delete or Silent Delete: refresh current page
                onDeleted: function () {
                    fetchOldBookings(currentOldPage);
                }
            });
        });
    }

    // =========================================================================
    // 6. FILTER CONTROLS: SEARCH & PAGE SIZE
    // =========================================================================

    // Customer search: 400ms debounce
    if (filterPartyInput) {
        filterPartyInput.addEventListener('input', function () {
            clearTimeout(partySearchTimer);
            partySearchTimer = setTimeout(() => {
                fetchOldBookings(1);
            }, 400);
        });
    }

    // Page size selector
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function () {
            fetchOldBookings(1);
        });
    }

    // =========================================================================
    // 7. DATE RANGE PICKER & PRESETS
    // =========================================================================

    function applyDateRange(from, to, presetName = '') {
        if (!dateRangeInput || !dateFilterFrom || !dateFilterTo) return;

        if (presetName === 'all' || !from || !to) {
            dateFilterFrom.value = '';
            dateFilterTo.value = '';
            dateRangeInput.value = '';
            dateRangeInput.placeholder = 'All Dates';
        } else {
            dateFilterFrom.value = formatDateISO(from);
            dateFilterTo.value = formatDateISO(to);
            dateRangeInput.value = `${formatDateDisplay(from)} - ${formatDateDisplay(to)}`;
        }

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

        fetchOldBookings(1);
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
                        if (customDateFrom && dateFilterFrom) customDateFrom.value = dateFilterFrom.value || formatDateISO(today);
                        if (customDateTo && dateFilterTo) customDateTo.value = dateFilterTo.value || formatDateISO(today);
                    }
                    presetButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    return;
                }

                if (customDatePanel) customDatePanel.style.display = 'none';

                if (preset === 'all') {
                    applyDateRange(null, null, 'all');
                    if (dateRangeDropdown) dateRangeDropdown.style.display = 'none';
                    return;
                }

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
                if (dateRangeDropdown) dateRangeDropdown.style.display = 'none';
            });
        });
    }

    // Apply Custom Date Range
    if (btnApplyCustomDate) {
        btnApplyCustomDate.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!customDateFrom || !customDateTo) return;

            const fromVal = customDateFrom.value;
            const toVal = customDateTo.value;

            if (!fromVal || !toVal) {
                if (customDateError) {
                    customDateError.textContent = 'Please select both From and To dates.';
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

    // Close dropdown on click outside
    document.addEventListener('click', function (e) {
        if (dateRangePickerWrapper && !dateRangePickerWrapper.contains(e.target)) {
            if (dateRangeDropdown) dateRangeDropdown.style.display = 'none';
        }
    });

    // =========================================================================
    // 8. INITIAL LOAD
    // =========================================================================
    fetchOldBookings(1);

});
