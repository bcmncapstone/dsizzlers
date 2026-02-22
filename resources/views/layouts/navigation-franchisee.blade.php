<nav class="navbar">
    <div class="max-w-7xl mx-auto" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisee.dashboard') }}" class="navbar-brand">
            <div class="navbar-logo">F</div>
            <div>
                <div style="font-size: 16px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Franchisee Portal</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav">
            <li><a href="{{ route('franchisee.dashboard') }}" class="{{ request()->routeIs('franchisee.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('franchisee.branch.dashboard') }}" class="{{ request()->routeIs('franchisee.branch.*') ? 'active' : '' }}">Manage Branch</a></li>
            <li><a href="{{ route('franchisee.cart.index') }}" class="{{ request()->routeIs('franchisee.cart.*') ? 'active' : '' }}">Cart</a></li>
            <li><a href="{{ route('franchisee.orders.index') }}" class="{{ request()->routeIs('franchisee.orders.*') ? 'active' : '' }}">Orders</a></li>
            <li><a href="{{ route('franchisee.item.index') }}" class="{{ request()->routeIs('franchisee.item.*') ? 'active' : '' }}">Items</a></li>
            <li><a href="{{ route('franchisee.reports.index') }}" class="{{ request()->routeIs('franchisee.reports.*') ? 'active' : '' }}">Reports</a></li>
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
