<?php
require_once 'config/database.php';
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
echo "\n--- Schema Details ---\n";
$tables = ['meetings', 'committees', 'users', 'agenda_items', 'votes', 'deliberations', 'agenda_comments', 'member_votes'];
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    }
}
