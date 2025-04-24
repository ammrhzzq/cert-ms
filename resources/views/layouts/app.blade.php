<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Certificate Management System')</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    @yield('styles')
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Component -->
        <x-sidebar :activeItem="$activeItem ?? ''" />

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header Component -->
            <x-header />

            <!-- Content Section -->
            <div class="dashboard-content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Check for dark mode cookie on page load
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeCookie = document.cookie
                .split('; ')
                .find(row => row.startsWith('darkMode='));

            if (darkModeCookie && darkModeCookie.split('=')[1] === 'true') {
                document.body.classList.add('dark-mode');

                // Update icons in all toggle buttons
                const modeToggles = document.querySelectorAll('.mode-toggle');
                modeToggles.forEach(toggle => {
                    toggle.innerHTML = '<i class="fas fa-sun"></i>';
                });
            }
        });
    </script>

    @yield('scripts')
</body>

</html>