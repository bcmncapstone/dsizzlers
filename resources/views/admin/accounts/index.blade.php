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
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">+ Create Account</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
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

        <!-- Franchisor Staff -->
        <div class="table-section">
            <div class="table-section-header">
                <h2>Franchisor Staff</h2>
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
                        @forelse($franchisorStaff as $s)
                            <tr>
                                <td>{{ $s->astaff_id }}</td>
                                <td>{{ $s->astaff_fname }} {{ $s->astaff_lname }}</td>
                                <td>{{ $s->astaff_contactNo }}</td>
                                <td>
                                    @php $st = $s->astaff_status; @endphp
                                    <span class="badge {{ $st === 'Active' ? 'badge-success' : 'badge-danger' }}">{{ $st }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('accounts.show', ['type' => 'franchisor_staff', 'id' => $s->astaff_id]) }}" class="table-action-btn table-action-edit">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="table-empty">No franchisor staff accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
