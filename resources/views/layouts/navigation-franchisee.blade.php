<nav class="navbar" data-navbar>
    <div class="navbar-shell">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisee.dashboard') }}" class="navbar-brand">
            <img src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg" alt="D-Sizzlers Logo" class="navbar-logo">
            <div>
                <div style="font-size: 16px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Franchisee Portal</div>
            </div>
        </a>

        <button type="button" class="navbar-toggle" data-navbar-toggle aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav" data-navbar-menu>
            <li><a href="{{ route('franchisee.dashboard') }}" class="{{ request()->routeIs('franchisee.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('franchisee.branch.dashboard') }}" class="{{ request()->routeIs('franchisee.branch.*') ? 'active' : '' }}">Manage Branch</a></li>
            <li><a href="{{ route('franchisee.cart.index') }}" class="{{ request()->routeIs('franchisee.cart.*') ? 'active' : '' }}">Cart</a></li>
            <li><a href="{{ route('franchisee.orders.index') }}" class="{{ request()->routeIs('franchisee.orders.*') ? 'active' : '' }}">Order</a></li>
            <li><a href="{{ route('franchisee.item.index') }}" class="{{ request()->routeIs('franchisee.item.*') ? 'active' : '' }}">Item</a></li>
            <li><a href="{{ route('franchisee.reports.index') }}" class="{{ request()->routeIs('franchisee.reports.*') ? 'active' : '' }}">Report</a></li>
            <li><a href="{{ route('franchisee.password') }}" class="{{ request()->routeIs('franchisee.password*') ? 'active' : '' }}">Account</a></li>
            
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger navbar-logout" style="margin: 0;">Log Out</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
