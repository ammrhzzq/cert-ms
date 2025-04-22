<!-- resources/views/components/header.blade.php -->
<div class="navbar">
    <div class="navbar-left">
        
    </div>
    <div class="navbar-right">
        <button class="icon-button">
            <i class="fas fa-cog"></i>
        </button>
        <button class="icon-button">
            <i class="fas fa-question-circle"></i>
        </button>
        <button class="icon-button notification">
            <i class="fas fa-bell"></i>
        </button>
        <button class="mode-toggle" id="mode-toggle">
            <i class="fas fa-moon"></i>
        </button>
        <div class="user-profile">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <span class="user-name">Staff ISCB</span>
        </div>
    </div>
</div>

<script>
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