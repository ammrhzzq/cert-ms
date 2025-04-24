<!-- resources/views/auth/auth.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $activeTab == 'login' ? 'Login' : ($activeTab == 'register' ? 'Register' : 'Reset Password') }}</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    </style>
</head>
<body>
    <button class="mode-toggle" id="mode-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="auth-container">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </div>
        
        <div class="form">
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

            <!-- Tab navigation -->
            <ul class="nav-tabs" id="authTabs">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'login' ? 'active' : '' }}" 
                       href="{{ route('show.login') }}">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'register' ? 'active' : '' }}" 
                       href="{{ route('show.register') }}">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'forgot-password' ? 'active' : '' }}" 
                       href="{{ route('password.request') }}">Forgot Password</a>
                </li>
            </ul>

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
                        <input type="password" name="password" placeholder="Enter your password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password_confirmation" placeholder="Confirm your password" 
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn">Sign Up</button>
                </form>
            @endif

            <!-- Content for Forgot Password -->
            @if($activeTab == 'forgot-password')
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <h2>Forgot Password</h2>

                    <div class="form-group">
                        <input type="email" name="email" id="email" placeholder="Enter your email" 
                               class="form-control" required value="{{ old('email') }}">
                    </div>

                    <button type="submit" class="btn">Send OTP</button>
                </form>
            @endif

            <!-- Content for OTP Verification -->
            @if($activeTab == 'verify-otp')
                <form method="POST" action="{{ route('password.otp.verify') }}">
                    @csrf
                    <h2>Verify OTP</h2>

                    <div class="form-group">
                        <input type="text" name="otp" placeholder="Enter OTP sent to your email" 
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn">Verify OTP</button>
                </form>
            @endif

            <!-- Content for Reset Password -->
            @if($activeTab == 'reset-password')
                <form method="POST" action="{{ route('password.reset') }}">
                    @csrf
                    <h2>Reset Password</h2>

                    <div class="form-group">
                        <input type="password" name="password" class="form-control" 
                               placeholder="New Password" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Confirm Password" required>
                    </div>

                    <button type="submit" class="btn">Reset Password</button>
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
    </script>
</body>
</html>