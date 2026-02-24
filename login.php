<?php
require_once __DIR__ . '/includes/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        $error = 'Veuillez remplir l\'email et le mot de passe.';
    } else {
        $stmt = $mysqli->prepare("SELECT id, password FROM user WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row && password_verify($password, $row['password'])) {
            login_user($row['id']);
            header('Location: ' . BASE . '/index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

$page_title = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>
<div class="form-page">
    <h1>Connexion</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="<?= BASE ?>/login.php" class="form-card">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
    <p class="form-footer">Pas de compte ? <a href="<?= BASE ?>/register.php">S'inscrire</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
