<?php
session_start();
// Redirect to User Management Settings tab
header('Location: ../user-management/index.php?tab=settings');
exit();
?>
