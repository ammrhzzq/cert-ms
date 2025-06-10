@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="form-container">
        <!-- Header Section -->
        <div style="margin-bottom: 30px;">
            <h1 style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                Certificate Verification Link
            </h1>
            <p style="color: var(--text-color); opacity: 0.7; margin: 0;">Share verification link with your client</p>
        </div>

        <!-- Certificate Information -->
        <div class="detail-container" style="margin-bottom: 30px; max-width: 100%;">
            <div class="detail-row">
                <div class="detail-column">
                    <div class="detail-group">
                        <div class="detail-label">Certificate Type</div>
                        <div class="detail-value">{{ $cert->cert_type }} ({{ $cert->iso_num }})</div>
                    </div>
                </div>
                <div class="detail-column">
                    <div class="detail-group">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">{{ $cert->comp_name }}</div>
                    </div>
                </div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Verification Status</div>
                <div class="detail-value" style="display: flex; align-items: center; gap: 10px;">
                    @if($verification->is_verified && $verification->verified_at)
                        <span class="status-badge status-certificate_issued">
                            <i class="fas fa-check-circle"></i> Verified
                        </span>
                        <span style="font-size: 14px; opacity: 0.7;">
                            on {{ $verification->verified_at->format('d M Y H:i') }}
                        </span>
                    @else
                        <span class="status-badge status-pending_client_verification">
                            <i class="fas fa-clock"></i> Pending Verification
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Step 1: Verification Link -->
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 600;">
                <span style="background-color: var(--primary-color); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">1</span>
                Verification Link
            </label>
            
            <div style="display: flex; gap: 10px; align-items: stretch;">
                <input type="text" 
                       id="verification-link" 
                       value="{{ $verificationUrl }}" 
                       readonly 
                       style="flex: 1; background-color: var(--hover-color);">
                <button class="btn-custom-primary" type="button" onclick="copyToClipboard()" id="copy-link-btn">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            
            @if($verification->expires_at)
            <div class="alert alert-danger" style="margin-top: 10px; margin-bottom: 0;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> This link will expire on {{ $verification->expires_at->format('d M Y, H:i') }}
            </div>
            @endif
        </div>

        <!-- Step 2: Email Template -->
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 600;">
                <span style="background-color: var(--primary-color); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">2</span>
                Email Template
            </label>
            
            <div class="detail-container" style="max-width: 100%; padding: 20px;">
                <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 16px; color: var(--text-color);">
                        <i class="fas fa-envelope"></i> Email
                    </h3>
                    <button class="btn-custom-primary-sm" onclick="copyEmailTemplate()" id="copy-email-btn" style="margin-left: auto;">
                        <i class="fas fa-copy"></i> Copy Template
                    </button>
                </div>
                
                <textarea id="email-template" 
                          readonly 
                          style="width: 100%; min-height: 300px; border: 1px solid var(--border-color); border-radius: 4px; padding: 15px; background-color: var(--input-bg); color: var(--text-color); font-family: monospace; font-size: 13px; line-height: 1.5; resize: vertical;">Subject: Certificate Verification Required - {{ $cert->comp_name }}

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
        </div>

        <!-- Instructions -->
        <div class="detail-container" style="max-width: 100%; background-color: var(--hover-color); border-left: 4px solid var(--primary-color);">
            <h3 style="margin-top: 0; font-size: 16px; color: var(--text-color);">
                <i class="fas fa-info-circle" style="margin-bottom: 8px;"></i> How to Use
            </h3>
            <ol style="margin: 0; padding-left: 20px; color: var(--text-color);">
                <li style="margin-bottom: 8px;">Copy the verification link using the "Copy" button above</li>
                <li style="margin-bottom: 8px;">Or copy the complete email template and send it to your client</li>
                <li style="margin-bottom: 8px;">The client will click the link to review and verify the certificate</li>
                <li style="margin-bottom: 0;">You'll be notified once the client completes the verification</li>
            </ol>
        </div>

        <!-- Action Buttons -->
        <div class="button-group" style="margin-top: 30px; justify-content: space-between;">
            <a href="{{ route('certificates.preview', $cert->id) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Certificate
            </a>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn-newtab" onclick="window.open('{{ $verificationUrl }}', '_blank')" type="button">
                    <i class="fas fa-external-link-alt"></i> Preview Verification Page
                </button>
            </div>
        </div>

        @if($verification->is_verified && $verification->verified_at)
        <div class="last-edited-info">
            Certificate verified on {{ $verification->verified_at->format('d M Y \a\t H:i') }}
        </div>
        @endif
    </div>
</div>

<!-- Success Modal (if needed) -->
<div id="success-modal" class="modal" style="display: none; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content">
        <div style="color: var(--primary-color); font-size: 48px; margin-bottom: 15px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 style="margin: 0 0 10px 0; color: var(--text-color);">Copied Successfully!</h3>
        <p style="margin: 0; color: var(--text-color); opacity: 0.8;">The content has been copied to your clipboard.</p>
        <div class="modal-actions" style="margin-top: 20px;">
            <button class="btn-custom-primary" onclick="closeModal()">OK</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard() {
        const element = document.getElementById('verification-link');
        const button = document.getElementById('copy-link-btn');
        
        // Select and copy the text
        element.select();
        element.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
        
        // Visual feedback
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.backgroundColor = 'var(--green-border)';
        
        // Reset after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.backgroundColor = 'var(--primary-color)';
        }, 2000);
        
        // Deselect the input
        element.blur();
        
        // Show success feedback
        showSuccessMessage('Verification link copied to clipboard!');
    }
    
    function copyEmailTemplate() {
        const element = document.getElementById('email-template');
        const button = document.getElementById('copy-email-btn');
        
        // Select and copy the text
        element.select();
        element.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
        
        // Visual feedback
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.backgroundColor = 'var(--green-border)';
        
        // Reset after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.backgroundColor = 'var(--primary-color)';
        }, 2000);
        
        // Deselect the textarea
        element.blur();
        
        // Show success feedback
        showSuccessMessage('Email template copied to clipboard!');
    }
    
    function refreshStatus() {
        // Reload the page to check for updated verification status
        window.location.reload();
    }
    
    function showSuccessMessage(message) {
        // Create a temporary success indicator
        const indicator = document.createElement('div');
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--green-border);
            color: white;
            padding: 15px 20px;
            border-radius: 4px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;
        indicator.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        
        // Add animation keyframes if not already present
        if (!document.querySelector('#success-animation-styles')) {
            const style = document.createElement('style');
            style.id = 'success-animation-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(indicator);
        
        // Remove after 3 seconds
        setTimeout(() => {
            indicator.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(indicator);
            }, 300);
        }, 3000);
    }
    
    function closeModal() {
        document.getElementById('success-modal').style.display = 'none';
    }
    
    // Auto-select text when clicking on input/textarea for easier copying
    document.getElementById('verification-link').addEventListener('click', function() {
        this.select();
    });
    
    document.getElementById('email-template').addEventListener('click', function() {
        this.select();
    });
</script>

{{-- Move PHP condition OUTSIDE the script block --}}
@if($verification->is_verified && $verification->verified_at)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showSuccessMessage('Certificate has been verified successfully!');
    });
</script>
@endif
@endsection