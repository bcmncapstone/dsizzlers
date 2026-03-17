<!-- resources/views/accounts/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="account-create-page">
    <div class="account-create-container">
        {{-- Header --}}
        <div class="account-create-header">
            <span class="account-create-icon">🍽️</span>
            <h2 class="account-create-title">Create Franchisee Account</h2>
            <p class="account-create-subtitle">Join D-Sizzlers today</p>
        </div>

        {{-- Form Container --}}
        <div class="account-create-form">
            {{-- Success Alert --}}
            @if(session('success'))
                <div class="account-success-alert">
                    ✓ {{ session('success') }}
                </div>
            @endif

            {{-- Error Alert --}}
            @if($errors->any())
                <div class="account-error-alert">
                    <ul class="account-error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf

                {{-- Name --}}
                <div class="account-form-group">
                    <label class="account-form-label">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter your full name"
                    >
                </div>

                {{-- Email --}}
                <div class="account-form-group">
                    <label class="account-form-label">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter your email"
                    >
                </div>

                {{-- Role --}}
                <div class="account-form-group">
                    <label class="account-form-label">Account Type</label>
                    <select name="role" required class="account-form-select">
                        <option value="">-- Select Account Type --</option>
                        <option value="franchisee">Franchisee</option>
                        <option value="astaff">Admin Staff</option>
                        <option value="fstaff">Franchisee Staff</option>
                    </select>
                </div>

                {{-- Password --}}
                <div class="account-form-group">
                    <label class="account-form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="account-form-input"
                        placeholder="Enter a secure password"
                    >
                </div>

                {{-- Confirm Password --}}
                <div class="account-form-group">
                    <label class="account-form-label">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="account-form-input"
                        placeholder="Confirm your password"
                    >
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="account-submit-btn">
                    Create Account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
