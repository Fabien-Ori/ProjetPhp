<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE . '/admin/');
    exit;
}

$stmt = $mysqli->prepare("SELECT id, username, email, balance, role FROM user WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    header('Location: ' . BASE . '/admin/');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $balance = (float) str_replace(',', '.', $_POST['balance'] ?? 0);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    if (!$username || !$email) {
        $error = 'Username et email requis.';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param('ssi', $username, $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $error = 'Username ou email déjà utilisé.';
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("UPDATE user SET username = ?, email = ?, balance = ?, role = ? WHERE id = ?");
            $stmt->bind_param('ssdsi', $username, $email, $balance, $role, $id);
            $stmt->execute();
            $stmt->close();
            $user = ['id' => $id, 'username' => $username, 'email' => $email, 'balance' => $balance, 'role' => $role];
            header('Location: ' . BASE . '/admin/');
            exit;
        }
    }
}

$page_title = 'Modifier utilisateur';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="form-page">
    <h1>Modifier l’utilisateur</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="<?= BASE ?>/admin/edit_user.php" class="form-card">
        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
        <label>Nom d’utilisateur</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
        <label>Solde (€)</label>
        <input type="text" name="balance" value="<?= htmlspecialchars($user['balance']) ?>">
        <label>Rôle</label>
        <select name="role">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= BASE ?>/admin/" class="btn btn-outline">Retour</a>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
