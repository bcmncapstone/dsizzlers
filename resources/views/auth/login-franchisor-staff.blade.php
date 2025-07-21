<form method="POST" action="{{ route('login.franchisorStaff') }}">
    @csrf
    <input type="text" name="username" placeholder="Username">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login as Franchisor Staff</button>
</form>
