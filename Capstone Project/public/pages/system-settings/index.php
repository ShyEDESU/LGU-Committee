<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/SystemSettingsHelper.php';

// Fetch system settings
$systemSettings = getSystemSettings();
$settings = $systemSettings;

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'System Settings';

// Check if user is admin
$userRoleLower = strtolower($_SESSION['user_role'] ?? 'User');
$isAdmin = ($userRoleLower === 'admin' || $userRoleLower === 'administrator' || $userRoleLower === 'super admin' ||
    $userRoleLower === 'super administrator');

// Redirect non-admins
if (!$isAdmin) {
    header('Location: ../dashboard.php');
    exit();
}

// Include shared header
include '../../includes/header.php';
?>

<div class="mb-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Configure global LGU information and system preferences</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Settings Tabs -->
    <div class="lg:col-span-2 space-y-6">
        <!-- General Information Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-100">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-building text-cms-red"></i>
                    LGU Information
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">LGU Name</label>
                        <input type="text" id="lgu_name" value="<?php echo htmlspecialchars($settings['lgu_name']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">LGU Email</label>
                        <input type="email" id="lgu_email"
                            value="<?php echo htmlspecialchars($settings['lgu_email']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">LGU Address</label>
                    <textarea id="lgu_address" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600"><?php echo htmlspecialchars($settings['lgu_address']); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact
                        Number</label>
                    <input type="text" id="lgu_contact"
                        value="<?php echo htmlspecialchars($settings['lgu_contact']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>
        </div>

        <!-- System Preferences Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-200">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-gear-wide-connected text-cms-red"></i>
                    System Configuration
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Timezone</label>
                        <select id="timezone"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                            <option value="Asia/Manila" <?php echo $settings['timezone'] === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (GMT+8)</option>
                            <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Backup
                            Frequency</label>
                        <select id="backup_frequency"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                            <option value="daily" <?php echo $settings['backup_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $settings['backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo $settings['backup_frequency'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Auto-Backup Enabled</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Automatically backup database records</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="auto_backup_enabled" class="sr-only peer" <?php echo $settings['auto_backup_enabled'] ? 'checked' : ''; ?>>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Security Settings Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-300">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-shield-lock text-cms-red"></i>
                    Security Settings
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Password
                        Length</label>
                    <input type="number" id="min_password_length"
                        value="<?php echo htmlspecialchars($settings['min_password_length']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Require Special Characters</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Enforce special characters in passwords</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="require_special_chars" class="sr-only peer" <?php echo $settings['require_special_chars'] ? 'checked' : ''; ?>>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Session Management Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-400">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-clock-history text-cms-red"></i>
                    Session Management
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Session Timeout
                        (minutes)</label>
                    <input type="number" id="session_timeout"
                        value="<?php echo htmlspecialchars($settings['session_timeout']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>
        </div>

        <!-- SMTP Configuration Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-500">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-envelope-at text-cms-red"></i>
                    SMTP Configuration
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMTP Host</label>
                        <input type="text" id="smtp_host"
                            value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMTP Port</label>
                        <input type="number" id="smtp_port"
                            value="<?php echo htmlspecialchars($settings['smtp_port']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMTP Username</label>
                    <input type="text" id="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMTP Password</label>
                    <input type="password" id="smtp_pass"
                        value="<?php echo htmlspecialchars($settings['smtp_pass']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMTP
                        Encryption</label>
                    <select id="smtp_encryption"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                        <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None
                        </option>
                        <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL
                        </option>
                        <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Localization Settings Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden animate-fade-in-up animation-delay-600">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-translate text-cms-red"></i>
                    Localization Settings
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default
                        Language</label>
                    <select id="default_language"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                        <option value="en" <?php echo $settings['default_language'] === 'en' ? 'selected' : ''; ?>>English
                        </option>
                        <!-- Add more language options as needed -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Format</label>
                    <input type="text" id="date_format"
                        value="<?php echo htmlspecialchars($settings['date_format']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Format</label>
                    <input type="text" id="time_format"
                        value="<?php echo htmlspecialchars($settings['time_format']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-end pt-2 pb-10">
            <button onclick="saveSettings()" id="saveBtn"
                class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-red-500/20 flex items-center gap-2">
                <i class="bi bi-save2"></i>
                Save All Settings
            </button>
        </div>
        <!-- Right Column: System Identity & Status -->
        <div class="space-y-6">
            <!-- System Identity Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-info-circle text-cms-red"></i>
                    System Identity
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">System
                            Title</label>
                        <input type="text" id="system_title"
                            value="<?php echo htmlspecialchars($settings['system_title']); ?>"
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">System
                            Acronym</label>
                        <input type="text" id="system_acronym"
                            value="<?php echo htmlspecialchars($settings['system_acronym']); ?>"
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                </div>
            </div>

            <!-- System Status & Maintenance -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-activity text-cms-red"></i>
                    System Status
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Maintenance Mode</p>
                            <p class="text-xs text-gray-500 italic">Disables non-admin access</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="maintenance_mode" class="sr-only peer" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                            </div>
                        </label>
                    </div>
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Log Retention
                            (Days)</label>
                        <input type="number" id="log_retention_days"
                            value="<?php echo htmlspecialchars($settings['log_retention_days']); ?>"
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    </div>
                </div>
            </div>

            <!-- Audit Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-900">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Audit Info</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Last Modified</span>
                        <span
                            class="text-gray-900 dark:text-white font-medium"><?php echo date('M j, Y H:i', strtotime($settings['updated_at'] ?? 'now')); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Updated By</span>
                        <span class="text-gray-900 dark:text-white font-medium">Administrator</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function saveSettings() {
        const btn = document.getElementById('saveBtn');
        const originalContent = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Saving...';

        const data = {
            lgu_name: document.getElementById('lgu_name').value,
            lgu_email: document.getElementById('lgu_email').value,
            lgu_address: document.getElementById('lgu_address').value,
            lgu_contact: document.getElementById('lgu_contact').value,
            backup_frequency: document.getElementById('backup_frequency').value,
            auto_backup_enabled: document.getElementById('auto_backup_enabled').checked ? 1 : 0,
            maintenance_mode: document.getElementById('maintenance_mode').checked ? 1 : 0,
            session_timeout: document.getElementById('session_timeout').value,
            min_password_length: document.getElementById('min_password_length').value,
            require_special_chars: document.getElementById('require_special_chars').checked ? 1 : 0,
            smtp_host: document.getElementById('smtp_host').value,
            smtp_port: document.getElementById('smtp_port').value,
            smtp_user: document.getElementById('smtp_user').value,
            smtp_pass: document.getElementById('smtp_pass').value,
            smtp_encryption: document.getElementById('smtp_encryption').value,
            log_retention_days: document.getElementById('log_retention_days').value,
            system_title: document.getElementById('system_title').value,
            system_acronym: document.getElementById('system_acronym').value,
            default_language: document.getElementById('default_language').value,
            date_format: document.getElementById('date_format').value,
            time_format: document.getElementById('time_format').value
        };

        try {
            const response = await fetch('ajax/save_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                // Show success toast or alert
                alert('Settings saved successfully!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }
</script>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>