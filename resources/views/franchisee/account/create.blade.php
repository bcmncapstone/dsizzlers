@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 40px auto;">
    <h2>Franchisee Staff Create Account</h2>

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

    <form method="POST" action="{{ route('account.store') }}">
        @csrf

        <div>
            <label>First Name:</label><br>
            <input type="text" name="fname" required>
        </div><br>

        <div>
            <label>Last Name:</label><br>
            <input type="text" name="lname" required>
        </div><br>

        <div>

        <div>
            <label>Contact Number:</label><br>
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

        <button type="submit">Create Account</button>
    </form>
</div>
</script>
@endsection
