@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Status & Action - Certificate Management System')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/preview.css') }}">
<link rel="stylesheet" href="{{ asset('css/badge.css') }}">
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
<div class="tabs">
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" 
       class="tab {{ request('status', 'all') == 'all' ? 'active' : '' }}">
        All
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_review'])) }}" 
       class="tab {{ request('status') == 'pending_review' ? 'active' : '' }}">
        Pending Review
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_client_verification'])) }}" 
       class="tab {{ request('status') == 'pending_client_verification' ? 'active' : '' }}">
        Pending Client Verification
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'client_verified'])) }}" 
       class="tab {{ request('status') == 'client_verified' ? 'active' : '' }}">
        Client Verified
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'need_revision'])) }}" 
       class="tab {{ request('status') == 'need_revision' ? 'active' : '' }}">
        Need Revision
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_hod_approval'])) }}" 
       class="tab {{ request('status') == 'pending_hod_approval' ? 'active' : '' }}">
        Pending HoD Approval
    </a>
    <a href="{{ route('certificates.index', array_merge(request()->except(['status', 'page']), ['status' => 'certificate_issued'])) }}" 
       class="tab {{ request('status') == 'certificate_issued' ? 'active' : '' }}">
        Issued
    </a>
</div>

<!-- Certificate table -->
<table class="table">
    <thead>
        <tr>
            <th class="sortable-header">
                <a href="{{ route('certificates.index', array_merge(request()->except(['sort_field', 'sort_direction']), [
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
                <a href="{{ route('certificates.index', array_merge(request()->except(['sort_field', 'sort_direction']), [
                    'sort_field' => 'created_at',
                    'sort_direction' => ($currentSortField == 'created_at' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                ])) }}">
                    Created
                    @if($currentSortField == 'created_at')
                        @if($currentSortDirection == 'asc')
                            <i class="fas fa-sort-up sort-icon"></i>
                        @else
                            <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                    @endif
                </a>
            </th>
            <th class="sortable-header">
                <a href="{{ route('certificates.index', array_merge(request()->except(['sort_field', 'sort_direction']), [
                    'sort_field' => 'last_edited_at',
                    'sort_direction' => ($currentSortField == 'last_edited_at' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                ])) }}">
                    Last Edited
                    @if($currentSortField == 'last_edited_at')
                        @if($currentSortDirection == 'asc')
                            <i class="fas fa-sort-up sort-icon"></i>
                        @else
                            <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                    @endif
                </a>
            </th>
            <th class="sortable-header">
                <a href="{{ route('certificates.index', array_merge(request()->except(['sort_field', 'sort_direction']), [
                    'sort_field' => 'status',
                    'sort_direction' => ($currentSortField == 'status' && $currentSortDirection == 'asc') ? 'desc' : 'asc'
                ])) }}">
                    Status
                    @if($currentSortField == 'status')
                        @if($currentSortDirection == 'asc')
                            <i class="fas fa-sort-up sort-icon"></i>
                        @else
                            <i class="fas fa-sort-down sort-icon"></i>
                        @endif
                    @endif
                </a>
            </th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($certs as $cert)
        <tr class="cert-row"
            data-href="{{ route('certificates.preview', ['cert' => $cert]) }}"
            style="cursor: pointer; transition: background-color 0.2s;">
            <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>
            <td>
                @if($cert->created_at)
                {{ \Carbon\Carbon::parse($cert->created_at)->format('d/m/Y H:i') }}<br>
                @if($cert->creator)
                by {{ $cert->creator->name }}
                @else
                Unknown Creator
                @endif
                @endif
            </td>
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
            <td>
                <span class="status-badge status-{{ $cert->status }}">
                    {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                </span>
            </td>
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
        <h3>Confirm Delete <strong id="certNameToDelete">[Item Name]</strong>?</h3>
        <p>Are you sure you want to delete this item? This action cannot be undone.</p>
        <div class="modal-actions">
            <button id="deleteCancelBtn" class="btn-back">Cancel</button>
            <button id="deleteConfirmBtn" class="confirm-btn">Delete</button>
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

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('deleteConfirmModal');
        const certNameSpan = document.getElementById('certNameToDelete');
        const confirmBtn = document.getElementById('deleteConfirmBtn');
        const cancelBtn = document.getElementById('deleteCancelBtn');
        let formToDelete = null;

        // Handle delete modal open
        document.querySelectorAll('.open-delete-modal').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                formToDelete = btn.closest('form');
                const row = btn.closest('tr');
                const name = row?.getAttribute('data-certificate-name') || 'this item';
                certNameSpan.textContent = name;
                modal.style.display = 'flex';
            });
        });

        // Handle delete confirm
        confirmBtn.addEventListener('click', function () {
            if (formToDelete) {
                formToDelete.submit();
            }
            modal.style.display = 'none';
        });

        // Handle delete cancel
        cancelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Handle clicking outside the modal
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
@endsection