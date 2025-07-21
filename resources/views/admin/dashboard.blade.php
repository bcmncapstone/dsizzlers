{{-- resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, Admin!</h1>

    {{-- Success message if redirected from account creation --}}
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <a href="{{ route('settings.index') }}">
    <button>Settings</button>
</a>

    {{-- Add Account Creation Button --}}
    <a href="{{ route('accounts.create') }}">
        <button>Create Account</button>
    </a>

    {{-- Add Logout Button --}}
    <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
