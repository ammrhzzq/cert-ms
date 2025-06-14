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
            <th>Certificate</th>
            <th>Created By</th>
            <th>Last Edited</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($certs as $cert)
        <tr class="clickable-row" 
            data-certificate-id="{{ $cert->id }}"
            data-certificate-name="{{ $cert->cert_type }}-{{ $cert->comp_name }}"
            data-preview-url="{{ route('certificates.preview', $cert->id) }}">
            <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>
            <td>
                @if($cert->creator)
                {{ $cert->creator->name }}<br>
                {{ \Carbon\Carbon::parse($cert->created_at)->format('d/m/Y H:i') }}
                @else
                Unknown Creator
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

            <td>{{ ucfirst(str_replace('_', ' ', $cert->status)) }}</td>
            <td>
                <div class="action-icons">
                    <a href="{{ route('certificates.preview', ['cert' => $cert]) }}" class="view-icon" title="View">
                        <i class="fa-regular fa-eye"></i>
                    </a>
                    @if ($cert->status == 'pending_review')
                    <a href="{{ route('certificates.edit', ['cert' => $cert]) }}" class="edit-icon" title="Edit">
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
                    <form action="{{ route('certificates.destroy', ['cert' => $cert]) }}" method="POST" class="delete-form">
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

        const clickableRows = document.querySelectorAll('.clickable-row');
        // Confirm delete
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Are you sure you want to delete this certificate?')) {
                    event.preventDefault();
                }
            });
        });

        // Handle clickable rows
        clickableRows.forEach(row => {
            row.addEventListener('click', function(event) {
                // Don't trigger if clicking on action buttons
                if (event.target.closest('.action-icons')) {
                    return;
                }

                const previewUrl = this.getAttribute('data-preview-url');
                
                // Navigate to the preview page
                window.location.href = previewUrl;
            });
        });
    });
</script>
@endsection