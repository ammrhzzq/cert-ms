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
                    <form action="{{ route('users.destroy', ['user' => $user]) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                    <a href="{{ route('users.edit', ['user' => $user]) }}" class="edit-icon" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>

                    <a href="{{ route('users.reset-password', ['user' => $user]) }}" class="reset-password-icon" title="Reset Password">
                        <i class="fas fa-key"></i>
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection