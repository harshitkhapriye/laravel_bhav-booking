<!-- =========================================================================
     MODAL: CONFIRM REMOVE CUSTOMER
     ========================================================================= -->
<div class="modal fade" id="confirmRemoveCustomerModal" tabindex="-1" aria-labelledby="confirmRemoveCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 12px 18px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <h6 class="modal-title fw-bold m-0" id="confirmRemoveCustomerModalLabel">Remove customer?</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                <p class="text-secondary mb-3" style="font-size: 13.5px; line-height: 1.5;">
                    This booking already has entries. What do you want to do?
                </p>
                <div class="d-flex flex-column gap-2 pt-1">
                    <button type="button" class="btn btn-outline-secondary rounded-pill py-2 px-3 fw-bold text-center" id="btnRemoveCustomerOnly">
                        <i class="fa-solid fa-user-xmark me-2 text-primary"></i> Remove customer only
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill py-2 px-3 fw-bold text-center" id="btnRemoveCustomerAndClear">
                        <i class="fa-solid fa-trash-can me-2"></i> Remove customer &amp; clear form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

