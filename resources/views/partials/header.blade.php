<header class="top-header fixed-top bg-white border-bottom shadow-sm">
    <div class="container-fluid px-3 d-flex align-items-center justify-content-between h-100">
        
        <!-- Left: Brand Logo & Global Search -->
        <div class="d-flex align-items-center gap-3">
            <!-- Brand Logo -->
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="ju-logo-box">JU</div>
                <span class="ju-brand-text">JUST UDHARI</span>
            </a>

            <!-- Search Bar -->
            <div class="header-search d-none d-md-flex align-items-center">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="form-control rounded-pill search-input" placeholder="Type here to search">
            </div>
        </div>

        <!-- Right: Action Buttons, Alerts & Profile -->
        <div class="d-flex align-items-center gap-2">
            <!-- Orange Filled Billing Button -->
            <button type="button" class="btn btn-header-orange rounded-pill px-3 py-1 d-flex align-items-center gap-1">
                <i class="fa-regular fa-file-lines"></i>
                <span>Billing</span>
            </button>

            <!-- Pill Dropdown: Billing -->
            <div class="dropdown d-none d-lg-inline-block">
                <button class="btn btn-header-pill rounded-pill px-3 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa-regular fa-file-lines text-warning me-1"></i>
                    <span>Billing</span>
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li><a class="dropdown-item" href="#">Sale Bill</a></li>
                    <li><a class="dropdown-item" href="#">Purchase Bill</a></li>
                    <li><a class="dropdown-item" href="#">Estimate</a></li>
                </ul>
            </div>

            <!-- Pill Dropdown: Inventory -->
            <div class="dropdown d-none d-lg-inline-block">
                <button class="btn btn-header-pill rounded-pill px-3 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-cube text-warning me-1"></i>
                    <span>Inventory</span>
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li><a class="dropdown-item" href="#">Item List</a></li>
                    <li><a class="dropdown-item" href="#">Stock Summary</a></li>
                </ul>
            </div>

            <!-- Pill Dropdown: Udhari -->
            <div class="dropdown d-none d-lg-inline-block">
                <button class="btn btn-header-pill rounded-pill px-3 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa-regular fa-clipboard text-warning me-1"></i>
                    <span>Udhari</span>
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li><a class="dropdown-item" href="#">Ledger</a></li>
                    <li><a class="dropdown-item" href="#">Payments</a></li>
                </ul>
            </div>

            <!-- Notification Bell with Red Badge -->
            <div class="position-relative notification-btn ms-1 me-2">
                <a href="#" class="text-secondary text-decoration-none">
                    <i class="fa-regular fa-bell fs-5"></i>
                    <span class="notification-badge">10</span>
                </a>
            </div>

            <!-- User Text HS & Avatar -->
            <div class="d-flex align-items-center gap-2 ms-1">
                <span class="user-initials fw-bold text-dark">HS</span>
                <div class="user-avatar-circle">
                    <span class="avatar-inner-text">JU</span>
                </div>
            </div>

        </div>
    </div>
</header>
