@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'Pending Approvals')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/client-index.css') }}">
@endsection

@section('content')
<h1>Pending User Approvals</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Pending users table -->
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pendingUsers as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <form action="{{ route('users.update', ['user' => $user]) }}" method="POST" class="role-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <select name="role" required>
                        <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                </form>
            </td>
            <td>
                <div class="action-icons">
                    <form action="{{ route('users.update', ['user' => $user]) }}" method="POST" class="approve-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <input type="hidden" name="is_approved" value="1">
                        <button type="submit" class="approve-icon" title="Approve">
                            <i class="fas fa-check-circle"></i> Approve
                        </button>
                    </form>

                    <form action="{{ route('users.destroy', ['user' => $user]) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Reject" onclick="return confirm('Are you sure you want to reject this user?')">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        
        @if(count($pendingUsers) == 0)
        <tr>
            <td colspan="4" class="text-center">No pending approvals</td>
        </tr>
        @endif
    </tbody>
</table>

<script>
    // Auto-submit role selection when changed
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelects = document.querySelectorAll('.role-form select');
        roleSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
</script>
@endsection