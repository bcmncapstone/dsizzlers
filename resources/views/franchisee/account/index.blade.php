@extends('layouts.app')

@section('content')
<div class="branch-info-page">
    <div class="branch-info-container">
        {{-- Header --}}
        <div class="branch-info-header">
            <h2>🏪 Branch Information</h2>
        </div>

        {{-- Branch Information or Empty State --}}
        @if($branch)
            <div class="branch-info-content">
                {{-- Branch Location --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Branch Location</span>
                    <span class="branch-info-value">{{ $branch->location }}</span>
                </div>

                {{-- Branch Manager --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Branch Manager</span>
                    <span class="branch-info-value">{{ $branch->first_name }} {{ $branch->last_name }}</span>
                </div>

                {{-- Email --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Email</span>
                    <span class="branch-info-value">{{ $branch->email }}</span>
                </div>

                {{-- Contact Number --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Contact Number</span>
                    <span class="branch-info-value">{{ $branch->contact_number }}</span>
                </div>

                {{-- Contract --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Contract</span>
                    <div class="branch-info-value">
                        @if($branch->contract_file)
                            <div class="branch-info-links">
                                <a href="{{ route('franchisee.branches.contract', $branch->branch_id) }}" target="_blank" class="branch-info-link">
                                    👁️ Preview
                                </a>
                                <span class="branch-info-separator">|</span>
                                <a href="{{ route('franchisee.branches.contract', ['id' => $branch->branch_id, 'mode' => 'download']) }}" class="branch-info-link">
                                    ⬇️ Download
                                </a>
                            </div>
                        @else
                            <span style="color: #999;">No contract uploaded</span>
                        @endif
                    </div>
                </div>

                {{-- Contract Expiration --}}
                <div class="branch-info-item">
                    <span class="branch-info-label">Contract Expiration</span>
                    <span class="branch-info-value">{{ $branch->contract_expiration }}</span>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="branch-empty-container">
                <span class="branch-empty-icon">🔗</span>
                <p class="branch-empty-message">
                    No branch assigned to your account yet.
                </p>
                <p class="branch-empty-contact">Please contact your administrator to assign a branch to your account.</p>
            </div>
        @endif
    </div>
</div>
@endsection
