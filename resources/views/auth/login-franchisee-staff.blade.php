<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franchisee Staff Login - D Sizzlers</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 40px; height: 40px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h2 class="login-title">Franchisee Staff Login</h2>
            </div>

            <!-- Role Selector -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="login_role">Switch Role:</label>
                <select name="login_role" id="login_role" class="form-control" onchange="switchLoginRole(this.value)">
                    <option value="{{ url('/admin/login') }}">Franchisor Login</option>
                    <option value="{{ url('/login/franchisee') }}">Franchisee Login</option>
                    <option value="{{ url('/login/franchisor-staff') }}">Franchisor Staff Login</option>
                    <option value="{{ url('/login/franchisee-staff') }}" selected>Franchisee Staff Login</option>
                </select>
            </div>

            @if(session('error'))
                <div class="login-error">{{ session('error') }}</div>
            @endif

            @if($errors->has('login_error'))
                <div class="login-error">{{ $errors->first('login_error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.franchiseeStaff') }}" class="login-form">
                @csrf
                
                <div class="login-form-group">
                    <label for="username" class="login-label">Username:</label>
                    <input type="text" id="username" name="username" class="login-input" required autofocus>
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-label">Password:</label>
                    <input type="password" id="password" name="password" class="login-input" required>
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