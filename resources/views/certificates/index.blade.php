@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Status & Action - Certificate Management System')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1>Status & Action</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Status tabs -->
<div class="status-tabs">
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" 
       class="status-tab {{ request('status', 'all') == 'all' ? 'active' : '' }}">
        All
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_review'])) }}" 
       class="status-tab {{ request('status') == 'pending_review' ? 'active' : '' }}">
        Pending Review
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_client_verification'])) }}" 
       class="status-tab {{ request('status') == 'pending_client_verification' ? 'active' : '' }}">
        Pending Client Verification
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'client_verified'])) }}" 
       class="status-tab {{ request('status') == 'client_verified' ? 'active' : '' }}">
        Client Verified
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'need_revision'])) }}" 
       class="status-tab {{ request('status') == 'need_revision' ? 'active' : '' }}">
        Need Revision
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_hod_approval'])) }}" 
       class="status-tab {{ request('status') == 'pending_hod_approval' ? 'active' : '' }}">
        Pending HoD Approval
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'certificate_issued'])) }}" 
       class="status-tab {{ request('status') == 'certificate_issued' ? 'active' : '' }}">
        Issued
    </a>
</div>

<!-- Certificate table -->
<table class="table">
    <thead>
        <tr>
            <th>Certificate</th>
            <th>Last Edited</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($certs as $cert)
        <tr class="cert-row"
            data-href="{{ route('certificates.show', ['cert' => $cert]) }}"
            style="cursor: pointer; transition: background-color 0.2s;">
            <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>
            <td>
                @if($cert->last_edited_at)
                {{ \Carbon\Carbon::parse($cert->last_edited_at)->format('d/m/Y H:i') }}<br>
                @if($cert->lastEditor)
                by {{ $cert->lastEditor->name }}
                @endif
                @else
                Not edited
                @endif
            </td>
            <td>{{ ucfirst(str_replace('_', ' ', $cert->status)) }}</td>
            <td>
                <div class="action-icons">
                    @if ($cert->status == 'pending_review')
                        <a href="{{ route('certificates.edit', ['cert' => $cert]) }}" class="edit-icon" title="Edit" onclick="event.stopPropagation();">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    @endif
                    @php
                        $user = auth()->user();
                        $canDelete = false;
                        if ($user->role === 'hod') {
                            $canDelete = true;
                        } elseif ($user->role === 'manager' && $cert->status !== 'certificate_issued') {
                            $canDelete = true;
                        } elseif ($user->role === 'staff' && $cert->status === 'pending_review') {
                            $canDelete = true;
                        }
                    @endphp
                    @if($canDelete)
                        <form action="{{ route('certificates.destroy', ['cert' => $cert]) }}" method="POST" class="delete-form" onsubmit="return confirmDelete(event);" onclick="event.stopPropagation();">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="delete-icon open-delete-modal" data-cert-id="{{ $cert->id }}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Type <strong>DELETE</strong> to confirm deletion. This action cannot be undone.</p>
        <input type="text" id="deleteConfirmInput" placeholder="Type DELETE to continue" style="margin-bottom: 8px;">
        <div id="deleteError" style="color: red; display: none; font-size: 14px; margin-bottom: 8px;">
            Please type DELETE to enable the button.
        </div>
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
        const filterToggle = document.getElementById('filterToggle');
        const filterPanel = document.getElementById('filterPanel');

        filterToggle.addEventListener('click', function() {
            if (filterPanel.style.display === 'none') {
                filterPanel.style.display = 'block';
            } else {
                filterPanel.style.display = 'none';
            }
        });

        // Add event listener for date field change
        const dateTypeSelect = document.getElementById('date_type');
        const dateValueSelect = document.getElementById('date_value');

        dateTypeSelect.addEventListener('change', function() {
            // Submit the form to refresh the page with the new date type
            this.form.submit();
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Make table row clickable for preview
        document.querySelectorAll('.cert-row').forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Prevent if clicking on a button or inside the action-icons
                if (e.target.closest('.edit-icon') || e.target.closest('.delete-form') || e.target.closest('.delete-icon')) return;
                const url = this.getAttribute('data-href');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        // Modal elements
        let modal = document.getElementById('deleteConfirmModal');
        let input = document.getElementById('deleteConfirmInput');
        let error = document.getElementById('deleteError');
        let confirmBtn = document.getElementById('deleteConfirmBtn');
        let cancelBtn = document.getElementById('deleteCancelBtn');
        let formToDelete = null;

        // Open modal on delete icon click
        document.querySelectorAll('.open-delete-modal').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formToDelete = btn.closest('form');
                input.value = '';
                confirmBtn.disabled = true;
                error.style.display = 'none';
                modal.style.display = 'flex';
            });
        });

        // Enable button only if DELETE is typed
        input.addEventListener('input', function() {
            if (input.value === 'DELETE') {
                confirmBtn.disabled = false;
                error.style.display = 'none';
            } else {
                confirmBtn.disabled = true;
                error.style.display = input.value.length > 0 ? 'block' : 'none';
            }
        });

        // Confirm delete
        confirmBtn.addEventListener('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
            modal.style.display = 'none';
        });

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
@endsection