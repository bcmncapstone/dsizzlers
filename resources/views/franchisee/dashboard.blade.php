@extends('layouts.app') 

@section('content')
    <div class="container">
        <h1>Franchisee Dashboard</h1>
        <p>Welcome, Franchisee!</p>
        <a href="{{ route('settings.password') }}" class="btn btn-primary">Update Password</a>

        <!-- Item -->
        <a href="{{ route('franchisee.item.index') }}" class="btn btn-secondary">Manage Branches</a>
    </div>
@endsection
