<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE . '/admin/');
    exit;
}
$id = (int) ($_POST['article_id'] ?? 0);
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM article WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
header('Location: ' . BASE . '/admin/');
exit;
