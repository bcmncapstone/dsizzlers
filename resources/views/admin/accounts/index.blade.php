@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Page Header -->
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>Accounts</h1>
                <p>Manage franchisee and franchisor staff accounts</p>
            </div>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">+ Add Account</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success js-flash-alert" data-timeout="{{ (int) session('flash_timeout', 3000) }}">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Franchisees -->
        <div class="table-section" style="margin-bottom: 2rem;">
            <div class="table-section-header">
                <h2>Franchisees</h2>
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
                        @forelse($franchisees as $f)
                            <tr>
                                <td>{{ $f->franchisee_id }}</td>
                                <td>{{ $f->franchisee_name }}</td>
                                <td>{{ $f->franchisee_contactNo }}</td>
                                <td>{{ $f->franchisee_email ?? '—' }}</td>
                                <td>
                                    @php $s = $f->franchisee_status; @endphp
                                    <span class="badge {{ $s === 'Active' ? 'badge-success' : 'badge-danger' }}">{{ $s }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('accounts.show', ['type' => 'franchisee', 'id' => $f->franchisee_id]) }}" class="table-action-btn table-action-edit">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">No franchisee accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Franchisor Staff - Active -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Active Franchisor Staff</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $activeFranchisorStaff = ($franchisorStaff ?? collect())->where('astaff_status', 'Active');
                        @endphp
                        @forelse($activeFranchisorStaff as $s)
                            <tr>
                                <td>{{ $s->astaff_id }}</td>
                                <td>{{ $s->astaff_fname }} {{ $s->astaff_lname }}</td>
                                <td>{{ $s->astaff_contactNo }}</td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="{{ route('accounts.show', ['type' => 'franchisor_staff', 'id' => $s->astaff_id]) }}" class="table-action-btn table-action-edit">View</a>
                                    <form action="{{ route('admin.franchisor-staff.archive', $s->astaff_id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Archive this staff account? They will lose access immediately.');">
                                        @csrf
                                        <button type="submit" class="table-action-btn" style="background:#dc2626; color:#fff;">Archive</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="table-empty">No active franchisor staff accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Franchisor Staff - Archived -->
        <div class="table-section" style="margin-top: 20px;">
            <div class="table-section-header">
                <h2>Archived Franchisor Staff</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $archivedFranchisorStaff = ($franchisorStaff ?? collect())->where('astaff_status', '!=', 'Active');
                        @endphp
                        @forelse($archivedFranchisorStaff as $s)
                            <tr>
                                <td>{{ $s->astaff_id }}</td>
                                <td>{{ $s->astaff_fname }} {{ $s->astaff_lname }}</td>
                                <td>{{ $s->astaff_contactNo }}</td>
                                <td><span class="badge badge-danger">Archived</span></td>
                                <td>
                                    <a href="{{ route('accounts.show', ['type' => 'franchisor_staff', 'id' => $s->astaff_id]) }}" class="table-action-btn table-action-edit">View</a>
                                    <form action="{{ route('admin.franchisor-staff.restore', $s->astaff_id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Restore this staff account? They will be able to log in again.');">
                                        @csrf
                                        <button type="submit" class="table-action-btn" style="background:#16a34a; color:#fff;">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="table-empty">No archived franchisor staff accounts.</td></tr>
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
</script>

@endsection
