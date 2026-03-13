@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow-sm p-8 rounded-lg mb-6">
            <h1>Edit Branch</h1>
            <p class="header-subtitle">Update branch location and manager details</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="error-alert">
                <h3>Please fix the following errors:</h3>
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.branches.update', $branch->branch_id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm p-8 rounded-lg">
            @csrf
            @method('PUT')

            <!-- Branch Location -->
            <div class="form-group">
                <label for="location" class="form-label">Branch Location</label>
                <input type="text" name="location" id="location" 
                       value="{{ old('location', $branch->location) }}" class="form-input" required>
                @error('location')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Manager Information Section -->
            <div class="section-divider">
                <h2 style="font-size: 16px; font-weight: 600; color: #1a1a1a; border-left: 3px solid #FF5722; padding-left: 12px;">
                    Manager Information
                </h2>
            </div>

            <!-- First Name -->
            <div class="form-group">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" 
                       value="{{ old('first_name', $branch->first_name) }}" class="form-input" required>
                @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" 
                       value="{{ old('last_name', $branch->last_name) }}" class="form-input" required>
                @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" 
                       value="{{ old('email', $branch->email) }}" class="form-input" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contact Number -->
            <div class="form-group">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" 
                       value="{{ old('contact_number', $branch->contact_number) }}" class="form-input" required>
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contract Section -->
            <div class="section-divider">
                <h2 style="font-size: 16px; font-weight: 600; color: #1a1a1a; border-left: 3px solid #FF5722; padding-left: 12px;">
                    Contract Information
                </h2>
            </div>

            <!-- Contract File Upload -->
            <div class="form-group">
                <label for="contract_file" class="form-label">Replace Contract (Optional)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="contract_file" id="contract_file" class="file-input">
                    <span class="file-input-label">Choose file or drag and drop</span>
                </div>
                <p class="field-help">Supported formats: PDF, DOC, DOCX (Max 10MB)</p>
                @error('contract_file')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Contract Expiration -->
            <div class="form-group">
                <label for="contract_expiration" class="form-label">Contract Expiration Date</label>
                <input type="date" name="contract_expiration" id="contract_expiration" 
                       value="{{ old('contract_expiration', $branch->contract_expiration) }}" class="form-input" required>
                @error('contract_expiration')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <!-- Form Buttons -->
            <div class="form-actions">
                <a href="{{ route('admin.branches.index') }}" class="cancel-button">Cancel</a>
                <button type="submit" class="submit-button">Update Branch</button>
            </div>
        </form>

    </div>
</div>
@endsection
