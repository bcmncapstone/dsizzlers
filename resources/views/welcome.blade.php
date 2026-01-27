<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Login Selection</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="welcome-page">
    <div class="welcome-box">
        <div class="welcome-logo-circle">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="8" r="4" fill="white"/>
                <path d="M6 21C6 17.134 8.686 14 12 14C15.314 14 18 17.134 18 21" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <h2 class="welcome-heading">Welcome to D'Sizzlers Franchise System</h2>
        <p class="welcome-instruction">Please choose your login type below:</p>

        <ul class="welcome-login-list">
            <li><a href="{{ url('/admin/login') }}" class="welcome-login-link">Franchisor Login</a></li>
            <li><a href="{{ url('/login/franchisee') }}" class="welcome-login-link">Franchisee Login</a></li>
            <li><a href="{{ url('/login/franchisor-staff') }}" class="welcome-login-link">Franchisor Staff Login</a></li>
            <li><a href="{{ url('/login/franchisee-staff') }}" class="welcome-login-link">Franchisee Staff Login</a></li>
        </ul>
    </div>
</div>

</body>
</html>