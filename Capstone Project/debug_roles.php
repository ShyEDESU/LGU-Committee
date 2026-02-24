<?php
require_once __DIR__ . '/app/helpers/UserHelper.php';
$roles = UserHelper_getRoles();
echo json_encode($roles, JSON_PRETTY_PRINT);
