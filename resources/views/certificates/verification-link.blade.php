@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Certificate Verification Link</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>Certificate:</strong> {{ $cert->cert_type }} ({{ $cert->iso_num }})</p>
                        <p><strong>Company:</strong> {{ $cert->comp_name }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="verification-link">Verification Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="verification-link" value="{{ $verificationUrl }}" readonly>
                            <div class="input-group-append">
                                {{-- Applied custom primary button style --}}
                                <button class="btn-custom-primary" type="button" onclick="copyToClipboard()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            This link will expire on {{ $verification->expires_at ? $verification->expires_at->format('d M Y H:i') : 'N/A' }}
                        </small>
                    </div>
                    
                    <div class="mt-4">
                        <h5>How to Share this Link</h5>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Email Template</h6>
                                <div class="form-group">
                                    <textarea class="form-control" rows="10" id="email-template" readonly>Subject: Certificate Verification Required - {{ $cert->comp_name }}

                                    Dear Client,

                                    We have prepared the draft certificate for your {{ $cert->cert_type }} ({{ $cert->iso_num }}) certification. Please review the certificate details and confirm their accuracy.

                                    To verify the certificate, please click the link below:
                                    {{ $verificationUrl }}

                                    This link will expire on {{ $verification->expires_at ? $verification->expires_at->format('d M Y, H:i') : 'N/A' }}.

                                    If you notice any discrepancies or require changes, please indicate so using the "Request Revision" option on the verification page.

                                    Thank you for your cooperation.

                                    Best regards,
                                    [Your Name]
                                    {{ config('app.name') }}</textarea>
                                </div>
                                {{-- Applied custom primary button style (smaller variant) --}}
                                <button class="btn-custom-primary-sm" onclick="copyEmailTemplate()">
                                    <i class="fas fa-copy"></i> Copy Email Template
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Verification Status</h6>
                                    @if($verification->is_verified && $verification->verified_at)
                                        <div class="alert alert-success mb-0">
                                            <strong>Verified</strong> on {{ $verification->verified_at->format('d M Y H:i') }}
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <strong>Pending Verification</strong><br>
                                            <small>Expires: {{ $verification->expires_at ? $verification->expires_at->format('d M Y H:i') : 'N/A' }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            {{-- Applied existing btn-back style --}}
                            <a href="{{ route('certificates.show', $cert->id) }}" class="btn-back btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Certificate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function copyToClipboard() {
        const element = document.getElementById('verification-link');
        element.select();
        document.execCommand('copy');
        
        // Show feedback
        const button = element.nextElementSibling.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }
    
    function copyEmailTemplate() {
        const element = document.getElementById('email-template');
        element.select();
        document.execCommand('copy');
        
        // Show feedback
        const button = document.querySelector('button[onclick="copyEmailTemplate()"]');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }
</script>
@endsection