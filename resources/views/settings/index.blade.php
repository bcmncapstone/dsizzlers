<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
</head>
<body>
    <h2>Settings</h2>

    <ul>
        @if(auth()->guard('franchisor_staff')->check())
            <li><a href="{{ route('franchisor-staff.password') }}">Update Password</a></li>
        @elseif(auth()->guard('franchisee_staff')->check())
            <li><a href="{{ route('franchisee-staff.password') }}">Update Password</a></li>
        @elseif(auth()->guard('franchisee')->check())
            <li><a href="{{ route('franchisee.password') }}">Update Password</a></li>
        @else
            <li><a href="{{ url('/login') }}">Login</a></li>
        @endif
    </ul>

    <a href="{{ url()->previous() }}">Back to Dashboard</a>
</body>
</html>
