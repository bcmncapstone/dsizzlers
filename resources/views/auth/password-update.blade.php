@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Update Your Password</h2>
    <form method="POST" action="{{ route('password.update.submit') }}">
        @csrf
        <div>
            <label>New Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Update Password</button>
    </form>
</div>
@endsection
