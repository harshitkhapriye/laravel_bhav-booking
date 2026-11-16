<!-- =========================================================================
     MODAL: REUSABLE CONFIRM ACTION (SHARED PARTIAL)
     ========================================================================= -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 12px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <i id="confirmModalIcon" class="me-1" style="display: none;"></i>
                    <h6 class="modal-title fw-bold m-0 fs-6" id="confirmActionModalLabel">Confirm Action</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p id="confirmModalMessage" class="text-dark mb-2" style="font-size: 13.5px; font-weight: 500;"></p>

                <!-- Optional Warning Line (e.g. Refund settlement warning) -->
                <div id="confirmModalWarning" class="alert alert-warning py-2 px-3 small d-flex align-items-start gap-2 mb-2" style="display: none; font-size: 12px; border-left: 4px solid #f59e0b;">
                    <i class="fa-solid fa-triangle-exclamation text-warning mt-1" style="font-size: 13px;"></i>
                    <span id="confirmModalWarningText" class="text-dark fw-semibold"></span>
                </div>

                <!-- Optional Details Key/Value Box -->
                <div id="confirmModalDetails" class="bg-light border rounded-3 p-2 mb-2 small" style="display: none; font-size: 12px;"></div>

                <!-- Optional Input Container (e.g. Select / Textarea / Cancel Reason) -->
                <div id="confirmModalInputWrapper" class="mt-2" style="display: none;">
                    <label id="confirmModalInputLabel" class="form-label fw-bold text-secondary mb-1" style="font-size: 11.5px;"></label>
                    <div id="confirmModalInputContainer"></div>
                    <div id="confirmModalInputError" class="text-danger small mt-1 fw-semibold" style="display: none; font-size: 11px;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light px-3 py-2 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold" id="confirmModalCancelBtn" data-bs-dismiss="modal" style="font-size: 12px; height: 34px;">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-bold" id="confirmModalConfirmBtn" style="font-size: 12px; height: 34px;">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

