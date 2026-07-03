<?php
/**
 * Email Configuration Override
 * 
 * Instructions:
 * - If you fill in these values, they will take PRIORITY over the website settings.
 * - If you leave them blank (''), the system will use the settings configured
 *   by the Administrator on the System Settings page of the website.
 * 
 * For Gmail:
 * - SMTP_HOST: smtp.gmail.com
 * - SMTP_PORT: 587 (TLS) or 465 (SSL)
 * - SMTP_USER: Your full Gmail address
 * - SMTP_PASS: Your 16-character "App Password" (NOT your regular password)
 * - SMTP_ENCRYPTION: 'tls' or 'ssl'
 */

// --- SMTP OVERRIDE SETTINGS ---
define('SMTP_HOST', '');       // e.g., 'smtp.gmail.com'
define('SMTP_PORT', '');       // e.g., 587
define('SMTP_USER', '');       // e.g., 'yourname@gmail.com'
define('SMTP_PASS', '');       // e.g., 'abcd efgh ijkl mnop'
define('SMTP_ENCRYPTION', ''); // e.g., 'tls'
// ------------------------------

/**
 * FROM SETTINGS
 * These define how the email appears in the recipient's inbox.
 */
define('MAIL_FROM_ADDRESS', '');
define('MAIL_FROM_NAME', '');

/**
 * APP BASE URL
 * Used to construct the verification links and logo URLs.
 * 
 * Set this to your actual domain (e.g., 'https://yourwebsite.com') if the
 * automatic detection in MailHelper.php doesn't work for your setup.
 * Otherwise, leave it as null or empty to enable automatic detection.
 */
define('APP_URL', '');
?>