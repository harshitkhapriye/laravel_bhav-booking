/**
 * Party Master JavaScript Module
 * Application: Just Udhari
 *
 * Implements:
 * 1. Debounced party search and type/page size filtering
 * 2. Asynchronous list rendering and pagination
 * 3. 3-Dot kebab action menu (View, Edit, Delete)
 * 4. Comprehensive party view modal with family members & recent bookings
 * 5. Reusable New/Edit party detail modal handling (with Aadhaar/PAN masking,
 *    sub-profile parent linking, image replacement/removal, and form validation)
 */

document.addEventListener('DOMContentLoaded', function () {
    // =========================================================================
    // STATE & CONFIGURATION
    // =========================================================================
    let currentPage = 1;
    let perPage = 10;
    let currentSearch = '';
    let currentPartyType = 'ALL';
    let searchDebounceTimer = null;
    let currentViewingPartyId = null;
    let isAdditionalExpanded = false;
    let parentSearchDebounceTimer = null;
    let parentSearchAbortController = null;
    let selectedParentCustomer = null;

    const routes = window.APP_ROUTES || {
        partiesList: '/parties/list',
        partiesShow: '/parties/__ID__',
        partiesUpdate: '/parties/__ID__',
        partiesDestroy: '/parties/__ID__',
        customerStore: '/customers',
        customerSearch: '/customers/search',
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // =========================================================================
    // DOM ELEMENTS - LIST & FILTERS
    // =========================================================================
    const filterPartySearch = document.getElementById('filterPartySearch');
    const filterPartyType = document.getElementById('filterPartyType');
    const filterPerPage = document.getElementById('filterPerPage');
    const partiesTableBody = document.getElementById('partiesTableBody');
    const partiesPaginationInfo = document.getElementById('partiesPaginationInfo');
    const partiesPaginationLinks = document.getElementById('partiesPaginationLinks');
    const btnOpenNewPartyModal = document.getElementById('btnOpenNewPartyModal');

    // =========================================================================
    // DOM ELEMENTS - MODAL: ADD / EDIT PARTY
    // =========================================================================
    const addCustomerModalEl = document.getElementById('addCustomerModal');
    const addCustomerModalLabel = document.getElementById('addCustomerModalLabel');
    const addCustomerForm = document.getElementById('addCustomerForm');
    const editPartyIdInput = document.getElementById('editPartyId');
    const btnSaveNewCustomer = document.getElementById('btnSaveNewCustomer');

    const partySegmentedToggle = document.getElementById('partySegmentedToggle');
    const btnToggleCustomer = document.getElementById('btnToggleCustomer');
    const btnToggleSupplier = document.getElementById('btnToggleSupplier');
    const newPartyTypeInput = document.getElementById('newPartyTypeInput');

    const newCustomerName = document.getElementById('newCustomerName');
    const newCustomerMobile = document.getElementById('newCustomerMobile');
    const newCustomerEmail = document.getElementById('newCustomerEmail');
    const newCustomerAddress = document.getElementById('newCustomerAddress');

    const checkFamilySubProfile = document.getElementById('checkFamilySubProfile');
    const familySubProfileDrawer = document.getElementById('familySubProfileDrawer');
    const parentCustomerSearchInput = document.getElementById('parentCustomerSearchInput');
    const parentCustomerClearBtn = document.getElementById('parentCustomerClearBtn');
    const hiddenParentCustomerId = document.getElementById('hiddenParentCustomerId');
    const parentCustomerSearchResults = document.getElementById('parentCustomerSearchResults');
    const parentCustomerSelectedBadge = document.getElementById('parentCustomerSelectedBadge');

    const additionalInfoSection = document.getElementById('additionalInfoSection');
    const btnToggleAdditionalInfo = document.getElementById('btnToggleAdditionalInfo');
    const iconToggleAdditional = document.getElementById('iconToggleAdditional');

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

    // Image Upload Elements
    const partyProfilePlaceholder = document.getElementById('partyProfilePlaceholder');
    const partyProfilePreview = document.getElementById('partyProfilePreview');
    const btnRemoveProfileImage = document.getElementById('btnRemoveProfileImage');
    const btnProfileCamera = document.getElementById('btnProfileCamera');
    const btnProfileFolder = document.getElementById('btnProfileFolder');
    const partyProfileCameraInput = document.getElementById('partyProfileCameraInput');
    const partyProfileFolderInput = document.getElementById('partyProfileFolderInput');
    const removeProfileImageInput = document.getElementById('removeProfileImageInput');

    const shopLogoPlaceholder = document.getElementById('shopLogoPlaceholder');
    const shopLogoPreview = document.getElementById('shopLogoPreview');
    const btnRemoveShopLogo = document.getElementById('btnRemoveShopLogo');
    const btnShopLogoCamera = document.getElementById('btnShopLogoCamera');
    const btnShopLogoFolder = document.getElementById('btnShopLogoFolder');
    const partyShopLogoCameraInput = document.getElementById('partyShopLogoCameraInput');
    const partyShopLogoFolderInput = document.getElementById('partyShopLogoFolderInput');
    const removeShopLogoInput = document.getElementById('removeShopLogoInput');

    // =========================================================================
    // DOM ELEMENTS - MODAL: VIEW PARTY
    // =========================================================================
    const partyViewModalEl = document.getElementById('partyViewModal');
    const partyViewModalLabel = document.getElementById('partyViewModalLabel');
    const viewPartyTypeBadge = document.getElementById('viewPartyTypeBadge');
    const viewPartyProfilePlaceholder = document.getElementById('viewPartyProfilePlaceholder');
    const viewPartyProfileImg = document.getElementById('viewPartyProfileImg');
    const viewPartyName = document.getElementById('viewPartyName');
    const viewPartyNumber = document.getElementById('viewPartyNumber');
    const viewPartyParentBadge = document.getElementById('viewPartyParentBadge');
    const viewPartyMobile = document.getElementById('viewPartyMobile');
    const viewPartyEmail = document.getElementById('viewPartyEmail');
    const viewPartyBirthday = document.getElementById('viewPartyBirthday');
    const viewPartyAnniversary = document.getElementById('viewPartyAnniversary');
    const viewPartyAddress = document.getElementById('viewPartyAddress');

    const viewPartyShopName = document.getElementById('viewPartyShopName');
    const viewPartyArea = document.getElementById('viewPartyArea');
    const viewPartyLocation = document.getElementById('viewPartyLocation');
    const viewPartyGst = document.getElementById('viewPartyGst');
    const viewPartyLicNo = document.getElementById('viewPartyLicNo');
    const viewPartyRegNo = document.getElementById('viewPartyRegNo');
    const viewPartyLogoTitle = document.getElementById('viewPartyLogoTitle');
    const viewPartyShopAddress = document.getElementById('viewPartyShopAddress');
    const viewPartyShopLogoContainer = document.getElementById('viewPartyShopLogoContainer');
    const viewPartyShopLogoImg = document.getElementById('viewPartyShopLogoImg');

    const viewPartyAadhaar = document.getElementById('viewPartyAadhaar');
    const viewPartyPan = document.getElementById('viewPartyPan');
    const viewFamilyMembersCount = document.getElementById('viewFamilyMembersCount');
    const viewFamilyMembersContainer = document.getElementById('viewFamilyMembersContainer');
    const viewLatestBookingsTableBody = document.getElementById('viewLatestBookingsTableBody');
    const btnEditFromViewModal = document.getElementById('btnEditFromViewModal');

    // Bootstrap Modal instances
    const addCustomerModal = addCustomerModalEl ? bootstrap.Modal.getOrCreateInstance(addCustomerModalEl) : null;
    const partyViewModal = partyViewModalEl ? bootstrap.Modal.getOrCreateInstance(partyViewModalEl) : null;

    // =========================================================================
    // 1. DATA FETCHING & RENDERING (PARTIES LIST)
    // =========================================================================

    /**
     * Fetch parties list from API with current filters and pagination.
     */
    function fetchParties(page = 1) {
        currentPage = page;
        showLoadingState();

        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage,
            party_type: currentPartyType,
            q: currentSearch,
        });

        fetch(`${routes.partiesList}?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error('Failed to fetch parties');
                return res.json();
            })
            .then((data) => {
                renderPartiesTable(data.rows || data.data || [], data.pagination || {});
                renderPagination(data.pagination || {});
            })
            .catch((err) => {
                console.error('Error fetching parties:', err);
                renderErrorState('Failed to load parties. Please try again.');
            });
    }

    function showLoadingState() {
        if (!partiesTableBody) return;
        partiesTableBody.innerHTML = `
            <tr class="table-loading-row">
                <td colspan="8" class="py-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="fw-semibold">Loading parties...</span>
                </td>
            </tr>
        `;
    }

    function renderErrorState(message) {
        if (!partiesTableBody) return;
        partiesTableBody.innerHTML = `
            <tr>
                <td colspan="8" class="py-4 text-center text-danger">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> ${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    /**
     * Render party rows in table.
     */
    function renderPartiesTable(rows, pagination) {
        if (!partiesTableBody) return;

        if (!rows || rows.length === 0) {
            partiesTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="py-5 text-center text-muted">
                        <i class="fa-solid fa-users-slash fs-3 d-block mb-2 text-secondary opacity-50"></i>
                        <span class="fw-semibold">No parties found matching your criteria.</span>
                    </td>
                </tr>
            `;
            return;
        }

        const startIndex = pagination.from ? pagination.from - 1 : (currentPage - 1) * perPage;
        let html = '';

        rows.forEach((party, index) => {
            const sn = startIndex + index + 1;

            // Photo avatar or placeholder
            let photoHtml = '';
            if (party.profile_image_url) {
                photoHtml = `<img src="${escapeHtml(party.profile_image_url)}" alt="${escapeHtml(party.name)}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">`;
            } else {
                photoHtml = `<div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center text-secondary" style="width: 32px; height: 32px;"><i class="fa-solid fa-user small"></i></div>`;
            }

            // Party Name, No, Supplier badge, and Family parent info
            const supplierBadge =
                party.party_type === 'SUPPLIER'
                    ? '<span class="badge bg-info-subtle text-info border ms-1" style="font-size: 10px;">SUPPLIER</span>'
                    : '';

            const parentInfo = party.parent
                ? `<div class="small text-muted" style="font-size: 11px;"><i class="fa-solid fa-user-group me-1" style="font-size: 10px;"></i>Family of ${escapeHtml(party.parent.name)}</div>`
                : '';

            // District & State
            let locationText = '-';
            if (party.district && party.state) {
                locationText = `${escapeHtml(party.district)}, ${escapeHtml(party.state)}`;
            } else if (party.district) {
                locationText = escapeHtml(party.district);
            } else if (party.state) {
                locationText = escapeHtml(party.state);
            }

            // Action 3-dot kebab menu
            const actionKebab = `
                <div class="dropdown action-dropdown-wrapper d-inline-block">
                    <button type="button"
                            class="btn btn-action-kebab"
                            data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}'
                            aria-expanded="false"
                            title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm action-kebab-menu py-1" style="min-width: 150px;">
                        <li>
                            <a class="dropdown-item py-1 px-3 d-flex align-items-center action-party-view" href="#" data-id="${party.id}">
                                <i class="fa-solid fa-eye text-primary me-2" style="width: 16px;"></i> View
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-1 px-3 d-flex align-items-center action-party-edit" href="#" data-id="${party.id}">
                                <i class="fa-solid fa-pen-to-square text-warning me-2" style="width: 16px;"></i> Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-1 px-3 d-flex align-items-center action-party-delete text-danger" href="#" data-id="${party.id}" data-name="${escapeHtml(party.name)}">
                                <i class="fa-solid fa-trash-can text-danger me-2" style="width: 16px;"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
            `;

            html += `
                <tr data-id="${party.id}">
                    <td class="text-center text-secondary small">${sn}</td>
                    <td class="text-center">${photoHtml}</td>
                    <td>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace" style="font-size: 11px;">${escapeHtml(party.customer_no)}</span>
                            <span class="fw-bold text-dark">${escapeHtml(party.name)}</span>
                            ${supplierBadge}
                        </div>
                        ${parentInfo}
                    </td>
                    <td>
                        <span class="font-monospace text-dark">${escapeHtml(party.mobile)}</span>
                    </td>
                    <td>
                        ${party.shop_name ? escapeHtml(party.shop_name) : '<span class="text-muted">-</span>'}
                    </td>
                    <td>
                        <span class="text-secondary small">${locationText}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1" title="${party.bookings_count} bookings">
                            ${party.bookings_count}
                        </span>
                    </td>
                    <td class="text-center">
                        ${actionKebab}
                    </td>
                </tr>
            `;
        });

        partiesTableBody.innerHTML = html;
        wireActionMenuEvents();
    }

    /**
     * Render pagination controls.
     */
    function renderPagination(pagination) {
        if (!partiesPaginationInfo || !partiesPaginationLinks) return;

        const total = pagination.total || 0;
        const from = pagination.from || 0;
        const to = pagination.to || 0;
        const lastPage = pagination.last_page || 1;

        partiesPaginationInfo.textContent = `Showing ${from} to ${to} of ${total} entries`;

        if (total === 0 || lastPage <= 1) {
            partiesPaginationLinks.innerHTML = '';
            return;
        }

        let linksHtml = '';

        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        linksHtml += `
            <li class="page-item ${prevDisabled}">
                <button class="page-link" data-page="${currentPage - 1}" aria-label="Previous">
                    &laquo;
                </button>
            </li>
        `;

        // Page numbers (max 5 numbers around current)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            linksHtml += `<li class="page-item"><button class="page-link" data-page="1">1</button></li>`;
            if (startPage > 2) {
                linksHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            const activeClass = p === currentPage ? 'active' : '';
            linksHtml += `
                <li class="page-item ${activeClass}">
                    <button class="page-link" data-page="${p}">${p}</button>
                </li>
            `;
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                linksHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            linksHtml += `<li class="page-item"><button class="page-link" data-page="${lastPage}">${lastPage}</button></li>`;
        }

        // Next button
        const nextDisabled = currentPage === lastPage ? 'disabled' : '';
        linksHtml += `
            <li class="page-item ${nextDisabled}">
                <button class="page-link" data-page="${currentPage + 1}" aria-label="Next">
                    &raquo;
                </button>
            </li>
        `;

        partiesPaginationLinks.innerHTML = linksHtml;

        // Wire page click events
        partiesPaginationLinks.querySelectorAll('.page-link[data-page]').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const targetPage = parseInt(this.getAttribute('data-page'), 10);
                if (targetPage && targetPage !== currentPage && targetPage >= 1 && targetPage <= lastPage) {
                    fetchParties(targetPage);
                }
            });
        });
    }

    // =========================================================================
    // 2. FILTER BAR EVENTS
    // =========================================================================

    // Search input (400ms debounce)
    if (filterPartySearch) {
        filterPartySearch.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            const query = this.value.trim();
            searchDebounceTimer = setTimeout(() => {
                currentSearch = query;
                fetchParties(1);
            }, 400);
        });
    }

    // Party Type filter
    if (filterPartyType) {
        filterPartyType.addEventListener('change', function () {
            currentPartyType = this.value;
            fetchParties(1);
        });
    }

    // Per Page filter
    if (filterPerPage) {
        filterPerPage.addEventListener('change', function () {
            perPage = parseInt(this.value, 10) || 10;
            fetchParties(1);
        });
    }

    // =========================================================================
    // 3. ACTION MENU EVENTS (VIEW, EDIT, DELETE)
    // =========================================================================

    function wireActionMenuEvents() {
        // View Party
        document.querySelectorAll('.action-party-view').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const partyId = this.getAttribute('data-id');
                openViewModal(partyId);
            });
        });

        // Edit Party
        document.querySelectorAll('.action-party-edit').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const partyId = this.getAttribute('data-id');
                openEditModal(partyId);
            });
        });

        // Delete Party
        document.querySelectorAll('.action-party-delete').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const partyId = this.getAttribute('data-id');
                const partyName = this.getAttribute('data-name') || 'this party';
                confirmDeleteParty(partyId, partyName);
            });
        });
    }

    // =========================================================================
    // 4. VIEW PARTY MODAL
    // =========================================================================

    function openViewModal(partyId) {
        currentViewingPartyId = partyId;
        resetViewModalData();

        if (partyViewModal) {
            partyViewModal.show();
        }

        const showUrl = routes.partiesShow.replace('__ID__', partyId);

        fetch(showUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error('Party not found');
                return res.json();
            })
            .then((party) => {
                populateViewModal(party);
            })
            .catch((err) => {
                console.error('Error fetching party details:', err);
                showToast('Failed to load party details', 'error');
                if (partyViewModal) partyViewModal.hide();
            });
    }

    function resetViewModalData() {
        if (viewPartyName) viewPartyName.textContent = 'Loading...';
        if (viewPartyNumber) viewPartyNumber.textContent = '';
        if (viewPartyTypeBadge) {
            viewPartyTypeBadge.className = 'badge rounded-pill ms-2 px-2 py-1 bg-secondary';
            viewPartyTypeBadge.textContent = '...';
        }
        if (viewPartyParentBadge) viewPartyParentBadge.style.display = 'none';
        if (viewPartyProfilePlaceholder) viewPartyProfilePlaceholder.style.display = 'block';
        if (viewPartyProfileImg) {
            viewPartyProfileImg.src = '';
            viewPartyProfileImg.style.display = 'none';
        }
        if (viewPartyMobile) viewPartyMobile.textContent = '-';
        if (viewPartyEmail) viewPartyEmail.textContent = '-';
        if (viewPartyBirthday) viewPartyBirthday.textContent = '-';
        if (viewPartyAnniversary) viewPartyAnniversary.textContent = '-';
        if (viewPartyAddress) viewPartyAddress.textContent = '-';

        if (viewPartyShopName) viewPartyShopName.textContent = '-';
        if (viewPartyArea) viewPartyArea.textContent = '-';
        if (viewPartyLocation) viewPartyLocation.textContent = '-';
        if (viewPartyGst) viewPartyGst.textContent = '-';
        if (viewPartyLicNo) viewPartyLicNo.textContent = '-';
        if (viewPartyRegNo) viewPartyRegNo.textContent = '-';
        if (viewPartyLogoTitle) viewPartyLogoTitle.textContent = '-';
        if (viewPartyShopAddress) viewPartyShopAddress.textContent = '-';
        if (viewPartyShopLogoContainer) viewPartyShopLogoContainer.style.display = 'none';

        if (viewPartyAadhaar) viewPartyAadhaar.textContent = '-';
        if (viewPartyPan) viewPartyPan.textContent = '-';
        if (viewFamilyMembersCount) viewFamilyMembersCount.textContent = '0';
        if (viewFamilyMembersContainer)
            viewFamilyMembersContainer.innerHTML = '<p class="text-muted small m-0 fst-italic">Loading...</p>';
        if (viewLatestBookingsTableBody) {
            viewLatestBookingsTableBody.innerHTML =
                '<tr><td colspan="7" class="text-center text-muted py-3">Loading bookings...</td></tr>';
        }
    }

    function populateViewModal(party) {
        if (viewPartyName) viewPartyName.textContent = party.name || '-';
        if (viewPartyNumber) viewPartyNumber.textContent = party.customer_no || '';

        // Type badge
        if (viewPartyTypeBadge) {
            if (party.party_type === 'SUPPLIER') {
                viewPartyTypeBadge.className = 'badge rounded-pill ms-2 px-2 py-1 bg-info text-dark';
                viewPartyTypeBadge.textContent = 'SUPPLIER';
            } else {
                viewPartyTypeBadge.className = 'badge rounded-pill ms-2 px-2 py-1 bg-success';
                viewPartyTypeBadge.textContent = 'CUSTOMER';
            }
        }

        // Parent badge if family sub-profile
        if (viewPartyParentBadge) {
            if (party.parent) {
                viewPartyParentBadge.textContent = `Sub-profile of: ${party.parent.name} (${party.parent.customer_no})`;
                viewPartyParentBadge.style.display = 'inline-block';
            } else {
                viewPartyParentBadge.style.display = 'none';
            }
        }

        // Profile image
        if (party.profile_image_url) {
            if (viewPartyProfileImg) {
                viewPartyProfileImg.src = party.profile_image_url;
                viewPartyProfileImg.style.display = 'block';
            }
            if (viewPartyProfilePlaceholder) viewPartyProfilePlaceholder.style.display = 'none';
        } else {
            if (viewPartyProfileImg) viewPartyProfileImg.style.display = 'none';
            if (viewPartyProfilePlaceholder) viewPartyProfilePlaceholder.style.display = 'block';
        }

        // Contact & Personal
        if (viewPartyMobile) viewPartyMobile.textContent = party.mobile || '-';
        if (viewPartyEmail) viewPartyEmail.textContent = party.email || '-';
        if (viewPartyBirthday) viewPartyBirthday.textContent = party.birthday ? formatDateDisplay(party.birthday) : '-';
        if (viewPartyAnniversary)
            viewPartyAnniversary.textContent = party.anniversary ? formatDateDisplay(party.anniversary) : '-';
        if (viewPartyAddress) viewPartyAddress.textContent = party.address || '-';

        // Shop / Business
        if (viewPartyShopName) viewPartyShopName.textContent = party.shop_name || '-';
        if (viewPartyArea) viewPartyArea.textContent = party.area || '-';

        let loc = '-';
        if (party.district && party.state) {
            loc = `${party.district}, ${party.state}`;
        } else if (party.district) {
            loc = party.district;
        } else if (party.state) {
            loc = party.state;
        }
        if (viewPartyLocation) viewPartyLocation.textContent = loc;

        if (viewPartyGst) viewPartyGst.textContent = party.gst_no || '-';
        if (viewPartyLicNo) viewPartyLicNo.textContent = party.lic_no || '-';
        if (viewPartyRegNo) viewPartyRegNo.textContent = party.reg_no || '-';
        if (viewPartyLogoTitle) viewPartyLogoTitle.textContent = party.logo_title || '-';
        if (viewPartyShopAddress) viewPartyShopAddress.textContent = party.shop_address || '-';

        // Shop Logo
        if (viewPartyShopLogoContainer && viewPartyShopLogoImg) {
            if (party.shop_logo_url) {
                viewPartyShopLogoImg.src = party.shop_logo_url;
                viewPartyShopLogoContainer.style.display = 'block';
            } else {
                viewPartyShopLogoContainer.style.display = 'none';
            }
        }

        // Statutory / KYC (Masked)
        if (viewPartyAadhaar) {
            viewPartyAadhaar.textContent = party.aadhaar_no_masked || 'Not Provided';
        }
        if (viewPartyPan) {
            viewPartyPan.textContent = party.pan_no_masked || 'Not Provided';
        }

        // Family Members List
        if (viewFamilyMembersContainer && viewFamilyMembersCount) {
            const family = party.family_members || [];
            viewFamilyMembersCount.textContent = family.length;

            if (family.length === 0) {
                viewFamilyMembersContainer.innerHTML =
                    '<p class="text-muted small m-0 fst-italic">No family sub-profiles registered for this party.</p>';
            } else {
                let famHtml = '<div class="row g-2">';
                family.forEach((member) => {
                    famHtml += `
                        <div class="col-12 col-md-6">
                            <div class="p-2 border rounded bg-white d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace me-1" style="font-size: 10px;">${escapeHtml(member.customer_no)}</span>
                                    <strong class="text-dark small">${escapeHtml(member.name)}</strong>
                                    <div class="text-muted small">${escapeHtml(member.mobile)}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-view-family-member" data-id="${member.id}" title="View sub-profile">
                                    <i class="fa-solid fa-eye small"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                famHtml += '</div>';
                viewFamilyMembersContainer.innerHTML = famHtml;

                // Click handler for viewing family sub-profile directly
                viewFamilyMembersContainer.querySelectorAll('.btn-view-family-member').forEach((btn) => {
                    btn.addEventListener('click', function () {
                        const mid = this.getAttribute('data-id');
                        openViewModal(mid);
                    });
                });
            }
        }

        // Latest Bhav Bookings Table
        if (viewLatestBookingsTableBody) {
            const bookings = party.latest_bookings || [];
            if (bookings.length === 0) {
                viewLatestBookingsTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3 fst-italic">
                            No bookings recorded yet.
                        </td>
                    </tr>
                `;
            } else {
                let bookHtml = '';
                bookings.forEach((b) => {
                    const tradeBadge =
                        b.trade_type === 'BUY'
                            ? '<span class="badge bg-success-subtle text-success border">BUY</span>'
                            : '<span class="badge bg-danger-subtle text-danger border">SELL</span>';

                    const statusBadge =
                        b.status === 'PENDING'
                            ? '<span class="badge bg-warning-subtle text-warning border">PENDING</span>'
                            : b.status === 'DELIVERED'
                              ? '<span class="badge bg-success-subtle text-success border">DELIVERED</span>'
                              : '<span class="badge bg-secondary-subtle text-secondary border">CANCELLED</span>';

                    bookHtml += `
                        <tr>
                            <td class="font-monospace fw-semibold">${escapeHtml(b.booking_no)}</td>
                            <td>${escapeHtml(b.booked_at)}</td>
                            <td>${tradeBadge}</td>
                            <td><strong>${escapeHtml(b.metal)}</strong></td>
                            <td class="text-end font-monospace">${escapeHtml(b.total_weight)}</td>
                            <td class="text-end font-monospace">₹${escapeHtml(b.total_amount)}</td>
                            <td class="text-center">${statusBadge}</td>
                        </tr>
                    `;
                });
                viewLatestBookingsTableBody.innerHTML = bookHtml;
            }
        }
    }

    // Switch from View Modal to Edit Modal
    if (btnEditFromViewModal) {
        btnEditFromViewModal.addEventListener('click', function () {
            if (currentViewingPartyId) {
                if (partyViewModal) partyViewModal.hide();
                openEditModal(currentViewingPartyId);
            }
        });
    }

    function formatDateDisplay(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        return dateStr;
    }

    // =========================================================================
    // 5. ADD / EDIT PARTY DETAIL MODAL LOGIC
    // =========================================================================

    /**
     * Open Modal in CREATE mode
     */
    if (btnOpenNewPartyModal) {
        btnOpenNewPartyModal.addEventListener('click', function () {
            openCreateModal();
        });
    }

    function openCreateModal() {
        resetPartyModal();
        if (editPartyIdInput) editPartyIdInput.value = '';
        if (addCustomerModalLabel) {
            addCustomerModalLabel.textContent = 'New Party Detail';
        }
        const titleIcon = document.querySelector('.party-modal-header .party-title-icon i');
        if (titleIcon) {
            titleIcon.className = 'fa-solid fa-user-plus text-primary-orange';
        }

        initStatesDropdown();
        if (addCustomerModal) addCustomerModal.show();
    }

    /**
     * Open Modal in EDIT mode
     */
    function openEditModal(partyId) {
        resetPartyModal();
        initStatesDropdown();

        if (editPartyIdInput) editPartyIdInput.value = partyId;
        if (addCustomerModalLabel) {
            addCustomerModalLabel.textContent = 'Edit Party Detail';
        }
        const titleIcon = document.querySelector('.party-modal-header .party-title-icon i');
        if (titleIcon) {
            titleIcon.className = 'fa-solid fa-user-pen text-primary-orange';
        }

        const showUrl = routes.partiesShow.replace('__ID__', partyId);

        fetch(showUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error('Party not found');
                return res.json();
            })
            .then((party) => {
                prefillEditForm(party);
                if (addCustomerModal) addCustomerModal.show();
            })
            .catch((err) => {
                console.error('Error opening edit modal:', err);
                showToast('Failed to load party details for edit', 'error');
            });
    }

    /**
     * Prefill form in EDIT mode with existing values
     */
    function prefillEditForm(party) {
        // Party Type
        setPartyType(party.party_type || 'CUSTOMER');

        // Name, Mobile, Email, Address
        if (newCustomerName) newCustomerName.value = party.name || '';
        if (newCustomerMobile) newCustomerMobile.value = party.mobile || '';
        if (newCustomerEmail) newCustomerEmail.value = party.email || '';
        if (newCustomerAddress) newCustomerAddress.value = party.address || '';

        // Family Sub-Profile
        if (party.parent_customer_id && party.parent) {
            if (checkFamilySubProfile) checkFamilySubProfile.checked = true;
            if (familySubProfileDrawer) familySubProfileDrawer.style.display = 'block';
            if (hiddenParentCustomerId) hiddenParentCustomerId.value = party.parent_customer_id;
            if (parentCustomerSearchInput) parentCustomerSearchInput.value = party.parent.name;
            if (parentCustomerClearBtn) parentCustomerClearBtn.style.display = 'block';
            if (parentCustomerSelectedBadge) {
                parentCustomerSelectedBadge.style.display = 'block';
                parentCustomerSelectedBadge.innerHTML = `
                    <div class="alert alert-info py-1 px-2 m-0 small d-flex align-items-center justify-content-between">
                        <span><strong>Selected Parent:</strong> ${escapeHtml(party.parent.name)} (${escapeHtml(party.parent.customer_no)})</span>
                    </div>
                `;
            }
            selectedParentCustomer = party.parent;
        } else {
            if (checkFamilySubProfile) checkFamilySubProfile.checked = false;
            if (familySubProfileDrawer) familySubProfileDrawer.style.display = 'none';
            clearParentCustomerSelection();
        }

        // Additional information fields
        if (newPartyBirthday) newPartyBirthday.value = party.birthday || '';
        if (newPartyAnniversary) newPartyAnniversary.value = party.anniversary || '';

        // Aadhaar: keep empty, show masked value in placeholder
        if (newPartyAadhaar) {
            newPartyAadhaar.value = '';
            newPartyAadhaar.placeholder = party.aadhaar_no_masked || 'Adhar No. (12 Digits)';
        }

        // PAN: keep empty, show masked value in placeholder
        if (newPartyPan) {
            newPartyPan.value = '';
            newPartyPan.placeholder = party.pan_no_masked || 'PAN NO.';
        }

        if (newPartyGst) newPartyGst.value = party.gst_no || '';
        if (newPartyLicNo) newPartyLicNo.value = party.lic_no || '';
        if (newPartyRegNo) newPartyRegNo.value = party.reg_no || '';
        if (newPartyLogoTitle) newPartyLogoTitle.value = party.logo_title || '';
        if (newPartyShopName) newPartyShopName.value = party.shop_name || '';
        if (newPartyArea) newPartyArea.value = party.area || '';
        if (newPartyShopAddress) newPartyShopAddress.value = party.shop_address || '';

        // State & District
        if (newPartyState) {
            newPartyState.value = party.state || '';
            renderDistrictField(party.state || '');
            const distInput = document.getElementById('newPartyDistrict');
            if (distInput && party.district) {
                distInput.value = party.district;
            }
        }

        // Profile Image
        if (party.profile_image_url) {
            if (partyProfilePreview) {
                partyProfilePreview.src = party.profile_image_url;
                partyProfilePreview.style.display = 'block';
            }
            if (partyProfilePlaceholder) partyProfilePlaceholder.style.display = 'none';
            if (btnRemoveProfileImage) btnRemoveProfileImage.style.display = 'inline-flex';
            if (removeProfileImageInput) removeProfileImageInput.value = '0';
        } else {
            clearProfileImageSelection();
        }

        // Shop Logo
        if (party.shop_logo_url) {
            if (shopLogoPreview) {
                shopLogoPreview.src = party.shop_logo_url;
                shopLogoPreview.style.display = 'block';
            }
            if (shopLogoPlaceholder) shopLogoPlaceholder.style.display = 'none';
            if (btnRemoveShopLogo) btnRemoveShopLogo.style.display = 'inline-flex';
            if (removeShopLogoInput) removeShopLogoInput.value = '0';
        } else {
            clearShopLogoSelection();
        }

        // Expand additional info if any optional field has data
        const hasAdditionalData =
            party.birthday ||
            party.anniversary ||
            party.has_aadhaar ||
            party.has_pan ||
            party.gst_no ||
            party.lic_no ||
            party.reg_no ||
            party.logo_title ||
            party.shop_name ||
            party.state ||
            party.district ||
            party.area ||
            party.shop_address ||
            party.shop_logo_url;

        if (hasAdditionalData) {
            expandAdditionalSection();
        } else {
            collapseAdditionalSection();
        }
    }

    /**
     * Reset Add/Edit Modal
     */
    function resetPartyModal() {
        if (addCustomerForm) addCustomerForm.reset();
        resetPartyModalErrors();
        clearProfileImageSelection();
        clearShopLogoSelection();
        clearParentCustomerSelection();
        setPartyType('CUSTOMER');
        collapseAdditionalSection(true);
        renderDistrictField('');
        if (editPartyIdInput) editPartyIdInput.value = '';
        if (removeProfileImageInput) removeProfileImageInput.value = '0';
        if (removeShopLogoInput) removeShopLogoInput.value = '0';
        if (newPartyAadhaar) newPartyAadhaar.placeholder = 'Adhar No. (12 Digits)';
        if (newPartyPan) newPartyPan.placeholder = 'PAN NO.';
        initPartyDateInputs();
    }

    function resetPartyModalErrors() {
        const errorEls = document.querySelectorAll('#addCustomerForm .field-error-text');
        errorEls.forEach((el) => {
            el.textContent = '';
            el.style.display = 'none';
        });

        const invalidInputs = document.querySelectorAll('#addCustomerForm .is-invalid');
        invalidInputs.forEach((input) => input.classList.remove('is-invalid'));
    }

    // Party Type segmented toggle
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
        btnToggleCustomer.addEventListener('click', () => setPartyType('CUSTOMER'));
    }
    if (btnToggleSupplier) {
        btnToggleSupplier.addEventListener('click', () => setPartyType('SUPPLIER'));
    }

    // Profile Image Selection & Removal
    function handleProfileImageSelected(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (partyProfilePreview && partyProfilePlaceholder && btnRemoveProfileImage) {
                partyProfilePreview.src = e.target.result;
                partyProfilePreview.style.display = 'block';
                partyProfilePlaceholder.style.display = 'none';
                btnRemoveProfileImage.style.display = 'inline-flex';
            }
            if (removeProfileImageInput) removeProfileImageInput.value = '0';
        };
        reader.readAsDataURL(file);
    }

    function clearProfileImageSelection() {
        if (partyProfileCameraInput) partyProfileCameraInput.value = '';
        if (partyProfileFolderInput) partyProfileFolderInput.value = '';
        if (partyProfilePreview) {
            partyProfilePreview.src = '';
            partyProfilePreview.style.display = 'none';
        }
        if (partyProfilePlaceholder) partyProfilePlaceholder.style.display = 'block';
        if (btnRemoveProfileImage) btnRemoveProfileImage.style.display = 'none';
        if (removeProfileImageInput) removeProfileImageInput.value = '1';
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

    // Shop Logo Selection & Removal
    function handleShopLogoSelected(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (shopLogoPreview && shopLogoPlaceholder && btnRemoveShopLogo) {
                shopLogoPreview.src = e.target.result;
                shopLogoPreview.style.display = 'block';
                shopLogoPlaceholder.style.display = 'none';
                btnRemoveShopLogo.style.display = 'inline-flex';
            }
            if (removeShopLogoInput) removeShopLogoInput.value = '0';
        };
        reader.readAsDataURL(file);
    }

    function clearShopLogoSelection() {
        if (partyShopLogoCameraInput) partyShopLogoCameraInput.value = '';
        if (partyShopLogoFolderInput) partyShopLogoFolderInput.value = '';
        if (shopLogoPreview) {
            shopLogoPreview.src = '';
            shopLogoPreview.style.display = 'none';
        }
        if (shopLogoPlaceholder) shopLogoPlaceholder.style.display = 'block';
        if (btnRemoveShopLogo) btnRemoveShopLogo.style.display = 'none';
        if (removeShopLogoInput) removeShopLogoInput.value = '1';
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

    // Family Sub-Profile Drawer & Parent Customer Selection
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
            }, 300);
        });
    }

    function performParentCustomerSearch(query) {
        if (!parentCustomerSearchResults) return;
        const searchUrl = routes.customerSearch;

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
            signal: parentSearchAbortController.signal,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((res) => res.json())
            .then((results) => {
                // Exclude current editing party from parent list to prevent self-referencing
                const currentEditId = editPartyIdInput ? parseInt(editPartyIdInput.value, 10) : null;
                const filtered = (results || []).filter((c) => !currentEditId || c.id !== currentEditId);

                if (filtered.length === 0) {
                    parentCustomerSearchResults.innerHTML = `
                    <div class="p-2 text-center text-muted small">No customers found</div>
                `;
                    return;
                }

                let html = '';
                filtered.forEach((customer) => {
                    html += `
                    <div class="customer-dropdown-item parent-search-item" data-id="${customer.id}" data-name="${escapeHtml(customer.name)}" data-mobile="${escapeHtml(customer.mobile)}" data-no="${escapeHtml(customer.customer_no)}">
                        <div class="fw-bold">${escapeHtml(customer.name)}</div>
                        <div class="small text-muted">${escapeHtml(customer.customer_no)} &bull; ${escapeHtml(customer.mobile)}</div>
                    </div>
                `;
                });
                parentCustomerSearchResults.innerHTML = html;

                parentCustomerSearchResults.querySelectorAll('.parent-search-item').forEach((item) => {
                    item.addEventListener('click', function () {
                        const chosen = {
                            id: this.getAttribute('data-id'),
                            name: this.getAttribute('data-name'),
                            mobile: this.getAttribute('data-mobile'),
                            customer_no: this.getAttribute('data-no'),
                        };

                        selectedParentCustomer = chosen;
                        if (hiddenParentCustomerId) hiddenParentCustomerId.value = chosen.id;
                        if (parentCustomerSearchInput) parentCustomerSearchInput.value = chosen.name;
                        if (parentCustomerSearchResults) parentCustomerSearchResults.style.display = 'none';

                        if (parentCustomerSelectedBadge) {
                            parentCustomerSelectedBadge.style.display = 'block';
                            parentCustomerSelectedBadge.innerHTML = `
                            <div class="alert alert-info py-1 px-2 m-0 small d-flex align-items-center justify-content-between">
                                <span><strong>Selected Parent:</strong> ${escapeHtml(chosen.name)} (${escapeHtml(chosen.customer_no)})</span>
                            </div>
                        `;
                        }

                        if (newCustomerMobile && !newCustomerMobile.value.trim() && chosen.mobile) {
                            newCustomerMobile.value = chosen.mobile;
                        }
                    });
                });
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                parentCustomerSearchResults.innerHTML = `
                <div class="p-2 text-center text-danger small">Search failed</div>
            `;
            });
    }

    // State & Dynamic District Population
    function initStatesDropdown() {
        if (!newPartyState || newPartyState.options.length > 1) return;
        const locations = window.INDIA_LOCATIONS;
        if (locations && Array.isArray(locations.states)) {
            locations.states.forEach((state) => {
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
        const districtList =
            locations && locations.districts && locations.districts[stateName] ? locations.districts[stateName] : null;

        if (districtList && Array.isArray(districtList) && districtList.length > 0) {
            let optionsHtml = '<option value="">Select District</option>';
            districtList.forEach((d) => {
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

        if (
            isNaN(year) ||
            year < 1900 ||
            val > todayStr ||
            (input.validity && (input.validity.rangeUnderflow || input.validity.rangeOverflow))
        ) {
            showError();
            return false;
        }

        clearError();
        return true;
    }

    // Expand / Collapse Additional Information Section
    function expandAdditionalSection() {
        if (!additionalInfoSection) return;
        isAdditionalExpanded = true;
        if (btnToggleAdditionalInfo) btnToggleAdditionalInfo.classList.add('expanded');
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
            if (isAdditionalExpanded) {
                additionalInfoSection.style.maxHeight = 'none';
                additionalInfoSection.style.overflow = 'visible';
            }
        };
        additionalInfoSection.addEventListener('transitionend', onTransitionEnd);
        setTimeout(() => {
            if (isAdditionalExpanded && additionalInfoSection.style.maxHeight !== 'none') {
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
        isAdditionalExpanded = false;
        if (btnToggleAdditionalInfo) btnToggleAdditionalInfo.classList.remove('expanded');
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
            if (!isAdditionalExpanded) {
                additionalInfoSection.style.maxHeight = '';
                additionalInfoSection.style.overflow = '';
            }
        };
        additionalInfoSection.addEventListener('transitionend', onCollapseEnd);
        setTimeout(() => {
            if (!isAdditionalExpanded) {
                onCollapseEnd();
            }
        }, 300);
    }

    if (btnToggleAdditionalInfo) {
        btnToggleAdditionalInfo.addEventListener('click', function () {
            if (isAdditionalExpanded) {
                collapseAdditionalSection();
            } else {
                expandAdditionalSection();
            }
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

    // =========================================================================
    // 6. SAVE / SUBMIT PARTY (CREATE OR UPDATE)
    // =========================================================================

    if (btnSaveNewCustomer) {
        btnSaveNewCustomer.addEventListener('click', function () {
            resetPartyModalErrors();

            const isEdit = editPartyIdInput && editPartyIdInput.value.trim() !== '';
            const partyId = isEdit ? editPartyIdInput.value.trim() : null;

            // Basic client-side required field check
            let hasError = false;
            if (!newCustomerName || !newCustomerName.value.trim()) {
                showFieldError('name', 'Full name is required.');
                hasError = true;
            }
            if (!newCustomerMobile || !newCustomerMobile.value.trim()) {
                showFieldError('mobile', 'Mobile number is required.');
                hasError = true;
            } else if (!/^[0-9]{10}$/.test(newCustomerMobile.value.trim())) {
                showFieldError('mobile', 'Mobile number must be exactly 10 digits.');
                hasError = true;
            }

            // If family sub-profile is checked, parent is required
            if (checkFamilySubProfile && checkFamilySubProfile.checked) {
                if (!hiddenParentCustomerId || !hiddenParentCustomerId.value.trim()) {
                    showFieldError('parent_customer_id', 'Please search and select the main customer.');
                    hasError = true;
                }
            }

            // Validate birthday and anniversary
            const isBirthdayValid = validatePartyDateInput(newPartyBirthday, 'errorNewPartyBirthday');
            const isAnniversaryValid = validatePartyDateInput(newPartyAnniversary, 'errorNewPartyAnniversary');

            if (!isBirthdayValid || !isAnniversaryValid) {
                expandAdditionalSection();
                if (!isBirthdayValid && newPartyBirthday) {
                    newPartyBirthday.focus();
                } else if (!isAnniversaryValid && newPartyAnniversary) {
                    newPartyAnniversary.focus();
                }
                hasError = true;
            }

            if (hasError) return;

            // Build FormData for multipart upload
            const formData = new FormData(addCustomerForm);

            // Append camera/folder file inputs if selected
            if (partyProfileCameraInput && partyProfileCameraInput.files && partyProfileCameraInput.files[0]) {
                formData.set('profile_image', partyProfileCameraInput.files[0]);
            } else if (partyProfileFolderInput && partyProfileFolderInput.files && partyProfileFolderInput.files[0]) {
                formData.set('profile_image', partyProfileFolderInput.files[0]);
            }

            if (partyShopLogoCameraInput && partyShopLogoCameraInput.files && partyShopLogoCameraInput.files[0]) {
                formData.set('shop_logo', partyShopLogoCameraInput.files[0]);
            } else if (
                partyShopLogoFolderInput &&
                partyShopLogoFolderInput.files &&
                partyShopLogoFolderInput.files[0]
            ) {
                formData.set('shop_logo', partyShopLogoFolderInput.files[0]);
            }

            // If Aadhaar or PAN is untouched in edit mode, don't submit raw masked placeholder
            if (isEdit) {
                if (newPartyAadhaar && !newPartyAadhaar.value.trim()) {
                    formData.delete('aadhaar_no');
                }
                if (newPartyPan && !newPartyPan.value.trim()) {
                    formData.delete('pan_no');
                }
                // Spoof PUT method for multipart update
                formData.append('_method', 'PUT');
            }

            // Target URL: POST /customers for create, POST /parties/{id} with _method=PUT for update
            const targetUrl = isEdit ? routes.partiesUpdate.replace('__ID__', partyId) : routes.customerStore;

            const originalButtonHtml = btnSaveNewCustomer.innerHTML;
            btnSaveNewCustomer.disabled = true;
            btnSaveNewCustomer.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
                .then((res) => {
                    return res.json().then((data) => {
                        if (!res.ok) {
                            handleValidationErrors(data);
                            throw new Error(data.message || 'Validation failed');
                        }
                        return data;
                    });
                })
                .then((data) => {
                    if (addCustomerModal) addCustomerModal.hide();
                    resetPartyModal();
                    showToast(isEdit ? 'Party updated successfully' : 'Party created successfully', 'success');
                    fetchParties(isEdit ? currentPage : 1);
                })
                .catch((err) => {
                    console.warn('Save party response:', err);
                })
                .finally(() => {
                    btnSaveNewCustomer.disabled = false;
                    btnSaveNewCustomer.innerHTML = originalButtonHtml;
                });
        });
    }

    function showFieldError(field, message) {
        const fieldErrorMap = {
            name: { error: document.getElementById('errorNewCustomerName'), input: newCustomerName },
            mobile: { error: document.getElementById('errorNewCustomerMobile'), input: newCustomerMobile },
            email: { error: document.getElementById('errorNewCustomerEmail'), input: newCustomerEmail },
            address: { error: document.getElementById('errorNewCustomerAddress'), input: newCustomerAddress },
            parent_customer_id: {
                error: document.getElementById('errorParentCustomerId'),
                input: parentCustomerSearchInput,
            },
            birthday: {
                error: document.getElementById('errorNewPartyBirthday'),
                input: newPartyBirthday,
                inAdditional: true,
            },
            anniversary: {
                error: document.getElementById('errorNewPartyAnniversary'),
                input: newPartyAnniversary,
                inAdditional: true,
            },
            aadhaar_no: {
                error: document.getElementById('errorNewPartyAadhaar'),
                input: newPartyAadhaar,
                inAdditional: true,
            },
            pan_no: { error: document.getElementById('errorNewPartyPan'), input: newPartyPan, inAdditional: true },
            gst_no: { error: document.getElementById('errorNewPartyGst'), input: newPartyGst, inAdditional: true },
            lic_no: { error: document.getElementById('errorNewPartyLicNo'), input: newPartyLicNo, inAdditional: true },
            reg_no: { error: document.getElementById('errorNewPartyRegNo'), input: newPartyRegNo, inAdditional: true },
            logo_title: {
                error: document.getElementById('errorNewPartyLogoTitle'),
                input: newPartyLogoTitle,
                inAdditional: true,
            },
            shop_name: {
                error: document.getElementById('errorNewPartyShopName'),
                input: newPartyShopName,
                inAdditional: true,
            },
            state: { error: document.getElementById('errorNewPartyState'), input: newPartyState, inAdditional: true },
            district: {
                error: document.getElementById('errorNewPartyDistrict'),
                input: document.getElementById('newPartyDistrict'),
                inAdditional: true,
            },
            area: { error: document.getElementById('errorNewPartyArea'), input: newPartyArea, inAdditional: true },
            shop_address: {
                error: document.getElementById('errorNewPartyShopAddress'),
                input: newPartyShopAddress,
                inAdditional: true,
            },
            profile_image: { error: document.getElementById('errorPartyProfileImage'), input: null },
            shop_logo: { error: document.getElementById('errorPartyShopLogo'), input: null, inAdditional: true },
        };

        const mapping = fieldErrorMap[field];
        if (mapping && mapping.error) {
            mapping.error.textContent = message;
            mapping.error.style.display = 'block';
            if (mapping.input) mapping.input.classList.add('is-invalid');
            if (mapping.inAdditional && !isAdditionalExpanded) {
                expandAdditionalSection();
            }
        }
    }

    function handleValidationErrors(data) {
        if (data.errors && typeof data.errors === 'object') {
            Object.keys(data.errors).forEach((field) => {
                const messages = data.errors[field];
                const msg = Array.isArray(messages) ? messages[0] : String(messages);
                showFieldError(field, msg);
            });
        } else {
            showToast(data.message || 'Failed to save party', 'error');
        }
    }

    // =========================================================================
    // 7. DELETE PARTY
    // =========================================================================

    function confirmDeleteParty(partyId, partyName) {
        const confirmed = window.confirm(`Are you sure you want to delete ${partyName}? This action cannot be undone.`);
        if (!confirmed) return;

        const deleteUrl = routes.partiesDestroy.replace('__ID__', partyId);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => {
                return res.json().then((data) => {
                    if (!res.ok) {
                        throw new Error(data.message || 'Cannot delete party');
                    }
                    return data;
                });
            })
            .then((data) => {
                showToast(data.message || 'Party deleted successfully', 'success');
                fetchParties(currentPage);
            })
            .catch((err) => {
                console.error('Delete party error:', err);
                showToast(err.message || 'Failed to delete party', 'error');
            });
    }

    // =========================================================================
    // 8. INITIALIZATION
    // =========================================================================
    initPartyDateInputs();
    fetchParties(1);
});
