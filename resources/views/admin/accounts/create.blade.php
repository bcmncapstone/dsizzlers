@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 40px auto;">
    <h2>Create Account</h2>

    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('accounts.store') }}">
        @csrf

        <div>
            <label>Role:</label><br>
            <select name="role" required onchange="toggleFields(this.value)">
                <option value="">Select</option>
                <option value="franchisee">Franchisee</option>
                <option value="franchisor_staff">Franchisor Staff</option>
                <option value="franchisee_staff">Franchisee Staff</option>
            </select>
        </div><br>

        <div>
            <label>First Name:</label><br>
            <input type="text" name="fname" required>
        </div><br>

        <div>
            <label>Last Name:</label><br>
            <input type="text" name="lname" required>
        </div><br>

        <div>
            <label>Contact:</label><br>
            <input type="text" name="contact" required>
        </div><br>

        <div>
            <label>Username:</label><br>
            <input type="text" name="username" required>
        </div><br>

        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div><br>

        {{-- Franchisee Only --}}
        <div id="franchisee_fields" style="display:none;">
            <div>
                <label>Email:</label><br>
                <input type="email" name="email">
            </div><br>

            <div>
                <label>Address:</label><br>
                <input type="text" name="address">
            </div><br>
        </div>

        {{-- Franchisee Staff Only --}}
        <div id="franchisee_staff_fields" style="display:none;">
            <div>
                <label>Select Franchisee:</label><br>
                <select name="franchisee_id">
                    <option value="">Select Franchisee</option>
                    @foreach($franchisees as $f)
                        <option value="{{ $f->franchisee_id }}">{{ $f->franchisee_name }}</option>
                    @endforeach
                </select>
            </div><br>
        </div>

        <button type="submit">Create Account</button>
    </form>
</div>
{{-- JavaScript to show/hide extra fields based on selected role --}}
<script>
    function toggleFields(role) {
        document.getElementById('franchisee_fields').style.display = (role === 'franchisee') ? 'block' : 'none';
        document.getElementById('franchisee_staff_fields').style.display = (role === 'franchisee_staff') ? 'block' : 'none';
    }
</script>
@endsection
