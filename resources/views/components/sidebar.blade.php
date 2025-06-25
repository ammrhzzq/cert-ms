<!-- resources/views/components/sidebar.blade.php -->
<?php
// Check if sidebar is collapsed using cookie
$isCollapsed = isset($_COOKIE['sidebarCollapsed']) && $_COOKIE['sidebarCollapsed'] === 'true';
?>

<div class="sidebar {{ $isCollapsed ? 'collapsed' : '' }}" id="sidebar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
    </div>
    <div class="sidebar-toggle-container">
        <button class="sidebar-toggle" id="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <ul class="menu">
        <li>
            <a href="{{ route('dashboard') }}" class="{{ $activeItem == 'dashboard' ? 'active' : '' }}" title="Dashboard">
                <i class="fas fa-home icon"></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('certificates.create') }}" class="{{ $activeItem == 'create' ? 'active' : '' }}" title="Create New Certificate">
                <i class="fas fa-plus-circle icon"></i>
                <span class="text">Create New</span>
            </a>
        </li>
        <li>
            <a href="{{ route('certificates.index') }}" class="{{ $activeItem == 'certificates' ? 'active' : '' }}" title="Status & Action">
                <i class="fas fa-clipboard-list icon"></i>
                <span class="text">Status & Action</span>
            </a>
        </li>
        <li>
            <a href="{{ route('certificates.view') }}" class="{{ $activeItem == 'view' ? 'active' : '' }}" title="View & Search">
                <i class="fas fa-search icon"></i>
                <span class="text">View & Search</span>
            </a>
        </li>
        <li>
            <a href="{{ route('templates.index') }}" class="{{ $activeItem == 'templates' ? 'active' : '' }}" title="Template Management">
                <i class="fas fa-file icon"></i>
                <span class="text">Template Management</span>
            </a>
        </li>
        <li>
            <a href="{{ route('clients.index') }}" class="{{ $activeItem == 'clients' ? 'active' : '' }}" title="Client Management">
                <i class="fas fa-users icon"></i>
                <span class="text">Client Management</span>
            </a>
        </li>
        @if(auth()->check() && auth()->user()->role === 'administrator')
        <li>
            <a href="{{ route('users.index') }}" class="{{ $activeItem == 'users' ? 'active' : '' }}" title="User Management">
                <i class="fas fa-user-cog icon"></i>
                <span class="text">User Management</span>
            </a>
        </li>
        @endif
    </ul>
</div>

<script>
    // Toggle sidebar
    document.getElementById('toggle-sidebar').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');

        // Update cookie for persistence
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.cookie = `sidebarCollapsed=${isCollapsed}; path=/; max-age=${60*60*24*365}`;

        // Adjust main content margin if needed
        if (document.querySelector('.main-content')) {
            document.querySelector('.main-content').classList.toggle('expanded');
        }
    });
</script>