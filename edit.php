<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$article_id = (int) ($_POST['article_id'] ?? $_GET['id'] ?? 0);
if (!$article_id) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM article WHERE id = ?");
$stmt->bind_param('i', $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$article) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

$uid = current_user_id();
$can_edit = ($article['author_id'] == $uid) || is_admin();
if (!$can_edit) {
    header('Location: ' . BASE . '/detail.php?id=' . $article_id);
    exit;
}

$stmt = $mysqli->prepare("SELECT quantity FROM stock WHERE article_id = ?");
$stmt->bind_param('i', $article_id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
$stmt->close();
$stock = $s ? (int) $s['quantity'] : 0;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $stmt = $mysqli->prepare("DELETE FROM article WHERE id = ?");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . BASE . '/account.php');
        exit;
    }
    if ($action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = isset($_POST['price']) ? (float) str_replace(',', '.', $_POST['price']) : 0;
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $image_link = trim($_POST['image_link'] ?? '');
        if (!$name || !$description) {
            $error = 'Nom et description requis.';
        } elseif ($price <= 0) {
            $error = 'Le prix doit être strictement positif.';
        } elseif ($quantity < 0) {
            $error = 'La quantité ne peut pas être négative.';
        } else {
            $stmt = $mysqli->prepare("UPDATE article SET name = ?, description = ?, price = ?, image_link = ? WHERE id = ?");
            $stmt->bind_param('ssdsi', $name, $description, $price, $image_link, $article_id);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("UPDATE stock SET quantity = ? WHERE article_id = ?");
            $stmt->bind_param('ii', $quantity, $article_id);
            if ($stmt->execute()) {
                $stmt->close();
            } else {
                $stmt = $mysqli->prepare("INSERT INTO stock (article_id, quantity) VALUES (?, ?)");
                $stmt->bind_param('ii', $article_id, $quantity);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: ' . BASE . '/detail.php?id=' . $article_id);
            exit;
        }
    }
}

$page_title = 'Modifier';
require_once __DIR__ . '/includes/header.php';
?>
<div class="form-page">
    <h1>Modifier l’article</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="<?= BASE ?>/edit.php" class="form-card form-wide">
        <input type="hidden" name="article_id" value="<?= $article_id ?>">
        <input type="hidden" name="action" value="update">
        <label>Nom</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($article['name']) ?>">
        <label>Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($article['description']) ?></textarea>
        <label>Prix (€)</label>
        <input type="text" name="price" required value="<?= htmlspecialchars($article['price']) ?>">
        <label>Quantité en stock</label>
        <input type="number" name="quantity" min="0" value="<?= $stock ?>">
        <label>Lien image</label>
        <input type="url" name="image_link" value="<?= htmlspecialchars($article['image_link'] ?? '') ?>">
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="<?= BASE ?>/detail.php?id=<?= $article_id ?>" class="btn btn-outline">Annuler</a>
        </div>
    </form>
    <form method="post" action="<?= BASE ?>/edit.php" class="form-delete" onsubmit="return confirm('Supprimer définitivement cet article ?');">
        <input type="hidden" name="article_id" value="<?= $article_id ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger">Supprimer l’article</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
