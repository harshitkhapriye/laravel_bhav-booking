<aside class="sidebar-wrapper">
    <!-- Top Hamburger Toggle Button -->
    <div class="sidebar-top-toggle">
        <button class="btn btn-sidebar-toggle shadow-sm" type="button" title="Menu">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
    </div>

    <!-- Vertical Navigation Icons -->
    <nav class="sidebar-nav">
        <ul class="sidebar-icon-list list-unstyled m-0 p-0">
            <li>
                <a href="#" class="sidebar-icon-link" title="Dashboard">
                    <i class="fa-solid fa-table-cells-large"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Invoices">
                    <i class="fa-regular fa-file-lines"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Reports">
                    <i class="fa-regular fa-clipboard"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('parties.index') }}" class="sidebar-icon-link {{ request()->routeIs('parties.*') ? 'active' : '' }}" title="Parties">
                    <i class="fa-solid fa-users"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('bhav-booking.index') }}" class="sidebar-icon-link {{ request()->routeIs('bhav-booking.*') ? 'active' : '' }}" title="Buy-Sell / Bhav Booking">
                    <i class="fa-solid fa-box"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Wallet">
                    <i class="fa-solid fa-wallet"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Bank & Accounts">
                    <i class="fa-solid fa-building-columns"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Tools & Settings">
                    <i class="fa-solid fa-wrench"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Sync">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Rates & Units">
                    <i class="fa-solid fa-ruler-combined"></i>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-icon-link" title="Store">
                    <i class="fa-solid fa-shop"></i>
                </a>
            </li>
        </ul>
    </nav>
</aside>
