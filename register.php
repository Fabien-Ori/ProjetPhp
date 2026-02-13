<?php
require_once __DIR__ . '/includes/init.php';

if (current_user_id()) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password_confirm'] ?? '';
    if (!$username || !$email || !$password) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (strlen($username) < 2) {
        $error = 'Le nom d’utilisateur doit faire au moins 2 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères.';
    } elseif ($password !== $password2) {
        $error = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $error = 'Ce nom d’utilisateur ou cette adresse email est déjà utilisé.';
        } else {
            $stmt->close();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $username, $email, $hash);
            if ($stmt->execute()) {
                $stmt->close();
                login_user($mysqli->insert_id);
                header('Location: ' . BASE . '/index.php');
                exit;
            }
            $stmt->close();
            $error = 'Erreur lors de la création du compte.';
        }
    }
}

$page_title = 'Inscription';
require_once __DIR__ . '/includes/header.php';
?>
<div class="form-page">
    <h1>Inscription</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="<?= BASE ?>/register.php" class="form-card">
        <label>Nom d’utilisateur</label>
        <input type="text" name="username" required minlength="2" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>Mot de passe</label>
        <input type="password" name="password" required minlength="6">
        <label>Confirmer le mot de passe</label>
        <input type="password" name="password_confirm" required minlength="6">
        <button type="submit" class="btn btn-primary">Créer mon compte</button>
    </form>
    <p class="form-footer">Déjà inscrit ? <a href="<?= BASE ?>/login.php">Se connecter</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
