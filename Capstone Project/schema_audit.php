<?php
require_once 'config/database.php';

echo "--- Schema Audit ---\n";
$tables = ['legislative_documents', 'referrals', 'users', 'committees'];
foreach ($tables as $t) {
    echo "\nTable: $t\n";
    $res = $conn->query("SHOW CREATE TABLE $t");
    if ($res) {
        $row = $res->fetch_assoc();
        echo $row['Create Table'] . "\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "\n--- Data Audit ---\n";
echo "Users:\n";
$res = $conn->query("SELECT user_id, username FROM users");
while ($row = $res->fetch_assoc())
    echo "ID: {$row['user_id']}, Username: {$row['username']}\n";

echo "Committees:\n";
$res = $conn->query("SELECT committee_id, committee_name FROM committees");
while ($row = $res->fetch_assoc())
    echo "ID: {$row['committee_id']}, Name: {$row['committee_name']}\n";
