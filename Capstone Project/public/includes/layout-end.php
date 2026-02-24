<?php
// Note: module-content-wrapper should be manually closed in the page file 
// before including the footer to ensure proper spacing and layout.
?>
</main>
</div><!-- /.flex-1.flex.flex-col -->
</div><!-- /.flex.h-screen -->

<!-- Template Scripts -->
<script src="<?php echo $footerPathPrefix; ?>assets/js/script-updated.js"></script>

<!-- Module Loading Overlay Handler -->
<script>
    // Module Loading Overlay Handler
    const moduleLoadingOverlay = document.getElementById('moduleLoadingOverlay');
    const MINIMUM_LOADING_TIME = 2000; // 2 seconds before navigation

    // Show loading overlay immediately when clicking navigation links
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');

        if (link && !link.hasAttribute('data-no-loader')) {
            const href = link.getAttribute('href');

            // Check if it's an internal navigation link (not external, hash, or javascript)
            if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('http')) {
                // Check if it's a module link
                if (href.includes('pages/') || href.includes('dashboard.php')) {
                    e.preventDefault(); // Intercept navigation for sequential flow

                    if (moduleLoadingOverlay) {
                        moduleLoadingOverlay.classList.remove('hidden');
                        moduleLoadingOverlay.style.display = 'flex';

                        // Defer actual navigation to allow animation to play on current page
                        setTimeout(() => {
                            window.location.href = href;
                        }, MINIMUM_LOADING_TIME);
                    } else {
                        window.location.href = href; // Fallback if overlay missing
                    }
                }
            }
        }
    }, true);

    // Ensure overlay is hidden on initial load (no phase 2)
    window.addEventListener('load', () => {
        if (moduleLoadingOverlay) {
            moduleLoadingOverlay.classList.add('hidden');
            moduleLoadingOverlay.style.display = '';
        }
    });

    window.addEventListener('pageshow', () => {
        if (moduleLoadingOverlay) {
            moduleLoadingOverlay.classList.add('hidden');
            moduleLoadingOverlay.style.display = '';
        }
    });
</script>

<!-- Logout Confirmation Modal & Overlay -->
<div id="logoutModal"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[110] flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform transition-all">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-box-arrow-right text-3xl text-red-600 dark:text-red-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Confirm Logout</h3>
        <p class="text-gray-600 dark:text-slate-400 mb-6 text-sm">Are you sure you want to end your session?</p>
        <div class="flex gap-3">
            <button onclick="closeLogoutModal()"
                class="flex-1 px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-white rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-slate-600 transition-all">Cancel</button>
            <button onclick="confirmLogout()"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">Logout</button>
        </div>
    </div>
</div>

<div id="logoutOverlay"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-md z-[120] flex items-center justify-center">
    <div class="text-center">
        <div class="mb-4 inline-block">
            <div class="w-16 h-16 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Securing your session</h3>
        <p class="text-white/60 text-sm">Clearing cache and encrypting data...</p>
        <div class="mt-6 w-48 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden">
            <div id="logoutProgress" class="h-full bg-white w-0 transition-all duration-[3s] ease-linear"></div>
        </div>
        <p class="mt-2 text-[10px] text-white/40 uppercase tracking-widest font-bold">System Ref: <span
                id="sessionTimer">3s</span></p>
    </div>
</div>

<script>
    function showLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function confirmLogout() {
        closeLogoutModal();
        const overlay = document.getElementById('logoutOverlay');
        const progress = document.getElementById('logoutProgress');
        const timer = document.getElementById('sessionTimer');

        overlay.classList.remove('hidden');
        setTimeout(() => { progress.style.width = '100%'; }, 100);

        let timeLeft = 3;
        const interval = setInterval(() => {
            timeLeft--;
            timer.textContent = timeLeft + 's';
            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.href = CMS_ROOT + 'auth/logout.php';
            }
        }, 1000);
    }
</script>

<!-- Unified Session Management -->
<script src="<?php echo $footerPathPrefix; ?>assets/js/session-manager.js"></script>

<script>
    console.log('CMS Premium Layout Initialized');
</script>
</body>

</html>