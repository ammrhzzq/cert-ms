<!-- resources/views/certificates/create.blade.php -->
@extends('layouts.app', ['activeItem' => 'create'])

@section('title', 'Create Certificate')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
<link rel="stylesheet" href="{{ asset('css/preview.css') }}">
@endsection

@section('content')

@php
    // Default to 'certificate_created' as the initial status
    $defaultStatus = 'certificate_created';
    $statusSteps = [
        'certificate_created' => 'Create Certificate',
        'pending_review' => 'Pending Review',
        'pending_client_verification' => 'Pending Client Verification',
        'client_verified' => 'Client Verified',
        'pending_hod_approval' => 'Pending HOD Approval',
        'certificate_issued' => 'Certificate Issued',
    ];
    $statusOrder = array_keys($statusSteps);
    $currentStatusIndex = array_search($defaultStatus, $statusOrder);
@endphp

<div class="card mb-4" style="margin-bottom: 20px; border-bottom: 3px solid var(--primary-color); border-left: 3px solid var(--primary-color);">
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
                    @endphp
                    <div class="cert-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="circle">{{ $loop->index + 1 }}</div>
                        <div class="label">{{ $label }}</div>
                    </div>
                    @if (!$loop->last)
                        <div class="connector {{ $stepIndex < $currentStatusIndex ? 'completed' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<h1>Create Certificate</h1>

<div class="container">
    <div class="form-container">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('certificates.store') }}" method="POST">
            @csrf
            @method('POST')

            <div class="form-group">
                <label>Certificate Type</label>
                <div class="select-container">
                    <select name="cert_type" required>
                        <option value="" selected disabled>Select Certificate Type</option>
                        <option value="ISMS">ISMS</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ISO Number</label>
                <div class="select-container">
                    <select name="iso_num" required>
                        <option value="" disabled selected>Select ISO Number</option>
                        <option value="ISO/IEC 27001:2022">ISO/IEC 27001:2022</option>
                    </select>
                </div>
            </div>

            <!-- Client Selection -->
            <div class="form-group">
                <label for="client_id">Select Client</label>
                <div class="select-container">
                    <select id="client_id" name="client_id">
                        <option value="" selected disabled>Select a client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->comp_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="client-select-hint">
                    <small>Select an existing client or enter details manually below</small>
                </div>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" id="comp_name" name="comp_name" placeholder="Company Name" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" id="comp_address1" name="comp_address1" placeholder="Address Line 1" required>
            </div>

            <div class="form-group">
                <input type="text" id="comp_address2" name="comp_address2" placeholder="Address Line 2">
            </div>

            <div class="form-group">
                <input type="text" id="comp_address3" name="comp_address3" placeholder="Address Line 3">
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="text" id="comp_phone1" name="comp_phone1" placeholder="Contact Number 1">
                    </div>
                    <div class="form-group">
                        <label>Contact Name</label>
                        <input type="text" id="phone1_name" name="phone1_name" placeholder="Contact Name">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="text" id="comp_phone2" name="comp_phone2" placeholder="Contact Number 2">
                    </div>
                    <div class="form-group">
                        <label>Contact Name</label>
                        <input type="text" id="phone2_name" name="phone2_name" placeholder="Contact Name">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Registration Date</label>
                        <input type="date" id="reg_date" name="reg_date" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" name="issue_date" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Expired Date</label>
                        <input type="date" name="exp_date" required>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="reset" class="btn-back">Reset</button>
                <input type="submit" value="Save" />
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const certTypeSelect = document.querySelector('select[name="cert_type"]');
        const isoNumSelect = document.querySelector('select[name="iso_num"]');

        certTypeSelect.addEventListener('change', function() {
            if (this.value === 'ISMS') {
                isoNumSelect.value = 'ISO/IEC 27001:2022';
            }
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        const clientSelect = document.getElementById('client_id');
        
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            
            if (clientId) {
                // Fetch client data via AJAX
                fetch(`/client/${clientId}/data`)
                    .then(response => response.json())
                    .then(client => {
                        // Populate form fields with client data
                        document.getElementById('comp_name').value = client.comp_name;
                        document.getElementById('comp_address1').value = client.comp_address1;
                        document.getElementById('comp_address2').value = client.comp_address2 || '';
                        document.getElementById('comp_address3').value = client.comp_address3 || '';
                        document.getElementById('comp_phone1').value = client.comp_phone1 || '';
                        document.getElementById('phone1_name').value = client.phone1_name || '';
                        document.getElementById('comp_phone2').value = client.comp_phone2 || '';
                        document.getElementById('phone2_name').value = client.phone2_name || '';
                    })
                    .catch(error => {
                        console.error('Error fetching client data:', error);
                    });
            } else {
                // Clear form fields if "Select a client" is chosen
                document.getElementById('comp_name').value = '';
                document.getElementById('comp_address1').value = '';
                document.getElementById('comp_address2').value = '';
                document.getElementById('comp_address3').value = '';
                document.getElementById('comp_phone1').value = '';
                document.getElementById('phone1_name').value = '';
                document.getElementById('comp_phone2').value = '';
                document.getElementById('phone2_name').value = '';
            }
        });
    });
</script>
@endsection