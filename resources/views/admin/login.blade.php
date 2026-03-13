<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franchisor Login - D Sizzlers</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 40px; height: 40px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="login-title">Franchisor Login</h2>
            </div>

            <!-- Role Selector -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="login_role">Switch Role:</label>
                <select name="login_role" id="login_role" class="form-control" onchange="switchLoginRole(this.value)">
                    <option value="{{ url('/admin/login') }}" selected>Franchisor Login</option>
                    <option value="{{ url('/login/franchisee') }}">Franchisee Login</option>
                    <option value="{{ url('/login/franchisor-staff') }}">Franchisor Staff Login</option>
                    <option value="{{ url('/login/franchisee-staff') }}">Franchisee Staff Login</option>
                </select>
            </div>

            @if ($errors->any())
                <div class="login-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form">
                @csrf
                
                <div class="login-form-group">
                    <label for="admin_username" class="login-label">Username:</label>
                    <input type="text" id="admin_username" name="admin_username" class="login-input" placeholder="admin" required autofocus>
                </div>

                <div class="login-form-group">
                    <label for="admin_pass" class="login-label">Password:</label>
                    <input type="password" id="admin_pass" name="admin_pass" class="login-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>

            <a href="{{ url('/') }}" class="login-back-link">← Back to Home</a>
        </div>
    </div>

    <script>
        function switchLoginRole(loginUrl) {
            if (loginUrl) {
                window.location.href = loginUrl;
            }
        }
    </script>
</body>
</html>