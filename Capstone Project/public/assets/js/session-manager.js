// Unified session management script
// Include this in both dashboard.php and header.php for consistency

// Session activity tracking
let lastActivity = Date.now();
const SESSION_TIMEOUT = 8 * 60 * 60 * 1000; // 8 hours (matches PHP config)
const HEARTBEAT_INTERVAL = 5 * 60 * 1000; // 5 minutes (reduced frequency)
let isNavigating = false;
let logoutInProgress = false;

// Update last activity time
function updateActivity() {
    lastActivity = Date.now();
}

// Track user activity
['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, updateActivity, true);
});

// Track navigation to prevent logout on page change
document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (link && link.href && link.href.includes(window.location.hostname)) {
        isNavigating = true;
    }
});

document.addEventListener('submit', function () {
    isNavigating = true;
});

// Send heartbeat to keep session alive
function sendHeartbeat() {
    if (logoutInProgress) return;

    const basePath = window.location.pathname.includes('/pages/') ? '../../../' : '../';
    fetch(basePath + 'app/controllers/AuthController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=heartbeat'
    }).catch(() => {
        console.log('Heartbeat failed - session may be expired');
    });
}

// Check for session timeout
function checkSessionTimeout() {
    if (logoutInProgress) return;

    const timeSinceActivity = Date.now() - lastActivity;

    if (timeSinceActivity > SESSION_TIMEOUT) {
        logoutInProgress = true;
        alert('Your session has expired due to inactivity. Please login again.');
        const basePath = window.location.pathname.includes('/pages/') ? '../../../' : '../';
        window.location.href = basePath + 'auth/login.php?reason=timeout';
    }
}

// Heartbeat and timeout check interval
setInterval(() => {
    const timeSinceActivity = Date.now() - lastActivity;
    // Only send heartbeat if user was active in last 10 minutes
    if (timeSinceActivity < 10 * 60 * 1000) {
        sendHeartbeat();
    }
    checkSessionTimeout();
}, HEARTBEAT_INTERVAL);

// DISABLED: Auto-logout on tab/window close
// This was causing logout on refresh!
// window.addEventListener('beforeunload', function (e) {
//     if (logoutInProgress || isNavigating) {
//         return;
//     }
//     navigator.sendBeacon(basePath + 'auth/logout_handler.php', 'auto_logout=true');
// });

console.log('Session manager initialized - 8 hour timeout, no auto-logout on close');
