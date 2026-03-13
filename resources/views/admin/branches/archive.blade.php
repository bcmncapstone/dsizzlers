@extends('layouts.app')

@section('content')
<h2>Archived Branches</h2>
<a href="{{ route('admin.branches.index') }}">⬅ Back to Active Branches</a>

<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-top:15px;">
    <thead>
        <tr>
            <th>Franchisee Name</th>
            <th>Location</th>
            <th>Email</th>
             <th>Contract</th>
            <th>Contract Expiration</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branches as $branch)
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
                <form method="POST" action="{{ route('admin.branches.restore', $branch->branch_id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background-color:green; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">
                        Restore
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
