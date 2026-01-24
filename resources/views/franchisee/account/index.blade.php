@extends('layouts.app')

@section('content')
<h2>My Branch Information</h2>

@if($branch)
<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-top:15px;">
    <tr><th>Branch Location</th><td>{{ $branch->location }}</td></tr>
    <tr><th>Branch Manager</th><td>{{ $branch->first_name }} {{ $branch->last_name }}</td></tr>
    <tr><th>Email</th><td>{{ $branch->email }}</td></tr>
    <tr><th>Contact Number</th><td>{{ $branch->contact_number }}</td></tr>
    <tr>
        <th>Contract</th>
        <td>
            @if($branch->contract_file)
                <a href="{{ route('franchisee.branches.contract', $branch->branch_id) }}" target="_blank">Preview</a>
                &nbsp;|&nbsp;
                <a href="{{ route('franchisee.branches.contract', ['id' => $branch->branch_id, 'mode' => 'download']) }}">Download</a>
            @else
                No contract uploaded
            @endif
        </td>
    </tr>
    <tr><th>Contract Expiration</th><td>{{ $branch->contract_expiration }}</td></tr>
</table>
@else
<div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #007bff;">
    <p style="margin: 0; color: #666;">No branch assigned to your account yet. Please contact your administrator.</p>
</div>
@endif
@endsection
