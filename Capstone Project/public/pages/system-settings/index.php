<?php
require_once __DIR__ . '/../../../config/session_config.php';
// Redirect to User Management Settings tab
header('Location: ../user-management/index.php?tab=settings');
exit();
?>