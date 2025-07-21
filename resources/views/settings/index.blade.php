<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
</head>
<body>
    <h2>Settings</h2>

    <ul>
        <li><a href="{{ route('settings.password') }}">Update Password</a></li>
    </ul>

    <a href="{{ url()->previous() }}">Back to Dashboard</a>
</body>
</html>
