<!DOCTYPE html>
<html>
<head>
    <title>Franchisor Staff Login</title>
</head>
<body>
    <h2>Franchisor Staff Login</h2>
    
    {{-- Show login error if any --}}
  @if($errors->has('login_error'))
    <p style="color:red;">{{ $errors->first('login_error') }}</p>
@endif

    {{-- Login form for franchisor staff --}}
    <form method="POST" action="{{ route('login.franchisorStaff') }}">
        @csrf
        <label>Username:</label>
        <input type="text" name="username" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
