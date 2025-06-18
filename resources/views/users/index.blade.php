@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'User List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1>User List</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Create button -->
<div class="action-button">
    <a href="{{ route('users.create') }}" class="create-btn">
        <i class="fas fa-plus"></i> Create User
    </a>
</div>

<!-- User table -->
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>
                <span class="badge {{ $user->is_approved ? 'badge-success' : 'badge-warning' }}">
                    {{ $user->is_approved ? 'Approved' : 'Pending' }}
                </span>
            </td>
            <td>
                <div class="action-icons">
                    <form action="{{ route('users.destroy', ['user' => $user]) }}" method="POST" class="delete-form" data-user-id="{{ $user->id }}" onsubmit="return false;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="delete-icon open-delete-modal" data-user-id="{{ $user->id }}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <a href="{{ route('users.edit', ['user' => $user]) }}" class="edit-icon" title="Edit">
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
        <h3>Confirm Delete User?</h3>
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