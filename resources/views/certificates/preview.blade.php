<!-- resources/views/certificates/preview.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Certificate Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/preview.css') }}">
<link rel="stylesheet" href="{{ asset('css/badge.css') }}">
@endsection

@section('content')
@php
$statusSteps = [
'create_certificate' => 'Create Certificate',
'pending_review' => 'Pending Review',
'pending_client_verification' => 'Pending Client Verification',
'client_verified' => 'Client Verified',
'pending_hod_approval' => 'Pending HOD Approval',
'certificate_issued' => 'Certificate Issued',
];

if ($cert->status === 'need_revision') {
if ($cert->revision_source === 'client') {
$statusSteps = array_slice($statusSteps, 0, 3, true) +
['need_revision' => 'Need Revision'] +
array_slice($statusSteps, 3, null, true);
} elseif ($cert->revision_source === 'scheme_head') {
$statusSteps = array_slice($statusSteps, 0, 5, true) +
['need_revision' => 'Need Revision'] +
array_slice($statusSteps, 5, null, true);
}
}

$statusOrder = array_keys($statusSteps);
$currentStatusIndex = array_search($cert->status, $statusOrder) ? array_search($cert->status, $statusOrder) : -1;
$stepNumber = 1;
@endphp
<div class="progress-card">
    <h1>Certificate Progress</h1>

    <div class="progress-body">
        <div class="cert-progress-wrapper">
            <div class="cert-progress-bar">
                @foreach ($statusSteps as $statusKey => $label)
                @php
                $stepIndex = array_search($statusKey, $statusOrder);
                $isCompleted = $stepIndex <= $currentStatusIndex;
                    $isCurrent=$stepIndex===$currentStatusIndex;
                    $isNeedRevision=$statusKey==='need_revision' ;
                    @endphp

                    <div class="cert-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isNeedRevision ? 'need-revision' : '' }}">
                    <div class="circle">
                        @if($isNeedRevision)
                        !
                        @else
                        {{ $stepNumber }}
                        @php $stepNumber++; @endphp
                        @endif
                    </div>
                    <div class="label">{{ $label }}</div>
            </div>
            @if (!$loop->last)
            <div class="connector {{ $stepIndex < $currentStatusIndex ? 'completed' : ''}}"></div>
            @endif
            @endforeach
        </div>
    </div>
</div>
</div>

<div class="detail-container">
    <div class="header">
        <h1>Certificate Details</h1>
        <span class="status-badge status-{{ $cert->status }}">
            {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
        </span>
    </div>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="detail-group">
        <div class="detail-label">Certificate Type</div>
        <div class="detail-value">{{ ucfirst($cert->cert_type) }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">ISO Number</div>
        <div class="detail-value">{{ $cert->iso_num }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">Company Name</div>
        <div class="detail-value">{{ $cert->comp_name }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">Address</div>
        <div class="detail-value">{{ $cert->comp_address1 }}</div>
        <div class="detail-value">{{ $cert->comp_address2 }}</div>
        <div class="detail-value">{{ $cert->comp_address3 }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">Scope</div>
        <div class="detail-value">{{ $cert->scope }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">Statement of Applicability</div>
        <div class="detail-value">{{ $cert->soa }}</div>
    </div>

    <div class="detail-group">
        <div class="detail-label">Certificate Number</div>
        <div class="detail-value">{{ $cert->cert_number }}</div>
    </div>

    <div class="detail-column">
        <div class="detail-group">
            <div class="detail-label">Registration Date</div>
            <div class="detail-value">{{ \Carbon\Carbon::parse($cert->reg_date)->format('d/m/Y') }}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Issue Date</div>
            <div class="detail-value">{{ \Carbon\Carbon::parse($cert->issue_date)->format('d/m/Y') }}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Expiry Date</div>
            <div class="detail-value">{{ \Carbon\Carbon::parse($cert->exp_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="detail-group">
    <div class="detail-label">Site(s)</div>
    @if($cert->sites && is_array($cert->sites) && count($cert->sites) > 0)
        @foreach($cert->sites as $index => $site)
            @if(!empty(trim($site)))
                <div class="detail-value site-item">
                {{ $index + 1 }}. {{ $site }}
                </div>
            @endif
        @endforeach
    @else
        <div class="detail-value">No sites specified</div>
    @endif
</div>

    @if ($cert->status === 'need_revision')
    @if(isset($comments) && count($comments) > 0)
    <div id="verificationNotification" class="verification pulse-animation">
        <button class="verification-close" onclick="closeNotification()">
            <i class="fas fa-times"></i>
        </button>
        <div class="verification-header">
            <div class="verification-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="verification-title">Revision Required</h4>
                <p class="verification-subtitle">{{ $cert->revision_source === 'client' ? 'Client' : 'HOD' }} has requested a revision.</p>
            </div>
        </div>
        <div class="verification-content">
            <p><strong>Status:</strong>
                <span class="verification-status">
                    <i class="fas fa-exclamation-triangle"></i> Need Revision
                </span>
            </p>
            <p><strong>Revision Source:</strong> {{ $cert->revision_source === 'client' ? 'Client' : 'HOD' }}</p>
            <p><strong>Comments:</strong></p>
            <ul class="comments-list">
                @foreach($comments as $comment)
                <li class="comment-item {{ $comment->comment_type == 'revision_request' ? 'revision-request' : ($comment->comment_type == 'verification' ? 'verification' : 'internal') }}">
                    <div class="comment-header">
                        <p class="comment-author">{{ $comment->commented_by }}</p>
                        <small class="comment-date">{{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}</small>
                    </div>

                    <p class="comment-text">Commented: {{ $comment->comment }}</p>

                </li>
                @endforeach
            </ul>
        </div>
        <div class="verification-action">
            @if(in_array(auth()->user()->role, ['scheme_manager', 'scheme_head']))
            <a href="{{ route('certificates.edit', $cert->id) }}" class="verification-btn primary">
                <i class="fas fa-edit"></i> Edit Certificate
            </a>
            @endif
        </div>
    </div>
    @endif
    @endif

    <div class="last-edited-info">
        @if($cert->last_edited_at)
        Last edited on {{ \Carbon\Carbon::parse($cert->last_edited_at)->format('d/m/Y H:i') }} (MYT)
        @if(isset($cert->lastEditor) && $cert->lastEditor)
        by {{ $cert->lastEditor->name }}
        @endif
        @else
        No edit history available
        @endif
    </div>

    <div class="button-group">
        <a href="{{ route('certificates.index') }}" class="btn-back">Back</a>
        @if (in_array(auth()->user()->role, ['scheme_manager', 'scheme_head']) && $cert->status == 'pending_review')
        <button class="confirm-btn" onclick="openConfirmModal()">Confirm</button>
        @endif
        @if (auth()->user()->role === 'scheme_head' && $cert->status == 'pending_hod_approval')
        <button class="approve-btn" onclick="openApproveModal()">Approve</button>
        <button class="reject-btn" onclick="openRejectModal()">Reject</button>
        @endif
    </div>
</div>

{{-- Notification for Assign Number --}}
@if(isset($verification) && $verification->is_verified && $cert->status === 'client_verified' && in_array(auth()->user()->role, ['scheme_manager', 'scheme_head']))
<div id="verificationNotification" class="verification pulse-animation">
    <button class="verification-close" onclick="closeNotification()">
        <i class="fas fa-times"></i>
    </button>

    <div class="verification-header">
        <div class="verification-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <h4 class="verification-title">Client has verified the data</h4>
            <p class="verification-subtitle">Verified on {{ $verification->verified_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <div class="verification-content">
        <p><strong>Status:</strong>
            <span class="verification-status">
                <i class="fas fa-check-circle"></i> Client Verified
            </span>
        </p>
    </div>

    <div class="verification-action">
        <a href="{{ route('certificates.assign-number.form', $cert->id) }}"
            class="verification-btn primary"
            onclick="trackAssignNumberClick()">
            <i class="fas fa-pencil"></i> Assign Cert Number
        </a>
        <a href="{{ route('certificates.verification-link', $cert->id) }}"
            class="verification-btn secondary"
            style="margin-left: 10px;"
            onclick="trackViewVerificationClick()">
            <i class="fas fa-eye"></i> Verification Details
        </a>
    </div>
</div>
@endif

{{-- Verification Notification --}}
@if($cert->status === 'pending_client_verification')
<div id="verificationNotification" class="verification pulse-animation">
    <button class="verification-close" onclick="closeNotification()">
        <i class="fas fa-times"></i>
    </button>

    <div class="verification-header">
        <div class="verification-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h4 class="verification-title">Client Verification Required</h4>
            <p class="verification-subtitle">{{ $cert->comp_name }}</p>
        </div>
    </div>

    <div class="verification-content">
        <p><strong>Status:</strong>
            <span class="verification-status">
                <i class="fas fa-clock"></i> Pending Client Verification
            </span>
        </p>
        <p><strong>Link Expires:</strong> {{ \Carbon\Carbon::parse($verification->expires_at)->format('d M Y H:i') }}</p>
    </div>

    <div class="verification-action">
        @if(isset($verification))
        <a href="{{ route('certificates.verification-link', $cert->id) }}" class="verification-btn primary">
            <i class="fas fa-link"></i> View Link
        </a>
        <form method="POST" action="{{ route('certificates.renew-verification', $cert->id) }}" style="display: inline;">
            @csrf
            <button type="submit" class="verification-btn">
                <i class="fas fa-sync-alt"></i> Renew Link
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('certificates.renew-verification', $cert->id) }}" style="display: inline;">
            @csrf
            <button type="submit" class="verification-btn primary">
                <i class="fas fa-plus"></i> Generate Link
            </button>
        </form>
        @endif
    </div>
</div>
@endif

@php $canVerify = in_array(auth()->user()->role, ['scheme_manager', 'scheme_head']); @endphp

{{-- Confirm Modal --}}
@if($canVerify && $cert->status == 'pending_review')
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>Review and Confirm</h3>
        <p>Are you sure you want to confirm the data? Please type <strong>CONFIRM</strong> to proceed.</p>
        <form method="POST" action="{{ route('certificates.confirm', $cert->id) }}">
            @csrf
            <input type="text" id="confirmationInput" name="confirmation_text" placeholder="Type CONFIRM to continue" required>
            <div class="modal-actions">
                <button type="submit" class="confirm-btn">Confirm</button>
                <button type="button" class="btn-back" onclick="closeConfirmModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Approve Modal --}}
@if(auth()->user()->role === 'scheme_head' && $cert->status === 'pending_hod_approval')
<div id="approveModal" class="modal">
    <div class="modal-content">
        <h3>Review and Approve</h3>
        <p>Are you sure you want to approve the certificate? Please type <strong>APPROVE</strong> to proceed.</p>
        <form method="POST" action="{{ route('certificates.hod-approval', $cert->id) }}">
            @csrf
            <input type="hidden" name="action" value="approve">
            <input type="text" id="hodConfirmationInput" name="hod_confirmation_text" placeholder="Type APPROVE to continue" required>
            <div class="modal-actions">
                <button type="submit" class="approve-btn">Approve</button>
                <button type="button" class="btn-back" onclick="closeApproveModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3>Review and Reject</h3>
        <p>Are you sure you want to reject the certificate? Please type <strong>REJECT</strong> to proceed.</p>
        <form method="POST" action="{{ route('certificates.hod-approval', $cert->id) }}">
            @csrf
            <input type="hidden" name="action" value="reject">
            <input type="text" id="hodRejectInput" name="hod_reject_text" placeholder="Type REJECT to continue" required>
            <div class="modal-description">
                <p>Please provide a reason for rejection:</p>
                <textarea class="form-control" id="unverify-comment" name="comment" rows="5" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="submit" class="reject-btn">Reject</button>
                <button type="button" class="btn-back" onclick="closeRejectModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Modal Functions
    function openConfirmModal() {
        showModal('confirmModal', 'confirmationInput');
    }

    function closeConfirmModal() {
        hideModal('confirmModal', 'confirmationInput');
    }

    function openApproveModal() {
        showModal('approveModal', 'hodConfirmationInput');
    }

    function closeApproveModal() {
        hideModal('approveModal', 'hodConfirmationInput');
    }

    function openRejectModal() {
        showModal('rejectModal', 'hodRejectInput');
    }

    function closeRejectModal() {
        hideModal('rejectModal', 'hodRejectInput');
    }

    // Helper Functions
    function showModal(modalId, inputId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('show');

        setTimeout(() => {
            const input = document.getElementById(inputId);
            if (input) input.focus();
        }, 300);

        document.body.style.overflow = 'hidden';
    }

    function hideModal(modalId, inputId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('show');

        setTimeout(() => {
            document.body.style.overflow = 'auto';
        }, 300);

        const input = document.getElementById(inputId);
        if (input) input.value = '';
    }

    // Notification Functions
    function closeNotification() {
        const notification = document.getElementById('verificationNotification');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.style.display = 'none';
            }, 400);
        }
    }

    // Show notification on page load
    document.addEventListener('DOMContentLoaded', function() {
        const notification = document.getElementById('verificationNotification');
        if (notification) {
            setTimeout(() => {
                notification.classList.add('show');
            }, 500);
        }
    });
</script>
<script>
    // Close modal on outside click
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    }
</script>
@endsection