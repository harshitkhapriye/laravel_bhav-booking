<!-- =========================================================================
     MODAL: NEW PARTY DETAIL (Add Customer / Supplier)
     Matches the exact UI design in the screenshots
     ========================================================================= -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 680px;">
        <div class="modal-content party-modal-card border-0 shadow-lg">
            
            <!-- Modal Header (Clean White, Orange Icon + Close Button) -->
            <div class="modal-header party-modal-header border-0 pb-0 pt-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="party-title-icon">
                        <i class="fa-solid fa-user-plus text-primary-orange"></i>
                    </span>
                    <h5 class="modal-title fw-bold text-dark m-0 party-modal-title" id="addCustomerModalLabel">
                        New Party Detail
                    </h5>
                </div>
                <button type="button" class="party-modal-close-round" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Form (multipart/form-data for image uploads) -->
            <form id="addCustomerForm" autocomplete="off" onsubmit="return false;" enctype="multipart/form-data">
                <input type="hidden" id="editPartyId" name="edit_party_id" value="">
                <input type="hidden" id="removeProfileImageInput" name="remove_profile_image" value="0">
                <input type="hidden" id="removeShopLogoInput" name="remove_shop_logo" value="0">
                <div class="modal-body party-modal-body px-4 py-3">
                    
                    <!-- TOP SECTION: Profile Image + Toggle + Primary Inputs -->
                    <div class="row g-3 align-items-start mb-2">
                        
                        <!-- Left Column: Circular Profile Avatar with Camera & Folder Buttons -->
                        <div class="col-auto text-center party-avatar-col">
                            <div class="party-avatar-outer mx-auto position-relative">
                                <div class="party-avatar-inner" id="partyProfileInner">
                                    <i class="fa-solid fa-user party-avatar-default-icon" id="partyProfilePlaceholder"></i>
                                    <img id="partyProfilePreview" src="" alt="Profile" class="party-avatar-img" style="display: none;">
                                </div>
                                <button type="button" class="party-img-remove-badge" id="btnRemoveProfileImage" title="Remove image" style="display: none;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <!-- Camera and Folder Round Buttons -->
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                <button type="button" class="party-mini-circle-btn" id="btnProfileCamera" title="Take photo with camera">
                                    <i class="fa-solid fa-camera"></i>
                                </button>
                                <button type="button" class="party-mini-circle-btn" id="btnProfileFolder" title="Choose image file">
                                    <i class="fa-solid fa-folder-open"></i>
                                </button>
                            </div>

                            <div class="party-avatar-label mt-1">PROFILE IMAGE</div>

                            <!-- Hidden file inputs -->
                            <input type="file" id="partyProfileCameraInput" accept="image/*" capture="environment" class="d-none">
                            <input type="file" id="partyProfileFolderInput" accept="image/*" class="d-none">
                            <div class="field-error-text text-danger small mt-1" id="errorPartyProfileImage" style="display: none;"></div>
                        </div>

                        <!-- Right Column: Segmented Toggle + Name + Mobile -->
                        <div class="col">
                            
                            <!-- Segmented Sliding Toggle (Customer / Supplier) -->
                            <div class="d-flex justify-content-end mb-3">
                                <div class="party-segmented-toggle" id="partySegmentedToggle" data-type="CUSTOMER">
                                    <div class="party-toggle-slider" id="partyToggleSlider"></div>
                                    <button type="button" class="party-toggle-btn active" data-type="CUSTOMER" id="btnToggleCustomer">
                                        <i class="fa-solid fa-store me-1"></i> Customer
                                    </button>
                                    <button type="button" class="party-toggle-btn" data-type="SUPPLIER" id="btnToggleSupplier">
                                        <i class="fa-solid fa-truck me-1"></i> Supplier
                                    </button>
                                </div>
                                <input type="hidden" name="party_type" id="newPartyTypeInput" value="CUSTOMER">
                            </div>

                            <!-- Full Name -->
                            <div class="mb-2">
                                <input type="text" class="form-control party-input-pill" id="newCustomerName" name="name" placeholder="Full Name" maxlength="100" required>
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewCustomerName" style="display: none;"></div>
                            </div>

                            <!-- Mobile Number -->
                            <div class="mb-2">
                                <input type="tel" class="form-control party-input-pill" id="newCustomerMobile" name="mobile" placeholder="Mobile Number" maxlength="10" required>
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewCustomerMobile" style="display: none;"></div>
                            </div>

                            <!-- Family Sub-Profile Checkbox -->
                            <div class="form-check d-flex align-items-center mt-2 mb-2">
                                <input class="form-check-input party-checkbox-custom" type="checkbox" id="checkFamilySubProfile" name="is_family_sub_profile" value="1">
                                <label class="form-check-label fw-bold party-checkbox-text ms-2" for="checkFamilySubProfile">
                                    ADD AS FAMILY SUB-PROFILE
                                </label>
                            </div>

                            <!-- Main Customer Search Drawer (shown when checkbox is checked) -->
                            <div id="familySubProfileDrawer" class="family-subprofile-container p-2 mb-2 rounded-3" style="display: none;">
                                <div class="small fw-bold text-secondary mb-1">
                                    MAIN CUSTOMER <span class="text-danger">*</span>
                                </div>
                                <div class="position-relative">
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user-group field-icon text-muted"></i>
                                        <input type="text" class="form-control party-input-pill ps-5 pe-5" id="parentCustomerSearchInput" placeholder="Search main customer by name or number..." autocomplete="off">
                                        <button type="button" class="btn-clear-parent" id="parentCustomerClearBtn" style="display: none;" title="Clear">&times;</button>
                                    </div>
                                    <input type="hidden" name="parent_customer_id" id="hiddenParentCustomerId" value="">
                                    
                                    <!-- Search Dropdown for Main Customer -->
                                    <div class="customer-dropdown-menu shadow" id="parentCustomerSearchResults" style="display: none;"></div>
                                </div>
                                <div id="parentCustomerSelectedBadge" class="selected-parent-info mt-1 ps-2" style="display: none;"></div>
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorParentCustomerId" style="display: none;"></div>
                            </div>

                            <!-- E-Mail Address (Optional) -->
                            <div class="mb-2">
                                <input type="email" class="form-control party-input-pill" id="newCustomerEmail" name="email" placeholder="E-Mail Address (Optional)" maxlength="150">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewCustomerEmail" style="display: none;"></div>
                            </div>

                        </div>
                    </div>

                    <!-- Party Address (Full Width) -->
                    <div class="mb-2">
                        <textarea class="form-control party-textarea-rounded" id="newCustomerAddress" name="address" rows="2" placeholder="Party Address" maxlength="500"></textarea>
                        <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewCustomerAddress" style="display: none;"></div>
                    </div>

                    <!-- =========================================================
                         EXPANDABLE ADDITIONAL INFORMATION SECTION (Screenshot 2)
                         ========================================================= -->
                    <div id="additionalInfoSection" class="additional-info-panel">
                        
                        <!-- Pill Heading with Decorative Lines -->
                        <div class="additional-divider-wrapper my-3 d-flex align-items-center">
                            <span class="additional-divider-line"></span>
                            <span class="additional-divider-pill">ADDITIONAL OPTIONAL INFORMATION</span>
                            <span class="additional-divider-line"></span>
                        </div>

                        <!-- 2-Column Fields Grid -->
                        <div class="row g-2">
                            
                            <!-- Birthday -->
                            <div class="col-12 col-md-6">
                                <label class="party-field-label" for="newPartyBirthday">Birthday</label>
                                <input type="date" class="form-control party-input-pill" id="newPartyBirthday" name="birthday" min="1900-01-01">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyBirthday" style="display: none;"></div>
                            </div>

                            <!-- Anniversary -->
                            <div class="col-12 col-md-6">
                                <label class="party-field-label" for="newPartyAnniversary">Anniversary</label>
                                <input type="date" class="form-control party-input-pill" id="newPartyAnniversary" name="anniversary" min="1900-01-01">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyAnniversary" style="display: none;"></div>
                            </div>

                            <!-- Aadhaar Number (12 Digits) -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill" id="newPartyAadhaar" name="aadhaar_no" placeholder="Adhar No. (12 Digits)" maxlength="12">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyAadhaar" style="display: none;"></div>
                            </div>

                            <!-- PAN Number -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill text-uppercase" id="newPartyPan" name="pan_no" placeholder="PAN NO." maxlength="10">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyPan" style="display: none;"></div>
                            </div>

                            <!-- GST Number -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill text-uppercase" id="newPartyGst" name="gst_no" placeholder="GST NUMBER" maxlength="15">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyGst" style="display: none;"></div>
                            </div>

                            <!-- Lic. No. -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill" id="newPartyLicNo" name="lic_no" placeholder="Lic. No." maxlength="100">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyLicNo" style="display: none;"></div>
                            </div>

                            <!-- Reg. No. -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill" id="newPartyRegNo" name="reg_no" placeholder="Reg. No." maxlength="100">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyRegNo" style="display: none;"></div>
                            </div>

                            <!-- Logo Title -->
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control party-input-pill" id="newPartyLogoTitle" name="logo_title" placeholder="Logo Title" maxlength="100">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyLogoTitle" style="display: none;"></div>
                            </div>

                            <!-- Shop / Business Name (Full Width) -->
                            <div class="col-12">
                                <input type="text" class="form-control party-input-pill" id="newPartyShopName" name="shop_name" placeholder="Shop / Business Name" maxlength="100">
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyShopName" style="display: none;"></div>
                            </div>

                            <!-- Select State -->
                            <div class="col-12 col-md-6">
                                <label class="party-field-label" for="newPartyState">Select State</label>
                                <select class="form-select party-input-pill" id="newPartyState" name="state">
                                    <option value="">Select State</option>
                                </select>
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyState" style="display: none;"></div>
                            </div>

                            <!-- Select District (Dynamic: select or text input) -->
                            <div class="col-12 col-md-6">
                                <label class="party-field-label" for="newPartyDistrict" id="labelPartyDistrict">Select District</label>
                                <div id="districtContainer">
                                    <select class="form-select party-input-pill" id="newPartyDistrict" name="district">
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                                <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyDistrict" style="display: none;"></div>
                            </div>

                            <!-- Left: Area + Shop Address -->
                            <div class="col-12 col-md-6">
                                <div class="mb-2">
                                    <input type="text" class="form-control party-input-pill" id="newPartyArea" name="area" placeholder="Enter Area / Landmark" maxlength="100">
                                    <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyArea" style="display: none;"></div>
                                </div>
                                <div>
                                    <textarea class="form-control party-textarea-rounded" id="newPartyShopAddress" name="shop_address" rows="3" placeholder="Shop Address" maxlength="500"></textarea>
                                    <div class="field-error-text text-danger small mt-1 ps-3" id="errorNewPartyShopAddress" style="display: none;"></div>
                                </div>
                            </div>

                            <!-- Right: Dashed orange "SHOP LOGO" upload box -->
                            <div class="col-12 col-md-6">
                                <div class="shop-logo-upload-box text-center position-relative">
                                    <div class="shop-logo-inner-content" id="shopLogoInner">
                                        <i class="fa-solid fa-user-tie shop-logo-silhouette" id="shopLogoPlaceholder"></i>
                                        <div class="shop-logo-title-text">SHOP LOGO</div>
                                        <img id="shopLogoPreview" src="" alt="Shop Logo" class="shop-logo-preview-img" style="display: none;">
                                    </div>
                                    <button type="button" class="party-img-remove-badge" id="btnRemoveShopLogo" title="Remove logo" style="display: none;">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <!-- Camera and Folder Buttons below Shop Logo box -->
                                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                    <button type="button" class="party-mini-circle-btn" id="btnShopLogoCamera" title="Take photo from camera">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>
                                    <button type="button" class="party-mini-circle-btn" id="btnShopLogoFolder" title="Choose file">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </button>
                                </div>

                                <!-- Hidden file inputs -->
                                <input type="file" id="partyShopLogoCameraInput" accept="image/*" capture="environment" class="d-none">
                                <input type="file" id="partyShopLogoFolderInput" accept="image/*" class="d-none">
                                <div class="field-error-text text-danger small mt-1 text-center" id="errorPartyShopLogo" style="display: none;"></div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Modal Footer (Pinned at bottom, smooth scrollable body above) -->
                <div class="modal-footer party-modal-footer px-4 py-3 border-0 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn party-btn-additional-toggle" id="btnToggleAdditionalInfo">
                        <i class="fa-solid fa-plus me-1" id="iconToggleAdditional"></i> Additional Information
                    </button>
                    
                    <button type="button" class="btn party-btn-save" id="btnSaveNewCustomer">
                        Save <i class="fa-solid fa-check ms-1"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
