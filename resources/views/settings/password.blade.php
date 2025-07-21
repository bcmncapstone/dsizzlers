<!DOCTYPE html>
<html>
<head>
    <title>Update Password</title>
</head>
<body>
    <h2>Update Password</h2>

    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div style="color:red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.password.update') }}">
        @csrf
        <label>New Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="password_confirmation" required><br><br>

        <button type="submit">Update Password</button>
    </form>

    <br>
    <a href="{{ route('settings.index') }}">Back to Settings</a>
</body>
</html>
