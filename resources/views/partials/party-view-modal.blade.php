<!-- =========================================================================
     MODAL: VIEW PARTY DETAIL
     Shows full details, masked Aadhaar/PAN, family sub-profiles, and latest bookings
     ========================================================================= -->
<div class="modal fade" id="partyViewModal" tabindex="-1" aria-labelledby="partyViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 780px;">
        <div class="modal-content party-modal-card border-0 shadow-lg">

            <!-- Modal Header -->
            <div class="modal-header party-modal-header border-0 pb-0 pt-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="party-title-icon">
                        <i class="fa-solid fa-id-card text-primary-orange"></i>
                    </span>
                    <h5 class="modal-title fw-bold text-dark m-0 party-modal-title" id="partyViewModalLabel">
                        Party Detail
                    </h5>
                    <span id="viewPartyTypeBadge" class="badge rounded-pill ms-2 px-2 py-1"></span>
                </div>
                <button type="button" class="party-modal-close-round" data-bs-dismiss="modal" aria-label="Close" title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body party-modal-body px-4 py-3">

                <!-- 1. Party Overview: Avatar + Core Info -->
                <div class="d-flex flex-wrap flex-sm-nowrap align-items-start gap-3 p-3 mb-3 bg-light rounded-3">
                    <div class="text-center flex-shrink-0 mx-auto mx-sm-0">
                        <div class="party-avatar-outer position-relative" style="width: 80px; height: 80px;">
                            <div class="party-avatar-inner" style="width: 80px; height: 80px;">
                                <i class="fa-solid fa-user party-avatar-default-icon fs-2" id="viewPartyProfilePlaceholder"></i>
                                <img id="viewPartyProfileImg" src="" alt="Photo" class="party-avatar-img" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-0" id="viewPartyName">-</h5>
                                <div class="text-muted small" id="viewPartyNumber">-</div>
                            </div>
                            <div id="viewPartyParentBadge" class="badge bg-secondary-subtle text-secondary border px-2 py-1 small" style="display: none;"></div>
                        </div>

                        <div class="row g-2 mt-2 small">
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary"><i class="fa-solid fa-phone me-1 text-muted"></i> Mobile:</span>
                                <strong id="viewPartyMobile" class="text-dark ms-1">-</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary"><i class="fa-solid fa-envelope me-1 text-muted"></i> Email:</span>
                                <span id="viewPartyEmail" class="text-dark ms-1">-</span>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary"><i class="fa-solid fa-cake-candles me-1 text-muted"></i> Birthday:</span>
                                <span id="viewPartyBirthday" class="text-dark ms-1">-</span>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary"><i class="fa-solid fa-ring me-1 text-muted"></i> Anniversary:</span>
                                <span id="viewPartyAnniversary" class="text-dark ms-1">-</span>
                            </div>
                            <div class="col-12">
                                <span class="text-secondary"><i class="fa-solid fa-location-dot me-1 text-muted"></i> Address:</span>
                                <span id="viewPartyAddress" class="text-dark ms-1">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Business & Shop Information -->
                <div class="p-3 mb-3 bg-light rounded-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-store text-primary-orange"></i> Shop & Business Information
                    </h6>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Shop / Business:</span>
                            <strong id="viewPartyShopName" class="text-dark ms-1">-</strong>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Area / Landmark:</span>
                            <span id="viewPartyArea" class="text-dark ms-1">-</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">District, State:</span>
                            <span id="viewPartyLocation" class="text-dark ms-1">-</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">GST No:</span>
                            <span id="viewPartyGst" class="text-dark fw-semibold ms-1">-</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Licence No:</span>
                            <span id="viewPartyLicNo" class="text-dark ms-1">-</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Registration No:</span>
                            <span id="viewPartyRegNo" class="text-dark ms-1">-</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Logo Title:</span>
                            <span id="viewPartyLogoTitle" class="text-dark ms-1">-</span>
                        </div>
                        <div class="col-12">
                            <span class="text-secondary">Shop Address:</span>
                            <span id="viewPartyShopAddress" class="text-dark ms-1">-</span>
                        </div>
                    </div>

                    <!-- Shop Logo preview if available -->
                    <div id="viewPartyShopLogoContainer" class="mt-2 pt-2 border-top" style="display: none;">
                        <span class="text-secondary small d-block mb-1">Shop Logo:</span>
                        <img id="viewPartyShopLogoImg" src="" alt="Shop Logo" class="rounded border p-1" style="max-height: 70px; max-width: 150px; object-fit: contain;">
                    </div>
                </div>

                <!-- 3. Statutory / KYC Information (Masked Aadhaar & PAN) -->
                <div class="p-3 mb-3 bg-light rounded-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-primary-orange"></i> KYC & Statutory Identification
                    </h6>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">Aadhaar Number:</span>
                            <span class="badge bg-white text-dark border ms-1 font-monospace" id="viewPartyAadhaar">
                                -
                            </span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-secondary">PAN Number:</span>
                            <span class="badge bg-white text-dark border ms-1 font-monospace" id="viewPartyPan">
                                -
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 4. Family Members / Sub-Profiles -->
                <div class="p-3 mb-3 bg-light rounded-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-users text-primary-orange me-1"></i> Family Sub-Profiles</span>
                        <span class="badge rounded-pill bg-secondary text-white" id="viewFamilyMembersCount">0</span>
                    </h6>
                    <div id="viewFamilyMembersContainer">
                        <p class="text-muted small m-0 fst-italic">No family sub-profiles registered for this party.</p>
                    </div>
                </div>

                <!-- 5. Latest 5 Bhav Bookings -->
                <div class="p-3 bg-light rounded-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-clock-rotate-left text-primary-orange me-1"></i> Latest Bhav Bookings (Max 5)</span>
                    </h6>
                    <div class="table-responsive" style="max-height: 220px;">
                        <table class="table table-sm table-bordered table-hover m-0 align-middle small text-nowrap">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Booking No</th>
                                    <th>Date & Time</th>
                                    <th>Trade</th>
                                    <th>Metal</th>
                                    <th class="text-end">Weight (gm)</th>
                                    <th class="text-end">Amount (₹)</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="viewLatestBookingsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3 fst-italic">
                                        No bookings recorded yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer party-modal-footer px-4 py-3 border-0 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn party-btn-save" id="btnEditFromViewModal">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Party
                </button>
            </div>

        </div>
    </div>
</div>
