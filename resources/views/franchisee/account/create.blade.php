@extends('layouts.app')

@section('content')
<div class="account-create-page">
    <div class="account-create-container">
        {{-- Header --}}
        <div class="account-create-header">
            <h2 class="account-create-title">Create Staff Account</h2>
            <p class="account-create-subtitle">Add a new staff member to your team</p>
        </div>

        {{-- Form Container --}}
        <div class="account-create-form">
            {{-- Success Alert --}}
            @if(session('success'))
                <div class="account-success-alert js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
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
            <form method="POST" action="{{ route('account.store') }}">
                @csrf

                {{-- First Name --}}
                <div class="account-form-group">
                    <label class="account-form-label">First Name *</label>
                    <input
                        type="text"
                        name="fname"
                        value="{{ old('fname') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter first name"
                    >
                </div>

                {{-- Last Name --}}
                <div class="account-form-group">
                    <label class="account-form-label">Last Name *</label>
                    <input
                        type="text"
                        name="lname"
                        value="{{ old('lname') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter last name"
                    >
                </div>

                {{-- Contact Number --}}
                <div class="account-form-group">
                    <label class="account-form-label">Contact Number *</label>
                    <input
                        type="text"
                        name="contact"
                        value="{{ old('contact') }}"
                        required
                        class="account-form-input"
                        placeholder="09XX XXX XXXX"
                    >
                </div>

                {{-- Email --}}
                <div class="account-form-group">
                    <label class="account-form-label">Email *</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter email address"
                    >
                </div>

                {{-- Username --}}
                <div class="account-form-group">
                    <label class="account-form-label">Username *</label>
                    <input
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        class="account-form-input"
                        placeholder="Enter username"
                    >
                </div>

                {{-- Password --}}
                <div class="account-form-group">
                    <label class="account-form-label">Password *</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="account-form-input"
                        placeholder="Enter password"
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

<script>
document.querySelectorAll('.js-flash-alert').forEach(function (el) {
    const timeout = parseInt(el.dataset.timeout || '3000', 10);

    setTimeout(function () {
        el.style.transition = 'opacity 0.4s ease';
        el.style.opacity = '0';

        setTimeout(function () {
            el.remove();
        }, 400);
    }, Number.isFinite(timeout) ? timeout : 3000);
});
</script>
@endsection
