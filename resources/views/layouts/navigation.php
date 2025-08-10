<!-- resources/views/layouts/navigation.blade.php -->
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('admin.dashboard') }}">
                        <strong>Admin Dashboard</strong>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('accounts.create')" :active="request()->routeIs('accounts.create')">
                        Create Account
                    </x-nav-link>

                    <x-nav-link :href="route('admin.branches.index')" :active="request()->routeIs('admin.branches.index')">
                        Branches
                    </x-nav-link>
                </div>
            </div>

            <!-- Logout Button -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <x-nav-link href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
