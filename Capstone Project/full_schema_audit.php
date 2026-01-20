<?php
require_once 'config/database.php';

$tables = [
    'committees',
    'committee_members',
    'meetings',
    'meeting_invitations',
    'attendance_records',
    'meeting_documents',
    'legislative_documents',
    'audit_logs'
];

echo "| Table | Field | Type | Null | Key | Default | Extra |\n";
echo "|-------|-------|------|------|-----|---------|-------|\n";

foreach ($tables as $table) {
    try {
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "| $table | " . $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . " |\n";
            }
        } else {
            echo "| $table | ERROR: Table not found or access denied | | | | | |\n";
        }
    } catch (Exception $e) {
        echo "| $table | EXCEPTION: " . $e->getMessage() . " | | | | | |\n";
    }
}
?>