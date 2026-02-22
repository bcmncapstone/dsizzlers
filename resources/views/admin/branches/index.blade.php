@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Header -->
        <div class="dashboard-header">
            <h1>Active Branches</h1>
            <p>Manage and view all branch locations</p>
        </div>

        <!-- Search Form -->
        <div class="card" style="margin-bottom: var(--spacing-lg); border-top: none; box-shadow: none;">
            <form method="GET" action="{{ route('admin.branches.index') }}" style="display: flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search branches..." style="flex: 1;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">+ Add Branch</a>
                <a href="{{ route('admin.branches.archived') }}" class="btn btn-secondary">View Archived</a>
            </div>
        </div>

        <!-- Branches Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Branch Manager</th>
                        <th>Location</th>
                        <th>Email</th>
                        <th>Contract</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                    <tr>
                        <td><strong>{{ $branch->first_name }} {{ $branch->last_name }}</strong></td>
                        <td>{{ $branch->location }}</td>
                        <td>{{ $branch->email }}</td>
                        <td>
                            @if($branch->contract_file)
                                <a href="{{ route('admin.branches.downloadContract', $branch->branch_id) }}" target="_blank" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Preview</a>
                                <a href="{{ route('admin.branches.downloadContract', ['id' => $branch->branch_id, 'mode' => 'download']) }}" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;">Download</a>
                            @else
                                <span style="color: var(--dsizzlers-gray-dark);">No file</span>
                            @endif
                        </td>
                        <td>{{ $branch->contract_expiration }}</td>
                        <td>
                            <a href="{{ route('admin.branches.edit', $branch->branch_id) }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">✏️ Edit</a>
                            <form method="POST" action="{{ route('admin.branches.archive', $branch->branch_id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">🗄 Archive</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--dsizzlers-gray-dark);">
                            No active branches found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
