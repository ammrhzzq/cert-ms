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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
                </ol>
                <img src="{{ asset('images/create_client_dashboard.png') }}" alt="Create Client Dashboard" class="manual-img">
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
                <ol>
                    <li>Navigate to the <strong>Dashboard</strong> from the main menu.</li>
                    <li>Click on <strong>Create New Client</strong> to open the client creation form.</li>
                    <li>Fill in all required client details such as name, company, and contact information.</li>
                    <li>Click <strong>Submit</strong> to add the new client to the system.</li>
                    <li>After creation, the client will appear in the client list on the dashboard.</li>
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