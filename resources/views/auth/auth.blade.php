<!-- resources/views/auth/auth.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($activeTab == 'login')
        Login
        @elseif($activeTab == 'register')
        Register
        @elseif($activeTab == 'verify-email')
        Verify Email
        @else
        Reset Password
        @endif
    </title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/password-strength.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <button class="mode-toggle" id="mode-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="auth-container">
        <div class="form">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
            </div>

            @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Tab navigation (hide for email verification) -->
            @if($activeTab !== 'verify-email')
            <ul class="tabs" id="authTabs">
                <li class="item">
                    <a class="link {{ $activeTab == 'login' ? 'active' : '' }}"
                        href="{{ route('show.login') }}">Login</a>
                </li>
                <li class="item">
                    <a class="link {{ $activeTab == 'register' ? 'active' : '' }}"
                        href="{{ route('show.register') }}">Register</a>
                </li>
            </ul>
            @endif

            <!-- Content for Login -->
            @if($activeTab == 'login')
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <h2>Log In</h2>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Enter your email" class="form-control"
                        required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Enter your password" class="form-control" required>
                </div>

                <button type="submit" class="btn">Log In</button>

                <div class="help-text">
                    <p class="text-muted">
                        <small>Forgot your password? Contact system administrator for a password reset.</small>
                    </p>
                </div>
            </form>
            @endif

            <!-- Content for Register -->
            @if($activeTab == 'register')
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <h2>Sign Up</h2>

                <div class="form-group">
                    <input type="text" name="name" placeholder="Enter your name" class="form-control"
                        required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Enter your email" class="form-control"
                        required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Enter your password" class="form-control"
                        id="password" required>

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
                    <input type="password" name="password_confirmation" placeholder="Confirm your password"
                        class="form-control" id="passwordConfirm" required>
                    <div id="passwordMatch" style="margin-top: 5px; font-size: 12px; display: none;"></div>
                </div>

                <button type="submit" class="btn" id="submitBtn">Sign Up</button>
            </form>
            @endif

            <!-- Content for Email Verification -->
            @if($activeTab == 'verify-email')
            <form action="{{ route('verify.email', ['user' => $user->id]) }}" method="POST">
                @csrf
                <h2>Verify Your Email</h2>

                <p class="text-center text-muted">
                    We've sent a 6-digit verification code to<br>
                    <strong>{{ $user->email }}</strong>
                </p>

                <div class="form-group">
                    <input type="text"
                        name="verification_code"
                        placeholder="000000"
                        class="form-control verification-code-input"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        value="{{ old('verification_code') }}"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <button type="submit" class="btn">Verify Email</button>

                <div class="resend-section">
                    <p class="text-muted">
                        <small>Didn't receive the code?</small>
                    </p>
                    <form action="{{ route('resend.verification', ['user' => $user->id]) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="resend-btn">Resend Code</button>
                    </form>
                </div>

                <div class="help-text">
                    <p class="text-muted text-center">
                        <small>The code expires in 10 minutes</small>
                    </p>
                </div>
            </form>
            @endif
        </div>
    </div>

    <script>
        // Check for saved dark mode preference
        document.addEventListener('DOMContentLoaded', function() {
            // Check if dark mode cookie exists
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }

            const isDarkMode = getCookie('darkMode') === 'true';
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                document.getElementById('mode-toggle').innerHTML = '<i class="fas fa-sun"></i>';
            }

            // Auto-focus verification code input
            const verificationInput = document.querySelector('input[name="verification_code"]');
            if (verificationInput) {
                verificationInput.focus();
            }

            // Initialize password strength checker
            initPasswordStrengthChecker();
        });

        // Toggle dark mode
        document.getElementById('mode-toggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');

            // Update icon
            this.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';

            // Set cookie for persistence
            document.cookie = `darkMode=${isDarkMode}; path=/; max-age=${60*60*24*365}`;
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
</body>

</html>