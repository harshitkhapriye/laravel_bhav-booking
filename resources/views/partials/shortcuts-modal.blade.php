<!-- =========================================================================
     MODAL: KEYBOARD SHORTCUTS CHEAT SHEET
     ========================================================================= -->
<div class="modal fade" id="shortcutsModal" tabindex="-1" aria-labelledby="shortcutsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--navy-dark); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="title-orange-dot"></span>
                    <h6 class="modal-title fw-bold m-0" id="shortcutsModalLabel">
                        <i class="fa-solid fa-keyboard me-2 text-warning"></i> Keyboard Shortcuts
                    </h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted small mb-3">
                    Speed up your billing workflow with fast keyboard shortcuts.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th style="width: 45%;">Shortcut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl + S</kbd> or <kbd class="bg-dark text-white px-2 py-1 rounded">F2</kbd>
                                </td>
                                <td><strong>Save / Update</strong> current booking</td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Esc</kbd>
                                </td>
                                <td><strong>Cancel edit</strong> / close dropdowns or modals</td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Alt + B</kbd>
                                </td>
                                <td>Switch trade mode to <strong class="text-success">BUY</strong></td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Alt + S</kbd>
                                </td>
                                <td>Switch trade mode to <strong class="text-danger">SELL</strong></td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Alt + C</kbd>
                                </td>
                                <td>Focus <strong>Customer</strong> search input</td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Alt + N</kbd>
                                </td>
                                <td>Open <strong>New Party</strong> modal</td>
                            </tr>
                            <tr>
                                <td>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Alt + P</kbd>
                                </td>
                                <td><strong>Print</strong> last saved or viewed voucher</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-light border small text-muted mt-3 mb-0 py-2 px-3">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                    Shortcuts are disabled while actively typing inside a modal dialog (except <code>Esc</code> to close).
                </div>
            </div>
            <div class="modal-footer bg-light px-3 py-2 border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-1 btn-sm fw-bold" data-bs-dismiss="modal">
                    Got it
                </button>
            </div>
        </div>
    </div>
</div>
