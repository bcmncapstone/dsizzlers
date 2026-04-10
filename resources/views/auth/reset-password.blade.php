<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - D Sizzlers</title>
    <link rel="icon" type="image/jpeg" href="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h2 class="login-title">Reset Password</h2>
                <p>Create a new password for {{ $roleLabel }}</p>
            </div>

            @if($errors->any())
                <div class="login-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update', ['role' => $role]) }}" class="login-form">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="login-form-group">
                    <label for="email" class="login-label">Email Address:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="login-input"
                        value="{{ old('email', $email) }}"
                        required
                        autofocus
                    >
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-label">New Password:</label>
                    <input type="password" id="password" name="password" class="login-input" required>
                </div>

                <div class="login-form-group">
                    <label for="password_confirmation" class="login-label">Confirm Password:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="login-input" required>
                </div>

                <button type="submit" class="login-button">Reset Password</button>
            </form>

            <a href="{{ route($loginRoute) }}" class="login-back-link">← Back to Login</a>
        </div>
    </div>
</body>
</html>
