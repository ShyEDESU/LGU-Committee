<?php
// Handle path prefixing and root path determination
// Use variables already defined in header.php if they exist
if (!isset($footerPathPrefix)) {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    if (strpos($currentDir, '/pages/') !== false) {
        $footerPathPrefix = '../../';
        $rootPath = '../../../';
    } elseif (strpos($currentDir, '/public') !== false) {
        $footerPathPrefix = '';
        $rootPath = '../';
    } else {
        $footerPathPrefix = 'public/';
        $rootPath = './';
    }
}

// Fetch system settings for branding
require_once __DIR__ . '/../../app/helpers/SystemSettingsHelper.php';
$settings = getSystemSettings();
$themeColor = $settings['theme_color'] ?? '#dc2626';
$systemLogo = $settings['lgu_logo_path'] ?? 'assets/images/logo.png';
?>

<!-- Redesigned System Footer -->
<footer class="bg-slate-950 text-slate-300 py-16 border-t border-slate-800 font-sans">
    <div class="max-w-[1600px] mx-auto px-4 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 mb-12">
            <!-- Branding & About -->
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <img src="<?php echo $footerPathPrefix . $systemLogo; ?>" alt="Logo"
                        class="w-16 h-16 object-contain">
                    <div>
                        <h4 class="text-white font-black text-xl tracking-tight uppercase">City of Valenzuela</h4>
                        <p class="text-[<?php echo $themeColor; ?>] text-xs font-bold uppercase tracking-widest"
                            style="color: <?php echo $themeColor; ?>;">Legislative Office</p>
                    </div>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                    Making legislative documents accessible to all citizens. Transparency and efficiency in city
                    governance through digital tracking.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="md:pl-12">
                <h4 class="text-white font-bold text-sm mb-8 uppercase tracking-[0.2em]">Quick Links</h4>
                <ul class="space-y-4 text-sm font-semibold">
                    <li><a href="<?php echo $rootPath; ?>index.php" class="hover:text-white transition-colors">Home</a>
                    </li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/committee-meetings/index.php"
                            class="hover:text-white transition-colors">Browse Documents</a></li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/referral-management/index.php"
                            class="hover:text-white transition-colors">Track Progress</a></li>
                    <li><a href="<?php echo $rootPath; ?>index.php#leaders"
                            class="hover:text-white transition-colors">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div>
                <h4 class="text-white font-bold text-sm mb-8 uppercase tracking-[0.2em]">Contact Information</h4>
                <ul class="space-y-6 text-sm font-semibold">
                    <li class="flex items-start space-x-4">
                        <i class="bi bi-geo-alt text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">Valenzuela City Hall, MacArthur Highway,<br>Valenzuela City, Metro
                            Manila</span>
                    </li>
                    <li class="flex items-center space-x-4">
                        <i class="bi bi-telephone text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">(02) 8352-1000</span>
                    </li>
                    <li class="flex items-center space-x-4">
                        <i class="bi bi-envelope text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">legislative@valenzuela.gov.ph</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="pt-8 border-t border-slate-900 text-center">
            <p class="text-slate-600 text-[10px] font-bold uppercase tracking-[0.3em]">
                &copy; <?php echo date('Y'); ?> City Government of Valenzuela. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>