{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/cert-ms/resources/views/clients/index.blade.php --}}
@extends('layouts.app', ['activeItem' => 'clients'])

@section('title', 'Client List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1>Client List</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Create button -->
<div class="action-button">
    <a href="{{ route('clients.create') }}" class="create-btn">
        <i class="fas fa-plus"></i> Create Client
    </a>
</div>

<!-- Client table -->
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Contact Person</th>
            <th>Contact Number</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clients as $client)
        <tr>
            <td>{{ $client->comp_name }}</td>
            <td>
                {{ $client->comp_address1 }}<br>
                {{ $client->comp_address2 }}<br>
                {{ $client->comp_address3 }}
            </td>
            <td>
                1. {{ $client->phone1_name }}<br>
                2. {{ $client->phone2_name }}
            </td>
            <td>
                1. {{ $client->comp_phone1 }}<br>
                2. {{ $client->comp_phone2 }}
            </td>
            <td>
                <div class="action-icons">
                    <form action="{{ route('clients.destroy', ['client' => $client]) }}" method="POST" class="delete-form" data-client-id="{{ $client->id }}" data-client-name="{{ $client->comp_name }}" onsubmit="return false;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="delete-icon open-delete-modal" data-client-id="{{ $client->id }}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <a href="{{ route('clients.edit', ['client' => $client]) }}" class="edit-icon" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Delete <strong id="clientNameToDelete">[Client Name]</strong>?</h3>
        <p>Are you sure you want to delete this client? This action cannot be undone.</p>
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
    let modal = document.getElementById('deleteConfirmModal');
    let confirmBtn = document.getElementById('deleteConfirmBtn');
    let cancelBtn = document.getElementById('deleteCancelBtn');
    let clientNameToDelete = document.getElementById('clientNameToDelete');
    let formToDelete = null;

    // Open modal on delete icon click
    document.querySelectorAll('.open-delete-modal').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            formToDelete = btn.closest('form');
            const clientName = formToDelete.getAttribute('data-client-name');
            clientNameToDelete.textContent = clientName;
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