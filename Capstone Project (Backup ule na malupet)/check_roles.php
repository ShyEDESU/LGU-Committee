<?php
require_once 'config/database.php';
$result = $conn->query("SELECT role_name FROM roles");
while ($row = $result->fetch_assoc()) {
    echo $row['role_name'] . "\n";
}
?>