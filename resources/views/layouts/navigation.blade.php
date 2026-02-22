<nav class="navbar">
    <div class="max-w-7xl mx-auto" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <!-- Logo and Brand -->
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <div class="navbar-logo">D</div>
            <div>
                <div style="font-size: 16px; font-weight: 700;">D-SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Admin Portal</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav">
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('accounts.create') }}" class="{{ request()->routeIs('accounts.create') ? 'active' : '' }}">Create Account</a></li>
            <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.index') ? 'active' : '' }}">Branches</a></li>
            <li><a href="{{ route('admin.stock.index') }}" class="{{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">Stock</a></li>
            <li><a href="{{ route('admin.manageOrder.index') }}" class="{{ request()->routeIs('admin.manageOrder.*') ? 'active' : '' }}">Orders</a></li>
            <li><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Reports</a></li>
            
            <li>
                <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger navbar-logout" style="margin: 0;">Log Out</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
