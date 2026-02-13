<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE . '/admin/');
    exit;
}
$id = (int) ($_POST['user_id'] ?? 0);
if ($id && $id !== current_user_id()) {
    $stmt = $mysqli->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
header('Location: ' . BASE . '/admin/');
exit;
