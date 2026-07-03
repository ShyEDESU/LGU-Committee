<?php
require_once 'config/database.php';
$result = $conn->query('SELECT * FROM roles');
while ($row = $result->fetch_assoc()) {
    echo $row['role_id'] . ': ' . $row['role_name'] . PHP_EOL;
}
?>