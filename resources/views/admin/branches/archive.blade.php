@extends('layouts.app')

@section('content')
<h2>Archived Branches</h2>
<a href="{{ route('admin.branches.index') }}">⬅ Back to Active Branches</a>

<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-top:15px;">
    <thead>
        <tr>
            <th>Location</th>
            <th>Email</th>
            <th>Contract Expiration</th>
            <th>Contract File</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branches as $branch)
        <tr>
            <td>{{ $branch->location }}</td>
            <td>{{ $branch->email }}</td>
            <td>{{ $branch->contract_expiration }}</td>
            <td>
                @if($branch->contract_file)
                    <a href="{{ route('admin.branches.downloadContract', $branch->branch_id) }}" target="_blank">Preview</a>
                    &nbsp;|&nbsp;
                    <a href="{{ route('admin.branches.downloadContract', ['id' => $branch->branch_id, 'mode' => 'download']) }}">Download</a>
                @else
                    No file
                @endif
            </td>
            <td>
                <form method="POST" action="{{ route('admin.branches.restore', $branch->branch_id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background-color:green; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">
                        🔄 Restore
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
