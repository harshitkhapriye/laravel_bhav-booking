<!-- =========================================================================
     MODAL: SAVE / UPDATE SUCCESS POPUP
     ========================================================================= -->
<div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-labelledby="saveSuccessModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 20px;">
            <div class="modal-body p-3">
                <!-- Animated Green Check Icon -->
                <div class="save-success-icon-wrapper mb-3">
                    <div class="save-success-circle">
                        <i class="fa-solid fa-check save-success-check"></i>
                    </div>
                </div>

                <!-- Title -->
                <h5 class="fw-bold text-dark mb-3" id="saveSuccessModalTitle">Transaction Saved Successfully!</h5>

                <!-- Transaction Details Card -->
                <div class="p-3 bg-light rounded-3 text-start small mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <span class="text-muted">Booking No:</span>
                        <strong class="text-dark fs-6" id="successBookingNo">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <span class="text-muted">Customer:</span>
                        <strong class="text-dark" id="successCustomerName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <span class="text-muted">Trade &amp; Metal:</span>
                        <div>
                            <span id="successTradeBadge" class="badge rounded-pill px-2 py-1 fw-bold">SELL</span>
                            <span id="successMetalBadge" class="badge rounded-pill px-2 py-1 fw-bold">GOLD</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <span class="text-muted">Total Weight:</span>
                        <strong class="text-dark" id="successTotalWeight">0.000 gm</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Amount:</span>
                        <strong class="text-success fs-6" id="successTotalAmount">₹0.00</strong>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-center gap-2 pt-2">
                    <button type="button" class="btn btn-outline-print rounded-pill px-4 py-2" id="btnSuccessPrint">
                        <i class="fa-solid fa-print me-2"></i> Print
                    </button>
                    <button type="button" class="btn btn-navy-list rounded-pill px-5 py-2 fw-bold" id="btnSuccessOk" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
