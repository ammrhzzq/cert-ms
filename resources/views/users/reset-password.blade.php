@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'Reset Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Reset Password for {{ $user->name }}</h1>

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

        <div class="user-info">
            <p><strong>User:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
        </div>

        <form action="{{ route('users.reset-password.update', $user) }}" method="POST">
            @csrf

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
            </div>

            <div class="button-group">
                <a href="{{ route('users.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Reset Password" />
            </div>
        </form>
    </div>
</div>
@endsection