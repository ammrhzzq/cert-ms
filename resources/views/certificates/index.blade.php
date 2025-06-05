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
                            <button type="submit" class="delete-icon" title="Delete">
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

    function confirmDelete(event) {
        if (!confirm('Are you sure you want to delete this certificate? This action cannot be undone.')) {
            event.preventDefault();
            return false;
        }
        return true;
    }
</script>
@endsection