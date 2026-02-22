<nav class="navbar">
    <div class="max-w-7xl mx-auto" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisee-staff.dashboard') }}" class="navbar-brand">
            <div class="navbar-logo">F</div>
            <div>
                <div style="font-size: 14px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 10px; opacity: 0.9;">Franchisee Portal</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav">
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
