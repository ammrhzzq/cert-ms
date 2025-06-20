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
                    <form action="{{ route('users.destroy', ['user' => $user]) }}" method="POST" class="delete-form" data-user-name="{{ $user->name }}" onsubmit="return false;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="delete-icon open-delete-modal" title="Delete">
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
        <h3>Confirm Delete <strong id="userNameToDelete">[User Name]</strong>?</h3>
        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        <div class="modal-actions">
            <button id="deleteConfirmBtn" class="confirm-btn">Delete</button>
            <button id="deleteCancelBtn" class="btn-back">Cancel</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation modal
    const modal = document.getElementById('deleteConfirmModal');
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    const cancelBtn = document.getElementById('deleteCancelBtn');
    const userNameToDelete = document.getElementById('userNameToDelete');
    let formToDelete = null;

    document.querySelectorAll('.open-delete-modal').forEach(button => {
        button.addEventListener('click', function() {
            const form = button.closest('.delete-form');
            const userName = form.getAttribute('data-user-name');
            userNameToDelete.textContent = userName;
            formToDelete = form;
            modal.style.display = 'flex';
        });
    });

    confirmBtn.addEventListener('click', function() {
        if (formToDelete) {
            formToDelete.submit();
        }
        modal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection