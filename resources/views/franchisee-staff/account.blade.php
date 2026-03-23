@extends('layouts.franchisee-staff')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow-sm sm:rounded-lg p-8 mb-6">
            <h1 class="text-3xl font-bold text-gray-900">My Account</h1>
            <p class="text-sm text-gray-600 mt-2">Manage your account information</p>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Errors -->
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Account Form -->
        <form action="{{ route('franchisee-staff.account.update') }}" method="POST" class="bg-white shadow-sm sm:rounded-lg p-8">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-900 mb-2">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition @error('email') border-red-500 @enderror"
                    value="{{ old('email', $user->fstaff_email) }}"
                    placeholder="Enter email address"
                    required
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="contactNo" class="block text-sm font-medium text-gray-900 mb-2">Phone Number</label>
                <input 
                    type="text" 
                    name="contactNo" 
                    id="contactNo"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition @error('contactNo') border-red-500 @enderror"
                    value="{{ old('contactNo', $user->fstaff_contactNo) }}" 
                    placeholder="Enter phone number"
                    required
                >
                @error('contactNo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition"
                >
                    Save Changes
                </button>
                <a 
                    href="{{ route('franchisee-staff.dashboard') ?? '/' }}" 
                    class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
