<?php
/**
 * System Audit & Flow Verification Script
 * This script checks for common errors in database connections, file paths, and theme consistency.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Audit Diagnostic</h1>";

// 1. Database Connection Check
echo "<h2>1. Database Connection</h2>";
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    if (isset($conn) && $conn->connect_error) {
        echo "<p style='color:red;'>FAIL: Database connection failed: " . $conn->connect_error . "</p>";
    } elseif (isset($conn)) {
        echo "<p style='color:green;'>SUCCESS: Database connected successfully.</p>";

        // Check core tables
        $tables = ['users', 'committees', 'meetings', 'legislative_documents', 'notifications', 'referrals', 'tasks'];
        echo "<ul>";
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<li>Table '$table' exists.</li>";
            } else {
                echo "<li style='color:red;'>Table '$table' MISSING!</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange;'>WARNING: Database file exists but \$conn variable not found.</p>";
    }
} else {
    echo "<p style='color:red;'>FAIL: config/database.php not found.</p>";
}

// 2. Navigation Link Verification
echo "<h2>2. Navigation Link Verification</h2>";
$headerFile = 'public/includes/header.php';
if (file_exists($headerFile)) {
    $content = file_get_contents($headerFile);
    preg_match_all('/href="([^"]+)"/', $content, $matches);
    $links = array_unique($matches[1]);

    echo "<ul>";
    foreach ($links as $link) {
        if ($link === '#' || strpos($link, 'javascript:') === 0 || strpos($link, 'http') === 0)
            continue;

        // Standardize path for check
        $checkPath = 'public/includes/' . $link;
        if (file_exists($checkPath)) {
            echo "<li>Link '$link' is VALID.</li>";
        } else {
            echo "<li style='color:red;'>Link '$link' is BROKEN! (Expected at: $checkPath)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>FAIL: public/includes/header.php not found.</p>";
}

// 3. Theme Consistency Check (Grep for legacy colors)
echo "<h2>3. Theme Consistency Check</h2>";
exec('grep -r "blue-" public/ | head -n 5', $outputBlue);
if (!empty($outputBlue)) {
    echo "<p style='color:orange;'>WARNING: Some blue theme elements might still exist (showing first 5):</p>";
    echo "<pre>" . implode("\n", $outputBlue) . "</pre>";
} else {
    echo "<p style='color:green;'>SUCCESS: No blue- classes found in public directory.</p>";
}

exec('grep -r "v-navy" public/ | grep -v "tail-config" | head -n 5', $outputNavy);
if (!empty($outputNavy)) {
    echo "<p style='color:orange;'>WARNING: Some v-navy elements might still exist:</p>";
    echo "<pre>" . implode("\n", $outputNavy) . "</pre>";
} else {
    echo "<p style='color:green;'>SUCCESS: No v-navy classes found excluding Tailwind config.</p>";
}

echo "<h2>Audit Complete</h2>";
?>