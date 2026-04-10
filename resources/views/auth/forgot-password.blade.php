<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - D Sizzlers</title>
    <link rel="icon" type="image/jpeg" href="https://res.cloudinary.com/drhw4lbzz/image/upload/v1773841657/Logo1_q5e2hk.jpg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h2 class="login-title">Forgot Password</h2>
                <p>Reset {{ $roleLabel }} account password</p>
            </div>

            @if(session('status'))
                <div class="login-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="login-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email', ['role' => $role]) }}" class="login-form">
                @csrf

                <input type="hidden" name="username" value="{{ old('username', $prefilledUsername ?? '') }}">

                <div class="login-form-group">
                    <label for="email" class="login-label">Email Address:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="login-input"
                        value="{{ old('email', $prefilledEmail ?? '') }}"
                        required
                        readonly
                        autofocus
                    >
                </div>

                @if(empty(old('email', $prefilledEmail ?? '')))
                    <p style="margin-top: -8px; margin-bottom: 12px; font-size: 14px; color: #555;">
                        Enter your username on the login page first, then click Forgot Password.
                    </p>
                @endif

                <button type="submit" class="login-button" {{ empty(old('email', $prefilledEmail ?? '')) ? 'disabled' : '' }}>Send Reset Link</button>
            </form>

            <a href="{{ route($loginRoute) }}" class="login-back-link">← Back to Login</a>
        </div>
    </div>
</body>
</html>
