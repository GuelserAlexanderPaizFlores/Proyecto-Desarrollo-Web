<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
logout();
header('Location: ' . url('/public/index.php'));
exit;
