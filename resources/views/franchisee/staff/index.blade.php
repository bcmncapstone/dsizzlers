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
                                    <button class="table-action-btn table-action-edit" onclick="viewStaffDetails(@js(trim($s->fstaff_fname . ' ' . $s->fstaff_lname)), @js($s->fstaff_email ?? 'N/A'), @js($s->fstaff_contactNo ?? 'N/A'))">
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
                                    <button class="table-action-btn table-action-edit" onclick="viewStaffDetails(@js(trim($s->fstaff_fname . ' ' . $s->fstaff_lname)), @js($s->fstaff_email ?? 'N/A'), @js($s->fstaff_contactNo ?? 'N/A'))">
                                        View
                                    </button>
                                    <form action="{{ route('franchisee.staff.restore', $s->fstaff_id) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Restore this staff account? They will be able to log in again.');">
                                        @csrf
                                        <button type="submit" class="table-action-btn" style="background:#A8DB93; color:#fff;">Restore</button>
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

<div id="staff-details-modal" class="staff-details-modal" aria-hidden="true">
    <div class="staff-details-backdrop" onclick="closeStaffDetails()"></div>
    <div class="staff-details-dialog" role="dialog" aria-modal="true" aria-labelledby="staff-details-title">
        <div class="staff-details-header">
            <div>
                <p class="staff-details-eyebrow">Staff Details</p>
                <h3 id="staff-details-title">Staff Account</h3>
            </div>
            <button type="button" class="staff-details-close" onclick="closeStaffDetails()" aria-label="Close staff details">&times;</button>
        </div>
        <div class="staff-details-body">
            <div class="staff-detail-row">
                <span>Name</span>
                <strong id="staff-detail-name"></strong>
            </div>
            <div class="staff-detail-row">
                <span>Email</span>
                <strong id="staff-detail-email"></strong>
            </div>
            <div class="staff-detail-row">
                <span>Contact</span>
                <strong id="staff-detail-contact"></strong>
            </div>
        </div>
        <div class="staff-details-footer">
            <button type="button" class="btn btn-primary" onclick="closeStaffDetails()">Close</button>
        </div>
    </div>
</div>

<style>
.staff-details-modal {
    align-items: center;
    display: none;
    inset: 0;
    justify-content: center;
    padding: 24px;
    position: fixed;
    z-index: 10050;
}

.staff-details-modal.is-open {
    display: flex;
}

.staff-details-backdrop {
    background: rgba(17, 24, 39, 0.55);
    inset: 0;
    position: absolute;
}

.staff-details-dialog {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
    max-width: 460px;
    overflow: hidden;
    position: relative;
    width: min(100%, 460px);
}

.staff-details-header {
    align-items: flex-start;
    background: #ff8f5f;
    color: #332216;
    display: flex;
    justify-content: space-between;
    padding: 22px 24px;
}

.staff-details-eyebrow {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0;
    margin: 0 0 4px;
    text-transform: uppercase;
}

.staff-details-header h3 {
    font-size: 26px;
    margin: 0;
}

.staff-details-close {
    align-items: center;
    background: rgba(255, 255, 255, 0.34);
    border: 0;
    border-radius: 999px;
    color: #332216;
    cursor: pointer;
    display: inline-flex;
    font-size: 26px;
    height: 36px;
    justify-content: center;
    line-height: 1;
    width: 36px;
}

.staff-details-body {
    display: grid;
    gap: 12px;
    padding: 24px;
}

.staff-detail-row {
    background: #fff4e3;
    border: 1px solid #ffd6bd;
    border-radius: 8px;
    display: grid;
    gap: 5px;
    padding: 14px 16px;
}

.staff-detail-row span {
    color: #7c4a2b;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}

.staff-detail-row strong {
    color: #111827;
    font-size: 16px;
    overflow-wrap: anywhere;
}

.staff-details-footer {
    display: flex;
    justify-content: flex-end;
    padding: 0 24px 24px;
}
</style>

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
    document.getElementById('staff-detail-name').textContent = name || 'N/A';
    document.getElementById('staff-detail-email').textContent = email || 'N/A';
    document.getElementById('staff-detail-contact').textContent = contact || 'N/A';

    const modal = document.getElementById('staff-details-modal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeStaffDetails() {
    const modal = document.getElementById('staff-details-modal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeStaffDetails();
    }
});
</script>

@endsection
