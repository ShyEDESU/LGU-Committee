<?php
/**
 * Mail Helper - Handles sending emails via SMTP or PHP Mail
 * 
 * This helper uses settings from config/mail.php if available, 
 * otherwise it falls back to the database system_settings.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/SystemSettingsHelper.php';

// Try to include the optional mail config
if (file_exists(__DIR__ . '/../../config/mail.php')) {
    require_once __DIR__ . '/../../config/mail.php';
}

/**
 * Send a verification email to a new user
 */
function sendVerificationEmail($userEmail, $userName, $token)
{
    $settings = getMailSettings();
    // Base URL for links
    $baseUrl = defined('APP_URL') ? rtrim(APP_URL, '/') : 'http://localhost/Capstone%20Project';
    $baseUrl = str_replace(' ', '%20', $baseUrl);

    // Build absolute URL for verification link
    $verificationLink = $baseUrl . "/public/auth/verify.php?token=" . $token;

    // Get system branding
    $systemSettings = getSystemSettings();
    $themeColor = $systemSettings['theme_color'] ?? '#dc2626';
    $lguName = $systemSettings['lgu_name'] ?? 'Legislative Services MS';

    // Build absolute URL for logo - System Logo path starts with assets/
    $logoPath = $systemSettings['lgu_logo_path'] ?? 'assets/images/logo.png'; // Assuming lgu_logo_path is in systemSettings
    $lguLogo = $baseUrl . "/public/" . ltrim($logoPath, '/');

    $subject = "Verify Your Account - " . $lguName;

    // Use a simpler but professional template to reduce phishing flags
    // Gmail/Outlook flag emails where link text doesn't match the URL, or where the "From" domain mismatches the "Link" domain.
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background-color: #f8fafc; color: #334155; }
            .wrapper { width: 100%; padding: 40px 0; background-color: #f8fafc; }
            .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
            .header { background-color: $themeColor; padding: 48px 32px; text-align: center; color: white; }
            .logo-bg { background: white; width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; padding: 8px; display: inline-block; }
            .logo-img { width: 100%; height: 100%; object-fit: contain; }
            .content { padding: 48px; text-align: center; }
            .h1 { font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 16px; }
            .p { font-size: 16px; color: #64748b; line-height: 1.6; margin-bottom: 32px; }
            .btn-link { display: inline-block; padding: 18px 36px; background-color: $themeColor; color: white !important; text-decoration: none; border-radius: 12px; font-weight: 800; font-size: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            .footer { background-color: #f1f5f9; padding: 32px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
            .raw-link { margin-top: 32px; font-size: 12px; color: #94a3b8; word-break: break-all; text-align: left; border-top: 1px solid #f1f5f9; padding-top: 24px; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='container'>
                <div class='header'>
                    " . ($lguLogo ? "
                    <div class='logo-bg'>
                        <img src='$lguLogo' alt='Logo' class='logo-img'>
                    </div>" : "") . "
                    <h2 style='margin:0; font-size: 20px; font-weight: 800;'>$lguName</h2>
                    <p style='margin:4px 0 0; opacity: 0.9; font-size: 13px;'>Legislative Services Committee Management System</p>
                </div>
                <div class='content'>
                    <h1 class='h1'>Verify Your Email</h1>
                    <p class='p'>Hello $userName, thank you for joining our system. Please click the button below to verify your account and get started.</p>
                    
                    <a href='$verificationLink' class='btn-link'>Complete Verification</a>
                    
                    <div class='raw-link'>
                        <strong>Security Note:</strong> If the button above doesn't work, copy and paste this URL into your browser:<br>
                        <span style='color: $themeColor;'>$verificationLink</span>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " $lguName. All rights reserved.</p>
                    <p>Powered by Legislative CMS</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    return sendMail($userEmail, $subject, $message, $settings, true);
}

/**
 * Robust SendMail function supporting SMTP with Authentication and TLS/SSL
 */
function sendMail($to, $subject, $body, $settings = null, $isHighPriority = false)
{
    if (!$settings)
        $settings = getMailSettings();

    // If SMTP host is not set, fallback to PHP mail() - unlikely to work for modern SMTP but kept as safety
    if (empty($settings['host'])) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        if ($isHighPriority) {
            $headers .= "X-Priority: 1 (Highest)\r\n";
            $headers .= "Importance: High\r\n";
        }
        $headers .= "From: " . $settings['from_name'] . " <" . $settings['from_email'] . ">" . "\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    try {
        $host = $settings['host'];
        $port = $settings['port'];
        $user = $settings['user'];
        $pass = $settings['pass'];
        $encryption = strtolower($settings['encryption']);
        $fromEmail = $settings['from_email'];
        $fromName = $settings['from_name'];

        // If password looks like a base64 encrypted string (very rough check) 
        // and decryption works, use it. SecurityHelper::decrypt returns original if it fails.
        if (!empty($pass) && strlen($pass) > 40) {
            $pass = SecurityHelper::decrypt($pass);
        }

        // Add encryption prefix if port 465 (SSL)
        if ($encryption === 'ssl' && strpos($host, 'ssl://') === false) {
            $host = 'ssl://' . $host;
        }

        $socket = fsockopen($host, $port, $errno, $errstr, 15);
        if (!$socket)
            throw new Exception("Could not connect to SMTP host: $errstr ($errno)");

        $read = function ($socket) {
            $data = "";
            while ($str = fgets($socket, 1024)) {
                $data .= $str;
                if (substr($str, 3, 1) == " ")
                    break;
            }
            return $data;
        };

        $write = function ($socket, $cmd) use ($read) {
            fputs($socket, $cmd . "\r\n");
            return $read($socket);
        };

        $read($socket); // Catch the welcome message
        $write($socket, "EHLO " . $_SERVER['HTTP_HOST']);

        if ($encryption == 'tls') {
            $write($socket, "STARTTLS");
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Could not enable TLS encryption");
            }
            $write($socket, "EHLO " . $_SERVER['HTTP_HOST']); // Resend EHLO after TLS
        }

        if (!empty($user)) {
            $write($socket, "AUTH LOGIN");
            $write($socket, base64_encode($user));
            $write($socket, base64_encode($pass));
        }

        $write($socket, "MAIL FROM: <$fromEmail>");
        $write($socket, "RCPT TO: <$to>");
        $write($socket, "DATA");

        $headers = [
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=UTF-8",
            "From: \"$fromName\" <$fromEmail>",
            "To: <$to>",
            "Subject: $subject",
            "Date: " . date('r'),
            "X-Mailer: PHP/" . phpversion()
        ];

        if ($isHighPriority) {
            $headers[] = "X-Priority: 1 (Highest)";
            $headers[] = "X-MSMail-Priority: High";
            $headers[] = "Importance: High";
        }

        fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        $write($socket, "QUIT");
        fclose($socket);

        return true;
    } catch (Exception $e) {
        error_log("SMTPEmail Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if mail settings are being overridden by config/mail.php
 */
function isMailOverridden()
{
    $overridden = [];
    if (defined('SMTP_HOST') && !empty(SMTP_HOST))
        $overridden[] = 'Host';
    if (defined('SMTP_PORT') && !empty(SMTP_PORT))
        $overridden[] = 'Port';
    if (defined('SMTP_USER') && !empty(SMTP_USER))
        $overridden[] = 'Username';
    if (defined('SMTP_PASS') && !empty(SMTP_PASS))
        $overridden[] = 'Password';
    if (defined('SMTP_ENCRYPTION') && !empty(SMTP_ENCRYPTION))
        $overridden[] = 'Encryption';
    if (defined('MAIL_FROM_ADDRESS') && !empty(MAIL_FROM_ADDRESS) && MAIL_FROM_ADDRESS !== 'noreply@legislative-cms.gov')
        $overridden[] = 'From Email';
    if (defined('MAIL_FROM_NAME') && !empty(MAIL_FROM_NAME) && MAIL_FROM_NAME !== 'Legislative Services MS')
        $overridden[] = 'From Name';

    return $overridden;
}

/**
 * Get unified mail settings from File or Database
 */
function getMailSettings()
{
    $dbSettings = getSystemSettings();

    $pass = defined('SMTP_PASS') && !empty(SMTP_PASS) ? SMTP_PASS : ($dbSettings['smtp_pass'] ?? '');

    // Decrypt if it's from the database (not overridden by file)
    if (!defined('SMTP_PASS') || empty(SMTP_PASS)) {
        $pass = SecurityHelper::decrypt($pass);
    }

    return [
        'host' => defined('SMTP_HOST') && !empty(SMTP_HOST) ? SMTP_HOST : ($dbSettings['smtp_host'] ?? ''),
        'port' => defined('SMTP_PORT') && !empty(SMTP_PORT) ? SMTP_PORT : ($dbSettings['smtp_port'] ?? 587),
        'user' => defined('SMTP_USER') && !empty(SMTP_USER) ? SMTP_USER : ($dbSettings['smtp_user'] ?? ''),
        'pass' => $pass,
        'encryption' => defined('SMTP_ENCRYPTION') && !empty(SMTP_ENCRYPTION) ? SMTP_ENCRYPTION : ($dbSettings['smtp_encryption'] ?? 'tls'),
        'from_email' => defined('MAIL_FROM_ADDRESS') && !empty(MAIL_FROM_ADDRESS) ? MAIL_FROM_ADDRESS : ($dbSettings['lgu_email'] ?? 'noreply@legislative.gov'),
        'from_name' => defined('MAIL_FROM_NAME') && !empty(MAIL_FROM_NAME) ? MAIL_FROM_NAME : ($dbSettings['lgu_name'] ?? 'Legislative Services MS')
    ];
}
?>