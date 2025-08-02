@extends('layouts.app')

@section('content')
<form action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="text" name="location" placeholder="Branch Location" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="contact_number" placeholder="Contact Number" required>
    <label for="contract_file">Upload Contract:</label>
    <input type="file" name="contract_file" id="contract_file" required>
    <label for="date">Contract Expiration:</label>
    <input type="date" name="contract_expiration" required>
    <button type="submit">Add Branch</button>
</form>

@endsection
