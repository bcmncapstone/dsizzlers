<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Franchisor Login</title>
</head>
<body>
    <h2>Franchisor Login</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <<form method="POST" action="{{ route('admin.login.submit') }}">
    @csrf
    <label>Username:</label>
    <input type="text" name="admin_username" required><br>

    <label>Password:</label>
    <input type="password" name="admin_pass" required><br>

    <button type="submit">Login</button>
</form>

</body>
</html>
