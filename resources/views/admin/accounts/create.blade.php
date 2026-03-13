@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper">
    <div class="form-container">
        <h2>Create Account</h2>

        @if(session('success'))
            <div class="alert alert-success">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <strong>✕</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Role: *</label>
                <select name="role" class="form-control" required onchange="toggleFields(this.value)">
                    <option value="">Select Role</option>
                    <option value="franchisee">Franchisee</option>
                    <option value="franchisor_staff">Franchisor Staff</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">First Name: *</label>
                <input type="text" name="fname" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Last Name: *</label>
                <input type="text" name="lname" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Contact: *</label>
                <input type="text" name="contact" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Username: *</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password: *</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Franchisee Only Fields -->
            <div id="franchisee_fields" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Address:</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px;">Create Account</button>
        </form>
    </div>
</div>

<script>
function toggleFields(role) {
    const franchiseeFields = document.getElementById('franchisee_fields');
    if (role === 'franchisee') {
        franchiseeFields.style.display = 'block';
    } else {
        franchiseeFields.style.display = 'none';
    }
}
</script>

@endsection