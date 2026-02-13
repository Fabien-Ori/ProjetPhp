<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$uid = current_user_id();

// Actions: update quantity, remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $article_id = (int) ($_POST['article_id'] ?? 0);
    if ($action === 'remove' && $article_id) {
        $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
        $stmt->bind_param('ii', $uid, $article_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update' && $article_id) {
        $qty = (int) ($_POST['quantity'] ?? 0);
        if ($qty < 1) {
            $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
            $stmt->bind_param('ii', $uid, $article_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $mysqli->prepare("SELECT quantity FROM stock WHERE article_id = ?");
            $stmt->bind_param('i', $article_id);
            $stmt->execute();
            $s = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $max = $s ? (int)$s['quantity'] : 999;
            if ($qty > $max) $qty = $max;
            $stmt = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND article_id = ?");
            $stmt->bind_param('iii', $qty, $uid, $article_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: ' . BASE . '/cart.php');
    exit;
}

$result = $mysqli->query("
    SELECT c.article_id, c.quantity, a.name, a.price, a.image_link,
           COALESCE(s.quantity, 0) AS stock
    FROM cart c
    JOIN article a ON a.id = c.article_id
    LEFT JOIN stock s ON s.article_id = a.id
    WHERE c.user_id = $uid
");
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$total = 0;
foreach ($items as &$row) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
}
unset($row);

$user = current_user();
$balance = (float) $user['balance'];
$can_validate = $total > 0 && $balance >= $total;

$page_title = 'Panier';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-cart">
    <h1>Panier</h1>
    <?php if (empty($items)): ?>
        <p class="empty">Votre panier est vide.</p>
        <p><a href="<?= BASE ?>/index.php" class="btn btn-primary">Voir les cailloux</a></p>
    <?php else: ?>
        <p class="cart-balance">Votre solde : <strong><?= number_format($balance, 2, ',', ' ') ?> €</strong></p>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image_link'])): ?>
                                <img src="<?= htmlspecialchars($item['image_link']) ?>" alt="" class="cart-thumb">
                            <?php endif; ?>
                            <a href="<?= BASE ?>/detail.php?id=<?= (int)$item['article_id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                        </td>
                        <td><?= number_format($item['price'], 2, ',', ' ') ?> €</td>
                        <td>
                            <form method="post" class="cart-qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="article_id" value="<?= (int)$item['article_id'] ?>">
                                <input type="number" name="quantity" min="1" max="<?= (int)$item['stock'] ?: 999 ?>" value="<?= (int)$item['quantity'] ?>">
                                <button type="submit">Mettre à jour</button>
                            </form>
                        </td>
                        <td><?= number_format($item['subtotal'], 2, ',', ' ') ?> €</td>
                        <td>
                            <form method="post" onsubmit="return confirm('Retirer du panier ?');">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="article_id" value="<?= (int)$item['article_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="cart-total">Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong></p>
        <?php if (!$can_validate && $total > 0): ?>
            <p class="message error">Solde insuffisant. Ajoutez de l’argent sur <a href="<?= BASE ?>/account.php">votre compte</a>.</p>
        <?php endif; ?>
        <form method="get" action="<?= BASE ?>/cart/validate.php">
            <button type="submit" class="btn btn-primary" <?= !$can_validate ? 'disabled' : '' ?>>Passer la commande</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
