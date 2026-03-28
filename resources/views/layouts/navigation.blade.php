<nav class="navbar" data-navbar>
    <div class="navbar-shell">
        <!-- Logo and Brand -->
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <img src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg" alt="D-Sizzlers Logo" class="navbar-logo">
            <div>
                <div style="font-size: 16px; font-weight: 700;">D SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Admin Portal</div>
            </div>
        </a>

        <button type="button" class="navbar-toggle" data-navbar-toggle aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Desktop Navigation -->
        <ul class="navbar-nav" data-navbar-menu>
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.index') ? 'active' : '' }}">Create Account</a></li>
            <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.index') ? 'active' : '' }}">Contract</a></li>
            <li><a href="{{ route('admin.stock.index') }}" class="{{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">Stock</a></li>
            <li><a href="{{ route('admin.manageOrder.index') }}" class="{{ request()->routeIs('admin.manageOrder.*') ? 'active' : '' }}">Order</a></li>
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
