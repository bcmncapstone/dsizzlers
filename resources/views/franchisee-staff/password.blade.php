@extends('layouts.franchisee-staff')

@section('content')
<div class="password-page">
    <div class="password-wrapper">
        {{-- Header Section --}}
        <div class="password-header">
            <h1>Update Profile</h1>
            <p>Secure your account with a new password and username</p>
        </div>

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="password-alert password-alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="password-alert password-alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Password Form --}}
        <form action="{{ route('franchisee-staff.password.update') }}" method="POST" class="password-form">
            @csrf

            <div class="password-form-group">
                <label for="username">Username</label>
                <input 
                    type="text"
                    id="username"
                    name="username"
                    required
                    value="{{ old('username', auth('franchisee_staff')->user()->fstaff_username) }}"
                    placeholder="Enter your username"
                >
            </div>

            <div class="password-form-group">
                <label for="password">New Password</label>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    required
                    placeholder="Enter your new password"
                    @error('password') 
                        style="border-color: #f44336;" 
                    @enderror
                >
                @error('password')
                    <span style="color: #f44336; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="password-form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation"
                    name="password_confirmation" 
                    required
                    placeholder="Confirm your new password"
                    @error('password_confirmation') 
                        style="border-color: #f44336;" 
                    @enderror
                >
                @error('password_confirmation')
                    <span style="color: #f44336; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="password-submit-btn">
                Update Profile
            </button>
        </form>

        {{-- Back Link --}}
        <div class="password-back-link">
            <a href="{{ route('franchisee-staff.dashboard') }}">← Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
    // Auto-hide alert messages after 3 seconds
    const alerts = document.querySelectorAll('.password-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 3000);
    });

 const passwordForm = document.querySelector('.password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(event) {
            const confirmed = window.confirm('Are you sure you want to update your password?');

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }
</script>
@endsection