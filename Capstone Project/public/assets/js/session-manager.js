// Unified session management script
// Handles secure automatic logout and displays a modern countdown warning modal.

let lastActivity = Date.now();
const SESSION_TIMEOUT = 15 * 60 * 1000; // 15 minutes (900,000ms)
const WARNING_THRESHOLD = 14 * 60 * 1000; // Warn after 14 minutes of inactivity (60s before expiry)
const HEARTBEAT_INTERVAL = 60 * 1000; // Heartbeat every 1 minute to keep PHP session active
let logoutInProgress = false;
let isNavigating = false;
let warningModalActive = false;
let warningCountdownInterval = null;

// Track user interactions to reset local activity timer
function updateActivity() {
    lastActivity = Date.now();
    if (warningModalActive) {
        keepSessionAlive();
    }
}

['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, updateActivity, true);
});

// Avoid warning during intentional transitions
document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (link && link.href && link.href.includes(window.location.hostname)) {
        isNavigating = true;
    }
});
document.addEventListener('submit', function () {
    isNavigating = true;
});

// Trigger AJAX heartbeat to tell server we are active
function sendHeartbeat() {
    if (logoutInProgress) return;
    const basePath = window.location.pathname.includes('/pages/') ? '../../../' : '../';
    fetch(basePath + 'app/controllers/AuthController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=heartbeat'
    }).catch(() => {
        console.log('Heartbeat failed');
    });
}

// Show beautiful warning overlay modal
function showWarningModal() {
    if (warningModalActive || logoutInProgress) return;
    warningModalActive = true;

    // Inject modal styles if not already present
    if (!document.getElementById('session-warning-style')) {
        const style = document.createElement('style');
        style.id = 'session-warning-style';
        style.textContent = `
            #session-timeout-modal {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 9999999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.25s ease;
                pointer-events: none;
            }
            #session-timeout-modal.show {
                opacity: 1;
                pointer-events: auto;
            }
            #session-timeout-modal .modal-card {
                background: #fff;
                border-radius: 16px;
                padding: 32px;
                width: 100%;
                max-width: 440px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                text-align: center;
                transform: translateY(20px);
                transition: transform 0.25s ease;
            }
            .dark #session-timeout-modal .modal-card {
                background: #1f2937;
                border: 1px solid #374151;
            }
            #session-timeout-modal.show .modal-card {
                transform: translateY(0);
            }
            #session-timeout-modal .countdown-circle {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: #fee2e2;
                color: #dc2626;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                font-weight: 700;
                margin: 0 auto 20px;
            }
            .dark #session-timeout-modal .countdown-circle {
                background: rgba(220, 38, 38, 0.15);
            }
        `;
        document.head.appendChild(style);
    }

    // Create and append modal to body
    let modal = document.getElementById('session-timeout-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.innerHTML = `
            <div class="modal-card">
                <div class="countdown-circle" id="session-countdown-num">60</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Session Expiring</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    You have been inactive for a while. You will be logged out automatically in <span class="font-bold text-red-600" id="session-countdown-text">60</span> seconds.
                </p>
                <div class="flex gap-3 justify-center">
                    <button id="session-keep-alive-btn" class="px-5 py-2.5 bg-red-600 hover:bg-red-750 text-white rounded-lg font-semibold transition text-sm">
                        Keep Me Logged In
                    </button>
                    <button id="session-logout-btn" class="px-5 py-2.5 bg-gray-150 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-semibold transition text-sm">
                        Log Out
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Bind events
        document.getElementById('session-keep-alive-btn').addEventListener('click', keepSessionAlive);
        document.getElementById('session-logout-btn').addEventListener('click', triggerInactivityLogout);
    }

    // Show modal
    setTimeout(() => modal.classList.add('show'), 50);

    // Start countdown timer
    let timeLeft = 60;
    const numEl = document.getElementById('session-countdown-num');
    const textEl = document.getElementById('session-countdown-text');

    warningCountdownInterval = setInterval(() => {
        timeLeft--;
        if (numEl) numEl.textContent = timeLeft;
        if (textEl) textEl.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(warningCountdownInterval);
            triggerInactivityLogout();
        }
    }, 1000);
}

// Reset activity and clear warning state
function keepSessionAlive() {
    lastActivity = Date.now();
    warningModalActive = false;
    if (warningCountdownInterval) clearInterval(warningCountdownInterval);

    const modal = document.getElementById('session-timeout-modal');
    if (modal) {
        modal.classList.remove('show');
    }
    sendHeartbeat();
}

// Redirect user to logout
function triggerInactivityLogout() {
    if (logoutInProgress) return;
    logoutInProgress = true;
    if (warningCountdownInterval) clearInterval(warningCountdownInterval);

    const basePath = window.location.pathname.includes('/pages/') ? '../../../' : '../';
    window.location.href = basePath + 'auth/login.php?reason=timeout';
}

// Check status every second for immediate countdown trigger
setInterval(() => {
    if (isNavigating || logoutInProgress) return;

    const timeSinceActivity = Date.now() - lastActivity;

    if (timeSinceActivity >= SESSION_TIMEOUT) {
        triggerInactivityLogout();
    } else if (timeSinceActivity >= WARNING_THRESHOLD) {
        showWarningModal();
    }
}, 1000);

// Heartbeat tracker to keep PHP session fresh
setInterval(() => {
    const timeSinceActivity = Date.now() - lastActivity;
    if (timeSinceActivity < WARNING_THRESHOLD) {
        sendHeartbeat();
    }
}, HEARTBEAT_INTERVAL);

console.log('Premium Session Manager loaded: 15min timeout with 60s warning countdown.');
