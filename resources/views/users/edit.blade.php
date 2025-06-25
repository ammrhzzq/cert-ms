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
                    <option value="Certificate Admin" {{ $user->role == 'certificate_admin' ? 'selected' : '' }}>Certificate Admin</option>
                    <option value="Scheme Manager" {{ $user->role == 'scheme_manager' ? 'selected' : '' }}>Scheme Manager</option>
                    <option value="Scheme Head"
                            {{ $user->role == 'scheme_head' ? 'selected' : '' }}
                            {{ isset($schemeHeadExists) && $schemeHeadExists && $user->role !== 'scheme_head' ? 'disabled' : '' }}>
                    Scheme Head
                    </option>
                </select>
                @if(isset($schemeHeadExists) && $schemeHeadExists && $user->role !== 'scheme_head')
                <small class="form-text text-muted">Scheme Head role is already assigned to another user.</small>
                @endif
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="is_approved">
                    <option value="1" {{ $user->is_approved ? 'selected' : '' }}>Approved</option>
                    <option value="0" {{ !$user->is_approved ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="button-group">
                <a href="{{ route('users.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Update" />
            </div>
        </form>
    </div>
</div>
@endsection