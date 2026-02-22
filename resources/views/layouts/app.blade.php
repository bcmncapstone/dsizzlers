<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'D-Sizzlers') }}</title>

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
    </body>
</html>
