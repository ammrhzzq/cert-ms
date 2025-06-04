<!-- resources/views/certificates/show.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Certificate Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
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
            } elseif ($cert->revision_source === 'hod') {
                $statusSteps = array_slice($statusSteps, 0, 5, true) +
                    ['need_revision' => 'Need Revision'] +
                    array_slice($statusSteps, 5, null, true);
            }
        }

        $statusOrder = array_keys($statusSteps);
        $currentStatusIndex = array_search($cert->status, $statusOrder) ? array_search($cert->status, $statusOrder) : -1;
        $stepNumber = 1;
    @endphp
    <div class="card mb-4" style="border-bottom: 3px solid var(--primary-color); border-left: 3px solid var(--primary-color);">
        <div class="card-header">
            <h2>Certificate Progress</h2>
        </div>
        <div class="card-body" style="display: flex; justify-content: center; align-items: center; padding-top: 20px;">
            <div class="cert-progress-wrapper">
                <div class="cert-progress-bar">
                    @foreach ($statusSteps as $statusKey => $label)
                        @php
                            $stepIndex = array_search($statusKey, $statusOrder);
                            $isCompleted = $stepIndex <= $currentStatusIndex;
                            $isCurrent = $stepIndex === $currentStatusIndex;
                            $isNeedRevision = $statusKey === 'need_revision';
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
    <div class="container" style="margin-top: 20px;">
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
                            <div class="form-group">
                                <p style="margin-bottom: 15px;">Verification Link<p>
                                <div style="display: flex; gap: 10px; align-items: stretch;">
                                    <input type="text" id="verification-link" value="{{ $verificationUrl }}" readonly style="flex: 1; background-color: white;">
                                    <button class="btn-action" type="button" onclick="copyToClipboard()" id="copy-link-btn">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <!-- Optional: Preview Link Button -->
                            <div style="margin-top: 15px;">
                                <button class="btn-action" onclick="window.open('{{ $verificationUrl }}', '_blank')" type="button">
                                    <i class="fas fa-external-link-alt"></i> Open Verification Page
                                </button>
                                <form method="POST" action="{{ route('certificates.renew-verification', $cert->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-warning">Renew Link</button>
                                </form>
                            </div>

                            <!-- Script to copy the link -->
                            @push('scripts')
                            <script>
                                function copyToClipboard() {
                                    const element = document.getElementById('verification-link');
                                    const button = document.getElementById('copy-link-btn');

                                    navigator.clipboard.writeText(element.value).then(() => {
                                        const originalText = button.innerHTML;
                                        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                                        button.style.backgroundColor = 'var(--green-border)';

                                        setTimeout(() => {
                                            button.innerHTML = originalText;
                                            button.style.backgroundColor = '';
                                        }, 2000);
                                    }).catch(err => {
                                        console.error('Failed to copy: ', err);
                                    });
                                }
                            </script>
                            @endpush
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

            @if(isset($verification) && $verification->is_verified && $cert->status === 'client_verified' && in_array(auth()->user()->role, ['manager', 'hod']))
                <div class="assign-number-section mt-3">
                    <a href="{{ route('certificates.assign-number.form', $cert->id) }}" class="btn-action">Assign Certificate Number</a>
                </div>
            @endif

            @if ($cert->status === 'pending_hod_approval')
                <div class="detail-group">
                    <div class="detail-label">Final Certificate Preview</div>
                    <div class="detail-value">
                        <a href="{{ route('certificates.previewDraft', $cert->id) }}" class="btn-action" target="_blank">
                            <i class="fas fa-file-pdf"></i> Preview Final Certificate
                        </a>
                    </div>
                </div>
            @endif

            {{-- Client Comments --}}
            @if ($cert->status === 'need_revision' && $cert->revision_source === 'client')
                @if(isset($comments) && count($comments) > 0)
                    <div class="detail-group">
                        <div class="detail-label">Client Comments</div>
                        <div class="detail-value">
                            <ul style="list-style: none; padding-left: 0;">
                                @foreach($comments as $comment)
                                    @if($comment->comment_type == 'revision_request' && $cert->revision_source === 'client')
                                        <li style="background: #f9f9f9; border: 1px solid #e0e0e0; padding: 16px; border-radius: 6px; margin-bottom: 18px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <p style="font-size: 15px;">{{  $comment->commented_by }}</p>
                                                <small style="color: #888;">{{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}</small>
                                            </div>
                                            <p style="margin-top: 10px; color: #333; line-height: 1.5; font-weight: normal;">{{ $comment->comment }}</p>
                                            <div style="margin-top: 8px;">
                                                <span style="color: red;">Revision Requested</span>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @if(in_array(auth()->user()->role, ['manager', 'hod']))
                    <a href="{{ route('certificates.edit', $cert->id) }}" class="btn-action btn-warning">Edit Certificate</a>
                @endif
            @endif

            {{-- HOD Comments --}}
            @if ($cert->status === 'need_revision' && $cert->revision_source === 'hod')
                @if(isset($comments) && count($comments) > 0)
                    <div class="detail-group">
                        <div class="detail-label">HOD Comments</div>
                        <div class="detail-value">
                            <ul style="list-style: none; padding-left: 0;">
                                @foreach($comments as $comment)
                                    @if($comment->comment_type == 'revision_request' && $cert->revision_source === 'hod')
                                        <li style="background: #f9f9f9; border: 1px solid #e0e0e0; padding: 16px; border-radius: 6px; margin-bottom: 18px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <p style="font-size: 15px;">{{  $comment->commented_by }}</p>
                                                <small style="color: #888;">{{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}</small>
                                            </div>
                                            <p style="margin-top: 10px; color: #333; line-height: 1.5; font-weight: normal;">{{ $comment->comment }}</p>
                                            <div style="margin-top: 8px;">
                                                <span style="color: red;">Revision Requested</span>
                                            </div>
                                        </li>
                                    @endif
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
                <div class="form-group" style="margin-top: 15px;">
                    <label for="hod_reject_comment">Comment (required):</label>
                    <textarea name="hod_reject_comment" id="hod_reject_comment" rows="4" class="form-control" required></textarea>
                </div>
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
