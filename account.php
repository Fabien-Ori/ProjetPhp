<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$view_user_id = isset($_GET['user']) ? (int) $_GET['user'] : null;
$current_id = current_user_id();
if (!$view_user_id) $view_user_id = $current_id;

$stmt = $mysqli->prepare("SELECT id, username, email, balance, profile_photo, role FROM user WHERE id = ?");
$stmt->bind_param('i', $view_user_id);
$stmt->execute();
$view_user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$view_user) {
    header('Location: ' . BASE . '/account.php');
    exit;
}

$is_own = ($view_user_id === $current_id);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_info') {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } else {
            $stmt = $mysqli->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $email, $current_id);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                $stmt->close();
                $error = 'Cet email est déjà utilisé.';
            } else {
                $stmt->close();
                $stmt = $mysqli->prepare("UPDATE user SET email = ? WHERE id = ?");
                $stmt->bind_param('si', $email, $current_id);
                $stmt->execute();
                $stmt->close();
                $view_user['email'] = $email;
                $success = 'Email mis à jour.';
            }
        }
    } elseif ($action === 'update_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';
        $stmt = $mysqli->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->bind_param('i', $current_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || !password_verify($current, $row['password'])) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'Le nouveau mot de passe doit faire au moins 6 caractères.';
        } elseif ($new !== $confirm) {
            $error = 'Les deux mots de passe ne correspondent pas.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $current_id);
            $stmt->execute();
            $stmt->close();
            $success = 'Mot de passe mis à jour.';
        }
    } elseif ($action === 'add_balance') {
        $amount = (float) str_replace(',', '.', $_POST['amount'] ?? 0);
        if ($amount <= 0 || $amount > 10000) {
            $error = 'Montant invalide (entre 0.01 et 10000).';
        } else {
            $stmt = $mysqli->prepare("UPDATE user SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param('di', $amount, $current_id);
            $stmt->execute();
            $stmt->close();
            $view_user['balance'] = (float) $view_user['balance'] + $amount;
            $success = 'Solde mis à jour.';
        }
    }
}

$articles = [];
$result = $mysqli->query("SELECT id, name, price, image_link, publication_date FROM article WHERE author_id = $view_user_id ORDER BY publication_date DESC");
if ($result) $articles = $result->fetch_all(MYSQLI_ASSOC);

$invoices = [];
$purchases = [];
if ($is_own) {
    $result = $mysqli->query("SELECT id, transaction_date, amount FROM invoice WHERE user_id = $current_id ORDER BY transaction_date DESC");
    if ($result) $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $result = $mysqli->query("
        SELECT p.article_id, p.quantity, a.name, a.price, i.transaction_date
        FROM purchase p
        JOIN article a ON a.id = p.article_id
        JOIN invoice i ON i.id = p.invoice_id
        WHERE p.user_id = $current_id
        ORDER BY i.transaction_date DESC
    ");
    if ($result) $purchases = $result->fetch_all(MYSQLI_ASSOC);
}

$page_title = $is_own ? 'Mon compte' : 'Compte de ' . $view_user['username'];
require_once __DIR__ . '/includes/header.php';

if (isset($_GET['invoice'])) {
    $success = 'Commande validée. Merci !';
}
?>
<div class="page-account">
    <h1><?= $is_own ? 'Mon compte' : 'Compte de ' . htmlspecialchars($view_user['username']) ?></h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="message success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <section class="account-section">
        <h2>Informations</h2>
        <p><strong>Nom d’utilisateur :</strong> <?= htmlspecialchars($view_user['username']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($view_user['email']) ?></p>
        <p><strong>Solde :</strong> <?= number_format($view_user['balance'], 2, ',', ' ') ?> €</p>
        <?php if ($is_own): ?>
            <form method="post" class="form-inline">
                <input type="hidden" name="action" value="update_info">
                <label>Nouvel email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($view_user['email']) ?>" required>
                <button type="submit" class="btn btn-outline">Modifier l’email</button>
            </form>
            <form method="post" class="form-inline">
                <input type="hidden" name="action" value="update_password">
                <label>Mot de passe actuel</label>
                <input type="password" name="current_password" required>
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" required minlength="6">
                <label>Confirmer</label>
                <input type="password" name="new_password_confirm" required minlength="6">
                <button type="submit" class="btn btn-outline">Changer le mot de passe</button>
            </form>
            <form method="post" class="form-inline">
                <input type="hidden" name="action" value="add_balance">
                <label>Ajouter au solde (€)</label>
                <input type="text" name="amount" placeholder="10.00" required>
                <button type="submit" class="btn btn-primary">Créditer</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="account-section">
        <h2>Articles publiés</h2>
        <?php if (empty($articles)): ?>
            <p class="empty">Aucun article.</p>
        <?php else: ?>
            <div class="article-grid compact">
                <?php foreach ($articles as $a): ?>
                    <a href="<?= BASE ?>/detail.php?id=<?= (int)$a['id'] ?>" class="article-card">
                        <div class="article-card-img">
                            <?php if (!empty($a['image_link'])): ?>
                                <?php 
                                    $img_src = $a['image_link'];
                                    if (strpos($img_src, 'http') !== 0 && strpos($img_src, '/') !== 0) {
                                        $img_src = BASE . '/' . $img_src;
                                    }
                                ?>
                                <img src="<?= htmlspecialchars($img_src) ?>" alt="">
                            <?php else: ?>
                                <div class="article-card-placeholder">🪨</div>
                            <?php endif; ?>
                        </div>
                        <div class="article-card-body">
                            <h3><?= htmlspecialchars($a['name']) ?></h3>
                            <p class="price"><?= number_format($a['price'], 2, ',', ' ') ?> €</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($is_own): ?>
        <section class="account-section">
            <h2>Articles achetés</h2>
            <?php if (empty($purchases)): ?>
                <p class="empty">Aucun achat.</p>
            <?php else: ?>
                <ul class="purchase-list">
                    <?php foreach ($purchases as $p): ?>
                        <li>
                            <a href="/detail.php?id=<?= (int)$p['article_id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                            — <?= $p['quantity'] ?> × <?= number_format($p['price'], 2, ',', ' ') ?> €
                            — <?= date('d/m/Y H:i', strtotime($p['transaction_date'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
        <section class="account-section">
            <h2>Factures</h2>
            <?php if (empty($invoices)): ?>
                <p class="empty">Aucune facture.</p>
            <?php else: ?>
                <ul class="invoice-list">
                    <?php foreach ($invoices as $inv): ?>
                        <li>
                            Facture #<?= $inv['id'] ?> — <?= number_format($inv['amount'], 2, ',', ' ') ?> € — <?= date('d/m/Y H:i', strtotime($inv['transaction_date'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
