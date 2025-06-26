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
                    <div class="d-flex align-items-center mb-4" style="gap: 10px;">
                        <div style="flex: 1; text-align: center;">
                            <h3 style="margin:0;">{{ $cert->comp_name }} - {{ $cert->cert_type }}</h3>
                        </div>
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
                    </div>

                    @if(!$verification->is_verified)
                    <div class="mt-4" style="text-align: center;">
                        <hr>
                        <h3 style="margin-top: 8px;">Please verify this certificate</h3>
                        <p>Review the certificate details above and verify that they are correct.</p>
                        
                        <div class="button-group mt-3" style="display: flex; justify-content: center;">
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
                            <h5 class="mt-2">This certificate has been verified on {{ $verification->verified_at->format('d M Y') }} at {{ $verification->verified_at->format('H:i') }}
                                @if($verifier)
                                    by {{ $verifier->commented_by }}
                                @endif
                            </h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($comments->whereIn('comment_type', ['revision_request', 'verification', 'internal'])->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <button type="button" class="collapsible-btn"><h5>Comments History</h5></button>
                    </div>
                    <div class="collapsible-content">
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($comments->whereIn('comment_type', ['revision_request', 'verification', 'internal']) as $comment)
                                    <li class="list-group-item" style="border-radius: 4px; border: 0; border-bottom: 2px inset; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 10px;">
                                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                                            <div style="flex-grow: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-weight: 600; color: {{ $comment->comment_type === 'verification' ? '#155724' : '#000000' }};">{{ $comment->commented_by }}</span>
                                                    <small style="color: #888;">{{ $comment->created_at->diffForHumans() }} ({{ $comment->created_at->format('d M Y H:i') }})</small>
                                                </div>
                                                <div style="margin-top: 8px; margin-left: 12px; color: {{ $comment->comment_type === 'verification' ? '#155724' : '#000000' }}; line-height: 1.5; white-space: pre-line;">
                                                    {{ $comment->comment }}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
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

    var btn = document.querySelector('.collapsible-btn');
    var content = document.querySelector('.collapsible-content');

    btn.addEventListener('click', function () {
        this.classList.toggle('active');

        if (content.style.maxHeight) {
            content.style.maxHeight = null;
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
        }
    });
});
</script>
@endsection