<nav class="navbar">
    <div class="max-w-7xl mx-auto" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <!-- Logo and Brand -->
        <a href="{{ route('franchisor-staff.dashboard') }}" class="navbar-brand">
            <div class="navbar-logo">D</div>
            <div>
                <div style="font-size: 16px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Franchisor Staff Portal</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav">
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
