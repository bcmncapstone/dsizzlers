<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'D-Sizzlers') }}</title>
        <link rel="icon" type="image/jpeg" href="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            window.currentUserId = @json(auth()->id());
        </script>
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: #F5F5F5;">
        @if(auth()->guard('admin')->check())
            @include('layouts.navigation')
        @elseif(auth()->guard('franchisee')->check())
            @include('layouts.navigation-franchisee')
        @elseif(auth()->guard('franchisor_staff')->check())
            @include('layouts.navigation-franchisor-staff')
        @elseif(auth()->guard('franchisee_staff')->check())
            @include('layouts.navigation-franchisee-staff')
        @else
            @include('layouts.navigation')
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Floating Messages Button and Modal -->
        @if(auth()->guard('franchisee')->check() || auth()->guard('admin')->check())
        <button id="openChatModalBtn" class="floating-messages-btn" title="Messages" type="button" style="z-index:9999;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4.913 2.658c2.075-.27 4.19-.408 6.337-.408 2.147 0 4.262.139 6.337.408 1.922.25 3.291 1.861 3.405 3.727a4.403 4.403 0 0 0-1.032-.211 50.89 50.89 0 0 0-8.42 0c-2.358.196-4.04 2.19-4.04 4.434v4.286a4.47 4.47 0 0 0 2.433 3.984L7.28 21.53A.75.75 0 0 1 6 21v-4.03a48.527 48.527 0 0 1-1.087-.128C2.905 16.58 1.5 14.833 1.5 12.862V6.638c0-1.97 1.405-3.718 3.413-3.979Z" />
                <path d="M15.75 7.5c-1.376 0-2.739.057-4.086.169C10.124 7.797 9 9.103 9 10.609v4.285c0 1.507 1.128 2.814 2.67 2.94 1.243.102 2.5.157 3.768.165l2.782 2.781a.75.75 0 0 0 1.28-.53v-2.39l.33-.026c1.542-.125 2.67-1.433 2.67-2.94v-4.286c0-1.505-1.125-2.811-2.664-2.94A49.392 49.392 0 0 0 15.75 7.5Z" />
            </svg>
        </button>
        <div id="chatModal" style="display:none; position:fixed; bottom:100px; right:32px; width:420px; max-width:95vw; max-height:80vh; background:#fff; border-radius:22px; box-shadow:0 22px 60px rgba(15, 23, 42, 0.18); z-index:10000; overflow:hidden; border:1px solid rgba(255, 91, 36, 0.12);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 18px; background:linear-gradient(135deg, #ff6a2b 0%, #ff4f18 100%); color:#fff; border-bottom:1px solid rgba(255,255,255,0.18);">
                <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                    <div id="chatModalHeaderLeft" style="display:flex; align-items:center; gap:10px;"></div>
                    <div id="chatModalTitle" style="font-size:15px; font-weight:700; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Messages</div>
                </div>
                <button
                    onclick="document.getElementById('chatModal').style.display='none'"
                    type="button"
                    aria-label="Close messages"
                    style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; flex-shrink:0; border:none; border-radius:999px; background:rgba(255,255,255,0.18); color:#fff; font-size:22px; line-height:1; cursor:pointer; box-shadow:0 8px 18px rgba(0,0,0,0.12); transition:background 0.2s ease, transform 0.2s ease;"
                    onmouseover="this.style.background='rgba(255,255,255,0.28)'; this.style.transform='scale(1.05)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.18)'; this.style.transform='scale(1)'"
                >&times;</button>
            </div>
            <div id="chatModalContent" style="height:calc(80vh - 68px); overflow:auto; background:linear-gradient(180deg, #fff7f2 0%, #ffffff 120%);"></div>
        </div>
        @include('communication._floating-chat-modal-script')
        @endif
        @stack('scripts')
        <!-- Footer -->
        <footer class="w-full bg-gray-800 text-white text-center py-4 mt-8">
            <div class="container mx-auto">
                &copy; {{ date('Y') }} DSizzlers. All rights reserved.
            </div>
        </footer>
    </body>
</html>
