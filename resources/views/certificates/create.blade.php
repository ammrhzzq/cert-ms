<!-- resources/views/certificates/create.blade.php -->
@extends('layouts.app', ['activeItem' => 'create'])

@section('title', 'Create Certificate')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
<link rel="stylesheet" href="{{ asset('css/preview.css') }}">
@endsection

@section('content')
@php
$statusSteps = [
'create_certificate' => 'Create Certificate',
'pending_review' => 'Pending Review',
'pending_client_verification' => 'Pending Client Verification',
'client_verified' => 'Client Verified',
'pending_hod_approval' => 'Pending HOD Approval',
'certificate_issued' => 'Certificate Issued',
];

$currentStatus = 'create_certificate'; // Current step for create page
$statusOrder = array_keys($statusSteps);
$currentStatusIndex = array_search($currentStatus, $statusOrder);
$stepNumber = 1;
@endphp

<div class="progress-card">
    <h1>Certificate Progress</h1>
    <div class="progress-body">
        <div class="cert-progress-wrapper">
            <div class="cert-progress-bar">
                @foreach ($statusSteps as $statusKey => $label)
                @php
                $stepIndex = array_search($statusKey, $statusOrder);
                $isCompleted = $stepIndex < $currentStatusIndex;
                    $isCurrent=$stepIndex===$currentStatusIndex;
                    @endphp

                    <div class="cert-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">
                    <div class="circle">
                        {{ $stepNumber }}
                        @php $stepNumber++; @endphp
                    </div>
                    <div class="label">{{ $label }}</div>
            </div>
            @if (!$loop->last)
            <div class="connector {{ $stepIndex < $currentStatusIndex ? 'completed' : ''}}"></div>
            @endif
            @endforeach
        </div>
    </div>
</div>
</div>

<form action="{{ route('certificates.store') }}" method="POST">
    @csrf
    @method('POST')

    <div class="container">
        <h1>Create Certificate</h1>
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

            <div class="form-row">
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
                    <input type="text" name="iso_num" placeholder="ISO Number" required>
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
                <input type="text" id="comp_address1" name="comp_address1" placeholder="Unit/Department/Branch" required>
            </div>

            <div class="form-group">
                <input type="text" id="comp_address2" name="comp_address2" placeholder="Lot/Unit No., Building/Street Name, Area Name">
            </div>

            <div class="form-group">
                <input type="text" id="comp_address3" name="comp_address3" placeholder="Postcode, City, State">
            </div>

            <div class="form-group">
                <label>Scope</label>
                <textarea name="scope" id="scope" rows="3" placeholder="Enter scope of certification"></textarea>
            </div>

             <div class="form-group">
                <label>SOA (Statement of Applicability)</label>
                <input type="text" id="soa" name="soa" placeholder="Statement of Applicability"></input>
            </div>

             <div class="form-group">
                <label>Registration Number</label>
                <input type="text" id="cert_number" name="cert_number" placeholder="Certification Number"></input>
            </div>

            <div class="form-column">
                <div class="form-row">
                    <div class="form-group">
                        <label>Registration Date</label>
                        <input type="date" id="reg_date" name="reg_date" required>
                    </div>
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" id="issue_date" name="issue_date" required>
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" id="exp_date" name="exp_date" required>
                    </div>
                </div>
            </div>

            <div class="sites-section">
                <label>Sites</label>
                <p>Add sites if applicable</p>

                <div id="sites-container" class="sites-container">
                    <!-- Initial site input (hidden by default) -->
                </div>

                <button type="button" id="add-site-btn" class="btn-add-site">+ Add Site</button>
            </div>

        </div>
    </div>

    <div class="client-container">
        <div class="form-container">
            <h1>Client Details</h1>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Name 1</label>
                        <input type="text" id="phone1_name" name="phone1_name" placeholder="Contact Name">
                    </div>
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="number" id="comp_phone1" name="comp_phone1" placeholder="Contact Number">
                    </div>
                    <div class="form-group">
                        <label>Contact Email 1</label>
                        <input type="email" id="comp_email1" name="comp_email1" placeholder="Contact Email">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Name 2</label>
                        <input type="text" id="phone2_name" name="phone2_name" placeholder="Contact Name">
                    </div>
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="number" id="comp_phone2" name="comp_phone2" placeholder="Contact Number">
                    </div>
                    <div class="form-group">
                        <label>Contact Email 2</label>
                        <input type="email" id="comp_email2" name="comp_email2" placeholder="Contact Email">
                    </div>
                </div>
            </div>
            <div class="button-group">
                <button type="reset" class="btn-back">Reset</button>
                <input type="submit" value="Save" />
            </div>
        </div>
    </div>
</form>


@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clientSelect = document.getElementById('client_id');
        const sitesContainer = document.getElementById('sites-container');
        const addSiteBtn = document.getElementById('add-site-btn');
        let siteCounter = 0;

        // Client selection functionality
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
                        document.getElementById('comp_email1').value = client.comp_email1 || '';
                        document.getElementById('comp_phone2').value = client.comp_phone2 || '';
                        document.getElementById('phone2_name').value = client.phone2_name || '';
                        document.getElementById('comp_email2').value = client.comp_email2 || '';
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
                document.getElementById('comp_email1').value = '';
                document.getElementById('phone1_name').value = '';
                document.getElementById('comp_phone2').value = '';
                document.getElementById('phone2_name').value = '';
                document.getElementById('comp_email2').value = '';
            }
        });

        // Sites functionality
        function createSiteInput(index = null) {
            const actualIndex = index !== null ? index : siteCounter;
            const siteGroup = document.createElement('div');
            siteGroup.className = 'site-input-group';
            siteGroup.innerHTML = `
                <textarea name="sites[${actualIndex}]" rows="3" placeholder="Enter site location"></textarea>
                <button type="button" class="btn-remove-site" onclick="removeSite(this)">Remove</button>
            `;
            return siteGroup;
        }

        function addSite() {
            const siteInput = createSiteInput();
            sitesContainer.appendChild(siteInput);
            siteCounter++;
        }

        window.removeSite = function(button) {
            button.parentElement.remove();
            // Re-index remaining inputs
            const siteInputs = sitesContainer.querySelectorAll('textarea[name^="sites["]');
            siteInputs.forEach((input, index) => {
                input.name = `sites[${index}]`;
            });
            siteCounter = siteInputs.length;
        };

        addSiteBtn.addEventListener('click', addSite);

        // Form reset functionality
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            // Clear sites container
            sitesContainer.innerHTML = '';
            siteCounter = 0;
        });
    });
</script>
@endsection