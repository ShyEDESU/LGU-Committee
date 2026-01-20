<?php
require_once 'config/database.php';
$tables = ['attendance_records', 'meeting_documents', 'meeting_invitations', 'agenda_distribution', 'committee_members'];
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    }
}
