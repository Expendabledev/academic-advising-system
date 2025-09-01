<?php
require_once __DIR__ . '/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: /swepgroup17/views/auth/login.php');
exit();