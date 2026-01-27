<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
            <a href="{{ route('franchisee-staff.dashboard') }}" class="text-lg font-semibold">🏠 Franchisee Staff Dashboard</a>
        </div>
        <div class="flex gap-4 items-center">
                <a href="{{ route('franchisee-staff.dashboard') }}">Dashboard</a>
                <a href="{{ route('franchisee_staff.orders.index') }}">Orders</a>
                <a href="{{ route('franchisee_staff.item.index') }}">Inventory</a>
                <a href="{{ route('franchisee-staff.password') }}">Update Password</a>
                <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-red-600">Logout</a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>
