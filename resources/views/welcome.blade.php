<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Login Selection</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="login-logo-circle">
                    <img
                        src="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg"
                        alt="D-Sizzlers Logo"
                        class="login-logo"
                    >
                </div>
                <h2 class="login-title">Welcome to D Sizzlers!</h2>
            </div>

            <!-- Role Selector -->
            <div class="login-form-group">
                <label class="login-label" for="login_role">Select Your Role</label>
                <select name="login_role" id="login_role" class="login-input" onchange="updateFormForRole(this.value)">
                    <option value="">-- Choose your login role --</option>
                    <option value="franchisor"> Franchisor</option>
                    <option value="franchisee">Franchisee</option>
                    <option value="franchisor-staff">Franchisor Staff</option>
                    <option value="franchisee-staff">Franchisee Staff</option>
                </select>
            </div>

            @if(session('error'))
                <div class="login-error">{{ session('error') }}</div>
            @endif

            @if($errors->has('login_error'))
                <div class="login-error">{{ $errors->first('login_error') }}</div>
            @endif

            <!-- Login Form -->
            <div id="loginFormContainer">
                <div style="text-align: center; margin: 20px 0; padding-bottom: 20px; border-bottom: 2px solid var(--dsizzlers-gray); font-size: 14px; color: var(--dsizzlers-gray-dark);">
                    <strong>Login with your credentials</strong>
                </div>
                
                <form method="POST" action="{{ route('login.unified') }}" class="login-form" id="loginForm">
                    @csrf
                    <input type="hidden" id="role_type" name="role_type" value="">
                    
                    <div class="login-form-group">
                        <label for="username" class="login-label">Username:</label>
                        <input type="text" id="username" name="username" class="login-input" placeholder="Enter your username" required autofocus>
                    </div>

                    <div class="login-form-group">
                        <label for="password" class="login-label">Password:</label>
                        <input type="password" id="password" name="password" class="login-input" placeholder="Enter your password" required>
                    </div>

                    <div style="text-align: center; margin-bottom: 16px;">
                        <a href="#" id="forgot-link" class="login-back-link" style="margin: 0; display: none;">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-button">LOGIN</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const forgotRoutes = {
            'franchisor':       '{{ route('password.request', ['role' => 'admin']) }}',
            'franchisee':       '{{ route('password.request', ['role' => 'franchisee']) }}',
            'franchisor-staff': '{{ route('password.request', ['role' => 'franchisor-staff']) }}',
            'franchisee-staff': '{{ route('password.request', ['role' => 'franchisee-staff']) }}',
        };

        function updateFormForRole(role) {
            const roleTypeInput = document.getElementById('role_type');
            roleTypeInput.value = role;

            const forgotLink = document.getElementById('forgot-link');
            if (role && forgotRoutes[role]) {
                forgotLink.href = forgotRoutes[role];
                forgotLink.style.display = 'inline';
            } else {
                forgotLink.style.display = 'none';
            }

            if (role) {
                document.getElementById('username').focus();
            }
        }

        document.getElementById('forgot-link').addEventListener('click', function (e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const base = this.getAttribute('href');
            window.location.href = username ? `${base}?username=${encodeURIComponent(username)}` : base;
        });

        // Prevent submission if role is not selected
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const role = document.getElementById('role_type').value;
            if (!role) {
                e.preventDefault();
                alert('Please select a login role first');
            }
        });

        // Auto-hide error messages after 3 seconds
        const errorMessages = document.querySelectorAll('.login-error');
        errorMessages.forEach(function(errorMsg) {
            setTimeout(function() {
                errorMsg.style.transition = 'opacity 0.5s ease-out';
                errorMsg.style.opacity = '0';
                setTimeout(function() {
                    errorMsg.remove();
                }, 500);
            }, 3000);
        });
    </script>
</body>
</html>
