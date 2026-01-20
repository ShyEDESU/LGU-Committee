<?php
require_once 'config/database.php';
$tables = ['votes', 'deliberations', 'agenda_comments', 'member_votes', 'attendance', 'meeting_documents', 'meeting_minutes', 'meeting_invitees'];
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "Error: Table $table not found\n";
    }
}
