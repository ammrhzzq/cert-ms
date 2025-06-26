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

        @if(isset($chemeHeadExists) && $schemeHeadExists)
        <div class="alert alert-info">
            <p>Note: The Scheme Head role is already assigned to another user. There can only be one Scheme Head in the system.</p>
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
                @if(isset($schemeHeadEmail))
                <small class="form-text text-muted">Note: The email "{{ $schemeHeadEmail }}" is reserved for the Scheme Head role.</small>
                @endif
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="certificate_admin" {{ old('role') == 'certificate_admin' ? 'selected' : '' }}>Certificate Admin</option>
                    <option value="scheme_manager" {{ old('role') == 'scheme_manager' ? 'selected' : '' }}>Scheme Manager</option>
                    <option value="scheme_head" {{ old('role') == 'scheme_head' ? 'selected' : '' }}>Scheme Head</option>
                </select>
                @if(isset($schemeHeadExists) && $schemeHeadExists)
                <small class="form-text text-muted">Scheme Head role is already assigned.</small>
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