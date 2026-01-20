<?php
require_once 'config/database.php';

$res = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

echo "# Database Table Audit\n\n";
foreach ($tables as $table) {
    echo "## Table: $table\n";
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        echo "| Field | Type | Null | Key | Default | Extra |\n";
        echo "|-------|------|------|-----|---------|-------|\n";
        while ($row = $result->fetch_assoc()) {
            echo "| " . $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . " |\n";
        }
    } else {
        echo "Error describing table $table\n";
    }
    echo "\n";
}
?>