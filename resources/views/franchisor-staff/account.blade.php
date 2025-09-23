@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Account (Franchisor Staff)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('franchisor-staff.account.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="contactNo" class="form-label">Phone Number</label>
            <input type="text" name="contactNo" id="contactNo"
                   class="form-control @error('contactNo') is-invalid @enderror"
                   value="{{ old('contactNo', $user->astaff_contactNo) }}" required>
            @error('contactNo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
