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
                            <p><strong>Link Expires:</strong> {{ \Carbon\Carbon::parse($verification->expires_at)->format('d M Y H:i') }}</p>
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
            
            <!-- Added Certificate Documents Section -->
            <div class="detail-group">
                <div class="detail-label">Certificate Documents</div>
                <div class="detail-value">
                    @if($cert->draft_path)
                        <div class="document-actions">
                            <a href="{{ route('certificates.preview-draft', $cert->id) }}" class="btn-action" target="_blank">Preview Draft</a>
                            <a href="{{ route('certificates.download', $cert->id) }}" class="btn-action">Download Draft</a>
                        </div>
                    @endif

                    @if($cert->generate_pdf_path)
                        <div class="document-actions">
                            <a href="{{ route('certificates.preview-final', $cert->id) }}" class="btn-action" target="_blank">Preview Final Certificate</a>
                            <a href="{{ route('certificates.download', $cert->id) }}" class="btn-action">Download Final Certificate</a>
                        </div>
                    @endif
                    
                    @if(!$cert->draft_path && !$cert->generate_pdf_path)
                        <p>No certificate documents have been generated yet.</p>
                        
                        @if($cert->status == 'pending_review')
                            <button type="button" class="btn-action" onclick="openConfirmModal()">
                                Generate Draft Certificate
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            @if(isset($comments) && count($comments) > 0)
                <div class="detail-group">
                    <div class="detail-label">Comments History</div>
                    <div class="detail-value">
                        <div class="comments-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Comment</th>
                                        <th>From</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comments as $comment)
                                        <tr class="{{ $comment->comment_type == 'revision_request' ? 'warning-row' : '' }}">
                                            <td>{{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}</td>
                                            <td>{{ $comment->comment }}</td>
                                            <td>{{ $comment->commented_by }}</td>
                                            <td>
                                                @if($comment->comment_type == 'verification')
                                                    <span class="status-badge status-success">Verification</span>
                                                @elseif($comment->comment_type == 'revision_request')
                                                    <span class="status-badge status-warning">Revision Request</span>
                                                @else
                                                    <span class="status-badge status-secondary">Internal</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
            @if (in_array(auth()->user()->role, ['manager', 'hod']) && $cert->status == 'pending_review')
                <button class="confirm-btn" onclick="openConfirmModal()">Confirm Data</button>  
            @endif 
        </div>
    </div>
</div>

@php
    $canVerify = in_array(auth()->user()->role, ['manager', 'hod']);
@endphp

@if($canVerify && $cert->status !== 'client_verified')
    <div class="review-section">
        <button class="btn btn-warning" onclick="doucment.getElementById('confirmModal').style.display='block'">Review & Confirm</button>
    </div>

    <div id="confirmModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Review and Confirm</h3>
            
            <p>Are you sure you want to confirm the data? Please type <strong>CONFIRM</strong> to proceed.</p>
            <form id='confirmForm' method="POST" action="{{ route('certificates.confirm', $cert->id) }}">
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

<script>
    function openConfirmModal(){
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal(){
        document.getElementById('confirmModal').style.display = 'none';
    }
</script>
@endsection
