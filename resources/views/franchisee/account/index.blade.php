@extends('layouts.app')

@section('content')
<div class="branch-info-page">
    <div class="branch-info-container">
        {{-- Header --}}
        <div class="branch-info-header">
            <h2>Contract Information</h2>
        </div>

        {{-- Branch Information or Empty State --}}
        @if($branches->isNotEmpty())
            @foreach($branches as $branch)
                <div class="branch-info-content" style="margin-bottom: 20px;">
                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract #</span>
                        <span class="branch-info-value">{{ $loop->iteration }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Location</span>
                        <span class="branch-info-value">{{ $branch->location }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Franchisee Name</span>
                        <span class="branch-info-value">{{ $branch->first_name }} {{ $branch->last_name }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Email</span>
                        <span class="branch-info-value">{{ $branch->email }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contact Number</span>
                        <span class="branch-info-value">{{ $branch->contact_number }}</span>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract</span>
                        <div class="branch-info-value">
                            @if($branch->contract_file)
                                <div class="branch-info-links">
                                    <a href="{{ route('franchisee.branches.contract', $branch->branch_id) }}" target="_blank" class="branch-info-link">
                                        View
                                    </a>
                                    <span class="branch-info-separator">|</span>
                                    <a href="{{ route('franchisee.branches.contract', ['id' => $branch->branch_id, 'mode' => 'download']) }}" class="branch-info-link">
                                        Download
                                    </a>
                                </div>
                            @else
                                <span style="color: #999;">No contract uploaded</span>
                            @endif
                        </div>
                    </div>

                    <div class="branch-info-item">
                        <span class="branch-info-label">Contract Expiration</span>
                        <span class="branch-info-value">{{ optional($branch->contract_expiration)->format('Y-m-d') ?? 'N/A' }}</span>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Empty State --}}
            <div class="branch-empty-container">
                <p class="branch-empty-message">
                    No active contract assigned to your account yet.
                </p>
                <p class="branch-empty-contact">Please contact your administrator to assign a contract to your account.</p>
            </div>
        @endif
    </div>
</div>
@endsection
