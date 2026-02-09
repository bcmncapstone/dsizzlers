{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<h1>Welcome, Admin!</h1>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<a href="{{ route('accounts.create') }}">
    <button>Add Account</button>
</a>

<a href="{{ route('admin.branches.index') }}">
    <button>Branch Account</button>
</a>

<a href="{{ route('admin.items.create') }}">
    <button>Add Item</button>
</a>

<a href="{{ route('admin.manageOrder.index') }}">
    <button>Order</button>
</a>

<a href="{{ route('admin.stock.index') }}">
    <button>Stock Management</button>
</a>

<a href="{{ route('admin.reports.index') }}">
    <button>Reports</button>
</a>

<a href="{{ route('communication.index') }}">
    <button>Message</button>
</a>
@endsection
