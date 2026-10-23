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
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Global helper: Format Date object to DD-MM-YYYY
function formatDateDisplay(d) {
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

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

// Global helper: Display floating Bootstrap toast notifications
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        alert(message);
        return;
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
                <span>${escapeHtml(message)}</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    } else {
        setTimeout(() => toastEl.remove(), 3500);
    }
}

// Global helper: Check whether a booking can be edited or deleted (PENDING, 0 delivered, 0 payment)
function isBookingModifiable(booking) {
    if (!booking) return false;
    const statusUpper = (booking.status || '').toUpperCase();
    return statusUpper === 'PENDING'
        && (parseFloat(booking.delivered_weight) || 0) === 0
        && (parseFloat(booking.given_amount) || 0) === 0
        && (parseFloat(booking.received_amount) || 0) === 0;
}

// Global helper: Generate a concise text summary of a booking
function generateBookingSummary(booking) {
    if (!booking) return '';
    const bookingNo = booking.booking_no || '-';
    const dateStr = formatDateTimeDisplay(booking.booked_at);
    const customer = booking.customer || {};
    const customerName = customer.name || 'Customer';
    const trade = (booking.trade_type || '').toUpperCase();
    const metal = (booking.metal || '').toUpperCase();
    const wt = (parseFloat(booking.total_weight) || 0).toFixed(3);
    const amt = formatIndianCurrency(booking.total_amount || 0);

    return `Booking: ${bookingNo}\nDate: ${dateStr}\nCustomer: ${customerName}\nTrade: ${trade}\nMetal: ${metal}\nWeight: ${wt} gm\nAmount: ₹${amt}`;
}

// Global helper: Share booking details via Web Share API or Clipboard fallback
function shareBooking(booking) {
    const summary = generateBookingSummary(booking);
    if (navigator.share) {
        navigator.share({
            title: `Bhav Booking ${booking.booking_no || ''}`,
            text: summary
        }).catch(err => {
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
        navigator.clipboard.writeText(text).then(() => {
            showToast('Booking details copied', 'success');
        }).catch(() => {
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
function whatsappBooking(booking) {
    const customer = booking.customer || {};
    const rawMobile = String(customer.mobile || '').replace(/\D/g, '');
    if (!rawMobile || rawMobile.length < 10) {
        showToast('Customer mobile number not available', 'warning');
        return;
    }
    const clean10 = rawMobile.slice(-10);
    const summary = generateBookingSummary(booking);
    const url = `https://wa.me/91${clean10}?text=${encodeURIComponent(summary)}`;
    window.open(url, '_blank');
}

// Global helper: Confirm and execute normal or silent soft delete
function deleteBookingWithConfirm(bookingId, bookingNo, amount, isSilent, onDeletedCallback) {
    const formattedAmt = formatIndianCurrency(amount || 0);
    const confirmMessage = isSilent
        ? `Silently delete booking ${bookingNo} (₹${formattedAmt})? No message will be sent.`
        : `Delete booking ${bookingNo} (₹${formattedAmt})? Customer will be informed.`;

    if (!window.confirm(confirmMessage)) return;

    // Note: Future customer notification webhook / SMS would be triggered for normal Delete,
    // but bypassed for Silent Delete.
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
        const msg = isSilent ? `Booking ${bookingNo} silently deleted` : `Booking ${bookingNo} deleted`;
        showToast(msg, 'success');
        if (typeof onDeletedCallback === 'function') {
            onDeletedCallback(bookingId, bookingNo);
        }
    })
    .catch(err => {
        console.warn('Booking delete error:', err);
    });
}

// Global helper: Fetch booking details and display the shared View Booking modal
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
            viewMetalBadge.textContent = booking.metal;
            viewMetalBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isGold ? 'badge-metal-gold' : 'badge-metal-silver'}`;
        }

        if (viewTypeBadge) {
            const isEntry = (booking.entry_type || '').toUpperCase() === 'ONLY_ENTRY';
            viewTypeBadge.textContent = isEntry ? 'Only Entry' : 'Bhav Cut + Ledger';
            viewTypeBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${isEntry ? 'badge-type-entry' : 'badge-type-ledger'}`;
        }

        if (viewStatusBadge) {
            const st = (booking.status || 'PENDING').toUpperCase();
            viewStatusBadge.textContent = st;
            let stClass = 'badge-status-pending';
            if (st === 'PARTIAL') stClass = 'badge-status-partial';
            else if (st === 'DELIVERED') stClass = 'badge-status-delivered';
            else if (st === 'CANCELLED') stClass = 'badge-status-cancelled';
            viewStatusBadge.className = `badge rounded-pill px-3 py-1 fw-bold ${stClass}`;
        }

        const customer = booking.customer || {};
        if (viewCustomerName) viewCustomerName.textContent = customer.name || '-';
        if (viewCustomerNo) viewCustomerNo.textContent = customer.customer_no || '-';
        if (viewCustomerMobile) viewCustomerMobile.textContent = customer.mobile || 'N/A';
        if (viewCustomerCity) viewCustomerCity.textContent = customer.city || 'N/A';

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

        if (viewTotalWeight) viewTotalWeight.textContent = `${(parseFloat(booking.total_weight) || 0).toFixed(3)} gm`;
        if (viewTotalAmount) viewTotalAmount.textContent = `₹${formatIndianCurrency(booking.total_amount || 0)}`;
        if (viewDeliveredWeight) viewDeliveredWeight.textContent = `${(parseFloat(booking.delivered_weight) || 0).toFixed(3)} gm`;
        if (viewPendingWeight) viewPendingWeight.textContent = `${(parseFloat(booking.pending_weight) || 0).toFixed(3)} gm`;

        if (viewNarration) viewNarration.textContent = booking.narration || 'None';

        if (btnViewModalPrint) {
            btnViewModalPrint.onclick = function () {
                window.open(getRoute('bhavBookingPrint', booking.id), '_blank');
            };
        }

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

// Global helper: Generate HTML for the 3-dot kebab action dropdown menu
function renderActionKebabMenu(booking) {
    const isModifiable = isBookingModifiable(booking);
    const disabledAttr = isModifiable ? '' : 'disabled';
    const disabledClass = isModifiable ? '' : 'disabled text-muted';
    const disabledTooltip = isModifiable ? '' : 'This booking has delivery/payment and cannot be edited';

    const bookingId = booking.id;
    const bookingNo = escapeHtml(booking.booking_no || '');
    const amount = escapeHtml(booking.total_amount || 0);

    return `
        <div class="dropdown action-dropdown-wrapper d-inline-block">
            <button type="button"
                    class="btn btn-action-kebab"
                    data-bs-toggle="dropdown"
                    data-bs-popper-config='{"strategy":"fixed"}'
                    aria-expanded="false"
                    title="Actions">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm action-kebab-menu py-1" style="min-width: 175px;">
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-view" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-eye text-success me-2" style="width: 16px;"></i> View
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-edit ${disabledClass}" href="#" data-id="${bookingId}" ${disabledAttr} title="${escapeHtml(disabledTooltip)}">
                        <i class="fa-solid fa-pen-to-square text-warning me-2" style="width: 16px;"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-1 px-3 d-flex align-items-center action-menu-print" href="#" data-id="${bookingId}">
                        <i class="fa-solid fa-print text-warning me-2" style="width: 16px;"></i> Print
                    </a>
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

    // 2. Print Clicked
    const printItem = e.target.closest('.action-menu-print');
    if (printItem) {
        e.preventDefault();
        const id = parseInt(printItem.getAttribute('data-id'), 10);
        if (id) {
            window.open(getRoute('bhavBookingPrint', id), '_blank');
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
            shareBooking(booking);
        } else if (id) {
            fetch(getRoute('bhavBookingShow', id)).then(r => r.json()).then(b => shareBooking(b));
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
            whatsappBooking(booking);
        } else if (id) {
            fetch(getRoute('bhavBookingShow', id)).then(r => r.json()).then(b => whatsappBooking(b));
        }
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
        deleteBookingWithConfirm(id, bNo, amt, false, callbacks.onDeleted);
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
        deleteBookingWithConfirm(id, bNo, amt, true, callbacks.onDeleted);
        return;
    }
}
