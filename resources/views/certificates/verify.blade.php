<!-- resources/views/certificates/verify.blade.php -->
@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
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
                        <h5>{{ $cert->comp_name }} - {{ $cert->cert_type }}</h5>
                        <span class="status-badge status-{{ $verification->is_verified ? 'client_verified' : 'pending_review' }}">
                            {{ $verification->is_verified ? 'Verified' : 'Pending Verification' }}
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
                                <iframe class="embed-responsive-item" src="{{ route('certificates.preview-draft', $cert->id) }}" allowfullscreen></iframe>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('certificates.preview-draft', $cert->id) }}" class="btn-action" target="_blank">Open PDF in New Tab</a>
                            </div>
                        </div>
                    </div>

                    @if(!$verification->is_verified)
                    <div class="mt-4">
                        <hr>
                        <h5>Please verify this certificate</h5>
                        <p>Review the certificate details above and verify that they are correct.</p>
                        
                        <div class="button-group mt-3">
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
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <h5 class="mt-2">This certificate has been verified on {{ $verification->verified_at->format('d M Y') }}</h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($comments->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5>Comments History</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($comments as $comment)
                        <li class="list-group-item {{ $comment->comment_type == 'revision_request' ? 'list-group-item-warning' : 'list-group-item-light' }}">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->commented_by }}</strong>
                                <small>{{ $comment->created_at->format('d M Y H:i') }}</small>
                            </div>
                            <p class="mt-2 mb-0">{{ $comment->comment }}</p>
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
            
            <h5>Verify Certificate</h5>
            <p>Are you sure you want to verify this certificate?</p>
            <p>By verifying, you confirm that all details are correct and the certificate can be issued in its current form.</p>
            
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="comment">Additional Comments (Optional)</label>
                <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
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
            <input type="hidden" name="action" value="unverify">
            
            <h5>Request Changes</h5>
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