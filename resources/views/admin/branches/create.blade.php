@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('admin.branches.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="text" name="location" placeholder="Branch Location" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="contact_number" placeholder="Contact Number" required>
    <input type="file" name="contract_file" required>
    <input type="date" name="contract_expiration" required>
    <button type="submit">Add Branch</button>
</form>

@endsection
