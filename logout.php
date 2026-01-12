<?php
require_once __DIR__ . '/app_init.php';
require_once 'auth_functions.php';

logoutUser();
header('Location: login.php');
exit;
?>