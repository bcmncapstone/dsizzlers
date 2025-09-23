@extends('layouts.app')

@section('content')
<h2>Active Branches</h2>

{{-- Search Form --}}
<form method="GET" action="{{ route('admin.branches.index') }}">
    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search branches...">
    <button type="submit">Search</button>
</form>

{{-- Navigation Links --}}
<a href="{{ route('admin.branches.create') }}">Add Branch</a>
<a href="{{ route('admin.branches.archived') }}">View Archived Branches</a>

<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-top:15px;">
    <thead>
        <tr>
            <th>Branch Manager</th>
            <th>Location</th>
            <th>Email</th>
            <th>Contract</th>
            <th>Contract Expiration</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($branches as $branch)
        <tr>
            <td>{{ $branch->first_name }} {{ $branch->last_name }}</td>
            <td>{{ $branch->location }}</td>
            <td>{{ $branch->email }}</td>
            <td>
                @if($branch->contract_file)
                    <a href="{{ route('admin.branches.downloadContract', $branch->branch_id) }}" target="_blank">Preview</a>
                    <br>
                    <a href="{{ route('admin.branches.downloadContract', ['id' => $branch->branch_id, 'mode' => 'download']) }}">Download</a>
                @else
                    No file
                @endif
            </td>
            <td>{{ $branch->contract_expiration }}</td>
            <td>
                <a href="{{ route('admin.branches.edit', $branch->branch_id) }}" style="background:#3490dc; color:white; padding:5px 8px; border-radius:4px; text-decoration:none;">✏️ Edit</a>
                <form method="POST" action="{{ route('admin.branches.archive', $branch->branch_id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background-color:red; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:4px;">
                        🗄 Archive
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center;">No active branches found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
