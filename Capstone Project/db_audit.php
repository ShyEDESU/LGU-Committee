<?php
require_once 'config/database.php';

$tables = ['meetings', 'attendance_records', 'referrals', 'tasks', 'legislative_documents'];
echo "--- Table Counts ---\n";
foreach ($tables as $t) {
    $res = $conn->query("SELECT COUNT(*) as c FROM $t");
    echo "$t: " . ($res ? $res->fetch_assoc()['c'] : 'ERROR') . "\n";
}

echo "\n--- Referrals by Status ---\n";
$res = $conn->query("SELECT status, COUNT(*) as c FROM referrals GROUP BY status");
if ($res)
    while ($row = $res->fetch_assoc())
        echo $row['status'] . ": " . $row['c'] . "\n";

echo "\n--- Tasks by Status ---\n";
$res = $conn->query("SELECT status, COUNT(*) as c FROM tasks GROUP BY status");
if ($res)
    while ($row = $res->fetch_assoc())
        echo $row['status'] . ": " . $row['c'] . "\n";

echo "\n--- Attendance by Status ---\n";
$res = $conn->query("SELECT status, COUNT(*) as c FROM attendance_records GROUP BY status");
if ($res)
    while ($row = $res->fetch_assoc())
        echo $row['status'] . ": " . $row['c'] . "\n";

echo "\n--- Sample Meeting Date ---\n";
$res = $conn->query("SELECT meeting_date FROM meetings LIMIT 1");
if ($res)
    echo "Date: " . ($res->fetch_assoc()['meeting_date'] ?? 'None') . "\n";
