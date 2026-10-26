<!-- =========================================================================
     MODAL: TODAY'S RATE (RATE MASTER)
     ========================================================================= -->
<div class="modal fade" id="dailyRateModal" tabindex="-1" aria-labelledby="dailyRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <h6 class="modal-title fw-bold m-0" id="dailyRateModalLabel">
                        Today's Bullion Rates
                    </h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dailyRateForm" autocomplete="off" onsubmit="return false;">
                <div class="modal-body p-3">
                    <div class="text-muted small mb-3">
                        <i class="fa-regular fa-calendar-days text-primary me-1"></i>
                        Date: <strong class="text-dark">{{ now()->format('d M, Y') }}</strong>
                        <span class="d-block text-secondary mt-1" style="font-size: 11px;">
                            (Rates are quoted per 10 grams)
                        </span>
                    </div>

                    <!-- Gold Rate Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1" for="modalGoldRate">
                            GOLD RATE (₹ / 10gm) <span class="text-danger">*</span>
                        </label>
                        <div class="input-with-suffix">
                            <input type="text"
                                   inputmode="decimal"
                                   class="form-control form-input-pill pe-5 text-end fw-bold"
                                   id="modalGoldRate"
                                   placeholder="e.g. 165000">
                            <span class="field-suffix">₹</span>
                        </div>
                        <div id="goldRateError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                    </div>

                    <!-- Silver Rate Input -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary mb-1" for="modalSilverRate">
                            SILVER RATE (₹ / 10gm) <span class="text-danger">*</span>
                        </label>
                        <div class="input-with-suffix">
                            <input type="text"
                                   inputmode="decimal"
                                   class="form-control form-input-pill pe-5 text-end fw-bold"
                                   id="modalSilverRate"
                                   placeholder="e.g. 1800">
                            <span class="field-suffix">₹</span>
                        </div>
                        <div id="silverRateError" class="field-error-text text-danger small mt-1 ps-2" style="display: none;"></div>
                    </div>

                    <div id="dailyRateGlobalError" class="alert alert-danger py-1 px-2 small mt-2 d-none"></div>
                </div>
                <div class="modal-footer bg-light px-3 py-2 border-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm fw-bold" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-save rounded-pill px-4 py-1 btn-sm text-white fw-bold" id="btnSaveDailyRates">
                        <i class="fa-solid fa-check me-1"></i> Save Rates
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
