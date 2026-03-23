<nav class="navbar" data-navbar>
    <div class="navbar-shell">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisor-staff.dashboard') }}" class="navbar-brand">
            <img src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg" alt="D-Sizzlers Logo" class="navbar-logo">
            <div>
                <div style="font-size: 16px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Franchisor Staff Portal</div>
            </div>
        </a>

        <button type="button" class="navbar-toggle" data-navbar-toggle aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav" data-navbar-menu>
            <li><a href="{{ route('franchisor-staff.dashboard') }}" class="{{ request()->routeIs('franchisor-staff.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('franchisor-staff.items.index') }}" class="{{ request()->routeIs('franchisor-staff.items.*') ? 'active' : '' }}">Items</a></li>
            <li><a href="{{ route('franchisor-staff.stock.index') }}" class="{{ request()->routeIs('franchisor-staff.stock.*') ? 'active' : '' }}">Item Stock</a></li>
            <li><a href="{{ route('franchisor-staff.password') }}" class="{{ request()->routeIs('franchisor-staff.password') ? 'active' : '' }}">Update Password</a></li>
            <li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger navbar-logout" style="margin: 0;">Log Out</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
