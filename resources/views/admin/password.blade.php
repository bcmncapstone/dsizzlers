@extends('layouts.app')

@section('content')
<div class="password-page">
    <div class="password-shell">
        @if (session('success'))
            <div class="password-alert password-alert-success js-flash-alert" data-timeout="3000">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="password-alert password-alert-error">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="password-card">
            <div class="password-card-header">
                <div>
                    <h2>User Profile</h2>
                    <p>Review your current details and enter the new username or password you want to use.</p>
                </div>
            </div>

            <form action="{{ route('admin.password.update') }}" method="POST" class="password-form">
                @csrf

                <div class="password-form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="{{ old('username', auth('admin')->user()->admin_username) }}"
                        placeholder="Enter your username"
                        @error('username') aria-invalid="true" @enderror
                    >
                    <span class="password-field-note"></span>
                    @error('username')
                        <span class="password-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="password-form-grid">
                    <div class="password-form-group">
                        <label for="old_password">Current Password</label>
                        <div class="password-input-wrap">
                            <input
                                type="password"
                                id="old_password"
                                name="old_password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your current password"
                                @error('old_password') aria-invalid="true" @enderror
                            >
                            <button type="button" class="password-toggle-btn" data-target="old_password" aria-label="Show password">
                                Show
                            </button>
                        </div>
                        <span class="password-field-note"></span>
                        @error('old_password')
                            <span class="password-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="password-form-group">
                        <label for="password">New Password</label>
                        <div class="password-input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                                @error('password') aria-invalid="true" @enderror
                            >
                            <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password">
                                Show
                            </button>
                        </div>
                        <span class="password-field-note"></span>
                        @error('password')
                            <span class="password-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="password-form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="password-input-wrap">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter your new password"
                            @error('password_confirmation') aria-invalid="true" @enderror
                        >
                        <button type="button" class="password-toggle-btn" data-target="password_confirmation" aria-label="Show password">
                            Show
                        </button>
                    </div>
                    <span class="password-field-note"></span>
                    @error('password_confirmation')
                        <span class="password-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="password-actions">
                    <button type="submit" class="password-submit-btn">
                        Save Changes
                    </button>

                    <a href="{{ route('admin.dashboard') }}" class="password-secondary-link">
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    (function() {
        const alerts = document.querySelectorAll('.js-flash-alert');
        alerts.forEach(function(alert) {
            const timeout = Number(alert.dataset.timeout || 3000);
            window.setTimeout(function() {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                window.setTimeout(function() {
                    alert.remove();
                }, 300);
            }, timeout);
        });

        document.querySelectorAll('.password-toggle-btn').forEach(function(button) {
            const target = document.getElementById(button.dataset.target);
            if (!target) return;

            button.addEventListener('click', function() {
                const shouldShow = target.type === 'password';
                target.type = shouldShow ? 'text' : 'password';
                button.textContent = shouldShow ? 'Hide' : 'Show';
                button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
            });
        });

        const passwordForm = document.querySelector('.password-form');
        if (!passwordForm) return;

        passwordForm.addEventListener('submit', async function(event) {
            if (passwordForm.dataset.confirmApproved === 'true') {
                delete passwordForm.dataset.confirmApproved;
                return;
            }

            event.preventDefault();
            const confirmed = await window.showConfirmModal('Are you sure you want to update your password?', {
                confirmText: 'Save Changes',
            });

            if (confirmed) {
                passwordForm.dataset.confirmApproved = 'true';
                passwordForm.requestSubmit();
            }
        });
    })();
</script>
@endsection
