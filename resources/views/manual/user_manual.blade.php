{{-- filepath: resources/views/manual/user_manual.blade.php --}}
@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/manual.css') }}">
@endsection

@section('title', 'User Manual')

@section('content')
<h1 style="margin-top: 20px">User Manual</h1>
    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">1</span>
                <h3><strong>Navigating Main Dashboard</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <h4>Dashboard Overview</h4>
                <img src="{{ asset('images/dashboard_1.png') }}" alt="Main Dashboard 1" class="manual-img">
                <ol>
                    <li>Side Menu Bar</li>
                    <li>User Manual Button</li>
                    <li>Notification Button</li>
                    <li>Dark Mode Button</li>
                    <li>User Profile</li>
                    <li>Logout Button</li>
                <img src="{{ asset('images/dashboard_2.png') }}" alt="Main Dashboard 2" class="manual-img">
                    <li>Side Menu:
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>Dashboard</li>
                            <li>Create New</li>
                            <li>Status and Action</li>
                            <li>View and Search</li>
                            <li>Template Management</li>
                            <li>Client Management</li>
                            <li>User Management</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">2</span>
                <h3><strong>How to Create Certificate</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <h4 style="margin-bottom: 20px">Creating Certificate</h4>
                <img src="{{ asset('images/create_cert_form.png') }}" alt="Cert Form" class="manual-img">
                <p style="margin-bottom: 20px">Steps to Create Certificate</p>
                <ol>
                    <li>Fill in the required data:</li>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>Certificate Type</li>
                            <li>ISO Number</li>
                            <li>Select Client (If there is existing client)</li>
                            <li>Company Name</li>
                            <li>Address Line 1</li>
                            <li>Address Line 2</li>
                            <li>Address Line 3</li>
                            <li>Contact Person 1 Name</li>
                            <li>Contact Number 1</li>
                            <li>Contact Person 2 Name</li>
                            <li>Contact Number 2</li>
                            <li>Registration Date</li>
                            <li>Issue Date</li>
                            <li>Expiry Date</li>
                        </ul>
                    <li>Click on <strong>Save</strong>.</li>
                    <li>The certificate process is created.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">3</span>
                <h3><strong>Certificate Verification Process</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <h4>Certificate Progress Bar</h4>
                <img src="{{ asset('images/cert_progress.png') }}" alt="Cert Progress" class="manual-img">
                <ol>
                    <img src="{{ asset('images/pending_review.png') }}" alt="Pending Review" class="manual-img">

                    <li>After saving, the certificate will be in <strong>Pending Review</strong> status.</li>
                    <li>Click on <strong>Submit for Review</strong>.</li>
                    <li>The certificate will be sent for review by <strong>Manager</strong> and will change to <strong>Pending Client Verification</strong>.</li>

                    <img src="{{ asset('images/pending_client_verification.png') }}" alt="Pending Client Verification" class="manual-img">

                    <li>After review, the certificate will be in <strong>Pending Client Verification</strong> status.</li>
                    <li>The manager will copy the link generated and <strong>Send to Client</strong>.</li>

                    <img src="{{ asset('images/client_verification_page.png') }}" alt="Client Verification Page" class="manual-img">

                    <li>The client will receive the link and will be able to view the certificate.</li>
                    <li>On the verification page, the client will see the certificate details and can verify it.</li>
                    <li>If there is error, the client can click on <strong>Request Changes</strong> and provide details.</li>
                    <li>The system will show the comment</li>
                    <li>Once the client verifies the certificate, it will change to <strong>Client Verified</strong>.</li>

                    <img src="{{ asset('images/client_verify_page.png') }}" alt="Client Verified" class="manual-img">

                    <li>After client verification, the certificate will be in <strong>Client Verified</strong> status.</li>
                    <li><strong>Manager</strong> will assign <strong>Certificate Number</strong> by clicking on Assign Certificate Number</strong>.</li>

                    <img src="{{ asset('images/assign_num_button.png') }}" alt="Assign Certificate Number Button" class="manual-img">

                    <li>On the Assign Certificate Number page, the manager will fill in the certificate number.</li>

                    <img src="{{ asset('images/assign_certificate_num.png') }}" alt="Assign Certificate Number Form" class="manual-img">

                    <li>After assigning the certificate number, the certificate will be in <strong>Pending HOD Approval</strong> status.</li>

                    <img src="{{ asset('images/hod_verification.png') }}" alt="Pending HOD Approval" class="manual-img">

                    <li>HOD will review the certificate.</li>
                    <li>If there is any error, HOD can click on <strong>Reject</strong> and provide details.</li>

                    <img src="{{ asset('images/hod_comment_status.png') }}" alt="Reject Certificate" class="manual-img">
                    <li><strong>HOD</strong> will review the certificate and click on <strong>Approve</strong>.</li>
                    <li>After approval, the certificate will be in <strong>Certificate Issued</strong> status.</li>

                    <img src="{{ asset('images/issued_status.png') }}" alt="Certificate Issued" class="manual-img">

                    <li>The certificate is generated and can be viewed in View and Search section.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">4</span>
                <h3><strong>Searching for Certificate</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <ol>
                    <img src="{{ asset('images/search_dashboard.png') }}" alt="Search Dashboard" class="manual-img">
                    <li>Navigate to the <strong>View and Search</strong> section from the main menu.</li>
                    <li>Use the search bar to enter keywords related to the certificate you want to find.</li>
                    <li>Click on the <strong>Search</strong> button to filter the results.</li>
                    <li>The system will display a list of certificates matching your search criteria.</li>
                    
                    <img src="{{ asset('images/cert_preview.png') }}" alt="Create Client Dashboard" class="manual-img">
                    <li>Click on a certificate to view its details.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">5</span>
                <h3><strong>Managing Template</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <img src="{{ asset('images/template_dashboard.png') }}" alt="Template Dashboard" class="manual-img">
                <ol>
                    <li>Navigate to the <strong>Template Management</strong> from the main menu.</li>
                    <li>Click on <strong>Upload New Template</strong> to open the template upload form.</li>

                    <img src="{{ asset('images/upload_template_form.png') }}" alt="Upload Template Form" class="manual-img">

                    <li>Fill in the required details such as template name and select the template file.</li>
                    <li>Click <strong>Upload</strong> to add the new template to the system.</li>
                    <li>After uploading, the template will appear in the template list on the dashboard.</li>
                    <li>Click on a template to view its details.</li>

                    <img src="{{ asset('images/preview_template.png') }}" alt="Preview Template" class="manual-img">
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">6</span>
                <h3><strong>Create Client</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <img src="{{ asset('images/client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
                <ol>
                    <li>Navigate to the <strong>Client Management</strong> from the main menu.</li>

                    <img src="{{ asset('images/create_client_form.png') }}" alt="Create Client Dashboard" class="manual-img">

                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <button type="button" class="collapsible-btn" style="margin-bottom: 0; display: flex; align-items: center; gap: 12px;">
                <span class="manual-step-circle">7</span>
                <h3><strong>Create User</strong></h3>
                <i class="fa fa-chevron-down" style="margin-left:auto; color: var(--text-color);"></i>
            </button>
        </div>
        <div class="collapsible-content">
            <div class="card-body">
                <img src="{{ asset('images/user_dashboard.png') }}" alt="Create User Dashboard" class="manual-img">
                <ol>
                    <li>Navigate to the <strong>User Management</strong> from the main menu.</li>

                    <img src="{{ asset('images/create_user_form.png') }}" alt="Create User Form" class="manual-img">
                    <li>Click on <strong>Create User</strong> to open the user creation form.</li>
                    <li>Fill in all required user details such as Name, Email, Role, Password and Confirm Password.</li>
                    <li>Click <strong>Submit</strong> to add the new user to the system.</li>
                    <li>After creation, the user will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
            </div>
        </div>
    </div>
    
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.collapsible-btn').forEach(function(btn) {
        btn.addEventListener('click', function () {
            // Close all other sections
            document.querySelectorAll('.collapsible-btn').forEach(function(otherBtn) {
                if (otherBtn !== btn) {
                    otherBtn.classList.remove('active');
                    var otherContent = otherBtn.closest('.card').querySelector('.collapsible-content');
                    var otherIcon = otherBtn.querySelector('i');
                    if (otherContent) otherContent.style.maxHeight = null;
                    if (otherIcon) {
                        otherIcon.classList.remove('fa-chevron-up');
                        otherIcon.classList.add('fa-chevron-down');
                    }
                }
            });

            // Toggle this section
            var content = btn.closest('.card').querySelector('.collapsible-content');
            var icon = btn.querySelector('i');
            btn.classList.toggle('active');
            if (content.style.maxHeight && content.style.maxHeight !== "0px") {
                content.style.maxHeight = null;
                if(icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                if(icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            }
        });
    });
});
</script>
@endsection