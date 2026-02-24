<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/SystemSettingsHelper.php';
require_once __DIR__ . '/../../../app/helpers/PermissionHelper.php';

// Fetch system settings
$systemSettings = getSystemSettings();
$settings = $systemSettings;

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'System Settings';

require_once __DIR__ . '/../../../app/helpers/MailHelper.php';

// Check if user has permission to view settings
$userId = $_SESSION['user_id'];
if (!canViewModule($userId, 'settings')) {
    header('Location: ../../dashboard.php');
    exit();
}

// Check for mail overrides
$mailOverrides = isMailOverridden();

// Include shared header
include '../../includes/header.php';
?>

<div
    class="sticky top-0 z-10 bg-gray-50/80 dark:bg-[#1a1a1a]/80 backdrop-blur-md -mx-4 px-4 py-4 mb-8 border-b border-gray-200 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="animate-fade-in">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Settings</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-2">
            <i class="bi bi-gear-fill text-cms-red"></i>
            Configure global LGU information and system preferences
        </p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="saveSettings()" id="saveBtnTop"
            class="bg-cms-red text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-lg hover:opacity-90 flex items-center gap-2">
            <i class="bi bi-save2"></i>
            Save All Changes
        </button>
    </div>
</div>

<?php if (!empty($mailOverrides)): ?>
    <div
        class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl animate-fade-in flex items-start gap-3 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill text-amber-600 dark:text-amber-500 mt-1"></i>
        <div>
            <h4 class="text-amber-800 dark:text-amber-400 font-bold text-sm">Configuration Override Active</h4>
            <p class="text-amber-700 dark:text-amber-500 text-xs mt-1 leading-relaxed">
                The following SMTP settings are currently being overridden by <code>config/mail.php</code>:
                <span
                    class="font-semibold underline decoration-amber-300 dark:decoration-amber-700"><?php echo implode(', ', $mailOverrides); ?></span>.
                Changes made to these fields in the UI will not take effect until the override is removed from the file.
            </p>
        </div>
    </div>
<?php endif; ?>

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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">System Base
                            URL</label>
                        <input type="url" id="base_url" placeholder="http://domain.com/Capstone%20Project"
                            value="<?php echo htmlspecialchars($settings['base_url'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                        <p class="text-[10px] text-gray-500 mt-1 italic"><i class="bi bi-info-circle-fill"></i> Used for
                            verification links in emails.</p>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                            <i class="bi bi-palette text-cms-red"></i>
                            Theme Color
                        </label>
                        <div class="flex flex-col gap-3">
                            <div class="flex gap-2">
                                <input type="color" id="theme_color" oninput="syncHexFromPicker(this.value)"
                                    value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#dc2626'); ?>"
                                    class="h-11 w-16 p-1 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                                <input type="text" id="theme_color_hex" oninput="syncPickerFromHex(this.value)"
                                    maxlength="7"
                                    value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#dc2626'); ?>"
                                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600 font-mono uppercase">
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="setPresetColor('#991b1b')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #991b1b;"
                                    title="Valenzuela Official Red (#991b1b)"></button>
                                <button onclick="setPresetColor('#dc2626')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #dc2626;"
                                    title="Valenzuela Vibrant Red (#dc2626)"></button>
                                <button onclick="setPresetColor('#2563eb')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #2563eb;" title="Modern Blue"></button>
                                <button onclick="setPresetColor('#16a34a')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #16a34a;" title="Eco Green"></button>
                                <button onclick="setPresetColor('#7c3aed')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #7c3aed;" title="Royal Purple"></button>
                                <button onclick="setPresetColor('#111827')"
                                    class="w-10 h-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200 transition-transform hover:scale-110"
                                    style="background-color: #111827;" title="Midnight Dark"></button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">LGU Logo
                            Path</label>
                        <input type="text" id="lgu_logo_path" readonly
                            value="<?php echo htmlspecialchars($settings['lgu_logo_path'] ?? 'assets/images/logo.png'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-400 rounded-lg bg-gray-50 cursor-not-allowed">
                        <p class="text-[10px] text-gray-500 mt-1 italic"><i class="bi bi-lock-fill"></i> Logo path is
                            locked to maintain system integrity.</p>
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
            <div
                class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-envelope-at text-cms-red"></i>
                    SMTP Configuration
                </h2>
                <button type="button" onclick="testSMTP()"
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 shadow-sm">
                    <i class="bi bi-send-check text-green-600"></i>
                    Test Connection
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 mb-2">
                    <div class="flex">
                        <i class="bi bi-info-circle text-blue-600 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-bold mb-1">How to use your email (Gmail/Outlook):</p>
                            <p>For Gmail, use <strong>smtp.gmail.com</strong> (Port 587/TLS). IMPORTANT: You MUST use a
                                16-character <strong>App Password</strong>, not your regular login password. <a
                                    href="https://myaccount.google.com/apppasswords" target="_blank"
                                    class="underline hover:text-blue-600">Get one here</a>.</p>
                        </div>
                    </div>
                </div>
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
                    <input type="password" id="smtp_pass" placeholder="•••••••• (Leave blank to keep existing)"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    <p class="text-xs text-gray-500 mt-1 italic">For security, the current password is not displayed.
                    </p>
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
    function syncHexFromPicker(color) {
        document.getElementById('theme_color_hex').value = color;
    }

    function syncPickerFromHex(hex) {
        if (/^#[0-9A-F]{6}$/i.test(hex)) {
            document.getElementById('theme_color').value = hex;
        }
    }

    function setPresetColor(hex) {
        document.getElementById('theme_color').value = hex;
        document.getElementById('theme_color_hex').value = hex;
    }

    async function saveSettings() {
        const topBtn = document.getElementById('saveBtnTop');
        const topOriginalContent = topBtn ? topBtn.innerHTML : '';

        const setBtnLoading = (loading) => {
            if (topBtn) {
                if (loading) {
                    topBtn.disabled = true;
                    topBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Saving...';
                } else {
                    topBtn.disabled = false;
                    topBtn.innerHTML = topOriginalContent;
                }
            }
        };

        setBtnLoading(true);

        const data = {
            lgu_name: document.getElementById('lgu_name').value,
            lgu_email: document.getElementById('lgu_email').value,
            base_url: document.getElementById('base_url').value,
            lgu_address: document.getElementById('lgu_address').value,
            lgu_contact: document.getElementById('lgu_contact').value,
            backup_frequency: document.getElementById('backup_frequency').value,
            auto_backup_enabled: document.getElementById('auto_backup_enabled').checked ? 1 : 0,
            theme_color: document.getElementById('theme_color_hex').value,
            lgu_logo_path: document.getElementById('lgu_logo_path').value,
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

            let result;
            const responseText = await response.text();

            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Invalid JSON response:', responseText);
                throw new Error('Server returned invalid JSON. Raw response: ' + responseText.substring(0, 500));
            }

            if (result.success) {
                alert('Settings saved successfully!');
                location.reload();
            } else {
                let msg = result.message || 'Unknown error';
                if (result.debug_output) {
                    msg += '\n\nDebug Info: ' + result.debug_output;
                }
                alert('Error: ' + msg);
                setBtnLoading(false);
            }
        } catch (error) {
            console.error('Save Error:', error);
            alert('Save Failed: ' + error.message);
            setBtnLoading(false);
        }
    }
    async function testSMTP() {
        const recipient = prompt("Enter an email address to send a test message to:");
        if (!recipient) return;

        const data = {
            recipient: recipient,
            smtp_host: document.getElementById('smtp_host').value,
            smtp_port: document.getElementById('smtp_port').value,
            smtp_user: document.getElementById('smtp_user').value,
            smtp_pass: document.getElementById('smtp_pass').value,
            smtp_encryption: document.getElementById('smtp_encryption').value
        };

        try {
            const response = await fetch('ajax/test_smtp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                alert('Test email sent successfully! Please check your inbox.');
            } else {
                alert('SMTP Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred during the test.');
        }
    }
</script>

</div> <!-- Closing module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>