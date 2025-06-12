{{-- resources/views/certificates/verify.blade.php --}}
@extends('layouts.client')

@section('styles')
<style>
/* Self-contained styles instead of relying on external CSS */
:root {
    --primary-color: #f2651f;
    --sidebar-bg: #ffffff;
    --card-bg: #ffffff;
    --text-color: #333333;
    --border-color: #ddd;
    --input-bg: #ffffff;
    --hover-color: #f8f9fa;
    --cancel-hover: #e2e6ea;
    --orange-border: #fd7e14;
    --yellow-border: #ffc107;
    --red-border: #dc3545;
    --green-border: #28a745;
    --blue-border: #007bff;
}

/* General styling */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
}

/* Main container styling */
.container {
    width: 100%;
    max-width: 100%;
    margin: 20px auto;
    margin-top: 0px;
    padding: 0 15px;
}

.col-md-10 {
    flex: 0 0 83.333333%;
    max-width: 83.333333%;
    padding: 0 15px;
    margin: 0 auto;
}

.justify-content-center {
    justify-content: center;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 15px;
}

/* Card styling */
.card {
    background-color: var(--card-bg);
    border-radius: 8px;
    border-bottom: 3px solid var(--primary-color);
    border-left: 3px solid var(--primary-color);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.card-header {
    padding: 20px 30px;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--card-bg);
    border-radius: 8px 8px 0 0;
}

.card-body {
    padding: 30px;
}

.card-header h4, .card-header h5 {
    font-size: 24px;
    font-weight: 600;
    color: var(--text-color);
    margin: 0;
}

/* Flexbox utilities */
.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.mt-3 {
    margin-top: 1rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.text-center {
    text-align: center;
}

/* Table styling */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
}

.table th, .table td {
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    text-align: left;
    vertical-align: top;
}

.table th {
    background-color: var(--hover-color);
    font-weight: 600;
    color: var(--text-color);
}

.table td {
    color: var(--text-color);
}

.table-bordered {
    border: 1px solid var(--border-color);
}

/* Status badge styling */
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: white;
    font-size: 14px;
}

.status-client_verified {
    background-color: var(--green-border);
}

.status-pending_review {
    background-color: var(--orange-border);
}

/* Alert styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background-color: #fef2f2;
    border: 1px solid var(--green-border);
    color: #155724;
}

.alert-info {
    background-color: #e1f5fe;
    border: 1px solid var(--blue-border);
    color: #0c5460;
}

.alert-danger {
    background-color: #fef2f2;
    border: 1px solid var(--red-border);
    color: #b91c1c;
}

.alert-warning {
    background-color: #fff8e1;
    border: 1px solid var(--yellow-border);
    color: #856404;
}

/* Form styling */
.form-group {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--text-color);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 14px;
    background-color: var(--input-bg);
    color: var(--text-color);
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(242, 101, 34, 0.2);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* Button styling */
.button-group {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}

.confirm-btn {
    background-color: var(--primary-color);
    color: var(--sidebar-bg);
    border: none;
    padding: 10px 20px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.confirm-btn:hover {
    background-color: var(--primary-color);
    opacity: 0.9;
}

.btn-back {
    background-color: var(--border-color);
    color: var(--text-color);
    border: none;
    padding: 10px 20px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-back:hover {
    background-color: var(--cancel-hover);
}

.btn-action {
    background-color: var(--primary-color);
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-block;
    margin-right: 10px;
    transition: background-color 0.3s ease;
}

.btn-action:hover {
    background-color: #e75e1f;
}

/* Modal styling */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: var(--card-bg);
    color: var(--text-color);
    padding: 30px;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    text-align: center;
    animation: fadeIn 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px);}
    to { opacity: 1; transform: translateY(0);}
}

/* List styling */
.list-group {
    padding-left: 0;
    list-style: none;
    margin-bottom: 0;
}

.list-group-item {
    position: relative;
    display: block;
    padding: 15px;
    margin-bottom: -1px;
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
}

.list-group-item-warning {
    background-color: #fff8e1;
    border-color: var(--yellow-border);
}

.list-group-item-light {
    background-color: var(--hover-color);
}

/* Embed responsive */
.embed-responsive {
    position: relative;
    display: block;
    width: 100%;
    padding: 0;
    overflow: hidden;
    height: 441px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.embed-responsive-item {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Text utilities */
.text-danger {
    color: var(--red-border) !important;
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        width: 95%;
        padding: 20px;
    }

    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .col-md-10 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card-body {
        padding: 20px;
    }

    .button-group {
        flex-direction: column;
        gap: 10px;
    }

    .modal-content {
        margin: 20px;
        width: calc(100% - 40px);
    }

    .badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 12px;
    font-weight: 500;
    }

    .badge-warning {
        background-color: #ffeeba;
        color: #856404;
    }

    .badge-success {
        background-color: #c3e6cb;
        color: #155724;
    }
}
</style>
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