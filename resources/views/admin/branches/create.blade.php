@extends('layouts.app')

@section('content')
<h2>Add New Branch</h2>

<form action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="text" name="location" placeholder="Branch Location" value="{{ old('location') }}" required>
    <input type="text" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
    <input type="text" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
    <input type="text" name="contact_number" placeholder="Contact Number" value="{{ old('contact_number') }}" required>

    <label for="contract_file">Upload Contract:</label>
    <input type="file" name="contract_file" id="contract_file" required>

    <label for="contract_expiration">Contract Expiration:</label>
    <input type="date" name="contract_expiration" value="{{ old('contract_expiration') }}" required>

    <button type="submit">Add Branch</button>
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
