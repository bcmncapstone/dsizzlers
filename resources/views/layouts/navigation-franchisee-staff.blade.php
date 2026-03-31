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
            <li><a href="{{ route('franchisee-staff.dashboard') }}" class="{{ request()->routeIs('franchisee-staff.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('franchisee_staff.orders.index') }}" class="{{ request()->routeIs('franchisee_staff.orders.*') ? 'active' : '' }}">Order</a></li>
             <li><a href="{{ route('franchisee_staff.item.index') }}" class="{{ request()->routeIs('franchisee_staff.item.*') ? 'active' : '' }}">Item</a></li>
            <li><a href="{{ route('franchisee-staff.stock.index') }}" class="{{ request()->routeIs('franchisee-staff.stock.*') ? 'active' : '' }}">Stock</a></li>
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
