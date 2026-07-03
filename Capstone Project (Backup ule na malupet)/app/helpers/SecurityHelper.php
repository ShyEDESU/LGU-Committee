<?php
/**
 * SecurityHelper.php
 * Provides encryption and decryption utilities for sensitive data.
 */

class SecurityHelper
{

    /**
     * Reversible AES-256 encryption
     */
    public static function encrypt($data)
    {
        if (empty($data))
            return $data;

        if (!function_exists('openssl_encrypt')) {
            throw new Exception("Encryption Error: OpenSSL PHP extension is not enabled on this server.");
        }

        $key = defined('SECURITY_KEY') ? SECURITY_KEY : 'default-secret-key-change-me';
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $ciphertext_raw = openssl_encrypt($data, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        return base64_encode($iv . $hmac . $ciphertext_raw);
    }

    /**
     * Reversible AES-256 decryption
     */
    public static function decrypt($ciphertext)
    {
        if (empty($ciphertext))
            return $ciphertext;

        try {
            $key = defined('SECURITY_KEY') ? SECURITY_KEY : 'default-secret-key-change-me';
            $c = base64_decode($ciphertext);
            $cipher = "AES-256-CBC";
            $ivlen = openssl_cipher_iv_length($cipher);

            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);

            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }
        } catch (Exception $e) {
            return $ciphertext; // Return as is if decryption fails
        }

        return $ciphertext;
    }
}
?>