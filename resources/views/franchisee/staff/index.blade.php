@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Page Header -->
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>Staff Account</h1>
                <p>Manage your franchisee staff members</p>
            </div>
            <a href="{{ route('account.create') }}" class="btn btn-primary">+ Create Staff Account</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                <strong>!</strong> {{ session('error') }}
            </div>
        @endif

        <!-- Franchisee Staff -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Active Staff Members</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeStaff ?? collect() as $s)
                            <tr>
                                <td>{{ $s->fstaff_id }}</td>
                                <td>{{ $s->fstaff_fname }} {{ $s->fstaff_lname }}</td>
                                <td>{{ $s->fstaff_contactNo }}</td>
                                <td>{{ $s->fstaff_email ?? '—' }}</td>
                                <td>
                                    @php
                                        $isArchived = false;
                                    @endphp
                                    <span class="badge {{ $isArchived ? 'badge-danger' : 'badge-success' }}">{{ $isArchived ? 'Archived' : 'Active' }}</span>
                                </td>
                                <td>
                                    <button class="table-action-btn table-action-edit" onclick="viewStaffDetails('{{ $s->fstaff_fname }} {{ $s->fstaff_lname }}', '{{ $s->fstaff_email }}', '{{ $s->fstaff_contactNo }}')">
                                        View
                                    </button>
                                    <form action="{{ route('franchisee.staff.archive', $s->fstaff_id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Archive this staff account? They will lose access immediately.');">
                                        @csrf
                                        <button type="submit" class="table-action-btn" style="background:#dc2626; color:#fff;">Archive</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">No active staff accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-section" style="margin-top: 20px;">
            <div class="table-section-header">
                <h2>Archived Staff Accounts</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedStaff ?? collect() as $s)
                            <tr>
                                <td>{{ $s->fstaff_id }}</td>
                                <td>{{ $s->fstaff_fname }} {{ $s->fstaff_lname }}</td>
                                <td>{{ $s->fstaff_contactNo }}</td>
                                <td>{{ $s->fstaff_email ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-danger">Archived</span>
                                </td>
                                <td>
                                    <button class="table-action-btn table-action-edit" onclick="viewStaffDetails('{{ $s->fstaff_fname }} {{ $s->fstaff_lname }}', '{{ $s->fstaff_email }}', '{{ $s->fstaff_contactNo }}')">
                                        View
                                    </button>
                                    <form action="{{ route('franchisee.staff.restore', $s->fstaff_id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Restore this staff account? They will be able to log in again.');">
                                        @csrf
                                        <button type="submit" class="table-action-btn" style="background:#16a34a; color:#fff;">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">No archived staff accounts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

function viewStaffDetails(name, email, contact) {
    alert('Staff: ' + name + '\nEmail: ' + email + '\nContact: ' + contact);
}
</script>

@endsection
