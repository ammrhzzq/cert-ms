@extends('layouts.app', ['activeItem' => 'users'])

@section('title', 'Reset Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
<link rel="stylesheet" href="{{ asset('css/password-strength.css') }}">

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
            <p><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
        </div>

        <form action="{{ route('users.reset-password.update', $user) }}" method="POST">
            @csrf

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password" class="form-control" id="password" required>

                <!-- Password Strength Indicator -->
                <div class="password-strength" id="passwordStrength">
                    <h6>Password Strength</h6>
                    <div class="strength-bar">
                        <div class="strength-progress" id="strengthProgress"></div>
                    </div>
                    <ul class="password-requirements" id="passwordRequirements">
                        <li id="req-length">
                            <i class="fas fa-times requirement-unmet"></i>
                            At least 8 characters
                        </li>
                        <li id="req-lowercase">
                            <i class="fas fa-times requirement-unmet"></i>
                            One lowercase letter
                        </li>
                        <li id="req-uppercase">
                            <i class="fas fa-times requirement-unmet"></i>
                            One uppercase letter
                        </li>
                        <li id="req-number">
                            <i class="fas fa-times requirement-unmet"></i>
                            One number
                        </li>
                        <li id="req-special">
                            <i class="fas fa-times requirement-unmet"></i>
                            One special character
                        </li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" placeholder="Confirm new password" class="form-control" id="passwordConfirm" required>
                <div id="passwordMatch" style="margin-top: 5px; font-size: 12px; display: none;"></div>

            </div>

            <div class="button-group">
                <a href="{{ route('users.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Reset Password" />
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize password strength checker
        initPasswordStrengthChecker();
    });

    // Password Strength Checker
    function initPasswordStrengthChecker() {
        const passwordInput = document.getElementById('password');
        const passwordConfirm = document.getElementById('passwordConfirm');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthProgress = document.getElementById('strengthProgress');
        const submitBtn = document.getElementById('submitBtn');
        const passwordMatch = document.getElementById('passwordMatch');

        if (!passwordInput || !strengthIndicator) return;

        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (password.length > 0) {
                strengthIndicator.classList.add('active');
                checkPasswordStrength(password);
            } else {
                strengthIndicator.classList.remove('active');
            }
        });

        passwordInput.addEventListener('focus', function() {
            if (this.value.length > 0) {
                strengthIndicator.classList.add('active');
            }
        });

        // Password confirmation matching
        if (passwordConfirm && passwordMatch) {
            passwordConfirm.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;

                if (confirmPassword.length > 0) {
                    passwordMatch.style.display = 'block';
                    if (password === confirmPassword) {
                        passwordMatch.innerHTML = '<i class="fas fa-check" style="color: #28a745;"></i> Passwords match';
                        passwordMatch.style.color = '#28a745';
                    } else {
                        passwordMatch.innerHTML = '<i class="fas fa-times" style="color: #dc3545;"></i> Passwords do not match';
                        passwordMatch.style.color = '#dc3545';
                    }
                } else {
                    passwordMatch.style.display = 'none';
                }
            });
        }
    }

    function checkPasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        // Update requirement indicators
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-lowercase', requirements.lowercase);
        updateRequirement('req-uppercase', requirements.uppercase);
        updateRequirement('req-number', requirements.number);
        updateRequirement('req-special', requirements.special);

        // Calculate strength score
        const score = Object.values(requirements).filter(Boolean).length;
        const strengthProgress = document.getElementById('strengthProgress');

        // Update progress bar
        const percentage = (score / 5) * 100;
        strengthProgress.style.width = percentage + '%';

        // Update progress bar color
        strengthProgress.className = 'strength-progress';
        if (score <= 2) {
            strengthProgress.classList.add('strength-weak');
        } else if (score === 3) {
            strengthProgress.classList.add('strength-fair');
        } else if (score === 4) {
            strengthProgress.classList.add('strength-good');
        } else {
            strengthProgress.classList.add('strength-strong');
        }
    }

    function updateRequirement(elementId, isMet) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');

        if (isMet) {
            icon.className = 'fas fa-check requirement-met';
            element.classList.remove('requirement-unmet');
            element.classList.add('requirement-met');
        } else {
            icon.className = 'fas fa-times requirement-unmet';
            element.classList.remove('requirement-met');
            element.classList.add('requirement-unmet');
        }
    }
</script>
@endsection