<!DOCTYPE html>
<html>
<head>
    <title>Welcome - Login Selection</title>
</head>
<body>

    <!-- Page Heading -->
    <h2>Welcome to D'Sizzlers Franchise System</h2>

    <!-- Instructional Note -->
    <p>Please choose your login type below:</p>

    <!-- List of login options (plain links) -->
    <ul>
        <!-- Link for Franchisor (Admin) login -->
        <li><a href="{{ url('/admin/login') }}">Franchisor Login</a></li>

        <!-- Link for Franchisee login -->
        <li><a href="{{ url('/login/franchisee') }}">Franchisee Login</a></li>

        <!-- Link for Franchisor Staff login -->
        <li><a href="{{ url('/login/franchisor-staff') }}">Franchisor Staff Login</a></li>

        <!-- Link for Franchisee Staff login -->
        <li><a href="{{ url('/login/franchisee-staff') }}">Franchisee Staff Login</a></li>
    </ul>

</body>
</html>
