{{-- resources/views/branches/index.blade.php --}}
@extends('layouts.app')

@section('content')
<h2>Active Branches</h2>

<form method="GET" action="{{ route('admin.branches.index') }}">
    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search branches...">
    <button type="submit">Search</button>
</form>

<a href="{{ route('admin.branches.create') }}">Add Branch</a>
<a href="{{ route('admin.branches.archived') }}">View Archived Branches</a>

<table>
    <thead>
        <tr>
            <th>Location</th><th>Email</th><th>Contract Expiration</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branches as $branch)
        <tr>
            <td>{{ $branch->location }}</td>
            <td>{{ $branch->email }}</td>
            <td>{{ $branch->contract_expiration }}</td>
            <td>
                <form method="POST" action="{{ route('admin.branches.archive', $branch->id) }}">
                    @csrf
                    <button type="submit">Archive</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection