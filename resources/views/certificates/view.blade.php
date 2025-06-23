@extends('layouts.app', ['activeItem' => 'view'])

@section('title', 'Certificates List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/view.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
@endsection

@section('content')
<h1>Certificate List</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<div class="actions-bar">
    <div class="search-container">
        <form action="{{ route('certificates.view') }}" method="GET" class="search-form">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="search" class="search-input" placeholder="Search for certificate" value="{{ request('search') }}">
            <button type="submit" class="search-submit" style="display: none;">Search</button>
        </form>
        <button class="filter-btn" id="filterToggle">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>

    <!-- Filter panel (hidden by default) -->
    <div class="filter-panel" id="filterPanel" style="display: none;">
        <form action="{{ route('certificates.view') }}" method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="cert_type">Certificate Type:</label>
                    <select name="cert_type" id="cert_type">
                        <option value="all">All Types</option>
                        @foreach($certTypes as $type)
                        <option value="{{ $type }}" {{ request('cert_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="iso_num">ISO Number:</label>
                    <select name="iso_num" id="iso_num">
                        <option value="all">All ISO Numbers</option>
                        @foreach($isoNumbers as $iso)
                        <option value="{{ $iso }}" {{ request('iso_num') == $iso ? 'selected' : '' }}>{{ $iso }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label for="comp_name">Company Name:</label>
                    <select name="comp_name" id="comp_name">
                        <option value="all">All Companies</option>
                        @foreach($companyNames as $name)
                        <option value="{{ $name }}" {{ request('comp_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_type">Date Field:</label>
                    <select name="date_type" id="date_type">
                        <option value="reg_date" {{ request('date_type') == 'reg_date' ? 'selected' : '' }}>Registration Date</option>
                        <option value="issue_date" {{ request('date_type') == 'issue_date' ? 'selected' : '' }}>Issue Date</option>
                        <option value="exp_date" {{ request('date_type') == 'exp_date' ? 'selected' : '' }}>Expiry Date</option>
                    </select>

                    <select name="date_value" id="date_value">
                        <option value="all">All Dates</option>
                        @if(request('date_type') == 'issue_date' || request('date_type') == '')
                        @foreach($issueDates as $date)
                        <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </option>
                        @endforeach
                        @elseif(request('date_type') == 'exp_date')
                        @foreach($expDates as $date)
                        <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </option>
                        @endforeach
                        @else
                        @foreach($regDates as $date)
                        <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="apply-filter-btn">Apply Filters</button>
                <a href="{{ route('certificates.view') }}" class="reset-filter-btn">Reset</a>
            </div>
        </form>
    </div>

    <!-- Certificate table -->
    <table class="table">
        <thead>
            <tr>
                <th class="sortable-header">
                    <a href="{{ route('certificates.view', array_merge(request()->except(['sort_field', 'sort_direction']), [
                        'sort_field' => 'comp_name',
                        'sort_direction' => ($currentSortField == 'comp_name' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                    ])) }}">
                        Certificate
                        @if($currentSortField == 'comp_name')
                        @if($currentSortDirection == 'asc')
                        <i class="fas fa-sort-up sort-icon"></i>
                        @else
                        <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                        @endif
                    </a>
                </th>
                <th class="sortable-header">
                    <a href="{{ route('certificates.view', array_merge(request()->except(['sort_field', 'sort_direction']), [
                        'sort_field' => 'issue_date',
                        'sort_direction' => ($currentSortField == 'issue_date' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                    ])) }}">
                        Issue Date
                        @if($currentSortField == 'issue_date')
                        @if($currentSortDirection == 'asc')
                        <i class="fas fa-sort-up sort-icon"></i>
                        @else
                        <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                        @endif
                    </a>
                </th>
                <th class="sortable-header">
                    <a href="{{ route('certificates.view', array_merge(request()->except(['sort_field', 'sort_direction']), [
                        'sort_field' => 'exp_date',
                        'sort_direction' => ($currentSortField == 'exp_date' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                    ])) }}">
                        Expiry Date
                        @if($currentSortField == 'exp_date')
                        @if($currentSortDirection == 'asc')
                        <i class="fas fa-sort-up sort-icon"></i>
                        @else
                        <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                        @endif
                    </a>
                </th>
                <th>Certificate ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($certs as $cert)
            @if ($cert->status == 'certificate_issued')
            <tr class="clickable-row"
                data-certificate-id="{{ $cert->id }}"
                data-certificate-name="{{ $cert->cert_type }}-{{ $cert->comp_name }}"
                data-preview-url="{{ route('certificates.preview-final', $cert->id) }}">
                <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>

                <td>{{ \Carbon\Carbon::parse($cert->issue_date)->format('d/m/Y') }}</td>

                <td>{{ \Carbon\Carbon::parse($cert->exp_date)->format('d/m/Y') }}</td>

                <td>{{ $cert->cert_number }}</td>

                <td>
                    <div class="action-icons">
                        <a href="#" class="view-icon"
                            data-certificate-id="{{ $cert->id }}"
                            data-certificate-name="{{ $cert->cert_type }}-{{ $cert->comp_name }}"
                            data-preview-url="{{ route('certificates.preview-final', $cert->id) }}"
                            title="View">
                            <i class="far fa-eye"></i>
                        </a>
                        <form action="{{ route('certificates.destroy', ['cert' => $cert]) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-icon" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <!-- Certificate Preview Modal -->
    <div id="certificatePreviewModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeCertificateModal">&times;</span>
            <div class="certificate-preview-content">
                <div class="preview-header">
                    <h3 id="certificatePreviewTitle">Certificate Preview</h3>
                    <div class="modal-actions">
                        <a href="#" id="openCertificateNewTab" class="btn-download" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                    </div>
                </div>
                <div class="preview-container">
                    <iframe id="certificatePreviewFrame" src=""></iframe>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterToggle = document.getElementById('filterToggle');
            const filterPanel = document.getElementById('filterPanel');

            // Certificate Preview Modal elements
            const certificatePreviewModal = document.getElementById('certificatePreviewModal');
            const closeCertificateModal = document.getElementById('closeCertificateModal');
            const certificatePreviewFrame = document.getElementById('certificatePreviewFrame');
            const certificatePreviewTitle = document.getElementById('certificatePreviewTitle');
            const openCertificateNewTab = document.getElementById('openCertificateNewTab');
            const previewBtns = document.querySelectorAll('.view-icon');
            const clickableRows = document.querySelectorAll('.clickable-row');

            // Handle filter toggle
            filterToggle.addEventListener('click', function() {
                if (filterPanel.style.display === 'none') {
                    filterPanel.style.display = 'block';
                } else {
                    filterPanel.style.display = 'none';
                }
            });

            // Function to open certificate preview
            function openCertificatePreview(certificateId, certificateName, previewUrl) {
                // Set modal content
                certificatePreviewTitle.textContent = `${certificateName}`;
                certificatePreviewFrame.src = previewUrl;
                openCertificateNewTab.href = previewUrl;

                // Show modal
                certificatePreviewModal.style.display = 'block';
            }

            // Handle certificate preview button clicks
            previewBtns.forEach(btn => {
                btn.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation(); // Prevent row click when clicking the eye icon

                    const certificateId = this.getAttribute('data-certificate-id');
                    const certificateName = this.getAttribute('data-certificate-name');
                    const previewUrl = this.getAttribute('data-preview-url');

                    openCertificatePreview(certificateId, certificateName, previewUrl);
                });
            });

            // Handle clickable rows
            clickableRows.forEach(row => {
                row.addEventListener('click', function(event) {
                    // Don't trigger if clicking on action buttons
                    if (event.target.closest('.action-icons')) {
                        return;
                    }

                    const certificateId = this.getAttribute('data-certificate-id');
                    const certificateName = this.getAttribute('data-certificate-name');
                    const previewUrl = this.getAttribute('data-preview-url');

                    openCertificatePreview(certificateId, certificateName, previewUrl);
                });
            });

            // Close certificate preview modal
            closeCertificateModal.addEventListener('click', function() {
                certificatePreviewModal.style.display = 'none';
                certificatePreviewFrame.src = ''; // Clear iframe to stop loading
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === certificatePreviewModal) {
                    certificatePreviewModal.style.display = 'none';
                    certificatePreviewFrame.src = ''; // Clear iframe to stop loading
                }
            });

            // Add event listener for date field change
            const dateTypeSelect = document.getElementById('date_type');
            const dateValueSelect = document.getElementById('date_value');

            dateTypeSelect.addEventListener('change', function() {
                // Submit the form to refresh the page with the new date type
                this.form.submit();
            });

            // Confirm delete
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation(); // Prevent row click when clicking delete

                    if (confirm('Are you sure you want to delete this certificate?')) {
                        // If confirmed, submit the form
                        this.submit();
                    }
                });
            });

            // Handle iframe load errors for preview thumbnails
            const previewIframes = document.querySelectorAll('.certificate-preview iframe');
            previewIframes.forEach(iframe => {
                iframe.addEventListener('error', function() {
                    console.error('Failed to load certificate preview thumbnail');
                    this.style.display = 'none';
                    this.parentNode.innerHTML = '<div class="preview-not-available"><i class="fas fa-certificate" style="font-size: 48px; margin-bottom: 8px;"></i><span>Preview not available</span></div>';
                });
            });

            // Handle iframe load errors for modal
            certificatePreviewFrame.addEventListener('error', function() {
                console.error('Failed to load certificate preview');
                this.style.display = 'none';
                this.parentNode.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 1rem;"></i><br>Preview not available<br><small>Click "Open in New Tab" to view the certificate</small></div>';
            });
        });
    </script>
    @endsection