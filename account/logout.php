<?php
/**
 * Logout
 * خروج کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

logoutUser();

header('Location: login.php');
exit;
?>