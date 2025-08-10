@extends('layouts.app')

@section('content')
<h2>Archived Branches</h2>
<a href="{{ route('admin.branches.index') }}">Back to Active Branches</a>

<table>
    <thead>
        <tr>
            <th>Location</th><th>Email</th><th>Contract Expiration</th><th>Contract File</th>
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
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
