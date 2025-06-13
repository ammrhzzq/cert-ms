<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificate Verification</title>
    
    <!-- Base Styles -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .main-header {
            background-color: #FF7A00;
            color: white;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .main-footer {
            background-color: #f5f5f5;
            padding: 20px 0;
            text-align: center;
            margin-top: 30px;
            border-top: 1px solid #eee;
        }
        .main-content {
            min-height: calc(100vh - 180px);
            padding: 20px 0;
        }
    </style>
    
    <!-- Additional Styles -->
    @yield('styles')
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h2>Certificate Verification Page</h2>
        </div>
    </header>
    
    <main class="main-content">
        @yield('content')
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} CyberSecurity Malaysia @ UPM. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Scripts -->
    @yield('scripts')
</body>
</html>