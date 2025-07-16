@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Account</h2>

    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('accounts.store') }}">
        @csrf

        <label>Role:</label>
        <select name="role" required onchange="toggleFields(this.value)">
            <option value="">Select</option>
            <option value="franchisee">Franchisee</option>
            <option value="franchisor_staff">Franchisor Staff</option>
            <option value="franchisee_staff">Franchisee Staff</option>
        </select><br>

        <label>First Name:</label>
        <input type="text" name="fname" required><br>

        <label>Last Name:</label>
        <input type="text" name="lname" required><br>

        <label>Contact:</label>
        <input type="text" name="contact" required><br>

        <label>Username:</label>
        <input type="text" name="username" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        {{-- Only for Franchisee --}}
        <div id="franchisee_fields" style="display:none;">
            <label>Email:</label>
            <input type="email" name="email"><br>

            <label>Address:</label>
            <input type="text" name="address"><br>
        </div>

        {{-- Only for Franchisee Staff --}}
        <div id="franchisee_staff_fields" style="display:none;">
            <label>Select Franchisee:</label>
            <select name="franchisee_id">
                <option value="">Select Franchisee</option>
                @foreach($franchisees as $f)
                    <option value="{{ $f->franchisee_id }}">{{ $f->franchisee_name }}</option>
                @endforeach
            </select><br>
        </div>

        <button type="submit">Create Account</button>
    </form>
</div>

{{-- No JavaScript (for your preference): fallback shown, but if allowed: --}}
<script>
    function toggleFields(role) {
        document.getElementById('franchisee_fields').style.display = (role === 'franchisee') ? 'block' : 'none';
        document.getElementById('franchisee_staff_fields').style.display = (role === 'franchisee_staff') ? 'block' : 'none';
    }
</script>
@endsection
