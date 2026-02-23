<?php
require_once __DIR__ . '/includes/init.php';
// Detail is public
$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

$stmt = $mysqli->prepare("
    SELECT a.id, a.name, a.description, a.price, a.image_link, a.publication_date, a.author_id, u.username
    FROM article a
    JOIN user u ON u.id = a.author_id
    WHERE a.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$article) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

// Stock
$stmt = $mysqli->prepare("SELECT quantity FROM stock WHERE article_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stockRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$stock = $stockRow ? (int) $stockRow['quantity'] : 0;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_id()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_cart') {
        $qty = (int) ($_POST['quantity'] ?? 1);
        if ($qty < 1) $qty = 1;
        if ($stock > 0 && $qty > $stock) $qty = $stock;
        $uid = current_user_id();
        $stmt = $mysqli->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND article_id = ?");
        $stmt->bind_param('ii', $uid, $id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            if ($stock > 0 && $newQty > $stock) $newQty = $stock;
            $stmt = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param('ii', $newQty, $existing['id']);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $mysqli->prepare("INSERT INTO cart (user_id, article_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $uid, $id, $qty);
            $stmt->execute();
            $stmt->close();
        }
        $message = 'Ajouté au panier.';
    }
}

$page_title = $article['name'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-detail">
    <?php if ($message): ?><p class="message success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <div class="detail-layout">
        <div class="detail-media">
            <?php if (!empty($article['image_link'])): ?>
                <?php 
                    $img_src = $article['image_link'];
                    if (strpos($img_src, 'http') !== 0 && strpos($img_src, '/') !== 0) {
                        $img_src = BASE . '/' . $img_src;
                    }
                ?>
                <img src="<?= htmlspecialchars($img_src) ?>" alt="">
            <?php else: ?>
                <div class="detail-placeholder">🪨</div>
            <?php endif; ?>
        </div>
        <div class="detail-info">
            <h1><?= htmlspecialchars($article['name']) ?></h1>
            <p class="detail-price"><?= number_format($article['price'], 2, ',', ' ') ?> €</p>
            <p class="detail-meta">Publié par <a href="<?= BASE ?>/account.php?user=<?= (int)$article['author_id'] ?>"><?= htmlspecialchars($article['username']) ?></a></p>
            <?php if ($stock !== 0): ?>
                <p class="stock">En stock : <?= $stock ?></p>
            <?php else: ?>
                <p class="stock out">Rupture de stock</p>
            <?php endif; ?>
            <div class="detail-desc"><?= nl2br(htmlspecialchars($article['description'])) ?></div>
            <?php if (current_user_id()): ?>
                <?php if ($stock > 0): ?>
                    <form method="post" class="detail-cart-form">
                        <input type="hidden" name="action" value="add_cart">
                        <label>Quantité</label>
                        <input type="number" name="quantity" min="1" max="<?= $stock ?>" value="1">
                        <button type="submit" class="btn btn-primary">Ajouter au panier</button>
                    </form>
                <?php endif; ?>
                <?php
                $uid = current_user_id();
                $is_author = ($article['author_id'] == $uid) || is_admin();
                if ($is_author): ?>
                    <form method="post" action="<?= BASE ?>/edit.php" class="detail-edit-form">
                        <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                        <button type="submit" class="btn btn-outline">Modifier / Supprimer</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p><a href="<?= BASE ?>/login.php">Connectez-vous</a> pour ajouter ce caillou au panier.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
