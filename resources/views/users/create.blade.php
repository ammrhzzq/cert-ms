@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'Create User')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Create User</h1>

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

        @if(isset($hodExists) && $hodExists)
        <div class="alert alert-info">
            <p>Note: The HOD role is already assigned to another user. There can only be one HOD in the system.</p>
        </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                @if(isset($hodEmail))
                <small class="form-text text-muted">Note: The email "{{ $hodEmail }}" is reserved for the HOD role.</small>
                @endif
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="hod" {{ old('role') == 'hod' ? 'selected' : '' }} {{ isset($hodExists) && $hodExists ? 'disabled' : '' }}>Head of Department</option>
                </select>
                @if(isset($hodExists) && $hodExists)
                <small class="form-text text-muted">HOD role is already assigned.</small>
                @endif
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            </div>

            <div class="button-group">
                <a href="{{ route('users.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Create User" />
            </div>
        </form>
    </div>
</div>
@endsection