<form method="POST" action="{{ route('login.franchiseeStaff') }}">
    @csrf
    <input type="text" name="username" placeholder="Username">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login as Franchisee Staff</button>
</form>
