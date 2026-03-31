<nav class="navbar" data-navbar>
    <div class="navbar-shell">
        <!-- Logo and Brand -->
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <img src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg" alt="D-Sizzlers Logo" class="navbar-logo">
            <div>
                <div style="font-size: 16px; font-weight: 700;">D SIZZLERS</div>
                <div style="font-size: 11px; opacity: 0.9;">Franchisor Portal</div>
            </div>
        </a>

        <button type="button" class="navbar-toggle block md:hidden" data-navbar-toggle aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Menu -->
        <ul class="navbar-nav flex-col md:flex-row md:flex md:static absolute right-0 top-full bg-white md:bg-transparent w-full md:w-auto shadow md:shadow-none z-50 transition-all duration-200 ease-in-out hidden md:flex" data-navbar-menu>
                    <style>
                    @media (max-width: 1024px) {
                        .navbar-nav {
                            display: none;
                        }
                        .navbar-nav.is-open {
                            display: flex !important;
                        }
                        .navbar-nav.is-open a {
                            color: #FF5722 !important; /* dsizzlers orange */
                        }
                        .navbar-nav.is-open a.active {
                            color: #C41C00 !important; /* highlight active link */
                        }
                    }
                    </style>
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
