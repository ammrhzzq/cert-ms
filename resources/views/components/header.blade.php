<!-- resources/views/components/header.blade.php -->
<div class="navbar">
    <div class="navbar-left">
        
    </div>
    <div class="navbar-right">
        <button class="icon-button" title="Settings">
            <i class="fas fa-cog"></i>
        </button>
        <button class="icon-button" title="Help">
            <i class="fas fa-question-circle"></i>
        </button>
        
        <!-- Notification Button with Popout -->
        <div class="notification-container">
            <button class="icon-button notification" id="notificationBtn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </button>
            
            <!-- Notification Popout -->
            <div class="notification-popout" id="notificationPopout">
                <div class="notification-header">
                    <h4>Notifications</h4>
                    <button class="close-btn" id="closeNotifications">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="notification-body" id="notificationBody">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>
        
        <button class="mode-toggle" id="mode-toggle" title="Dark Mode">
            <i class="fas fa-moon"></i>
        </button>
        <div class="user-profile">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <span class="user-name">{{ Auth::user()->name }}</span>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>

<script>
    // Toggle dark mode
    document.getElementById('mode-toggle').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        
        // Update icon
        this.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        this.title = isDarkMode ? 'Light Mode' : 'Dark Mode';
        
        // Set cookie for persistence
        document.cookie = `darkMode=${isDarkMode}; path=/; max-age=${60*60*24*365}`;
    });

    // Notification System
    class NotificationSystem {
        constructor() {
            this.notificationBtn = document.getElementById('notificationBtn');
            this.notificationPopout = document.getElementById('notificationPopout');
            this.notificationBadge = document.getElementById('notificationBadge');
            this.notificationBody = document.getElementById('notificationBody');
            this.closeBtn = document.getElementById('closeNotifications');
            
            this.init();
            this.loadNotifications();
            
            // Auto-refresh notifications every 5 minutes
            setInterval(() => this.loadNotifications(), 5 * 60 * 1000);
        }

        init() {
            // Toggle popout
            this.notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.togglePopout();
            });

            // Close popout
            this.closeBtn.addEventListener('click', () => {
                this.hidePopout();
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.notificationPopout.contains(e.target) && !this.notificationBtn.contains(e.target)) {
                    this.hidePopout();
                }
            });
        }

        togglePopout() {
            const isVisible = this.notificationPopout.classList.contains('show');
            if (isVisible) {
                this.hidePopout();
            } else {
                this.showPopout();
                this.loadNotifications();
            }
        }

        showPopout() {
            this.notificationPopout.classList.add('show');
        }

        hidePopout() {
            this.notificationPopout.classList.remove('show');
        }

        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications');
                const data = await response.json();
                
                this.updateBadge(data.total_count);
                this.renderNotifications(data.notifications);
            } catch (error) {
                console.error('Failed to load notifications:', error);
                this.renderError();
            }
        }

        updateBadge(count) {
            if (count > 0) {
                this.notificationBadge.textContent = count > 99 ? '99+' : count;
                this.notificationBadge.style.display = 'block';
                this.notificationBtn.classList.add('has-notifications');
            } else {
                this.notificationBadge.style.display = 'none';
                this.notificationBtn.classList.remove('has-notifications');
            }
        }

        renderNotifications(notifications) {
            if (notifications.length === 0) {
                this.notificationBody.innerHTML = `
                    <div class="no-notifications">
                        <i class="fas fa-check-circle"></i>
                        <p>No pending notifications</p>
                    </div>
                `;
                return;
            }

            const html = notifications.map(notification => `
                <div class="notification-item ${notification.type}" onclick="window.location.href='${notification.action_url}'">
                    <div class="notification-icon">
                        <i class="fas ${this.getIconByType(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                    </div>
                    <div class="notification-count">
                        ${notification.count}
                    </div>
                </div>
            `).join('');

            this.notificationBody.innerHTML = html;
        }

        renderError() {
            this.notificationBody.innerHTML = `
                <div class="notification-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load notifications</p>
                </div>
            `;
        }

        getIconByType(type) {
            const icons = {
                'warning': 'fa-exclamation-triangle',
                'success': 'fa-check-circle',
                'danger': 'fa-times-circle',
                'info': 'fa-info-circle'
            };
            return icons[type] || 'fa-bell';
        }
    }

    // Initialize notification system when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        new NotificationSystem();
    });
</script>