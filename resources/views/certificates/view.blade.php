{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/cert-ms/resources/views/certificates/view.blade.php --}}
@extends('layouts.app', ['activeItem' => 'view'])

@section('title', 'Certificates List')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<link rel="stylesheet" href="{{ asset('css/view.css') }}">
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
@endsection

@section('content')
<h1>List of Certificate</h1>

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
                <th>Certificate</th>
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th>Certificate ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($certs as $cert)
            @if ($cert->status == 'certificate_issued')
            <tr class="cert-row" 
                data-certificate-id="{{ $cert->id }}"
                data-certificate-name="{{ $cert->cert_type }}-{{ $cert->comp_name }}"
                data-preview-url="{{ route('certificates.preview-final', $cert->id) }}" style="cursor: pointer;">
                <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>

                <td>{{ \Carbon\Carbon::parse($cert->issue_date)->format('d/m/Y') }}</td>

                <td>{{ \Carbon\Carbon::parse($cert->exp_date)->format('d/m/Y') }}</td>

                <td>{{ $cert->cert_number }}</td>
                
                <td>
                    <div class="action-icons">
                        <form action="{{ route('certificates.destroy', ['cert' => $cert]) }}" method="POST" class="delete-form" data-cert-name="{{ $cert->cert_type }} - {{ $cert->comp_name }}" onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-icon open-delete-modal" title="Delete">
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

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Delete <strong id="certNameToDelete">[Certificate Name]</strong>?</h3>
        <p>Are you sure you want to delete the certificate? This action cannot be undone.</p>
        <div class="modal-actions">
            <button id="deleteConfirmBtn" class="confirm-btn" disabled>Delete</button>
            <button id="deleteCancelBtn" class="btn-back">Cancel</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('deleteConfirmModal');
        const certNameSpan = document.getElementById('certNameToDelete');
        const confirmBtn = document.getElementById('deleteConfirmBtn');
        const cancelBtn = document.getElementById('deleteCancelBtn');
        let formToDelete = null;

        // Open modal with cert name
        document.querySelectorAll('.open-delete-modal').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formToDelete = btn.closest('form');
                const certName = formToDelete.getAttribute('data-cert-name');
                certNameSpan.textContent = certName;
                modal.style.display = 'flex';
            });
        });

        // Confirm delete
        confirmBtn.addEventListener('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
            modal.style.display = 'none';
        });

        // Cancel delete
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });


        filterToggle.addEventListener('click', function() {
            if (filterPanel.style.display === 'none') {
                filterPanel.style.display = 'block';
            } else {
                filterPanel.style.display = 'none';
            }
        });

        // Add event listener for date field change
        const dateTypeSelect = document.getElementById('date_type');
        dateTypeSelect.addEventListener('change', function() {
            this.form.submit();
        });

        // Make table row clickable for preview
        document.querySelectorAll('.cert-row').forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Prevent if clicking on a button or inside the action-icons
                if (e.target.closest('.delete-form') || e.target.closest('.delete-icon')) return;
                const pdfUrl = this.getAttribute('data-preview-url');
                if (pdfUrl) {
                    Fancybox.show([{ src: pdfUrl, type: "iframe" }], {
                        groupAll: true,
                        animated: false,
                        dragToClose: false,
                        showClass: false,
                        hideClass: false,
                        Toolbar: {
                            display: ["zoom", "fullscreen", "download", "close"],
                        },
                        iframe: {
                            preload: false,
                            css: {
                                width: '100%',
                                height: '90vh',
                                transform: 'scale(0.95)',
                                transformOrigin: 'top center'
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection