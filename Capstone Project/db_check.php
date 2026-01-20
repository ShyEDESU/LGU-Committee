<?php
require_once __DIR__ . '/config/database.php';

echo "Database Connection: " . ($conn ? "OK" : "FAILED") . "\n";

$tables = [
    'legislative_documents',
    'committees',
    'referrals',
    'meetings',
    'users'
];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    echo "Table '$table': " . ($result->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";
    if ($result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) FROM $table")->fetch_row()[0];
        echo "  - Records: $count\n";
    }
}
?>