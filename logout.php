<?php
require_once __DIR__ . '/includes/init.php';
logout_user();
header('Location: ' . BASE . '/index.php');
exit;
