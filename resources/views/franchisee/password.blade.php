@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Update Franchisee Password</h2>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('franchisee.password.update') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>
@endsection
