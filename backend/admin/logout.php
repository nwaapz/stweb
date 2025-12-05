<?php
/**
 * Logout
 * خروج از سیستم
 */

session_start();
session_destroy();
header('Location: login.php');
exit;
