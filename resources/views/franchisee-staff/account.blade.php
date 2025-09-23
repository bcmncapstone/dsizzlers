@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Account (Franchisee Staff)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('franchisee-staff.account.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="contactNo" class="form-label">Phone Number</label>
            <input type="text" name="contactNo" id="contactNo"
                   class="form-control @error('contactNo') is-invalid @enderror"
                   value="{{ old('contactNo', $user->fstaff_contactNo) }}" required>
            @error('contactNo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <a href="{{ route('settings.password') }}" class="btn btn-primary">Update Password</a>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
