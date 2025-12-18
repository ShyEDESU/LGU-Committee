<?php
/**
 * System Settings & Configuration Module
 * Manage system-wide settings, security, and configuration
 */

session_start();
require_once '../../../public/includes/header-sidebar.php';
?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings & Configuration</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage system-wide settings, security, and configuration options</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6 mt-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-8 overflow-x-auto">
            <button class="px-4 py-3 font-semibold text-cms-red border-b-2 border-cms-red transition whitespace-nowrap" onclick="showTab('general')">
                <i class="bi bi-sliders mr-2"></i>General Settings
            </button>
            <button class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition whitespace-nowrap" onclick="showTab('security')">
                <i class="bi bi-shield-lock mr-2"></i>Security
            </button>
            <button class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition whitespace-nowrap" onclick="showTab('email')">
                <i class="bi bi-envelope mr-2"></i>Email Configuration
            </button>
            <button class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition whitespace-nowrap" onclick="showTab('backup')">
                <i class="bi bi-cloud-check mr-2"></i>Backup & Recovery
            </button>
        </div>
    </div>

    <!-- General Settings Tab -->
    <div id="general-tab" class="active-tab">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-2xl">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">System Name</label>
                    <input type="text" value="Legislative Records Management System" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Organization</label>
                    <input type="text" value="City Government of Valenzuela" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">System Time Zone</label>
                    <select class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                        <option>Asia/Manila (UTC+8)</option>
                        <option>UTC</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Date Format</label>
                    <select class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                        <option>MM/DD/YYYY</option>
                        <option>DD/MM/YYYY</option>
                        <option>YYYY-MM-DD</option>
                    </select>
                </div>

                <button type="submit" class="px-6 py-2 bg-cms-red hover:bg-cms-dark text-white font-semibold rounded-lg transition">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Security Tab -->
    <div id="security-tab" class="hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Security Settings</h3>
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Requires additional verification for login</p>
                    </div>
                    <button class="px-4 py-2 bg-cms-red hover:bg-cms-dark text-white font-semibold rounded-lg transition">Enable</button>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Session Timeout</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">30 minutes of inactivity</p>
                    </div>
                    <input type="number" value="30" min="5" max="120" class="w-24 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Password Policy</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Enforce strong passwords</p>
                    </div>
                    <input type="checkbox" checked class="w-4 h-4 rounded cursor-pointer">
                </div>

                <button class="px-6 py-2 bg-cms-red hover:bg-cms-dark text-white font-semibold rounded-lg transition">Update Security Settings</button>
            </div>
        </div>
    </div>

    <!-- Email Configuration Tab -->
    <div id="email-tab" class="hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Email Configuration</h3>
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">SMTP Server</label>
                    <input type="email" placeholder="smtp.gmail.com" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">SMTP Port</label>
                    <input type="number" value="587" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Sender Email</label>
                    <input type="email" value="noreply@valenzuela.gov.ph" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cms-red outline-none">
                </div>

                <button type="submit" class="px-6 py-2 bg-cms-red hover:bg-cms-dark text-white font-semibold rounded-lg transition">Update Email Settings</button>
            </form>
        </div>
    </div>

    <!-- Backup & Recovery Tab -->
    <div id="backup-tab" class="hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Backup & Recovery</h3>
            <div class="space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-900 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="font-semibold text-green-900 dark:text-green-100">Last Backup: Today at 2:30 AM</p>
                    <p class="text-sm text-green-800 dark:text-green-200 mt-1">Size: 256 MB</p>
                </div>
                <button class="w-full px-6 py-3 bg-cms-red hover:bg-cms-dark text-white font-semibold rounded-lg transition">Create Backup Now</button>
                <button class="w-full px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Download Latest Backup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.classList.add('hidden'));
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    document.querySelectorAll('button[onclick^="showTab"]').forEach(btn => {
        btn.classList.remove('text-cms-red', 'border-cms-red');
        btn.classList.add('text-gray-600', 'dark:text-gray-400');
    });
    event.target.closest('button').classList.remove('text-gray-600', 'dark:text-gray-400');
    event.target.closest('button').classList.add('text-cms-red', 'border-cms-red');
}
</script>
