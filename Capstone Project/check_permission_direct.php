<?php
require_once 'app/helpers/PermissionHelper.php';
require_once 'app/helpers/UserHelper.php';

// Find a chairman user
$result = $conn->query("SELECT user_id, u.role_id FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name = 'Committee Chairman' LIMIT 1");
$row = $result->fetch_assoc();

if ($row) {
    $userId = $row['user_id'];
    echo "Testing User ID: $userId (Committee Chairman)\n";
    echo "canViewModule('users'): " . (canViewModule($userId, 'users') ? 'TRUE' : 'FALSE') . "\n";

    // Debug hasPermission
    $user = UserHelper_getUserById($userId);
    echo "Fetched Role Name: " . ($user['role_name'] ?? 'NONE') . "\n";

    $matrix = getPermissionMatrix();
    $roleName = $user['role_name'];
    echo "Matrix defined for role? " . (isset($matrix[$roleName]) ? 'YES' : 'NO') . "\n";
    if (isset($matrix[$roleName])) {
        echo "Users module in matrix? " . (isset($matrix[$roleName]['users']) ? 'YES' : 'NO') . "\n";
        echo "Permissions for users: " . json_encode($matrix[$roleName]['users']) . "\n";
    }
} else {
    echo "No Committee Chairman found in database.\n";
}
?>