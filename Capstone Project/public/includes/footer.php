            </main>
        </div>
    </div>

    <script src="/assets/js/ui-enhancements.js"></script>

    <script>
        // Dark Mode Toggle Function
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            // Save preference to localStorage
            const isDarkMode = html.classList.contains('dark');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        }

        // Initialize dark mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme');
            const html = document.documentElement;
            
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            }
        });

        // Sidebar Toggle for Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Sidebar Collapse Toggle for Desktop
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Reinitialize charts on resize
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.add('collapsed');
            }
        });

        // Logout Function with Confirmation
        function logout() {
            showLogoutConfirmation();
        }
        
        function showLogoutConfirmation() {
            const modal = document.createElement('div');
            modal.id = 'logoutConfirmModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 animate-fade-in';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-md w-full animate-scale-in">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-box-arrow-right text-cms-red"></i>
                            Confirm Logout
                        </h3>
                        <button onclick="closeLogoutModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <i class="bi bi-x text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to log out?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">You will be redirected to the login page.</p>
                    </div>
                    
                    <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
                        <button onclick="closeLogoutModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 font-semibold transition">
                            Cancel
                        </button>
                        <button onclick="confirmLogout()" class="flex-1 px-4 py-2 text-white bg-cms-red hover:bg-cms-dark rounded-lg font-semibold transition flex items-center justify-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
        }
        
        function closeLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        function confirmLogout() {
            closeLogoutModal();
            
            fetch('../../../../app/controllers/AuthController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=logout'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '../../../../auth/login.php?logout=success';
                } else {
                    window.location.href = '../../../../auth/login.php?logout=success';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = '../../../../auth/login.php?logout=success';
            });
        }
        
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal && e.target === modal) {
                closeLogoutModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('logoutConfirmModal')) {
                closeLogoutModal();
            }
        });

        // Close sidebar when clicking a link on mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.remove('-translate-x-full');
                document.getElementById('sidebarOverlay').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
