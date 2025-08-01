@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Update Password</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('franchisor.settings.password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Update Password</button>
    </form>
</div>
@endsection
