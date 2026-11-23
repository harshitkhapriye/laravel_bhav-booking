<!-- Assign Karigar Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-900" style="color: #2d3748;">
                    <i class="fa fa-user-plus mr-2 text-primary"></i> Assign to Karigar
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-800 mr-2" onclick="$('#addKarigarModal').modal('show')">
                        <i class="fa fa-plus mr-1"></i> Add Karigar
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="search-karigar mb-4">
                    <div class="premium-input-group position-relative" style="border-radius: 12px; height: 45px; padding: 0 15px; background: #f7fafc; border: 1px solid #edf2f7;">
                        <i class="fa fa-search text-muted"></i>
                        <input type="text" id="karigar_search_input" placeholder="Search Karigar..." oninput="searchKarigars()" style="border: none; background: transparent; width: 100%; font-size: 0.9rem; font-weight: 600; outline: none;">
                    </div>
                </div>
                
                <div class="karigar-list-container" id="karigar_list_area" style="max-height: 350px; overflow-y: auto;">
                    {{-- Karigar Items will be loaded here --}}
                    <div class="text-center py-5">
                        <i class="fa fa-spinner fa-spin text-primary mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted fw-700 mb-0">Fetching Karigars...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-800" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Karigar Modal (Side Modal Style) -->
<div class="modal fade right" id="addKarigarModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px; margin-right: 20px;">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-900" style="color: #2d3748;">
                    <i class="fa fa-user-plus mr-2 text-success"></i> Add New Karigar
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addKarigarForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-800 small text-muted mb-1">Karigar Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-800 small text-muted mb-1">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="10 Digit Mobile" required maxlength="10" oninput="this.value=this.value.replace(/\D/g, '').slice(0, 10)">
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-800 small text-muted mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="Enter email">
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-800 small text-muted mb-1">Specialty</label>
                        <input type="text" name="specialty" class="form-control rounded-pill px-3" placeholder="e.g. Gold Chains, Polishing">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-800 small text-muted mb-1">State</label>
                                <select name="state_id" id="karigar_state" class="form-control rounded-pill px-3">
                                    <option value="">Select State</option>
                                    @foreach(states() as $state)
                                        <option value="{{ $state->code }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-800 small text-muted mb-1">District</label>
                                <select name="dist_id" id="karigar_district" class="form-control rounded-pill px-3">
                                    <option value="">Select District</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-800 small text-muted mb-1">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Enter full address" style="border-radius: 15px;"></textarea>
                    </div>
                   

                    <div class="col-12 my-2 p-0">
                        <a href="javascript:void(0);" onclick="$('#karigar_extra_info').slideToggle();" style="color: #3182ce; font-size: 0.75rem; font-weight: 800; text-decoration: none;">
                            <i class="fa fa-chevron-down mr-1"></i> More Details (Identity/Tax)
                        </a>
                    </div>

                    <div id="karigar_extra_info" style="display: none;">
                        <div class="form-group mb-3">
                             <label class="fw-800 small text-muted mb-1">PIN Code</label>
                             <input type="text" name="pin_code" class="form-control rounded-pill px-3" placeholder="6 Digits" maxlength="6" oninput="this.value=this.value.replace(/\D/g, '').slice(0, 6)">
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-800 small text-muted mb-1">Aadhaar No.</label>
                            <input type="text" name="adhar" class="form-control rounded-pill px-3" placeholder="12 Digits" maxlength="12" oninput="this.value=this.value.replace(/\D/g, '').slice(0, 12)">
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-800 small text-muted mb-1">PAN No.</label>
                            <input type="text" name="pan" class="form-control rounded-pill px-3" placeholder="PAN Number">
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-800 small text-muted mb-1">GST Number</label>
                            <input type="text" name="gst_num" class="form-control rounded-pill px-3" placeholder="GST Number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-800" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-800" id="saveKarigarBtn">Save Karigar</button>
                </div>
            </form>
        </div>
    </div>
</div>
