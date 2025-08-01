<!-- resources/views/layouts/navigation-franchisee.blade.php -->
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
        <div>
            <a href="{{ route('franchisee.dashboard') }}" class="text-lg font-semibold">Franchisee Dashboard</a>
        </div>
          <div class="flex gap-4 items-center">
            <a href="{{ route('orders.index') }}">Orders</a>
            <a href="{{ route('inventory.index') }}">Inventory</a>
            <a href="{{ route('settings.password') }}">Update Password</a>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-red-600">Logout</a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>
