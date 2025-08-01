<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
            <a href="{{ route('franchisor-staff.dashboard') }}" class="text-lg font-semibold">Franchisor Staff Dashboard</a>
        </div>
        <div class="flex gap-4 items-center">
            <a href="{{ route('branches.index') }}">Branches</a>
            <a href="{{ route('items.index') }}">Items</a>
            <a href="{{ route('settings.password') }}">Update Password</a>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-red-600"> Logout</a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>
