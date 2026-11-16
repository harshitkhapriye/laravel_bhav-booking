/* exported escapeHtml, formatIndianCurrency, amountToIndianWords, formatDateTimeDisplay, formatDateISO, formatDateDisplay, formatDateTimeTwoLines, formatBookingDateTime, pad2, metalIconSvg, renderMultipleRatesBadge, buildItemsPopoverContent, openItemsPopover, closeActiveItemsPopover, disposeAllItemsPopovers, initItemsPopovers, getLedgerEmptyStateSvg, renderSkeletonTableRows, getCsrfToken, getRoute, showToast, showDeleteUndoToast, isBookingModifiable, generateBookingSummary, shareBooking, copySummaryToClipboard, fallbackClipboardCopy, whatsappBooking, deleteBookingWithConfirm, viewBookingDetails, renderActionKebabMenu, handleActionKebabMenuClick, showGlobalToast, apiFetch, setupModalAccessibility, initExportHandler, initKeyboardShortcuts, getBhavPrintFormat, setBhavPrintFormat, updatePrintButtonLabels, setTableSpotlight, clearTableSpotlight */
/**
 * Bhav Booking - Shared JavaScript Utilities & Handlers
 * Application: Just Udhari - Buy-Sell Module
 *
 * Centralized helpers loaded on both the Main Bhav Booking page and Old Booking List page:
 * - Text escaping and number formatting (Indian currency & decimals)
 * - Date/time formatting helpers
 * - CSRF & dynamic named route resolvers
 * - Toast notification dispenser
 * - Action Column 3-dot Kebab menu renderer and event handlers (View, Print, Share, WhatsApp, Delete, Silent Delete)
 * - View Booking Modal fetch and DOM populator
 * - Unified apiFetch wrapper with consistent error/session handling
 */

// Global helper: Escape HTML to prevent XSS injection
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Global helper: Format numbers into Indian currency format (e.g., 1,45,000.00)
function formatIndianCurrency(amount) {
    const num = parseFloat(amount);
    if (isNaN(num)) return '0.00';

    const parts = num.toFixed(2).split('.');
    let integerPart = parts[0];
    const decimalPart = parts[1];

    let isNegative = false;
    if (integerPart.startsWith('-')) {
        isNegative = true;
        integerPart = integerPart.substring(1);
    }

    let lastThree = integerPart.substring(integerPart.length - 3);
    const otherNumbers = integerPart.substring(0, integerPart.length - 3);
    if (otherNumbers !== '') {
        lastThree = ',' + lastThree;
    }
    const formattedInteger = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;

    return (isNegative ? '-' : '') + formattedInteger + '.' + decimalPart;
}

// Global helper: Convert an amount to Indian words (e.g. "Rupees One Lakh Sixty Five Thousand Only")
function amountToIndianWords(amount) {
    if (amount === null || amount === undefined || amount === '') {
        return 'Rupees Zero Only';
    }

    let num = parseFloat(amount);
    if (isNaN(num)) return 'Rupees Zero Only';
    if (num < 0) num = Math.abs(num);

    const parts = num.toFixed(2).split('.');
    const rupees = parseInt(parts[0], 10);
    const paise = parseInt(parts[1], 10);

    const units = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    ];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    function twoDigits(n) {
        if (n === 0) return '';
        if (n < 20) return units[n];
        const t = Math.floor(n / 10);
        const u = n % 10;
        return tens[t] + (u > 0 ? ' ' + units[u] : '');
    }

    function threeDigits(n) {
        const h = Math.floor(n / 100);
        const rem = n % 100;
        const res = [];
        if (h > 0) res.push(units[h] + ' Hundred');
        if (rem > 0) res.push(twoDigits(rem));
        return res.join(' ');
    }

    function convertNumber(n) {
        if (n === 0) return '';
        const crores = Math.floor(n / 10000000);
        n = n % 10000000;
        const lakhs = Math.floor(n / 100000);
        n = n % 100000;
        const thousands = Math.floor(n / 1000);
        const rem = n % 1000;

        const words = [];
        if (crores > 0) words.push(convertNumber(crores) + ' Crore');
        if (lakhs > 0) words.push(twoDigits(lakhs) + ' Lakh');
        if (thousands > 0) words.push(twoDigits(thousands) + ' Thousand');
        if (rem > 0) words.push(threeDigits(rem));
        return words.join(' ');
    }

    let rupeeWords = convertNumber(rupees);
    if (!rupeeWords) rupeeWords = 'Zero';

    let paiseWords = '';
    if (paise > 0) {
        paiseWords = twoDigits(paise) + ' Paise';
    }

    if (paiseWords) {
        return `Rupees ${rupeeWords} and ${paiseWords} Only`;
    }
    return `Rupees ${rupeeWords} Only`;
}
window.amountToIndianWords = amountToIndianWords;

// Global helper: Zero-pad single-digit numbers
function pad2(n) {
    return String(n).padStart(2, '0');
}

// Global helper: Generate inline SVG icon for Gold/Silver bullion bar
function metalIconSvg(metal) {
    const m = (metal || '').toUpperCase();
    if (m === 'GOLD') {
        return '<svg width="13" height="13" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="metal-icon-svg" aria-hidden="true">' +
            '<defs>' +
            '<linearGradient id="bhavGoldBarGrad" x1="0" y1="0" x2="1" y2="1">' +
            '<stop offset="0%" stop-color="#f5c542"/>' +
            '<stop offset="100%" stop-color="#d4a017"/>' +
            '</linearGradient>' +
            '<linearGradient id="bhavGoldBarTop" x1="0" y1="0" x2="0" y2="1">' +
            '<stop offset="0%" stop-color="#fff5ba"/>' +
            '<stop offset="100%" stop-color="#f5c542"/>' +
            '</linearGradient>' +
            '</defs>' +
            '<path d="M2 10.5 L3.8 4.5 L10.2 4.5 L12 10.5 Z" fill="url(#bhavGoldBarGrad)" stroke="#b8860b" stroke-width="0.8" stroke-linejoin="round"/>' +
            '<path d="M3.8 4.5 L10.2 4.5 L9.2 6.8 L4.8 6.8 Z" fill="url(#bhavGoldBarTop)" opacity="0.95"/>' +
            '<line x1="4.8" y1="5.2" x2="9.2" y2="5.2" stroke="#ffffff" stroke-width="0.75" stroke-linecap="round" opacity="0.85"/>' +
            '</svg>';
    }
    return '<svg width="13" height="13" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="metal-icon-svg" aria-hidden="true">' +
        '<defs>' +
        '<linearGradient id="bhavSilverBarGrad" x1="0" y1="0" x2="1" y2="1">' +
        '<stop offset="0%" stop-color="#e5e7eb"/>' +
        '<stop offset="100%" stop-color="#9ca3af"/>' +
        '</linearGradient>' +
        '<linearGradient id="bhavSilverBarTop" x1="0" y1="0" x2="0" y2="1">' +
        '<stop offset="0%" stop-color="#f9fafb"/>' +
        '<stop offset="100%" stop-color="#e5e7eb"/>' +
        '</linearGradient>' +
        '</defs>' +
        '<path d="M2 10.5 L3.8 4.5 L10.2 4.5 L12 10.5 Z" fill="url(#bhavSilverBarGrad)" stroke="#6b7280" stroke-width="0.8" stroke-linejoin="round"/>' +
        '<path d="M3.8 4.5 L10.2 4.5 L9.2 6.8 L4.8 6.8 Z" fill="url(#bhavSilverBarTop)" opacity="0.95"/>' +
        '<line x1="4.8" y1="5.2" x2="9.2" y2="5.2" stroke="#ffffff" stroke-width="0.75" stroke-linecap="round" opacity="0.9"/>' +
        '</svg>';
}
window.metalIconSvg = metalIconSvg;

// Global helper: Format date-time string into DD-MM-YYYY HH:MM AM/PM
function formatDateTimeDisplay(dateStr) {
    if (!dateStr) return '-';
    const dateObj = new Date(dateStr);
    if (isNaN(dateObj.getTime())) return String(dateStr);

    const dd = String(dateObj.getDate()).padStart(2, '0');
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    const yyyy = dateObj.getFullYear();

    let hours = dateObj.getHours();
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const hh = String(hours).padStart(2, '0');

    return `${dd}-${mm}-${yyyy} ${hh}:${minutes} ${ampm}`;
}

// Global helper: Format Date object to YYYY-MM-DD
function formatDateISO(d) {
    if (!d) return '';
    const dateObj = d instanceof Date ? d : new Date(d);
    if (isNaN(dateObj.getTime())) return String(d);
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Global helper: Format Date object or YYYY-MM-DD string to DD-MM-YYYY
function formatDateDisplay(d) {
    if (!d) return '-';
    if (typeof d === 'string') {
        const parts = d.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2].padStart(2, '0')}-${parts[1].padStart(2, '0')}-${parts[0]}`;
        }
        const parsed = new Date(d);
        if (!isNaN(parsed.getTime())) {
            d = parsed;
        } else {
            return String(d);
        }
    }
    if (d instanceof Date && !isNaN(d.getTime())) {
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    }
    return String(d);
}

// Global helper: Format server ISO/SQL datetime string into two compact lines: Date on line 1, Time on line 2
function formatBookingDateTime(dateString) {
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
    const dateFormatted = `${day}-${month}-${year}`;
    const timeFormatted = `${pad2(hours)}:${minutes} ${ampm}`;
    const fullDateTime = `${dateFormatted} ${timeFormatted}`;

    return (
        `<div class="booking-datetime-cell" title="${escapeHtml(fullDateTime)}" data-bs-toggle="tooltip" data-bs-placement="top">` +
        `<div class="fw-bold text-dark text-nowrap booking-date-line" style="font-size: 12px; font-weight: 700; line-height: 1.25;">${escapeHtml(dateFormatted)}</div>` +
        `<div class="text-muted text-nowrap booking-time-line" style="font-size: 11px; font-weight: 500; line-height: 1.25;">${timeFormatted}</div>` +
        `</div>`
    );
}

// Global helper: Format server ISO/SQL datetime string into two compact lines
function formatDateTimeTwoLines(dateString) {
    return formatBookingDateTime(dateString);
}
window.formatBookingDateTime = formatBookingDateTime;

// Global helper: Ledger / Box SVG illustration for list empty state
function getLedgerEmptyStateSvg() {
    return '<svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="empty-state-svg mb-2" aria-hidden="true">' +
        '<rect x="12" y="10" width="40" height="46" rx="6" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>' +
        '<rect x="12" y="10" width="10" height="46" rx="4" fill="#0f172a"/>' +
        '<circle cx="17" cy="18" r="2" fill="#f57c00"/>' +
        '<circle cx="17" cy="33" r="2" fill="#ffffff" opacity="0.8"/>' +
        '<circle cx="17" cy="48" r="2" fill="#ffffff" opacity="0.8"/>' +
        '<line x1="28" y1="20" x2="46" y2="20" stroke="#cbd5e1" stroke-width="2.5" stroke-linecap="round"/>' +
        '<line x1="28" y1="28" x2="42" y2="28" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>' +
        '<line x1="28" y1="36" x2="44" y2="36" stroke="#fed7aa" stroke-width="2" stroke-linecap="round"/>' +
        '<circle cx="43" cy="45" r="9" fill="#fff7ed" stroke="#f57c00" stroke-width="2"/>' +
        '<path d="M43 41 V49 M39 45 H47" stroke="#f57c00" stroke-width="1.8" stroke-linecap="round"/>' +
        '</svg>';
}
window.getLedgerEmptyStateSvg = getLedgerEmptyStateSvg;

// Global helper: Generate 5 skeleton shimmer rows matching table columns
function renderSkeletonTableRows(colCount, rowCount = 5) {
    let rowsHtml = '';
    const widths = [35, 65, 80, 50, 55, 60, 45, 70, 40, 50, 30];
    for (let i = 0; i < rowCount; i++) {
        let colsHtml = '';
        for (let j = 0; j < colCount; j++) {
            const w = widths[j % widths.length];
            colsHtml += `<td class="py-3 px-2 text-center align-middle"><span class="skeleton-bar" style="width: ${w}%;"></span></td>`;
        }
        rowsHtml += `<tr class="skeleton-row">${colsHtml}</tr>`;
    }
    return rowsHtml;
}
window.renderSkeletonTableRows = renderSkeletonTableRows;

// Global helper: Retrieve CSRF token from page meta tag
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Global helper: Resolve Laravel named routes registered in window.APP_ROUTES
function getRoute(routeName, id = null) {
    if (!window.APP_ROUTES || !window.APP_ROUTES[routeName]) {
        console.warn(`Route ${routeName} not registered in window.APP_ROUTES`);
        return '#';
    }
    const url = window.APP_ROUTES[routeName];
    return id !== null ? url.replace('__ID__', id) : url;
}

// Global helper: Display floating Bootstrap toast notifications (escaped by default, trusted HTML via options.html = true)
function showToast(message, type = 'info', delay = 4500, options = {}) {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        alert(message);
        return;
    }

    let toastDelay = 4500;
    let isTrustedHtml = false;

    if (typeof delay === 'object' && delay !== null) {
        isTrustedHtml = Boolean(delay.html);
    } else {
        if (typeof delay === 'number') {
            toastDelay = delay;
        }
        if (typeof options === 'object' && options !== null) {
            isTrustedHtml = Boolean(options.html);
        } else if (typeof options === 'boolean') {
            isTrustedHtml = options;
        }
    }

    const toastId = 'toast-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    let borderClass = 'border-primary';
    let iconHtml = '<i class="fa-solid fa-circle-info text-primary me-2"></i>';

    if (type === 'success') {
        borderClass = 'border-success';
        iconHtml = '<i class="fa-solid fa-circle-check text-success me-2"></i>';
    } else if (type === 'error' || type === 'danger') {
        borderClass = 'border-danger';
        iconHtml = '<i class="fa-solid fa-circle-xmark text-danger me-2"></i>';
    } else if (type === 'warning') {
        borderClass = 'border-warning';
        iconHtml = '<i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>';
    }

    // Always escape by default. Only allow unescaped HTML when explicitly requested with { html: true }
    const contentHtml = isTrustedHtml ? String(message) : escapeHtml(String(message ?? ''));
    const toastEl = document.createElement('div');
    toastEl.id = toastId;
    toastEl.className = `toast align-items-center bg-white border-start border-4 ${borderClass} shadow-sm mb-2`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center py-2 px-3 fw-semibold text-dark small">
                ${iconHtml}
                <span>${contentHtml}</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: toastDelay });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    } else {
        setTimeout(() => toastEl.remove(), toastDelay);
    }
}

// Global helper: Centralized fetch wrapper with robust CSRF, error handling, and JSON safety
async function apiFetch(url, options = {}) {
    const opts = Object.assign({}, options);
    opts.headers = Object.assign(
        {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        opts.headers || {}
    );

    const method = (opts.method || 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD' && !opts.headers['X-CSRF-TOKEN']) {
        const token = getCsrfToken();
        if (token) {
            opts.headers['X-CSRF-TOKEN'] = token;
        }
    }

    let response;
    try {
        response = await fetch(url, opts);
    } catch (networkError) {
        if (networkError.name === 'AbortError') {
            throw networkError;
        }
        console.error('Network request failed:', networkError);
        showToast('Server error / no internet. Please try again.', 'error');
        throw networkError;
    }

    let data = null;
    const contentType = response.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON response:', e);
            data = null;
        }
    } else {
        try {
            const text = await response.text();
            try {
                data = JSON.parse(text);
            } catch (_) {
                data = text ? { message: text } : null;
            }
        } catch (_) {
            data = null;
        }
    }

    if (response.status === 419) {
        showToast(
            'Session expired. Please refresh the page. <button type="button" class="btn btn-sm btn-outline-primary ms-2 py-0 px-2" onclick="window.location.reload()">Refresh</button>',
            'warning',
            10000,
            { html: true }
        );
        const err = new Error('Session expired');
        err.status = 419;
        err.response = response;
        err.data = data;
        throw err;
    }

    if (response.status === 429) {
        showToast('Too many requests, please wait a moment.', 'warning');
        const err = new Error('Too many requests');
        err.status = 429;
        err.response = response;
        err.data = data;
        throw err;
    }

    if (response.status === 404) {
        const msg = data && data.message ? data.message : 'Record not found';
        showToast(msg, 'error');
        const err = new Error(msg);
        err.status = 404;
        err.response = response;
        err.data = data;
        throw err;
    }

    if (response.status === 422) {
        const err = new Error(data && data.message ? data.message : 'Validation failed');
        err.status = 422;
        err.response = response;
        err.data = data;
        throw err;
    }

    if (!response.ok) {
        if (response.status >= 500) {
            console.error('Server error response:', response.status, data);
            showToast('Server error / no internet. Please try again.', 'error');
        } else {
            const msg = data && data.message ? data.message : `Request failed (${response.status})`;
            showToast(msg, 'error');
        }
        const err = new Error(data && data.message ? data.message : `HTTP ${response.status}`);
        err.status = response.status;
        err.response = response;
        err.data = data;
        throw err;
    }

    return data !== null ? data : {};
}

// Global helper: Modal focus accessibility (focus first field on open, return to opener on close)
function setupModalAccessibility(modalEl, defaultFocusEl = null) {
    if (!modalEl || modalEl.dataset.a11yInitialized === 'true') return;
    modalEl.dataset.a11yInitialized = 'true';

    let openerButton = null;
    modalEl.addEventListener('show.bs.modal', function (e) {
        openerButton = e.relatedTarget || document.activeElement;
    });
    modalEl.addEventListener('shown.bs.modal', function () {
        if (defaultFocusEl) {
            const target = typeof defaultFocusEl === 'string' ? modalEl.querySelector(defaultFocusEl) : defaultFocusEl;
            if (target && typeof target.focus === 'function') {
                target.focus();
                return;
            }
        }
        const firstField = modalEl.querySelector(
            'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]):not(.btn-close)'
        );
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus();
        }
    });
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (openerButton && typeof openerButton.focus === 'function') {
            openerButton.focus();
        }
    });
}

// Global helper: Check whether a booking can be edited or deleted (PENDING, 0 delivered, and NO external BUY_SELL payments)
function isBookingModifiable(booking) {
    if (!booking) return false;
    const statusUpper = (booking.status || '').toUpperCase();
    return (
        statusUpper === 'PENDING' && (parseFloat(booking.delivered_weight) || 0) === 0 && !booking.has_external_payments
    );
}

// Global helper: Generate a concise text summary of a booking or booking group
function generateBookingSummary(input) {
    if (!input) return '';
    const bookings = Array.isArray(input)
        ? input
        : Array.isArray(input.bookings) && input.bookings.length > 0
            ? input.bookings
            : [input];
    if (bookings.length === 0) return '';

    if (bookings.length === 1) {
        const booking = bookings[0];
        const bookingNo = booking.booking_no || '-';
        const dateStr = formatDateTimeDisplay(booking.booked_at);
        const customer = booking.customer || {};
        const customerName = customer.name || 'Customer';
        const trade = (booking.trade_type || '').toUpperCase();
        const metal = (booking.metal || '').toUpperCase();
        const wt = (parseFloat(booking.total_weight) || 0).toFixed(3);
        const amt = formatIndianCurrency(booking.total_amount || 0);

        const payments = Array.isArray(booking.payments) ? booking.payments : [];
        const totalPaid = payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
        const totalAmt = parseFloat(booking.total_amount) || 0;
        const balance = Math.max(0, totalAmt - totalPaid);

        let summaryText = `Booking: ${bookingNo}\nDate: ${dateStr}\nCustomer: ${customerName}\nTrade: ${trade}\nMetal: ${metal}\nWeight: ${wt} gm\nAmount: ₹${amt}`;
        summaryText += `\nPaid: ₹${formatIndianCurrency(totalPaid)}\nBalance: ₹${formatIndianCurrency(balance)}`;

        return summaryText;
    }

    // Multiple bookings in group
    const first = bookings[0];
    const customer = first.customer || {};
    const customerName = customer.name || 'Customer';
    const dateStr = formatDateTimeDisplay(first.booked_at);
    const trade = (first.trade_type || '').toUpperCase();

    let summaryText = `Booking Group Confirmation\nDate: ${dateStr}\nCustomer: ${customerName}\nTrade: ${trade}`;

    let grandAmount = 0;
    let grandPaid = 0;

    bookings.forEach((b) => {
        const bNo = b.booking_no || '-';
        const metal = (b.metal || '').toUpperCase();
        const wt = (parseFloat(b.total_weight) || 0).toFixed(3);
        const amt = parseFloat(b.total_amount) || 0;
        const payments = Array.isArray(b.payments) ? b.payments : [];
        const paid = payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);

        grandAmount += amt;
        grandPaid += paid;

        summaryText += `\n\n• ${bNo} (${metal}): ${wt} gm | ₹${formatIndianCurrency(amt)}`;
        if (paid > 0) {
            summaryText += ` | Paid: ₹${formatIndianCurrency(paid)}`;
        }
    });

    const balance = Math.max(0, grandAmount - grandPaid);
    summaryText += `\n\nTotal Amount: ₹${formatIndianCurrency(grandAmount)}`;
    summaryText += `\nTotal Paid: ₹${formatIndianCurrency(grandPaid)}`;
    summaryText += `\nBalance: ₹${formatIndianCurrency(balance)}`;

    return summaryText;
}

// Global helper: Share booking details via Web Share API or Clipboard fallback
function shareBooking(booking) {
    const summary = generateBookingSummary(booking);
    const title = Array.isArray(booking)
        ? 'Bhav Booking Group'
        : `Bhav Booking ${booking.booking_no || ''}`;
    if (navigator.share) {
        navigator
            .share({
                title: title,
                text: summary,
            })
            .catch((err) => {
                if (err.name !== 'AbortError') {
                    copySummaryToClipboard(summary);
                }
            });
    } else {
        copySummaryToClipboard(summary);
    }
}

function copySummaryToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
            .writeText(text)
            .then(() => {
                showToast('Booking details copied', 'success');
            })
            .catch(() => {
                fallbackClipboardCopy(text);
            });
    } else {
        fallbackClipboardCopy(text);
    }
}

function fallbackClipboardCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        showToast('Booking details copied', 'success');
    } catch (e) {
        showToast('Failed to copy booking details', 'error');
    }
    document.body.removeChild(ta);
}

// Global helper: Open WhatsApp web chat prefilled with booking summary
function whatsappBooking(input) {
    const bookings = Array.isArray(input)
        ? input
        : Array.isArray(input.bookings) && input.bookings.length > 0
            ? input.bookings
            : [input];
    const first = bookings[0] || {};
    const customer = first.customer || {};
    const rawMobile = String(customer.mobile || '').replace(/\D/g, '');
    if (!rawMobile || rawMobile.length < 10) {
        showToast('Customer mobile number not available', 'warning');
        return;
    }
    const clean10 = rawMobile.slice(-10);
    const summary = generateBookingSummary(input);
    const url = `https://wa.me/91${clean10}?text=${encodeURIComponent(summary)}`;
    window.open(url, '_blank');
}

// Global helper: Show toast with Undo countdown button and call restore on click
function showDeleteUndoToast(bookingId, bookingNo, isSilent, onRestoredCallback) {
    const escapedBookingNo = escapeHtml(bookingNo || 'Booking');
    let secondsLeft = 5;
    const btnUndoId = 'btnUndoDelete_' + bookingId + '_' + Date.now();
    const countdownId = 'undoCountdown_' + bookingId + '_' + Date.now();

    const undoMsg = `
        <div class="d-flex align-items-center justify-content-between gap-2 w-100">
            <span>Booking ${escapedBookingNo} ${isSilent ? 'silently deleted' : 'deleted'}</span>
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-2.5 py-0.5 fw-bold ms-2 text-nowrap" id="${btnUndoId}" style="font-size: 11px;">
                Undo (<span id="${countdownId}">${secondsLeft}</span>s)
            </button>
        </div>
    `;

    showToast(undoMsg, 'success', 5500, { html: true });

    let countdownInterval = setInterval(() => {
        secondsLeft -= 1;
        const countdownEl = document.getElementById(countdownId);
        if (countdownEl) {
            countdownEl.textContent = String(Math.max(0, secondsLeft));
        }
        if (secondsLeft <= 0) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }, 1000);

    setTimeout(() => {
        const undoBtn = document.getElementById(btnUndoId);
        if (undoBtn) {
            undoBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                undoBtn.disabled = true;
                undoBtn.textContent = 'Restoring...';

                const restoreUrl = getRoute('bhavBookingRestore', bookingId);
                apiFetch(restoreUrl, { method: 'POST' })
                    .then(() => {
                        showToast(`Booking ${escapedBookingNo} restored`, 'success');
                        const toastEl = undoBtn.closest('.toast');
                        if (toastEl && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                            const inst = bootstrap.Toast.getInstance(toastEl);
                            if (inst) inst.hide();
                        }
                        if (typeof onRestoredCallback === 'function') {
                            onRestoredCallback(bookingId);
                        }
                    })
                    .catch((err) => {
                        console.warn('Booking restore error:', err);
                    });
            });
        }
    }, 50);
}

// Global helpers: Thermal vs A4 Print Format Management
const PRINT_FORMAT_STORAGE_KEY = 'bhavPrintFormat';

function getBhavPrintFormat() {
    try {
        const val = localStorage.getItem(PRINT_FORMAT_STORAGE_KEY);
        return val === 'thermal' ? 'thermal' : 'a4';
    } catch (e) {
        return 'a4';
    }
}

function setBhavPrintFormat(format) {
    try {
        const fmt = format === 'thermal' ? 'thermal' : 'a4';
        localStorage.setItem(PRINT_FORMAT_STORAGE_KEY, fmt);
        updatePrintButtonLabels();
        return fmt;
    } catch (e) {
        return 'a4';
    }
}

function updatePrintButtonLabels() {
    const fmt = getBhavPrintFormat();
    const label = fmt === 'thermal' ? 'Thermal' : 'A4';

    const viewModalPrintLabel = document.getElementById('viewModalPrintLabel');
    if (viewModalPrintLabel) {
        viewModalPrintLabel.textContent = `Print Voucher (${label})`;
    }

    const successPrintLabel = document.getElementById('successPrintLabel');
    if (successPrintLabel) {
        successPrintLabel.textContent = `Print (${label})`;
    }
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
        updatePrintButtonLabels();
    });
}

/**
 * Global helper: Reusable Confirm Action Modal
 * Returns a Promise<{ confirmed: boolean, value?: any }>
 */
function showConfirmDialog(options = {}) {
    const {
        title = 'Confirm Action',
        message = '',
        details = null,
        confirmText = 'Confirm',
        confirmClass = 'btn-danger',
        icon = '',
        warning = '',
        input = null,
    } = options;

    return new Promise((resolve) => {
        const modalEl = document.getElementById('confirmActionModal');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            console.warn('Confirm action modal element (#confirmActionModal) not found in DOM.');
            resolve({ confirmed: false, value: null });
            return;
        }

        const titleEl = document.getElementById('confirmActionModalLabel');
        const iconEl = document.getElementById('confirmModalIcon');
        const messageEl = document.getElementById('confirmModalMessage');
        const warningEl = document.getElementById('confirmModalWarning');
        const warningTextEl = document.getElementById('confirmModalWarningText');
        const detailsEl = document.getElementById('confirmModalDetails');
        const inputWrapperEl = document.getElementById('confirmModalInputWrapper');
        const inputLabelEl = document.getElementById('confirmModalInputLabel');
        const inputContainerEl = document.getElementById('confirmModalInputContainer');
        const inputErrorEl = document.getElementById('confirmModalInputError');
        const cancelBtn = document.getElementById('confirmModalCancelBtn');
        const confirmBtn = document.getElementById('confirmModalConfirmBtn');

        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;

        if (iconEl) {
            if (icon) {
                iconEl.className = icon + ' me-1';
                iconEl.style.display = 'inline-block';
            } else {
                iconEl.style.display = 'none';
            }
        }

        if (warningEl && warningTextEl) {
            if (warning) {
                warningTextEl.textContent = warning;
                warningEl.style.display = 'flex';
            } else {
                warningEl.style.display = 'none';
            }
        }

        if (detailsEl) {
            detailsEl.innerHTML = '';
            let detailItems = [];
            if (Array.isArray(details)) {
                detailItems = details;
            } else if (details && typeof details === 'object') {
                detailItems = Object.entries(details).map(([k, v]) => ({ label: k, value: v }));
            }

            if (detailItems.length > 0) {
                detailsEl.style.display = 'block';
                detailItems.forEach((d) => {
                    const row = document.createElement('div');
                    row.className = 'd-flex justify-content-between py-1 border-bottom border-light-subtle';
                    const lbl = document.createElement('span');
                    lbl.className = 'text-muted';
                    lbl.textContent = d.label + ':';
                    const val = document.createElement('strong');
                    val.className = 'text-dark';
                    val.textContent = d.value;
                    row.appendChild(lbl);
                    row.appendChild(val);
                    detailsEl.appendChild(row);
                });
            } else {
                detailsEl.style.display = 'none';
            }
        }

        let selectReasonEl = null;
        let textReasonEl = null;
        let genericInputEl = null;

        if (inputWrapperEl && inputContainerEl) {
            inputContainerEl.innerHTML = '';
            if (inputErrorEl) {
                inputErrorEl.style.display = 'none';
                inputErrorEl.textContent = '';
            }

            if (input) {
                inputWrapperEl.style.display = 'block';
                if (inputLabelEl) {
                    inputLabelEl.textContent = input.label || (input.type === 'cancel_reason' ? 'Reason for Cancellation:' : 'Input:');
                }

                if (input.type === 'cancel_reason') {
                    selectReasonEl = document.createElement('select');
                    selectReasonEl.className = 'form-select form-select-sm mb-2';
                    selectReasonEl.id = 'confirmInputSelectReason';

                    const opts = Array.isArray(input.options) ? input.options : [
                        'Customer changed mind',
                        'Rate dispute',
                        'Wrong entry',
                        'Other',
                    ];

                    opts.forEach((opt) => {
                        const optEl = document.createElement('option');
                        optEl.value = opt;
                        optEl.textContent = opt;
                        selectReasonEl.appendChild(optEl);
                    });

                    textReasonEl = document.createElement('textarea');
                    textReasonEl.className = 'form-control form-control-sm';
                    textReasonEl.id = 'confirmInputTextReason';
                    textReasonEl.rows = 2;
                    textReasonEl.placeholder = 'Optional additional notes...';

                    selectReasonEl.addEventListener('change', function () {
                        if (selectReasonEl.value === 'Other') {
                            textReasonEl.placeholder = 'Please enter reason (minimum 3 characters)...';
                            textReasonEl.focus();
                        } else {
                            textReasonEl.placeholder = 'Optional additional notes...';
                            if (inputErrorEl) inputErrorEl.style.display = 'none';
                        }
                    });

                    inputContainerEl.appendChild(selectReasonEl);
                    inputContainerEl.appendChild(textReasonEl);
                } else if (input.type === 'select') {
                    genericInputEl = document.createElement('select');
                    genericInputEl.className = 'form-select form-select-sm';
                    (input.options || []).forEach((opt) => {
                        const optEl = document.createElement('option');
                        optEl.value = typeof opt === 'object' ? opt.value : opt;
                        optEl.textContent = typeof opt === 'object' ? opt.label : opt;
                        genericInputEl.appendChild(optEl);
                    });
                    inputContainerEl.appendChild(genericInputEl);
                } else if (input.type === 'textarea') {
                    genericInputEl = document.createElement('textarea');
                    genericInputEl.className = 'form-control form-control-sm';
                    genericInputEl.rows = input.rows || 3;
                    genericInputEl.placeholder = input.placeholder || '';
                    inputContainerEl.appendChild(genericInputEl);
                } else {
                    genericInputEl = document.createElement('input');
                    genericInputEl.type = input.type || 'text';
                    genericInputEl.className = 'form-control form-control-sm';
                    genericInputEl.placeholder = input.placeholder || '';
                    inputContainerEl.appendChild(genericInputEl);
                }
            } else {
                inputWrapperEl.style.display = 'none';
            }
        }

        if (confirmBtn) {
            confirmBtn.textContent = confirmText;
            confirmBtn.className = 'btn rounded-pill px-4 py-2 fw-bold ' + confirmClass;
        }

        let isConfirmed = false;
        let confirmedValue = null;

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

        function cleanup() {
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            modalEl.removeEventListener('shown.bs.modal', onShown);
            if (confirmBtn) confirmBtn.removeEventListener('click', onConfirm);
        }

        function onShown() {
            if (cancelBtn) cancelBtn.focus();
        }

        function onHidden() {
            cleanup();
            resolve({
                confirmed: isConfirmed,
                value: confirmedValue,
            });
        }

        function onConfirm() {
            if (input && input.type === 'cancel_reason') {
                const sel = selectReasonEl ? selectReasonEl.value : '';
                const txt = textReasonEl ? textReasonEl.value.trim() : '';

                if (sel === 'Other' && txt.length < 3) {
                    if (inputErrorEl) {
                        inputErrorEl.textContent = 'Please enter a reason (minimum 3 characters).';
                        inputErrorEl.style.display = 'block';
                    }
                    if (textReasonEl) textReasonEl.focus();
                    return;
                }

                confirmedValue = sel === 'Other' ? txt : (txt ? `${sel} - ${txt}` : sel);
            } else if (genericInputEl) {
                const val = genericInputEl.value.trim();
                if (input && input.required && !val) {
                    if (inputErrorEl) {
                        inputErrorEl.textContent = 'This field is required.';
                        inputErrorEl.style.display = 'block';
                    }
                    genericInputEl.focus();
                    return;
                }
                confirmedValue = val;
            }

            isConfirmed = true;
            modalInstance.hide();
        }

        modalEl.addEventListener('shown.bs.modal', onShown);
        modalEl.addEventListener('hidden.bs.modal', onHidden);
        if (confirmBtn) confirmBtn.addEventListener('click', onConfirm);

        modalInstance.show();
    });
}

// Global helper: Confirm and execute normal or silent soft delete
function deleteBookingWithConfirm(bookingId, bookingNo, amount, isSilent, onDeletedCallback, onRestoredCallback) {
    const formattedAmt = formatIndianCurrency(amount || 0);

    showConfirmDialog({
        title: isSilent ? 'Silent Delete Booking?' : 'Delete Booking?',
        message: isSilent
            ? `Silently delete booking ${bookingNo} (₹${formattedAmt})? No message will be sent.`
            : `Delete booking ${bookingNo} (₹${formattedAmt})? Customer will be informed.`,
        details: [
            { label: 'Booking No', value: bookingNo },
            ...(amount ? [{ label: 'Amount', value: `₹${formattedAmt}` }] : []),
        ],
        confirmText: isSilent ? 'Silent Delete' : 'Delete',
        confirmClass: 'btn-danger',
        icon: 'fa-solid fa-trash-can text-danger',
    }).then(({ confirmed }) => {
        if (!confirmed) return;

        const deleteUrl = getRoute('bhavBookingDelete', bookingId);
        apiFetch(deleteUrl, { method: 'DELETE' })
            .then(() => {
                if (typeof onDeletedCallback === 'function') {
                    onDeletedCallback(bookingId, bookingNo);
                }
                showDeleteUndoToast(bookingId, bookingNo, isSilent, onRestoredCallback);
            })
            .catch((err) => {
                console.warn('Booking delete error:', err);
                showToast(err.message || 'Failed to delete booking', 'danger');
            });
    });
}

// Global helper: Fetch booking details and display the shared View Booking modal
function viewBookingDetails(bookingId) {
    const showUrl = getRoute('bhavBookingShow', bookingId);

    apiFetch(showUrl)
        .then((booking) => {
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

            if (viewTradeTypeBadge) {
                const isBuy = (booking.trade_type || '').toUpperCase() === 'BUY';
                viewTradeTypeBadge.textContent = booking.trade_type;
                viewTradeTypeBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isBuy ? 'bg-success text-white' : 'bg-danger text-white'}`;
            }

            if (viewMetalBadge) {
                const isGold = (booking.metal || '').toUpperCase() === 'GOLD';
                viewMetalBadge.innerHTML = `${metalIconSvg(booking.metal)}<span>${escapeHtml(booking.metal || '')}</span>`;
                viewMetalBadge.className = `badge rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 ${isGold ? 'badge-metal-gold' : 'badge-metal-silver'}`;
            }

            if (viewTypeBadge) {
                const isEntry = (booking.entry_type || '').toUpperCase() === 'ONLY_ENTRY';
                viewTypeBadge.textContent = isEntry ? 'Only Entry' : 'Bhav Cut + Ledger';
                viewTypeBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isEntry ? 'badge-type-entry' : 'badge-type-ledger'}`;
            }

            const viewCancelledAlert = document.getElementById('viewCancelledAlert');
            const viewCancelledAt = document.getElementById('viewCancelledAt');
            const viewCancelReason = document.getElementById('viewCancelReason');

            if (viewStatusBadge) {
                const st = (booking.status || 'PENDING').toUpperCase();
                viewStatusBadge.textContent = st;
                let stClass = 'badge-status-pending';
                if (st === 'PARTIAL') stClass = 'badge-status-partial';
                else if (st === 'DELIVERED') stClass = 'badge-status-delivered';
                else if (st === 'CANCELLED') stClass = 'badge-status-cancelled bg-danger text-white';
                viewStatusBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${stClass}`;
            }

            if ((booking.status || '').toUpperCase() === 'CANCELLED') {
                if (viewCancelledAlert) viewCancelledAlert.style.display = 'block';
                if (viewCancelledAt) {
                    viewCancelledAt.textContent = booking.cancelled_at
                        ? formatDateTimeDisplay(booking.cancelled_at)
                        : '-';
                }
                if (viewCancelReason) {
                    viewCancelReason.textContent = booking.cancel_reason || 'No reason provided';
                }
            } else {
                if (viewCancelledAlert) viewCancelledAlert.style.display = 'none';
            }

            const customer = booking.customer || {};
            if (viewCustomerName) viewCustomerName.textContent = customer.name || '-';
            if (viewCustomerNo) viewCustomerNo.textContent = customer.customer_no || '-';
            if (viewCustomerMobile) viewCustomerMobile.textContent = customer.mobile || 'N/A';
            if (viewCustomerCity) viewCustomerCity.textContent = customer.city || 'N/A';

            if (viewItemsTableBody) {
                const items = Array.isArray(booking.items) ? booking.items : [];
                const viewItemsCountBadge = document.getElementById('viewItemsCountBadge');
                if (viewItemsCountBadge) {
                    viewItemsCountBadge.textContent = `${items.length} ${items.length === 1 ? 'item' : 'items'}`;
                }
                if (items.length === 0) {
                    viewItemsTableBody.innerHTML =
                        '<tr><td colspan="4" class="py-2 text-muted text-center">No items recorded</td></tr>';
                } else {
                    let itemsHtml = '';
                    items.forEach((it, idx) => {
                        const sn = it.sort_order || idx + 1;
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

            if (viewTotalWeight)
                viewTotalWeight.textContent = `${(parseFloat(booking.total_weight) || 0).toFixed(3)} gm`;
            if (viewTotalAmount) viewTotalAmount.textContent = `₹${formatIndianCurrency(booking.total_amount || 0)}`;
            if (viewDeliveredWeight)
                viewDeliveredWeight.textContent = `${(parseFloat(booking.delivered_weight) || 0).toFixed(3)} gm`;
            if (viewPendingWeight)
                viewPendingWeight.textContent = `${(parseFloat(booking.pending_weight) || 0).toFixed(3)} gm`;

            // Payments Table & Paid / Balance in View Modal
            const viewPaymentsTableBody = document.getElementById('viewPaymentsTableBody');
            const viewPaymentsPaid = document.getElementById('viewPaymentsPaid');
            const viewPaymentsBalance = document.getElementById('viewPaymentsBalance');
            const viewTotalsPaidCol = document.getElementById('viewTotalsPaidCol');
            const viewTotalsBalanceCol = document.getElementById('viewTotalsBalanceCol');
            const viewTotalsPaid = document.getElementById('viewTotalsPaid');
            const viewTotalsBalance = document.getElementById('viewTotalsBalance');

            const payments = Array.isArray(booking.payments) ? booking.payments : [];
            const totalPaid = payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
            const totalBookingAmt = parseFloat(booking.total_amount) || 0;
            const balance = Math.max(0, totalBookingAmt - totalPaid);

            if (viewPaymentsPaid) {
                viewPaymentsPaid.textContent = `₹${formatIndianCurrency(totalPaid)}`;
            }
            if (viewPaymentsBalance) {
                viewPaymentsBalance.textContent = `₹${formatIndianCurrency(balance)}`;
                viewPaymentsBalance.className = balance > 0 ? 'text-danger' : 'text-success';
            }

            // Stat chips in Totals grid (if payments exist)
            if (viewTotalsPaid) {
                viewTotalsPaid.textContent = `₹${formatIndianCurrency(totalPaid)}`;
            }
            if (viewTotalsBalance) {
                viewTotalsBalance.textContent = `₹${formatIndianCurrency(balance)}`;
                viewTotalsBalance.className = balance > 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
            }
            if (viewTotalsPaidCol && viewTotalsBalanceCol) {
                if (payments.length > 0) {
                    viewTotalsPaidCol.style.display = '';
                    viewTotalsBalanceCol.style.display = '';
                } else {
                    viewTotalsPaidCol.style.display = 'none';
                    viewTotalsBalanceCol.style.display = 'none';
                }
            }

            // VS TODAY Stat Chip in View Modal
            const viewVsToday = document.getElementById('viewVsToday');
            if (viewVsToday) {
                const diff = booking.rate_diff;
                if (diff === null || diff === undefined) {
                    viewVsToday.textContent = '—';
                    viewVsToday.className = 'fw-bold text-muted';
                    viewVsToday.removeAttribute('title');
                } else if (diff > 0) {
                    viewVsToday.textContent = `▲ ₹${formatIndianCurrency(diff)}`;
                    viewVsToday.className = 'fw-bold text-success';
                } else if (diff < 0) {
                    viewVsToday.textContent = `▼ ₹${formatIndianCurrency(Math.abs(diff))}`;
                    viewVsToday.className = 'fw-bold text-danger';
                } else {
                    viewVsToday.textContent = '₹0.00';
                    viewVsToday.className = 'fw-bold text-muted';
                }

                if (diff !== null && diff !== undefined) {
                    const unitGrams = window.BHAV_CONFIG?.rate_unit_grams || 10;
                    const bookedAvg = booking.booked_avg_rate !== null && booking.booked_avg_rate !== undefined
                        ? `₹${formatIndianCurrency(booking.booked_avg_rate).replace(/\.00$/, '')}`
                        : '-';
                    const todayRate = booking.today_rate_used !== null && booking.today_rate_used !== undefined
                        ? `₹${formatIndianCurrency(booking.today_rate_used).replace(/\.00$/, '')}`
                        : '-';
                    const pendingWt = (parseFloat(booking.pending_weight) || 0).toFixed(3);
                    viewVsToday.title = `Booked avg ${bookedAvg} /${unitGrams}gm • Today ${todayRate} /${unitGrams}gm • Pending ${pendingWt} gm`;
                }
            }

            if (viewPaymentsTableBody) {
                if (payments.length === 0) {
                    viewPaymentsTableBody.innerHTML =
                        '<tr><td colspan="4" class="py-2 text-muted text-center">No payments recorded</td></tr>';
                } else {
                    let pRowsHtml = '';
                    payments.forEach((p, pIdx) => {
                        const sn = p.sort_order || pIdx + 1;
                        const isCash = (p.mode || '').toUpperCase() === 'CASH';
                        const modeBadge = isCash
                            ? '<span class="badge-payment-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>'
                            : '<span class="badge-payment-online"><i class="fa-solid fa-globe"></i> Online</span>';
                        const methodRef = isCash
                            ? 'Cash Payment'
                            : `${escapeHtml(p.online_method || 'ONLINE')}${p.reference_no ? ' • ' + escapeHtml(p.reference_no) : ''}`;
                        const amtStr = formatIndianCurrency(p.amount || 0);

                        pRowsHtml += `
                        <tr>
                            <td class="text-center fw-bold text-muted">${sn}</td>
                            <td class="text-start">${modeBadge}</td>
                            <td class="text-start text-muted">${methodRef}</td>
                            <td class="text-end fw-bold text-dark">₹${amtStr}</td>
                        </tr>
                    `;
                    });
                    viewPaymentsTableBody.innerHTML = pRowsHtml;
                }
            }

            // Narration with one-line ellipsis and more toggle
            const btnToggleNarration = document.getElementById('btnToggleNarration');
            const narrationText = booking.narration ? booking.narration.trim() : '';
            if (viewNarration) {
                viewNarration.textContent = narrationText || 'None';
                viewNarration.className = 'text-dark view-narration-collapsed fst-italic';
            }
            if (btnToggleNarration) {
                btnToggleNarration.textContent = 'More';
                if (narrationText.length > 40) {
                    btnToggleNarration.style.display = 'inline';
                    btnToggleNarration.onclick = function () {
                        if (!viewNarration) return;
                        if (viewNarration.classList.contains('view-narration-collapsed')) {
                            viewNarration.classList.remove('view-narration-collapsed');
                            viewNarration.classList.add('view-narration-expanded');
                            btnToggleNarration.textContent = 'Less';
                        } else {
                            viewNarration.classList.remove('view-narration-expanded');
                            viewNarration.classList.add('view-narration-collapsed');
                            btnToggleNarration.textContent = 'More';
                        }
                    };
                } else {
                    btnToggleNarration.style.display = 'none';
                    btnToggleNarration.onclick = null;
                }
            }

            // Populate History Section (Collapsible card under payments)
            const viewHistoryCount = document.getElementById('viewHistoryCount');
            const viewHistoryEmpty = document.getElementById('viewHistoryEmpty');
            const viewHistoryTableWrapper = document.getElementById('viewHistoryTableWrapper');
            const viewHistoryTableBody = document.getElementById('viewHistoryTableBody');
            const logs = Array.isArray(booking.logs) ? booking.logs : [];

            if (viewHistoryCount) viewHistoryCount.textContent = logs.length;

            if (viewHistoryTableBody) {
                if (logs.length === 0) {
                    if (viewHistoryEmpty) viewHistoryEmpty.style.display = 'block';
                    if (viewHistoryTableWrapper) viewHistoryTableWrapper.style.display = 'none';
                    viewHistoryTableBody.innerHTML = '';
                } else {
                    if (viewHistoryEmpty) viewHistoryEmpty.style.display = 'none';
                    if (viewHistoryTableWrapper) viewHistoryTableWrapper.style.display = 'block';

                    let logsHtml = '';
                    logs.forEach((log) => {
                        let badgeClass = 'bg-secondary';
                        let actionText = log.action || '';
                        switch (log.action) {
                            case 'CREATED':
                                badgeClass = 'bg-success';
                                actionText = 'Created';
                                break;
                            case 'UPDATED':
                                badgeClass = 'bg-warning text-dark';
                                actionText = 'Updated';
                                break;
                            case 'DELETED':
                            case 'SILENT_DELETED':
                                badgeClass = 'bg-danger';
                                actionText = 'Deleted';
                                break;
                            case 'RESTORED':
                                badgeClass = 'bg-success';
                                actionText = 'Restored';
                                break;
                            case 'PRINTED':
                                badgeClass = 'bg-info text-dark';
                                actionText = 'Printed';
                                break;
                            case 'SHARED':
                                badgeClass = 'bg-primary';
                                actionText = 'Shared';
                                break;
                            case 'CANCELLED':
                                badgeClass = 'bg-danger text-white';
                                actionText = 'Cancelled';
                                break;
                        }

                        const dt = log.created_at
                            ? new Date(log.created_at).toLocaleString('en-IN', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true,
                            })
                            : '-';

                        const summary = escapeHtml(log.summary || log.action);
                        const ip = log.ip_address
                            ? `<span class="text-muted ms-1" style="font-size: 10px;">(${escapeHtml(log.ip_address)})</span>`
                            : '';

                        logsHtml += `
                        <tr>
                            <td class="text-nowrap text-secondary">${dt}</td>
                            <td><span class="badge ${badgeClass} rounded-pill px-2 py-1">${actionText}</span></td>
                            <td class="text-dark">${summary} ${ip}</td>
                        </tr>
                    `;
                    });
                    viewHistoryTableBody.innerHTML = logsHtml;
                }
            }

            // Reset history collapse to collapsed state by default on open
            const collapseViewHistoryEl = document.getElementById('collapseViewHistory');
            const viewHistoryChevron = document.getElementById('viewHistoryChevron');
            const viewHistoryCollapseText = document.getElementById('viewHistoryCollapseText');
            if (collapseViewHistoryEl) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseViewHistoryEl);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        collapseViewHistoryEl.classList.remove('show');
                    }
                } else {
                    collapseViewHistoryEl.classList.remove('show');
                }
                if (!collapseViewHistoryEl.dataset.listenerAttached) {
                    collapseViewHistoryEl.dataset.listenerAttached = 'true';
                    collapseViewHistoryEl.addEventListener('show.bs.collapse', function () {
                        const chev = document.getElementById('viewHistoryChevron');
                        const txt = document.getElementById('viewHistoryCollapseText');
                        if (chev) chev.classList.add('view-history-chevron-rotated');
                        if (txt) txt.textContent = 'Hide History';
                    });
                    collapseViewHistoryEl.addEventListener('hide.bs.collapse', function () {
                        const chev = document.getElementById('viewHistoryChevron');
                        const txt = document.getElementById('viewHistoryCollapseText');
                        if (chev) chev.classList.remove('view-history-chevron-rotated');
                        if (txt) txt.textContent = 'Show History';
                    });
                }
            }
            if (viewHistoryChevron) viewHistoryChevron.classList.remove('view-history-chevron-rotated');
            if (viewHistoryCollapseText) viewHistoryCollapseText.textContent = 'Show History';

            if (btnViewModalPrint) {
                btnViewModalPrint.onclick = function () {
                    const fmt = getBhavPrintFormat();
                    if (window.APP_ROUTES && window.APP_ROUTES.bhavBookingLogAction) {
                        apiFetch(getRoute('bhavBookingLogAction', booking.id), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'PRINTED' }),
                        }).catch(() => { });
                    }
                    const printUrl = getRoute('bhavBookingPrint', booking.id) + (fmt === 'thermal' ? '?format=thermal' : '');
                    window.open(printUrl, '_blank');
                };
            }

            const viewModalPrintGroup = document.getElementById('viewModalPrintGroup');
            if (viewModalPrintGroup) {
                const optA4 = viewModalPrintGroup.querySelector('.print-option-a4');
                const optThermal = viewModalPrintGroup.querySelector('.print-option-thermal');
                if (optA4) {
                    optA4.onclick = function (ev) {
                        ev.preventDefault();
                        setBhavPrintFormat('a4');
                        if (btnViewModalPrint) btnViewModalPrint.click();
                    };
                }
                if (optThermal) {
                    optThermal.onclick = function (ev) {
                        ev.preventDefault();
                        setBhavPrintFormat('thermal');
                        if (btnViewModalPrint) btnViewModalPrint.click();
                    };
                }
            }

            const modalEl = document.getElementById('viewBookingModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.show();
            }
        })
        .catch((err) => {
            console.warn('View details error:', err);
        });
}

// Global helper: Generate HTML for the 3-dot kebab action dropdown menu
function renderActionKebabMenu(booking) {
    const statusUpper = (booking.status || 'PENDING').toUpperCase();
    const isCancelled = statusUpper === 'CANCELLED';
    const deliveredWeight = parseFloat(booking.delivered_weight) || 0;
    const canCancel = !isCancelled && (statusUpper === 'PENDING' || statusUpper === 'PARTIAL') && deliveredWeight === 0;

    const bookingId = booking.id;
    const bookingNo = escapeHtml(booking.booking_no || '');
    const amount = escapeHtml(booking.total_amount || 0);

    const customerName = escapeHtml(
        (booking.customer && (booking.customer.name || booking.customer.party_name)) ||
        booking.party_name ||
        'Customer'
    );
    let dateStr = '';
    if (booking.booking_date) {
        dateStr = escapeHtml(formatDateDisplay(booking.booking_date));
    } else if (booking.booked_at) {
        dateStr = escapeHtml(formatDateDisplay(booking.booked_at));
    }
    const headerStripHtml = `
        <li class="action-kebab-header px-3 py-1.5 border-bottom mb-1 text-truncate" style="background-color: #f8fafc; cursor: default;">
            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px; line-height: 1.25;">${customerName}</div>
            <div class="text-muted text-truncate" style="font-size: 10px; line-height: 1.25; margin-top: 1px;">${dateStr ? dateStr + ' • ' : ''}${bookingNo}</div>
        </li>
    `;

    // Cancelled bookings cannot be edited or deleted; menu shows only View, Print, Share
    if (isCancelled) {
        return `
        <div class="dropdown action-dropdown-wrapper d-inline-block">
            <button type="button"
                    class="btn btn-action-kebab"
                    data-bs-toggle="dropdown"
                    data-bs-popper-config='{"strategy":"fixed"}'
                    aria-expanded="false"
                    aria-label="Actions"
                    title="Actions">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow action-kebab-menu py-1" style="min-width: 175px;">
                ${headerStripHtml}
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-view" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-eye text-success me-2" style="width: 16px;"></i> View
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-repeat" href="#" data-id="${bookingId}" data-booking-no="${bookingNo}">
                        <i class="fa-solid fa-copy text-info me-2" style="width: 16px;"></i> Repeat
                    </a>
                </li>
                <li class="px-2 py-0.5">
                    <div class="btn-group btn-group-sm w-100" style="height: 28px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center action-menu-print flex-grow-1 border-end-0 py-0 px-2 fw-semibold" data-id="${bookingId}" style="font-size: 12px;">
                            <i class="fa-solid fa-print text-warning me-1.5" style="width: 14px;"></i> Print
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split px-2 py-0 action-menu-print-split-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Print Format">
                            <span class="visually-hidden">Format</span>
                        </button>
                        <ul class="dropdown-menu shadow-sm py-1" style="min-width: 140px; font-size: 12px;">
                            <li><a class="dropdown-item py-1 px-3 action-menu-print" href="#" data-id="${bookingId}" data-format="a4">A4</a></li>
                            <li><a class="dropdown-item py-1 px-3 action-menu-print" href="#" data-id="${bookingId}" data-format="thermal">Thermal (80mm)</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-share" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-share-nodes text-primary me-2" style="width: 16px;"></i> Share
                    </a>
                </li>
            </ul>
        </div>
        `;
    }

    const isModifiable = isBookingModifiable(booking);
    const disabledAttr = isModifiable ? '' : 'disabled';
    const disabledClass = isModifiable ? '' : 'disabled text-muted';
    const disabledTooltip = isModifiable ? '' : 'This booking has delivery/payment and cannot be edited';

    const cancelItemHtml = canCancel
        ? `
        <li>
            <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-cancel" href="#" data-id="${bookingId}" data-booking-no="${bookingNo}" style="color: #ea580c;">
                <i class="fa-solid fa-ban me-2" style="width: 16px; color: #ea580c;"></i> Cancel booking
            </a>
        </li>
    `
        : '';

    return `
        <div class="dropdown action-dropdown-wrapper d-inline-block">
            <button type="button"
                    class="btn btn-action-kebab"
                    data-bs-toggle="dropdown"
                    data-bs-popper-config='{"strategy":"fixed"}'
                    aria-expanded="false"
                    aria-label="Actions"
                    title="Actions">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow action-kebab-menu py-1" style="min-width: 175px;">
                ${headerStripHtml}
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-view" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-eye text-success me-2" style="width: 16px;"></i> View
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-repeat" href="#" data-id="${bookingId}" data-booking-no="${bookingNo}">
                        <i class="fa-solid fa-copy text-info me-2" style="width: 16px;"></i> Repeat
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-edit ${disabledClass}" href="#" data-id="${bookingId}" ${disabledAttr} title="${escapeHtml(disabledTooltip)}">
                        <i class="fa-solid fa-pen-to-square text-warning me-2" style="width: 16px;"></i> Edit
                    </a>
                </li>
                <li class="px-2 py-0.5">
                    <div class="btn-group btn-group-sm w-100" style="height: 28px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center action-menu-print flex-grow-1 border-end-0 py-0 px-2 fw-semibold" data-id="${bookingId}" style="font-size: 12px;">
                            <i class="fa-solid fa-print text-warning me-1.5" style="width: 14px;"></i> Print
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split px-2 py-0 action-menu-print-split-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Print Format">
                            <span class="visually-hidden">Format</span>
                        </button>
                        <ul class="dropdown-menu shadow-sm py-1" style="min-width: 140px; font-size: 12px;">
                            <li><a class="dropdown-item py-1 px-3 action-menu-print" href="#" data-id="${bookingId}" data-format="a4">A4</a></li>
                            <li><a class="dropdown-item py-1 px-3 action-menu-print" href="#" data-id="${bookingId}" data-format="thermal">Thermal (80mm)</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-share" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-share-nodes text-primary me-2" style="width: 16px;"></i> Share
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-whatsapp" href="#" data-id="${bookingId}">
                        <i class="fa-brands fa-whatsapp text-success me-2" style="width: 16px;"></i> WhatsApp
                    </a>
                </li>
                ${cancelItemHtml}
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-delete text-danger ${disabledClass}" href="#" data-id="${bookingId}" data-booking-no="${bookingNo}" data-amount="${amount}" ${disabledAttr} title="${escapeHtml(disabledTooltip)}">
                        <i class="fa-solid fa-trash-can text-danger me-2" style="width: 16px;"></i> Delete
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-silent-delete text-danger ${disabledClass}" href="#" data-id="${bookingId}" data-booking-no="${bookingNo}" data-amount="${amount}" ${disabledAttr} title="${escapeHtml(disabledTooltip)}">
                        <i class="fa-solid fa-eraser text-danger me-2" style="width: 16px;"></i> Silent Delete
                    </a>
                </li>
            </ul>
        </div>
    `;
}

// Global helper: Cancel a booking with reason using the shared confirm modal
function cancelBookingWithConfirm(bookingId, bookingNo, booking, onCancelledCallback) {
    const isSell = (booking && booking.trade_type ? booking.trade_type : '').toUpperCase() === 'SELL';
    const payments = booking && Array.isArray(booking.payments) ? booking.payments : [];
    const totalPaid = payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0)
        || (booking ? (isSell ? parseFloat(booking.received_amount) : parseFloat(booking.given_amount)) : 0) || 0;

    let warningText = null;
    if (totalPaid > 0) {
        const term = isSell ? 'received' : 'given';
        warningText = `₹${formatIndianCurrency(totalPaid)} already ${term} — settle the refund separately.`;
    }

    showConfirmDialog({
        title: 'Cancel Booking?',
        message: `Are you sure you want to cancel booking ${bookingNo}?`,
        details: [
            { label: 'Booking No', value: bookingNo },
            ...(booking && booking.customer && booking.customer.name ? [{ label: 'Customer', value: booking.customer.name }] : []),
            ...(booking && booking.total_amount ? [{ label: 'Amount', value: `₹${formatIndianCurrency(booking.total_amount)}` }] : []),
        ],
        warning: warningText,
        confirmText: 'Cancel Booking',
        confirmClass: 'btn-danger',
        icon: 'fa-solid fa-ban text-danger',
        input: {
            type: 'cancel_reason',
            options: [
                'Customer changed mind',
                'Rate dispute',
                'Wrong entry',
                'Other',
            ],
        },
    }).then(({ confirmed, value }) => {
        if (!confirmed) return;
        const reason = (value || '').trim();
        if (!reason) {
            showToast('Please provide a reason to cancel the booking.', 'danger');
            return;
        }

        const cancelUrl = getRoute('bhavBookingCancel', bookingId);
        apiFetch(cancelUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason: reason }),
        }).then((res) => {
            showToast('Booking cancelled', 'success');
            if (typeof onCancelledCallback === 'function') {
                onCancelledCallback(res);
            }
        }).catch((err) => {
            showToast(err.message || 'Failed to cancel booking', 'danger');
        });
    });
}

// Global helper: Event delegation dispatcher for action kebab menu items
function handleActionKebabMenuClick(e, bookingsMap, callbacks = {}) {
    // 1. View Clicked
    const viewItem = e.target.closest('.action-menu-view');
    if (viewItem) {
        e.preventDefault();
        const id = parseInt(viewItem.getAttribute('data-id'), 10);
        if (id) viewBookingDetails(id);
        return;
    }

    // 1.5. Repeat Clicked
    const repeatItem = e.target.closest('.action-menu-repeat');
    if (repeatItem) {
        e.preventDefault();
        const id = parseInt(repeatItem.getAttribute('data-id'), 10);
        const bNo = repeatItem.getAttribute('data-booking-no') || '';
        if (id && typeof callbacks.onRepeat === 'function') {
            const booking = bookingsMap ? bookingsMap[id] : { id: id, booking_no: bNo };
            callbacks.onRepeat(booking || { id: id, booking_no: bNo });
        }
        return;
    }

    // Stop split toggle caret inside kebab menu from prematurely dismissing parent
    const printToggle = e.target.closest('.action-menu-print-split-toggle');
    if (printToggle) {
        e.stopPropagation();
    }

    // 2. Print Clicked
    const printItem = e.target.closest('.action-menu-print');
    if (printItem) {
        e.preventDefault();
        const id = parseInt(printItem.getAttribute('data-id'), 10);
        if (id) {
            let fmt = printItem.getAttribute('data-format');
            if (fmt) {
                setBhavPrintFormat(fmt);
            } else {
                fmt = getBhavPrintFormat();
            }
            const printUrl = getRoute('bhavBookingPrint', id) + (fmt === 'thermal' ? '?format=thermal' : '');
            window.open(printUrl, '_blank');
        }
        return;
    }

    // 3. Edit Clicked
    const editItem = e.target.closest('.action-menu-edit');
    if (editItem) {
        e.preventDefault();
        if (editItem.classList.contains('disabled')) {
            showToast('This booking has delivery/payment and cannot be edited', 'warning');
            return;
        }
        const id = parseInt(editItem.getAttribute('data-id'), 10);
        if (id && typeof callbacks.onEdit === 'function') {
            const booking = bookingsMap ? bookingsMap[id] : { id: id };
            callbacks.onEdit(booking || { id: id });
        }
        return;
    }

    // 4. Share Clicked
    const shareItem = e.target.closest('.action-menu-share');
    if (shareItem) {
        e.preventDefault();
        const id = parseInt(shareItem.getAttribute('data-id'), 10);
        const booking = bookingsMap ? bookingsMap[id] : null;
        if (booking) {
            if (booking.booking_group_id && bookingsMap) {
                const groupBookings = Object.values(bookingsMap).filter(
                    (b) => b && b.booking_group_id === booking.booking_group_id
                );
                if (groupBookings.length > 1) {
                    shareBooking(groupBookings);
                    return;
                }
            }
            shareBooking(booking);
        } else if (id) {
            apiFetch(getRoute('bhavBookingShow', id)).then((b) => shareBooking(b));
        }
        return;
    }

    // 5. WhatsApp Clicked
    const waItem = e.target.closest('.action-menu-whatsapp');
    if (waItem) {
        e.preventDefault();
        const id = parseInt(waItem.getAttribute('data-id'), 10);
        const booking = bookingsMap ? bookingsMap[id] : null;
        if (booking) {
            if (booking.booking_group_id && bookingsMap) {
                const groupBookings = Object.values(bookingsMap).filter(
                    (b) => b && b.booking_group_id === booking.booking_group_id
                );
                if (groupBookings.length > 1) {
                    whatsappBooking(groupBookings);
                    return;
                }
            }
            whatsappBooking(booking);
        } else if (id) {
            apiFetch(getRoute('bhavBookingShow', id)).then((b) => whatsappBooking(b));
        }
        return;
    }

    // 5.5 Cancel Booking Clicked
    const cancelItem = e.target.closest('.action-menu-cancel');
    if (cancelItem) {
        e.preventDefault();
        const id = parseInt(cancelItem.getAttribute('data-id'), 10);
        const bNo = cancelItem.getAttribute('data-booking-no') || 'Booking';
        const booking = bookingsMap ? bookingsMap[id] : null;
        cancelBookingWithConfirm(id, bNo, booking, callbacks.onCancelled);
        return;
    }

    // 6. Delete Clicked
    const deleteItem = e.target.closest('.action-menu-delete');
    if (deleteItem) {
        e.preventDefault();
        if (deleteItem.classList.contains('disabled')) {
            showToast('This booking has delivery/payment and cannot be edited', 'warning');
            return;
        }
        const id = parseInt(deleteItem.getAttribute('data-id'), 10);
        const bNo = deleteItem.getAttribute('data-booking-no') || 'Booking';
        const amt = deleteItem.getAttribute('data-amount') || 0;
        deleteBookingWithConfirm(id, bNo, amt, false, callbacks.onDeleted, callbacks.onRestored);
        return;
    }

    // 7. Silent Delete Clicked
    const silentItem = e.target.closest('.action-menu-silent-delete');
    if (silentItem) {
        e.preventDefault();
        if (silentItem.classList.contains('disabled')) {
            showToast('This booking has delivery/payment and cannot be edited', 'warning');
            return;
        }
        const id = parseInt(silentItem.getAttribute('data-id'), 10);
        const bNo = silentItem.getAttribute('data-booking-no') || 'Booking';
        const amt = silentItem.getAttribute('data-amount') || 0;
        deleteBookingWithConfirm(id, bNo, amt, true, callbacks.onDeleted, callbacks.onRestored);
        return;
    }
}

// =========================================================================
// GLOBAL TOAST NOTIFICATIONS (for 429 Throttle & System Messages)
// =========================================================================
function showGlobalToast(message, type = 'info') {
    let toastContainer = document.getElementById('globalToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'globalToastContainer';
        toastContainer.style.cssText =
            'position: fixed; top: 20px; right: 20px; z-index: 1090; min-width: 280px; max-width: 400px;';
        document.body.appendChild(toastContainer);
    }

    const toastEl = document.createElement('div');
    const bgClass =
        type === 'warning'
            ? 'bg-warning text-dark'
            : type === 'danger'
                ? 'bg-danger text-white'
                : type === 'success'
                    ? 'bg-success text-white'
                    : 'bg-primary text-white';
    toastEl.className = `toast align-items-center ${bgClass} border-0 shadow show mb-2`;
    toastEl.setAttribute('role', 'alert');
    toastEl.style.borderRadius = '12px';
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body fw-semibold small py-2 px-3">
                <i class="fa-solid fa-bell me-1"></i> ${escapeHtml(message)}
            </div>
            <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);
    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 400);
    }, 4500);
}


// =========================================================================
// ITEMS BREAKDOWN POPOVER ("Multiple" rates interactive popover)
// =========================================================================
let activeItemsPopover = null;
let itemsPopoversGlobalListenersInitialized = false;

/**
 * Render clickable "Multiple" badge button for table cells
 */
function renderMultipleRatesBadge(bookingId) {
    return `<button type="button" class="btn badge bg-light text-dark border text-nowrap btn-multiple-rates" data-booking-id="${bookingId}" aria-label="View rate breakdown" tabindex="0"><i class="fa-solid fa-layer-group text-primary me-1"></i>Multiple<i class="fa-solid fa-chevron-down ms-1 text-muted" style="font-size: 8.5px;"></i></button>`;
}

/**
 * Build rich DOM element for the items popover (pure createElement, no user HTML)
 */
function buildItemsPopoverContent(booking, isPinned, onClose) {
    const container = document.createElement('div');
    container.className = 'bhav-items-popover-box';

    // 1. Navy Header (#0f172a, title "Entries • BB-000xxx" + ×)
    const header = document.createElement('div');
    header.className = 'bhav-popover-header d-flex align-items-center justify-content-between text-white';
    header.style.backgroundColor = '#0f172a';
    header.style.padding = '8px 12px';
    header.style.margin = '-10px -12px 8px -12px';
    header.style.borderTopLeftRadius = '11px';
    header.style.borderTopRightRadius = '11px';

    const bookingNo = booking && booking.booking_no ? booking.booking_no : '';
    const headerTitle = document.createElement('div');
    headerTitle.className = 'fw-bold d-flex align-items-center gap-1.5';
    headerTitle.style.fontSize = '12px';
    headerTitle.style.letterSpacing = '0.3px';
    headerTitle.textContent = bookingNo ? `Entries • ${bookingNo}` : 'Entries';
    header.appendChild(headerTitle);

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-close btn-close-white btn-close-sm';
    closeBtn.setAttribute('aria-label', 'Close popover');
    closeBtn.style.fontSize = '10px';
    closeBtn.style.cursor = 'pointer';
    if (typeof onClose === 'function') {
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            onClose();
        });
    }
    header.appendChild(closeBtn);
    container.appendChild(header);

    // Sub-bar with Metal badge, entries count, trade badge
    const subBar = document.createElement('div');
    subBar.className = 'd-flex align-items-center justify-content-between mb-2 px-1';

    const subLeft = document.createElement('div');
    subLeft.className = 'd-flex align-items-center gap-1 flex-wrap';

    const bookingMetal = (booking.metal || 'GOLD').toUpperCase();
    const isGold = bookingMetal === 'GOLD';

    const metalBadge = document.createElement('span');
    metalBadge.className = `badge ${isGold ? 'badge-metal-gold' : 'badge-metal-silver'} d-inline-flex align-items-center gap-1`;
    const iconSpan = document.createElement('span');
    iconSpan.innerHTML = metalIconSvg(bookingMetal);
    metalBadge.appendChild(iconSpan);
    const metalText = document.createElement('span');
    metalText.textContent = isGold ? 'Gold' : 'Silver';
    metalBadge.appendChild(metalText);
    subLeft.appendChild(metalBadge);

    const items = Array.isArray(booking.items) ? booking.items : [];
    const countSpan = document.createElement('span');
    countSpan.className = 'text-muted fw-semibold';
    countSpan.style.fontSize = '11px';
    countSpan.textContent = `${items.length} ${items.length === 1 ? 'entry' : 'entries'}`;
    subLeft.appendChild(countSpan);

    subBar.appendChild(subLeft);

    const isBuy = (booking.trade_type || '').toUpperCase() === 'BUY';
    const tradeBadge = document.createElement('span');
    tradeBadge.className = `badge rounded-pill px-2 py-0.5 fw-bold ${isBuy ? 'bg-success text-white' : 'bg-danger text-white'}`;
    tradeBadge.style.fontSize = '10px';
    tradeBadge.textContent = isBuy ? 'BUY' : 'SELL';
    subBar.appendChild(tradeBadge);

    container.appendChild(subBar);

    // 2. Body Grid
    const gridWrapper = document.createElement('div');
    gridWrapper.className = 'bhav-popover-grid-wrapper';

    const table = document.createElement('table');
    table.className = 'bhav-popover-grid table table-sm mb-0 align-middle';

    const thead = document.createElement('thead');
    const trHead = document.createElement('tr');

    const thSn = document.createElement('th');
    thSn.className = 'text-center';
    thSn.style.width = '24px';
    thSn.textContent = '#';
    trHead.appendChild(thSn);

    const thMetal = document.createElement('th');
    thMetal.className = 'text-start';
    thMetal.textContent = 'Metal';
    trHead.appendChild(thMetal);

    const thWeight = document.createElement('th');
    thWeight.className = 'text-end';
    thWeight.textContent = 'Weight';
    trHead.appendChild(thWeight);

    const rateUnit = (window.BHAV_CONFIG && window.BHAV_CONFIG.rate_unit_grams)
        ? (Number(window.BHAV_CONFIG.rate_unit_grams) === 1 ? '/gm' : `/${window.BHAV_CONFIG.rate_unit_grams}gm`)
        : '/10gm';

    const thRate = document.createElement('th');
    thRate.className = 'text-end';
    thRate.textContent = `Rate (${rateUnit})`;
    trHead.appendChild(thRate);

    const thAmount = document.createElement('th');
    thAmount.className = 'text-end';
    thAmount.textContent = 'Amount (₹)';
    trHead.appendChild(thAmount);

    thead.appendChild(trHead);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    let totalWtCalc = 0;
    let totalAmtCalc = 0;

    if (items.length === 0) {
        const emptyTr = document.createElement('tr');
        const emptyTd = document.createElement('td');
        emptyTd.colSpan = 5;
        emptyTd.className = 'text-center text-muted py-2 small';
        emptyTd.textContent = 'No item entries';
        emptyTr.appendChild(emptyTd);
        tbody.appendChild(emptyTr);
    } else {
        items.forEach((it, idx) => {
            const tr = document.createElement('tr');
            const itemMetal = (it.metal || booking.metal || 'GOLD').toUpperCase();
            const isItemGold = itemMetal === 'GOLD';
            tr.className = isItemGold ? 'popover-row-gold' : 'popover-row-silver';

            const wNum = parseFloat(it.weight) || 0;
            const rNum = parseFloat(it.rate) || 0;
            const aNum = parseFloat(it.amount) || 0;
            totalWtCalc += wNum;
            totalAmtCalc += aNum;

            // #
            const tdSn = document.createElement('td');
            tdSn.className = 'text-center text-muted fw-bold small';
            tdSn.textContent = String(it.sort_order || idx + 1);
            tr.appendChild(tdSn);

            // Metal
            const tdMetal = document.createElement('td');
            tdMetal.className = 'text-start text-nowrap';
            const itemMetalBadge = document.createElement('span');
            itemMetalBadge.className = `badge ${isItemGold ? 'badge-metal-gold' : 'badge-metal-silver'} d-inline-flex align-items-center gap-1`;
            const itemIconSpan = document.createElement('span');
            itemIconSpan.innerHTML = metalIconSvg(itemMetal);
            itemMetalBadge.appendChild(itemIconSpan);
            const itemTextSpan = document.createElement('span');
            itemTextSpan.textContent = isItemGold ? 'Gold' : 'Silver';
            itemMetalBadge.appendChild(itemTextSpan);
            tdMetal.appendChild(itemMetalBadge);
            tr.appendChild(tdMetal);

            // Weight
            const tdWeight = document.createElement('td');
            tdWeight.className = 'text-end text-nowrap';
            tdWeight.textContent = `${wNum.toFixed(3)} gm`;
            tr.appendChild(tdWeight);

            // Rate
            const tdRate = document.createElement('td');
            tdRate.className = 'text-end text-nowrap';
            const formattedRate = (rNum % 1 === 0)
                ? formatIndianCurrency(rNum).replace(/\.00$/, '')
                : formatIndianCurrency(rNum);
            tdRate.textContent = `₹${formattedRate} ${rateUnit}`;
            tr.appendChild(tdRate);

            // Amount
            const tdAmount = document.createElement('td');
            tdAmount.className = 'text-end fw-bold text-dark text-nowrap';
            tdAmount.textContent = `₹${formatIndianCurrency(aNum)}`;
            tr.appendChild(tdAmount);

            tbody.appendChild(tr);
        });
    }

    table.appendChild(tbody);
    gridWrapper.appendChild(table);
    container.appendChild(gridWrapper);

    // 3. Footer
    const footer = document.createElement('div');
    footer.className = 'bhav-popover-footer d-flex align-items-center justify-content-between';

    const footerWt = document.createElement('div');
    footerWt.className = 'small fw-bold';
    const wtLabel = document.createElement('span');
    wtLabel.className = 'text-muted me-1';
    wtLabel.textContent = 'Total Wt:';
    footerWt.appendChild(wtLabel);
    const wtVal = document.createElement('span');
    const finalTotalWt = (parseFloat(booking.total_weight) || totalWtCalc).toFixed(3);
    wtVal.className = 'text-dark';
    wtVal.textContent = `${finalTotalWt} gm`;
    footerWt.appendChild(wtVal);
    footer.appendChild(footerWt);

    const footerAmt = document.createElement('div');
    footerAmt.className = 'small fw-bold';
    const amtLabel = document.createElement('span');
    amtLabel.className = 'text-muted me-1';
    amtLabel.textContent = 'Total:';
    footerAmt.appendChild(amtLabel);
    const amtVal = document.createElement('span');
    const finalTotalAmt = parseFloat(booking.total_amount) || totalAmtCalc;
    amtVal.className = isBuy ? 'text-success' : 'text-danger';
    amtVal.textContent = `₹${formatIndianCurrency(finalTotalAmt)}`;
    footerAmt.appendChild(amtVal);
    footer.appendChild(footerAmt);

    container.appendChild(footer);

    return container;
}

/**
 * Close active items breakdown popover if open
 */
function closeActiveItemsPopover() {
    clearTableSpotlight();
    if (activeItemsPopover) {
        if (activeItemsPopover.hoverTimeout) {
            clearTimeout(activeItemsPopover.hoverTimeout);
            activeItemsPopover.hoverTimeout = null;
        }
        if (activeItemsPopover.popover) {
            try {
                activeItemsPopover.popover.hide();
            } catch (err) {
                // Ignore if already disposed or hidden
            }
        }
        activeItemsPopover = null;
    }
}

/**
 * Open items breakdown popover for a button
 */
function openItemsPopover(btn, booking, pinned) {
    if (typeof bootstrap === 'undefined' || !bootstrap.Popover) return;
    if (!btn || !booking) return;

    if (activeItemsPopover && activeItemsPopover.badgeEl !== btn) {
        closeActiveItemsPopover();
    }

    const tr = btn.closest('tr');
    if (tr) {
        setTableSpotlight(tr);
    }

    let popover = bootstrap.Popover.getInstance(btn);
    if (!popover) {
        popover = new bootstrap.Popover(btn, {
            trigger: 'manual',
            html: true,
            container: 'body',
            placement: 'auto',
            sanitize: false,
            content: function () {
                const isPin = activeItemsPopover && activeItemsPopover.badgeEl === btn ? activeItemsPopover.isPinned : !!pinned;
                return buildItemsPopoverContent(booking, isPin, closeActiveItemsPopover);
            },
            popperConfig: {
                strategy: 'fixed'
            },
            customClass: 'bhav-items-popover-wrapper'
        });
    }

    activeItemsPopover = {
        badgeEl: btn,
        popover: popover,
        booking: booking,
        isPinned: !!pinned,
        hoverTimeout: null
    };

    popover.show();

    const tip = popover.tip;
    if (tip) {
        const bodyEl = tip.querySelector('.popover-body');
        if (bodyEl) {
            bodyEl.innerHTML = '';
            bodyEl.appendChild(buildItemsPopoverContent(booking, activeItemsPopover.isPinned, closeActiveItemsPopover));
        }

        tip.addEventListener('mouseenter', function () {
            if (activeItemsPopover && activeItemsPopover.hoverTimeout) {
                clearTimeout(activeItemsPopover.hoverTimeout);
                activeItemsPopover.hoverTimeout = null;
            }
        });

        tip.addEventListener('mouseleave', function () {
            if (activeItemsPopover && !activeItemsPopover.isPinned) {
                activeItemsPopover.hoverTimeout = setTimeout(function () {
                    closeActiveItemsPopover();
                }, 150);
            }
        });
    }
}

/**
 * Dispose all items breakdown popovers in container
 */
function disposeAllItemsPopovers(container) {
    closeActiveItemsPopover();
    if (!container) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const buttons = container.querySelectorAll('.btn-multiple-rates');
        buttons.forEach((btn) => {
            const inst = bootstrap.Popover.getInstance(btn);
            if (inst) {
                try {
                    inst.dispose();
                } catch (err) {
                    // Ignore
                }
            }
        });
    }
}

/**
 * Initialize items breakdown popovers on table body
 */
function initItemsPopovers(tableBody, getBookingSource) {
    if (!tableBody) return;

    if (!itemsPopoversGlobalListenersInitialized) {
        itemsPopoversGlobalListenersInitialized = true;
        document.addEventListener('click', function (e) {
            if (!activeItemsPopover) return;
            const btn = activeItemsPopover.badgeEl;
            const tip = activeItemsPopover.popover && activeItemsPopover.popover.tip;
            if (btn && btn.contains(e.target)) return;
            if (tip && tip.contains(e.target)) return;
            closeActiveItemsPopover();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && activeItemsPopover) {
                closeActiveItemsPopover();
            }
        });
    }

    function getBooking(id) {
        if (typeof getBookingSource === 'function') {
            return getBookingSource(id);
        }
        if (getBookingSource && typeof getBookingSource === 'object') {
            return getBookingSource[id];
        }
        return null;
    }

    const buttons = tableBody.querySelectorAll('.btn-multiple-rates');
    buttons.forEach((btn) => {
        const bookingId = btn.getAttribute('data-booking-id');
        const booking = getBooking(bookingId);
        if (!booking) return;

        btn.addEventListener('mouseenter', function () {
            if (activeItemsPopover && activeItemsPopover.badgeEl === btn) {
                if (activeItemsPopover.hoverTimeout) {
                    clearTimeout(activeItemsPopover.hoverTimeout);
                    activeItemsPopover.hoverTimeout = null;
                }
                return;
            }
            openItemsPopover(btn, booking, false);
        });

        btn.addEventListener('mouseleave', function () {
            if (activeItemsPopover && activeItemsPopover.badgeEl === btn && !activeItemsPopover.isPinned) {
                activeItemsPopover.hoverTimeout = setTimeout(function () {
                    closeActiveItemsPopover();
                }, 150);
            }
        });

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (activeItemsPopover && activeItemsPopover.badgeEl === btn) {
                if (activeItemsPopover.isPinned) {
                    closeActiveItemsPopover();
                } else {
                    activeItemsPopover.isPinned = true;
                    const tip = activeItemsPopover.popover && activeItemsPopover.popover.tip;
                    if (tip) {
                        const bodyEl = tip.querySelector('.popover-body');
                        if (bodyEl) {
                            bodyEl.innerHTML = '';
                            bodyEl.appendChild(buildItemsPopoverContent(booking, true, closeActiveItemsPopover));
                        }
                    }
                }
            } else {
                openItemsPopover(btn, booking, true);
            }
        });

        btn.addEventListener('focus', function () {
            if (!activeItemsPopover || activeItemsPopover.badgeEl !== btn) {
                openItemsPopover(btn, booking, false);
            }
        });

        btn.addEventListener('blur', function () {
            if (activeItemsPopover && activeItemsPopover.badgeEl === btn && !activeItemsPopover.isPinned) {
                activeItemsPopover.hoverTimeout = setTimeout(function () {
                    closeActiveItemsPopover();
                }, 150);
            }
        });

        btn.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeActiveItemsPopover();
            }
        });
    });
}

/**
 * Spotlight helpers: blur and dim other table rows when a popup is open
 */
function setTableSpotlight(tr) {
    if (!tr) return;
    const table = tr.closest('table');
    if (!table) return;

    clearTableSpotlight();

    table.classList.add('has-active-row');
    tr.classList.add('row-active');
}

function clearTableSpotlight() {
    document.querySelectorAll('.bhav-table.has-active-row, .old-bhav-table.has-active-row, .bhav-old-table.has-active-row').forEach((tbl) => {
        tbl.classList.remove('has-active-row');
    });
    document.querySelectorAll('tr.row-active').forEach((r) => {
        r.classList.remove('row-active');
    });
}

window.renderMultipleRatesBadge = renderMultipleRatesBadge;
window.buildItemsPopoverContent = buildItemsPopoverContent;
window.openItemsPopover = openItemsPopover;
window.closeActiveItemsPopover = closeActiveItemsPopover;
window.disposeAllItemsPopovers = disposeAllItemsPopovers;
window.initItemsPopovers = initItemsPopovers;
window.setTableSpotlight = setTableSpotlight;
window.clearTableSpotlight = clearTableSpotlight;

// =========================================================================
// CSV EXPORT HANDLER (Streamed chunked download)
// =========================================================================
function initExportHandler() {
    const exportBtn = document.getElementById('btnExportBookings');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const partyInput = document.getElementById('filterPartyInput');
        const dateFromInput = document.getElementById('dateFilterFrom');
        const dateToInput = document.getElementById('dateFilterTo');
        const tradeSelect = document.getElementById('filterTradeSelect');
        const metalSelect = document.getElementById('filterMetalSelect');
        const typeSelect = document.getElementById('filterTypeSelect');

        const isOldPage = window.location.pathname.includes('/old');
        const view = isOldPage ? 'old' : 'current';

        const params = new URLSearchParams({
            view: view,
            per_page: 100,
        });

        if (partyInput && partyInput.value.trim() !== '') {
            params.set('party', partyInput.value.trim());
        }
        if (dateFromInput && dateFromInput.value) {
            params.set('date_from', dateFromInput.value);
        }
        if (dateToInput && dateToInput.value) {
            params.set('date_to', dateToInput.value);
        }
        if (tradeSelect && tradeSelect.value && tradeSelect.value !== 'ALL') {
            params.set('trade_type', tradeSelect.value);
        }
        if (metalSelect && metalSelect.value && metalSelect.value !== 'ALL') {
            params.set('metal', metalSelect.value);
        }
        if (typeSelect && typeSelect.value && typeSelect.value !== 'ALL') {
            params.set('entry_type', typeSelect.value);
        }

        if (window.APP_ROUTES && window.APP_ROUTES.bhavBookingExport) {
            const exportUrl = `${window.APP_ROUTES.bhavBookingExport}?${params.toString()}`;
            window.location.href = exportUrl;
        }
    });
}

// =========================================================================
// KEYBOARD SHORTCUTS (Billing-software style)
// =========================================================================
function initKeyboardShortcuts() {
    // Open shortcuts cheat sheet modal
    const btnShortcuts = document.getElementById('btnOpenShortcutsModal');
    if (btnShortcuts) {
        btnShortcuts.addEventListener('click', function () {
            const modalEl = document.getElementById('shortcutsModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        const isModalOpen = document.body.classList.contains('modal-open') || !!document.querySelector('.modal.show');
        const activeEl = document.activeElement;
        const isTypingInModal =
            isModalOpen &&
            activeEl &&
            (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');

        // 1. ESC: cancel edit / close dropdowns / close modals (Allowed even while typing inside modal)
        if (e.key === 'Escape' || e.keyCode === 27) {
            clearTableSpotlight();
            closeActiveItemsPopover();
            if (isModalOpen) {
                const openModalEl = document.querySelector('.modal.show');
                if (openModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInst = bootstrap.Modal.getInstance(openModalEl);
                    if (modalInst) {
                        modalInst.hide();
                        e.preventDefault();
                        return;
                    }
                }
            }

            const customerDropdown = document.getElementById('customerSearchResults');
            if (customerDropdown && customerDropdown.style.display !== 'none') {
                customerDropdown.style.display = 'none';
                e.preventDefault();
                return;
            }

            const dateDropdown = document.getElementById('dateRangeDropdown');
            if (dateDropdown && dateDropdown.style.display !== 'none') {
                dateDropdown.style.display = 'none';
                e.preventDefault();
                return;
            }

            const cancelEditBtn =
                document.getElementById('linkCancelEditTop') || document.getElementById('btnCancelEdit');
            const editIndicator = document.getElementById('editModeIndicator');
            if (editIndicator && !editIndicator.classList.contains('d-none') && cancelEditBtn) {
                cancelEditBtn.click();
                e.preventDefault();
                return;
            }
            return;
        }

        // 2. Ctrl + S or F2: Save / Update Booking (Always prevent browser Save Page dialog)
        if ((e.ctrlKey && (e.key === 's' || e.key === 'S')) || e.key === 'F2') {
            e.preventDefault();
            if (isTypingInModal) {
                const saveCustomerBtn = document.getElementById('btnSaveNewCustomer');
                const addCustomerModal = document.getElementById('addCustomerModal');
                if (addCustomerModal && addCustomerModal.classList.contains('show') && saveCustomerBtn) {
                    saveCustomerBtn.click();
                }
                return;
            }
            const saveBtn = document.getElementById('btnSaveBooking') || document.getElementById('btnSave');
            if (saveBtn) {
                saveBtn.click();
            }
            return;
        }

        // Do NOT trigger remaining shortcuts (Alt+B, Alt+S, Alt+C, etc.) while actively typing inside a modal dialog
        if (isTypingInModal) {
            return;
        }

        // 3. Alt + B: Switch to BUY
        if (e.altKey && (e.key === 'b' || e.key === 'B')) {
            e.preventDefault();
            const btnBuy = document.getElementById('btnBuy');
            if (btnBuy) {
                btnBuy.click();
            }
            return;
        }

        // 4. Alt + S: Switch to SELL
        if (e.altKey && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            const btnSell = document.getElementById('btnSell');
            if (btnSell) {
                btnSell.click();
            }
            return;
        }

        // 5. Alt + C: Focus Customer Search
        if (e.altKey && (e.key === 'c' || e.key === 'C')) {
            e.preventDefault();
            const custInput = document.getElementById('customerSearchInput');
            if (custInput) {
                custInput.focus();
                custInput.select();
            }
            return;
        }

        // 6. Alt + N: Open New Party modal
        if (e.altKey && (e.key === 'n' || e.key === 'N')) {
            e.preventDefault();
            const openPartyBtn = document.getElementById('btnOpenAddCustomerModal');
            if (openPartyBtn) {
                openPartyBtn.click();
            }
            return;
        }

        // 7. Alt + P: Print (or Save & Print on booking form)
        if (e.altKey && (e.key === 'p' || e.key === 'P')) {
            e.preventDefault();
            const viewModalPrintBtn = document.getElementById('btnViewModalPrint');
            if (viewModalPrintBtn && viewModalPrintBtn.offsetParent !== null) {
                viewModalPrintBtn.click();
                return;
            }
            const formPrintBtn = document.getElementById('btnPrint');
            if (formPrintBtn) {
                if (!formPrintBtn.disabled) {
                    formPrintBtn.click();
                }
                return;
            }
            return;
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initExportHandler();
    initKeyboardShortcuts();
    document.querySelectorAll('.modal').forEach((modalEl) => {
        setupModalAccessibility(modalEl);
    });

    document.addEventListener('show.bs.dropdown', function (e) {
        const target = e.target;
        if (target && target.closest && (target.closest('.action-dropdown-wrapper') || (target.classList && target.classList.contains('btn-action-kebab')))) {
            const tr = target.closest('tr');
            if (tr) {
                setTableSpotlight(tr);
            }
        }
    });

    document.addEventListener('hidden.bs.dropdown', function (e) {
        const target = e.target;
        if (target && target.closest && (target.closest('.action-dropdown-wrapper') || (target.classList && target.classList.contains('btn-action-kebab')))) {
            if (!activeItemsPopover) {
                clearTableSpotlight();
            }
        }
    });
});
