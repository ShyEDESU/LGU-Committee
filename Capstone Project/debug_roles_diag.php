<?php
require_once 'config/database.php';
$result = $conn->query("SELECT role_name FROM roles");
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row['role_name'];
}
file_put_contents('debug_roles.txt', implode("\n", $roles));
echo "Done";
?>