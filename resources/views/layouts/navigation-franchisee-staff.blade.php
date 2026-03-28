<nav class="navbar" data-navbar>
    <div class="navbar-shell">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisee-staff.dashboard') }}" class="navbar-brand">
            <img src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg" alt="D-Sizzlers Logo" class="navbar-logo">
            <div>
                <div style="font-size: 14px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 10px; opacity: 0.9;">Franchisee Portal</div>
            </div>
        </a>

        <button type="button" class="navbar-toggle" data-navbar-toggle aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav" data-navbar-menu>
            <li><a href="{{ route('franchisee-staff.dashboard') }}" class="{{ request()->routeIs('franchisee-staff.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('franchisee_staff.orders.index') }}" class="{{ request()->routeIs('franchisee_staff.orders.*') ? 'active' : '' }}">Orders</a></li>
             <li><a href="{{ route('franchisee_staff.item.index') }}" class="{{ request()->routeIs('franchisee_staff.item.*') ? 'active' : '' }}">Item</a></li>
            <li><a href="{{ route('franchisee-staff.stock.index') }}" class="{{ request()->routeIs('franchisee-staff.stock.*') ? 'active' : '' }}">Item Stock</a></li>
            <li><a href="{{ route('franchisee-staff.password') }}" class="{{ request()->routeIs('franchisee-staff.password*') ? 'active' : '' }}">Update Password</a></li>
            <li><a href="{{ route('franchisee-staff.account.show') }}" class="{{ request()->routeIs('franchisee-staff.account.*') ? 'active' : '' }}">Edit Profile</a></li>

            
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger navbar-logout" style="margin: 0;">Log Out</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
