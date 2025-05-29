<!-- resources/views/certificates/show.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Certificate Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
    <h1>Certificate Details</h1>

    <div class="detail-container">

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
            <div class="detail-value">
                {{ $cert->comp_address1 }}
            </div>
            <div class="detail-value">
                {{ $cert->comp_address2 }}
            </div>
            <div class="detail-value">
                {{ $cert->comp_address3 }}
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Registration Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->reg_date)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Issue Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->issue_date)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Expired Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->exp_date)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        @if ($cert->cert_number)
            <div class="detail-group">
                <div class="detail-label">Certificate Number</div>
                <div class="detail-value">{{ $cert->cert_number }}</div>
            </div>
        @endif

        <div class="detail-group">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-{{ $cert->status }}">
                    {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                </span>
            </div>
        </div>

        @if($cert->status === 'pending_client_verification')
            <!-- Certificate Verification Section -->
            <div class="detail-group">
                <div class="detail-label">Certificate Verification</div>
                <div class="detail-value">
                    @if(isset($verification))
                        <div class="verification-info">
                            <p><strong>Verification Status:</strong>
                                @if($verification->is_verified)
                                    <span class="status-badge status-success">Verified on {{ \Carbon\Carbon::parse($verification->verified_at)->format('d M Y') }}</span>
                                @else
                                    <span class="status-badge status-warning" style="background-color: red">Awaiting Verification</span>
                                @endif
                            </p>
                            <p style="margin-top: 15px; margin-bottom: 15px;"><strong>Link Expires:</strong> {{ \Carbon\Carbon::parse($verification->expires_at)->format('d M Y H:i') }}</p>
                        </div>
                        <div class="verification-actions">
                            <a href="{{ route('certificates.verification-link', $cert->id) }}" class="btn-action">View Verification Link</a>
                            <form method="POST" action="{{ route('certificates.renew-verification', $cert->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-warning">Renew Link</button>
                            </form>
                        </div>
                    @else
                        <p>No verification link has been generated yet.</p>
                        <form method="POST" action="{{ route('certificates.renew-verification', $cert->id) }}">
                            @csrf
                            <button type="submit" class="btn-action">Generate Verification Link</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @if(isset($verification) && $verification->is_verified && $cert->status === 'client_verified' && auth()->user()->role === 'manager' && 'hod')
            <div class="assign-number-section mt-3">
                <a href="{{ route('certificates.assign-number.form', $cert->id) }}" class="btn-action">Assign Certificate Number</a>
            </div>
        @endif

        @if ($cert->status === 'need_revision')
        <!-- Comments Section -->
            @if(isset($comments) && count($comments) > 0)
                <div class="detail-group">
                    <div class="detail-label">Comments History</div>
                    <div class="detail-value">
                        <ul style="list-style: none; padding-left: 0;">
                            @foreach($comments as $comment)
                                <li 
                                    {{ $comment->comment_type == 'revision_request' ? '#dc3545' : 
                                    ($comment->comment_type == 'verification' ? '#28a745' : '#6c757d') }}
                                    padding: 16px; border-radius: 6px; margin-bottom: 12px;">

                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <p style="font-size: 15px;">{{  $comment->commented_by }}</p>
                                        <small style="color: #888;">{{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}</small>
                                    </div>

                                    <p style="margin-top: 10px; color: #333; line-height: 1.5;">{{ $comment->comment }}</p>

                                    <div style="margin-top: 8px;">
                                        @if($comment->comment_type == 'verification')
                                            <span class="status-badge status-success">Verification</span>
                                        @elseif($comment->comment_type == 'revision_request')
                                            <span class="status-badge status-warning" style="background-color: #dc3545; color: white;">Revision Request</span>
                                        @else
                                            <span class="status-badge status-secondary" style="background-color: #6c757d; color: white;">Internal</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            @if(in_array(auth()->user()->role, ['manager', 'hod']))
                <a href="{{ route('certificates.edit', $cert->id) }}" class="btn-action btn-warning">Edit Certificate</a>
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
            @if (in_array(auth()->user()->role, ['manager', 'hod']) && $cert->status == 'pending_review')
                <button class="confirm-btn" onclick="openConfirmModal()">Confirm Data</button>  
            @endif 
            @if (in_array(auth()->user()->role, ['hod']) && $cert->status == 'pending_hod_approval')
                <button class="confirm-btn" onclick="openApproveModal()">Approve</button>  
                <button class="confirm-btn" onclick="openRejectModal()">Reject</button>
            @endif
        </div>
    </div>
</div>

@php
    $canVerify = in_array(auth()->user()->role, ['manager', 'hod']);
@endphp

@if($canVerify && $cert->status == 'pending_review')
    <div id="confirmModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Review and Confirm</h3>
            
            <p>Are you sure you want to confirm the data? Please type <strong>CONFIRM</strong> to proceed.</p>
            <form id='confirmForm' method="POST" action="{{ route('certificates.confirm', $cert->id) }}">
                @csrf
                <input type="text" id="confirmationInput" name="confirmation_text" placeholder="Type CONFIRM to continue" required style="margin-bottom: 15px;">
                <div class="modal-actions">
                    <button type="submit" class="confirm-btn">Confirm</button>
                    <button type="button" class="btn-back" onclick="closeConfirmModal()">Cancel</button>
                </div>
            </form> 
        </div>
    </div>
@endif

@if(auth()->user()->role === 'hod' && $cert->status === 'pending_hod_approval')
    <div id="approveModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Review and Approve</h3>
            <p>Are you sure you want to approve the certificate? Please type <strong>APPROVE</strong> to proceed.</p>
            
            <form method="POST" action="{{ route('certificates.hod-approval', $cert->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="action" value="approve">
                <input type="text" id="hodConfirmationInput" name="hod_confirmation_text" placeholder="Type APPROVE to continue" required>
                <div class="modal-actions" style="margin-top: 15px;">
                    <button type="submit" class="confirm-btn" onclick="document.getElementById('hodAction').value='approve'">Approve</button>
                    <button type="button" class="btn-back" onclick="closeApproveModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Review and Reject</h3>
            <p>Are you sure you want to reject the certificate? Please type <strong>REJECT</strong> to proceed.</p>
            
            <form method="POST" action="{{ route('certificates.hod-approval', $cert->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="action" value="reject">
                <input type="text" id="hodRejectInput" name="hod_reject_text" placeholder="Type REJECT to continue" required>
                <div class="modal-actions" style="margin-top: 15px;">
                    <button type="submit" class="confirm-btn">Reject</button>
                    <button type="button" class="btn-back" onclick="closeRejectModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    function openConfirmModal(){
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal(){
        document.getElementById('confirmModal').style.display = 'none';
    }

    function openApproveModal(){
    document.getElementById('approveModal').style.display = 'flex';
    }

    function closeApproveModal(){
        document.getElementById('approveModal').style.display = 'none';
    }

    function openRejectModal(){
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal(){
        document.getElementById('rejectModal').style.display = 'none';
    }
</script>
@endsection
