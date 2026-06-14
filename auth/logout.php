<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
