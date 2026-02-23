<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = 'La quantité en stock ne peut pas être négative.';
    } else {
        $uid = current_user_id();
        $stmt = $mysqli->prepare("INSERT INTO article (name, description, price, author_id, image_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdis', $name, $description, $price, $uid, $image_link);
        if ($stmt->execute()) {
            $aid = $mysqli->insert_id;
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO stock (article_id, quantity) VALUES (?, ?)");
            $stmt->bind_param('ii', $aid, $quantity);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . BASE . '/detail.php?id=' . $aid);
            exit;
        }
        $stmt->close();
        $error = 'Erreur lors de la création de l’article.';
    }
}

$page_title = 'Mettre en vente';
require_once __DIR__ . '/includes/header.php';
?>
<div class="form-page">
    <h1>Vendre un caillou</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="<?= BASE ?>/sell.php" class="form-card form-wide" enctype="multipart/form-data">
        <label>Nom du caillou</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        <label>Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        <label>Prix (€)</label>
        <input type="text" name="price" required placeholder="0.00" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
        <label>Quantité en stock</label>
        <input type="number" name="quantity" min="0" value="<?= (int)($_POST['quantity'] ?? 1) ?>">
        <label>Image du caillou (JPG, PNG, GIF, WebP - max 5 MB)</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <button type="submit" class="btn btn-primary">Vendre ce caillou</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
