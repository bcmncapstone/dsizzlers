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
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 40px; height: 40px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h2 class="login-title">Welcome to D'Sizzlers</h2>
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

                    <button type="submit" class="login-button">LOGIN</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateFormForRole(role) {
            const roleTypeInput = document.getElementById('role_type');
            roleTypeInput.value = role;
            
            if (role) {
                // Focus on username field
                document.getElementById('username').focus();
            }
        }

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
