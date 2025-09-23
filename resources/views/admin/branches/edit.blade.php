@extends('layouts.app')

@section('content')
<h2>Edit Branch</h2>

<form action="{{ route('admin.branches.update', $branch->branch_id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="text" name="location" value="{{ old('location', $branch->location) }}" required>
    <input type="text" name="first_name" value="{{ old('first_name', $branch->first_name) }}" required>
    <input type="text" name="last_name" value="{{ old('last_name', $branch->last_name) }}" required>
    <input type="email" name="email" value="{{ old('email', $branch->email) }}" required>
    <input type="text" name="contact_number" value="{{ old('contact_number', $branch->contact_number) }}" required>

    <label for="contract_file">Replace Contract (optional):</label>
    <input type="file" name="contract_file" id="contract_file">
    
    <label for="contract_expiration">Contract Expiration:</label>
    <input type="date" name="contract_expiration" value="{{ old('contract_expiration', $branch->contract_expiration) }}" required>

    <button type="submit">Update Branch</button>
</form>

@if ($errors->any())
    <div style="color: red; margin-top: 10px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
