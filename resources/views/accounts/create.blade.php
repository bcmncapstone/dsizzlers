<!-- resources/views/accounts/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Account</h2>

    @if (session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form action="{{ route('accounts.store') }}" method="POST">
        @csrf

        <div>
            <label>Name:</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label>Role:</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="franchisee">Franchisee</option>
                <option value="astaff">Admin Staff</option>
                <option value="fstaff">Franchisee Staff</option>
            </select>
        </div>

        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirm Password:</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Create Account</button>
    </form>
</div>
@endsection
