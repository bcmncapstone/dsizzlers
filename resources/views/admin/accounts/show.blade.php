@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="form-container">

        @if(session('success'))
            <div class="alert alert-success js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        @php
            if ($type === 'franchisee') {
                $role        = 'Franchisee';
                $name        = $account->franchisee_name;
                $username    = $account->franchisee_username;
                $contact     = $account->franchisee_contactNo;
                $status      = $account->franchisee_status;
                $email       = $account->franchisee_email ?? null;
                $address     = $account->franchisee_address ?? null;
            } else {
                $role        = 'Franchisor Staff';
                $name        = $account->astaff_fname . ' ' . $account->astaff_lname;
                $username    = $account->astaff_username;
                $contact     = $account->astaff_contactNo;
                $status      = $account->astaff_status;
                $email       = null;
                $address     = null;
            }
        @endphp

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2>Account Details</h2>
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← All Accounts</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg" style="padding: 1.5rem;">
            <dl style="display:grid; grid-template-columns: 160px 1fr; gap: 0.75rem 1rem;">
                <dt class="form-label" style="font-weight:600;">Role</dt>
                <dd>{{ $role }}</dd>

                <dt class="form-label" style="font-weight:600;">Name</dt>
                <dd>{{ $name }}</dd>

                <dt class="form-label" style="font-weight:600;">Contact</dt>
                <dd>{{ $contact }}</dd>

                @if($email)
                    <dt class="form-label" style="font-weight:600;">Email</dt>
                    <dd>{{ $email }}</dd>
                @endif

                @if($address)
                    <dt class="form-label" style="font-weight:600;">Address</dt>
                    <dd>{{ $address }}</dd>
                @endif

                <dt class="form-label" style="font-weight:600;">Status</dt>
                <dd>
                    <span class="badge {{ $status === 'Active' ? 'badge-success' : 'badge-danger' }}">{{ $status }}</span>
                </dd>
            </dl>
        </div>

        <div style="margin-top: 1.5rem; display:flex; gap: 0.75rem;">
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← All Accounts</a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">+ Create Another</a>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.js-flash-alert').forEach(function (el) {
    var timeout = parseInt(el.dataset.timeout, 10) || 3000;
    setTimeout(function () {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity = '0';
        setTimeout(function () { el.remove(); }, 500);
    }, timeout);
});
</script>

@endsection
