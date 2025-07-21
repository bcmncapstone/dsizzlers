<!DOCTYPE html>
<html>
<head>
    <title>Franchisee Login</title>
</head>
<body>
    <h2>Franchisee Login</h2>
    
    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('login.franchisee') }}">
        @csrf
        <label>Username:</label>
        <input type="text" name="username" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
