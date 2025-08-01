@extends('layouts.app')

@section('content')
<h2>Archived Branches</h2>
<a href="{{ route('admin.branches.index') }}">Back to Active Branches</a>

<table>
    <thead>
        <tr>
            <th>Location</th><th>Email</th><th>Contract Expiration</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branches as $branch)
        <tr>
            <td>{{ $branch->location }}</td>
            <td>{{ $branch->email }}</td>
            <td>{{ $branch->contract_expiration }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
