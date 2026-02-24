<?php
/**
 * Mail Helper - Handles sending emails via SMTP or PHP Mail
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/SystemSettingsHelper.php';

// Try to include the optional mail config
if (file_exists(__DIR__ . '/../../config/mail.php')) {
    require_once __DIR__ . '/../../config/mail.php';
}

/**
 * Dynamically detect the base URL of the application
 */
function getDynamicBaseUrl()
{
    // 1. Use manual override if provided in config/mail.php
    if (defined('APP_URL') && !empty(APP_URL) && strpos(APP_URL, 'http') === 0) {
        return rtrim(APP_URL, '/');
    }

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Auto-detect URI path
    $uriPath = '';

    // Path to project root on the file system
    $projectRootDir = str_replace('\\', '/', realpath(__DIR__ . '/../../'));

    // Path to document root on the file system
    $docRoot = '';
    if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    }

    // Attempt 1: Using DOCUMENT_ROOT comparison
    if (!empty($docRoot) && stripos($projectRootDir, $docRoot) === 0) {
        $uriPath = substr($projectRootDir, strlen($docRoot));
    }

    // Attempt 2: Fallback to common folder names
    if (empty($uriPath) || $uriPath === '/') {
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || preg_match('/^\d+\.\d+\.\d+\.\d+$/', $host)) {
            if (stripos($projectRootDir, 'Capstone Project') !== false) {
                $uriPath = '/Capstone Project';
            }
        }
    }

    // Final normalization
    $uriPath = '/' . trim(str_replace('\\', '/', $uriPath), '/');
    if ($uriPath === '/')
        $uriPath = '';

    $baseUrl = $protocol . "://" . $host . $uriPath;

    return str_replace(' ', '%20', $baseUrl);
}

/**
 * Get logo info (raw data and mime type) for embedding
 */
function getLogoInfo()
{
    try {
        $systemSettings = getSystemSettings();
        $logoPath = $systemSettings['lgu_logo_path'] ?? 'assets/images/logo.png';
        $logoPath = str_replace('\\', '/', $logoPath);

        $cleanLogoPath = ltrim($logoPath, '/');
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));

        $potentialPaths = [
            $projectRoot . '/public/' . $cleanLogoPath,
            $projectRoot . '/' . $cleanLogoPath,
            $projectRoot . '/public/assets/images/logo.png',
        ];

        foreach ($potentialPaths as $path) {
            if (file_exists($path) && is_file($path)) {
                $data = @file_get_contents($path);
                if ($data !== false) {
                    // Magic byte detection for correct MIME type
                    $mime = 'image/png'; // default
                    if (strpos($data, "\x89PNG") === 0) {
                        $mime = 'image/png';
                    } elseif (strpos($data, "\xff\xd8\xff") === 0) {
                        $mime = 'image/jpeg';
                    } elseif (strpos($data, "RIFF") === 0 && strpos($data, "WEBP", 8) !== false) {
                        $mime = 'image/webp';
                    } elseif (strpos($data, "GIF") === 0) {
                        $mime = 'image/gif';
                    }

                    return [
                        'data' => $data,
                        'mime' => $mime,
                        'name' => basename($path)
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("MailHelper Error (Logo Info): " . $e->getMessage());
    }

    return null;
}

/**
 * Send a verification email to a new user
 */
function sendVerificationEmail($userEmail, $userName, $token)
{
    $settings = getMailSettings();
    $baseUrl = getDynamicBaseUrl();
    $verificationLink = $baseUrl . "/public/auth/verify.php?token=" . $token;

    $systemSettings = getSystemSettings();
    $themeColor = $systemSettings['theme_color'] ?? '#dc2626';
    $lguName = $systemSettings['lgu_name'] ?? 'Legislative Services MS';

    $logoInfo = getLogoInfo();
    $attachments = [];
    $lguLogoSrc = '';

    if ($logoInfo) {
        $attachments['lgu_logo'] = $logoInfo;
        $lguLogoSrc = 'cid:lgu_logo';
    }

    $subject = "Verify Your Account - " . $lguName;

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
            .logo-table { margin: 0 auto 20px; background-color: #ffffff; border-radius: 50%; }
            .logo-img { width: 60px; height: 60px; border-radius: 50%; display: block; border: none; }
            .header-text { margin: 0; font-size: 22px; font-weight: 800; line-height: 1.2; word-wrap: break-word; overflow-wrap: break-word; }
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
                    " . ($lguLogoSrc ? "
                    <table role='presentation' align='center' border='0' cellpadding='0' cellspacing='0' class='logo-table'>
                        <tr>
                            <td align='center' valign='middle' style='padding: 10px;'>
                                <img src='$lguLogoSrc' alt='Logo' class='logo-img'>
                            </td>
                        </tr>
                    </table>" : "") . "
                    <h2 class='header-text'>$lguName</h2>
                    <p style='margin:12px 0 0; opacity: 0.9; font-size: 13px;'>Legislative Services Committee Management System</p>
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

    return sendMail($userEmail, $subject, $message, $settings, true, $attachments);
}

/**
 * Send an OTP verification code email
 */
function sendOTPEmail($userEmail, $userName, $otp)
{
    $settings = getMailSettings();
    $baseUrl = getDynamicBaseUrl();

    // Get system branding
    $systemSettings = getSystemSettings();
    $themeColor = $systemSettings['theme_color'] ?? '#dc2626';
    $lguName = $systemSettings['lgu_name'] ?? 'Legislative Services MS';

    $logoInfo = getLogoInfo();
    $attachments = [];
    $lguLogoSrc = '';

    if ($logoInfo) {
        $attachments['lgu_logo'] = $logoInfo;
        $lguLogoSrc = 'cid:lgu_logo';
    }

    $subject = $otp . " is your verification code - " . $lguName;

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
            .logo-table { margin: 0 auto 20px; background-color: #ffffff; border-radius: 50%; }
            .logo-img { width: 60px; height: 60px; border-radius: 50%; display: block; border: none; }
            .header-text { margin: 0; font-size: 22px; font-weight: 800; line-height: 1.2; word-wrap: break-word; overflow-wrap: break-word; }
            .content { padding: 48px; text-align: center; }
            .h1 { font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
            .p { font-size: 16px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
            .otp-box { font-size: 36px; font-weight: 800; color: $themeColor; letter-spacing: 6px; padding: 24px; background-color: #f1f5f9; border-radius: 12px; border: 2px dashed #e2e8f0; display: inline-block; min-width: 200px; margin: 16px 0; }
            .footer { background-color: #f1f5f9; padding: 32px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='container'>
                <div class='header'>
                    " . ($lguLogoSrc ? "
                    <table role='presentation' align='center' border='0' cellpadding='0' cellspacing='0' class='logo-table'>
                        <tr>
                            <td align='center' valign='middle' style='padding: 10px;'>
                                <img src='$lguLogoSrc' alt='Logo' class='logo-img'>
                            </td>
                        </tr>
                    </table>" : "") . "
                    <h2 class='header-text'>$lguName</h2>
                    <p style='margin:12px 0 0; opacity: 0.9; font-size: 13px;'>Legislative Services Committee Management System</p>
                </div>
                <div class='content'>
                    <h1 class='h1'>Verify Your Identity</h1>
                    <p class='p'>Hello $userName, use the following code to complete your sign-in process.</p>
                    
                    <div class='otp-box'>$otp</div>
                    
                    <p class='p' style='margin-top: 24px; font-size: 14px;'>This code will expire in 5 minutes. If you did not request this code, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " $lguName. All rights reserved.</p>
                    <p>Powered by Legislative CMS</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    return sendMail($userEmail, $subject, $message, $settings, true, $attachments);
}

/**
 * Robust SendMail function supporting SMTP with multipart/related for inline images
 */
function sendMail($to, $subject, $body, $settings = null, $isHighPriority = false, $attachments = [])
{
    if (!$settings)
        $settings = getMailSettings();

    // If SMTP host is not set, fallback to PHP mail()
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

        if (!empty($pass) && strlen($pass) > 40) {
            $pass = SecurityHelper::decrypt($pass);
        }

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

        $read($socket);
        $write($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        if ($encryption == 'tls') {
            $write($socket, "STARTTLS");
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Could not enable TLS encryption");
            }
            $write($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }

        if (!empty($user)) {
            $write($socket, "AUTH LOGIN");
            $write($socket, base64_encode($user));
            $write($socket, base64_encode($pass));
        }

        $write($socket, "MAIL FROM: <$fromEmail>");
        $write($socket, "RCPT TO: <$to>");
        $write($socket, "DATA");

        // Construct multipart message if attachments exist
        if (empty($attachments)) {
            $headers = [
                "MIME-Version: 1.0",
                "Content-type: text/html; charset=UTF-8",
                "From: \"$fromName\" <$fromEmail>",
                "To: <$to>",
                "Subject: $subject",
                "Date: " . date('r'),
                "X-Mailer: PHP/" . phpversion()
            ];
            if ($isHighPriority)
                $headers[] = "Importance: High";

            fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        } else {
            $boundary = "PHP-mixed-" . md5(time());
            $headers = [
                "MIME-Version: 1.0",
                "Content-Type: multipart/related; boundary=\"$boundary\"",
                "From: \"$fromName\" <$fromEmail>",
                "To: <$to>",
                "Subject: $subject",
                "Date: " . date('r'),
                "X-Mailer: PHP/" . phpversion()
            ];
            if ($isHighPriority)
                $headers[] = "Importance: High";

            $messageBody = "--$boundary\r\n";
            $messageBody .= "Content-Type: text/html; charset=UTF-8\r\n";
            $messageBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $messageBody .= $body . "\r\n\r\n";

            foreach ($attachments as $cid => $attachment) {
                $messageBody .= "--$boundary\r\n";
                $messageBody .= "Content-Type: " . $attachment['mime'] . "; name=\"" . $attachment['name'] . "\"\r\n";
                $messageBody .= "Content-Transfer-Encoding: base64\r\n";
                $messageBody .= "Content-ID: <$cid>\r\n";
                $messageBody .= "Content-Disposition: inline; filename=\"" . $attachment['name'] . "\"\r\n\r\n";
                $messageBody .= chunk_split(base64_encode($attachment['data'])) . "\r\n";
            }
            $messageBody .= "--$boundary--\r\n";

            fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $messageBody . "\r\n.\r\n");
        }

        $write($socket, "QUIT");
        fclose($socket);

        return true;
    } catch (Exception $e) {
        error_log("SMTPEmail Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unified mail settings from File or Database
 */
function getMailSettings()
{
    $dbSettings = getSystemSettings();

    $pass = defined('SMTP_PASS') && !empty(SMTP_PASS) ? SMTP_PASS : ($dbSettings['smtp_pass'] ?? '');

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

/**
 * Check if mail settings are currently being overridden by config/mail.php
 * Returns an array of field labels that are overridden
 */
function isMailOverridden()
{
    $overrides = [];
    if (defined('SMTP_HOST') && !empty(SMTP_HOST))
        $overrides[] = 'SMTP Host';
    if (defined('SMTP_PORT') && !empty(SMTP_PORT))
        $overrides[] = 'Port';
    if (defined('SMTP_USER') && !empty(SMTP_USER))
        $overrides[] = 'Username';
    if (defined('SMTP_PASS') && !empty(SMTP_PASS))
        $overrides[] = 'Password';
    if (defined('SMTP_ENCRYPTION') && !empty(SMTP_ENCRYPTION))
        $overrides[] = 'Encryption';
    if (defined('MAIL_FROM_ADDRESS') && !empty(MAIL_FROM_ADDRESS))
        $overrides[] = 'From Email';
    if (defined('MAIL_FROM_NAME') && !empty(MAIL_FROM_NAME))
        $overrides[] = 'From Name';

    return $overrides;
}
?>