@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'Edit User')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Edit User</h1>

    <div class="form-container">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Full Name" value="{{ $user->name }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Email Address" value="{{ $user->email }}" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="hod" {{ $user->role == 'hod' ? 'selected' : '' }}>Head of Department</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="is_approved">
                    <option value="1" {{ $user->is_approved ? 'selected' : '' }}>Approved</option>
                    <option value="0" {{ !$user->is_approved ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="New Password">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="Confirm New Password">
            </div>

            <div class="button-group">
                <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection