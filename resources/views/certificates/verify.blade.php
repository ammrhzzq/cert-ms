{{-- resources/views/certificates/verify.blade.php --}}
@extends('layouts.client')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h4>Certificate Verification</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>{{ $cert->comp_name }} - {{ $cert->cert_type }}</h3>
                        <span class="status-badge status-{{ $cert->status }}">
                            {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Certificate Type</th>
                                    <td>{{ $cert->cert_type }}</td>
                                </tr>
                                <tr>
                                    <th>ISO Number</th>
                                    <td>{{ $cert->iso_num }}</td>
                                </tr>
                                <tr>
                                    <th>Company Name</th>
                                    <td>{{ $cert->comp_name }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>
                                        {{ $cert->comp_address1 }}<br>
                                        @if($cert->comp_address2) {{ $cert->comp_address2 }}<br> @endif
                                        @if($cert->comp_address3) {{ $cert->comp_address3 }} @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Registration Date</th>
                                    <td>{{ $cert->reg_date->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Issue Date</th>
                                    <td>{{ $cert->issue_date->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Expiry Date</th>
                                    <td>{{ $cert->exp_date->format('d-m-Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                <h5>Certificate Preview</h5>
                            </div>
                            <div class="embed-responsive embed-responsive-1by1">
                                <iframe class="embed-responsive-item" src="{{ route('certificates.previewDraft', $cert->id) }}" allowfullscreen></iframe>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('certificates.previewDraft', $cert->id) }}" class="btn-action" target="_blank">Open PDF in New Tab</a>
                            </div>
                        </div>
                    </div>

                    @if(!$verification->is_verified)
                    <div class="mt-4">
                        <hr>
                        <h3 style="margin-top: 8px;">Please verify this certificate</h3>
                        <p>Review the certificate details above and verify that they are correct.</p>
                        
                        <div class="button-group mt-3" style="display: flex; justify-content: left;">
                            <button type="button" class="confirm-btn" id="showVerifyModal">
                                <i class="fas fa-check-circle"></i> Verify Certificate
                            </button>
                            <button type="button" class="btn-back" id="showUnverifyModal">
                                <i class="fas fa-times-circle"></i> Request Changes
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="mt-4 text-center">
                        @php
                            $verifier = $comments->firstWhere('comment_type', 'verification');
                        @endphp
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <h5 class="mt-2">This certificate has been verified on {{ $verification->verified_at->format('d M Y') }}
                                @if($verifier)
                                    by {{ $verifier->commented_by }}
                                @endif
                            </h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($comments->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5>Comments</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($comments as $comment)
                        <li class="list-group-item {{ $comment->comment_type == 'revision_request' ? 'list-group-item-warning' : 'list-group-item-light' }}">
                            <div style="display: flex; align-items: flex-start; gap: 10p;">
                                <div style="flex-grow: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 600; color: #333;">{{ $comment->commented_by }}</span>
                                        <small style="color: #888;">{{ $comment->created_at->diffForHumans() }} ({{ $comment->created_at->format('d M Y H:i') }})</small>
                                    </div>
                                    <div style="margin-top: 8px; color: #444; line-height: 1.5;">
                                        {{ $comment->comment }}
                                    </div>
                                    @if ($comment->comment_type == 'revision_request')
                                        <div style="margin-top: 5px;">
                                            <span class="badge badge-warning" style="font-size: 12px;">[Request Change]</span>
                                        </div>
                                    @elseif($comment->comment_type === 'verification')
                                        <div style="margin-top: 5px;">
                                            <span class="badge badge-success" style="font-size: 12px;">[Verified]</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div class="modal" id="verifyModal" style="display: none;">
    <div class="modal-content">
        <form action="{{ route('certificates.process-verification', $verification->token) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="verify">
            
            <h3>Verify Certificate</h3>
            <p>Are you sure you want to verify this certificate?</p>
            <p>By verifying, you confirm that all details are correct and the certificate can be issued in its current form.</p>
            
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-back closeModal">Cancel</button>
                <button type="submit" class="confirm-btn">Confirm Verification</button>
            </div>
        </form>
    </div>
</div>

<!-- Unverify Modal -->
<div class="modal" id="unverifyModal" style="display: none;">
    <div class="modal-content">
        <form action="{{ route('certificates.process-verification', $verification->token) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="reject">
            
            <h3>Request Changes</h3>
            <p class="text-danger">You are requesting changes to this certificate.</p>
            
            <div class="form-group">
                <label for="unverify-name">Your Name</label>
                <input type="text" class="form-control" id="unverify-name" name="name" required>
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="unverify-comment">Please provide detailed feedback about what needs to be changed</label>
                <textarea class="form-control" id="unverify-comment" name="comment" rows="5" required></textarea>
                @error('comment')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-back closeModal">Cancel</button>
                <button type="submit" class="confirm-btn">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Font Awesome if not already included
    if (!document.querySelector('link[href*="font-awesome"]')) {
        const fontAwesome = document.createElement('link');
        fontAwesome.rel = 'stylesheet';
        fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        document.head.appendChild(fontAwesome);
    }
    
    // Verify Modal
    const verifyModal = document.getElementById('verifyModal');
    const showVerifyBtn = document.getElementById('showVerifyModal');
    
    if (showVerifyBtn) {
        showVerifyBtn.addEventListener('click', function() {
            verifyModal.style.display = 'flex';
        });
    }
    
    // Unverify Modal
    const unverifyModal = document.getElementById('unverifyModal');
    const showUnverifyBtn = document.getElementById('showUnverifyModal');
    
    if (showUnverifyBtn) {
        showUnverifyBtn.addEventListener('click', function() {
            unverifyModal.style.display = 'flex';
        });
    }
    
    // Close buttons
    const closeButtons = document.querySelectorAll('.closeModal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            verifyModal.style.display = 'none';
            unverifyModal.style.display = 'none';
        });
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === verifyModal) {
            verifyModal.style.display = 'none';
        }
        if (event.target === unverifyModal) {
            unverifyModal.style.display = 'none';
        }
    });
});
</script>
@endsection