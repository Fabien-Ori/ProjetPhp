<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();
$user = current_user();
$balance = (float) $user['balance'];

$result = $mysqli->query("
    SELECT c.article_id, c.quantity, a.name, a.price, a.author_id,
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

$error = '';
$success = '';

if (empty($items)) {
    header('Location: ' . BASE . '/cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billing_address = trim($_POST['billing_address'] ?? '');
    $billing_city = trim($_POST['billing_city'] ?? '');
    $billing_postal_code = trim($_POST['billing_postal_code'] ?? '');
    if (!$billing_address || !$billing_city || !$billing_postal_code) {
        $error = 'Veuillez remplir toutes les informations de facturation.';
    } elseif ($balance < $total) {
        $error = 'Solde insuffisant.';
    } else {
        $mysqli->begin_transaction();
        try {
            foreach ($items as $item) {
                $stock = (int) $item['stock'];
                if ($stock < (int) $item['quantity']) {
                    throw new Exception('Stock insuffisant pour : ' . $item['name']);
                }
            }
            $stmt = $mysqli->prepare("INSERT INTO invoice (user_id, amount, billing_address, billing_city, billing_postal_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('idsss', $uid, $total, $billing_address, $billing_city, $billing_postal_code);
            $stmt->execute();
            $invoice_id = $mysqli->insert_id;
            $stmt->close();

            foreach ($items as $item) {
                $stmt = $mysqli->prepare("INSERT INTO invoice_item (invoice_id, article_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiid', $invoice_id, $item['article_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                $stmt->close();
                $stmt = $mysqli->prepare("INSERT INTO purchase (user_id, article_id, invoice_id, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiii', $uid, $item['article_id'], $invoice_id, $item['quantity']);
                $stmt->execute();
                $stmt->close();
                $new_stock = (int) $item['stock'] - (int) $item['quantity'];
                $stmt = $mysqli->prepare("UPDATE stock SET quantity = ? WHERE article_id = ?");
                $stmt->bind_param('ii', $new_stock, $item['article_id']);
                $stmt->execute();
                $stmt->close();
                $seller_id = (int) $item['author_id'];
                if ($seller_id !== $uid) {
                    $seller_amount = $item['subtotal'];
                    $stmt = $mysqli->prepare("UPDATE user SET balance = balance + ? WHERE id = ?");
                    $stmt->bind_param('di', $seller_amount, $seller_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $stmt = $mysqli->prepare("UPDATE user SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param('di', $total, $uid);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $stmt->close();
            $mysqli->commit();
            $_SESSION['last_invoice_id'] = $invoice_id;
            header('Location: ' . BASE . '/account.php?invoice=1');
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = $e->getMessage();
        }
    }
}

$can_validate = $balance >= $total;
if (!$can_validate) {
    header('Location: ' . BASE . '/cart.php');
    exit;
}

$page_title = 'Valider la commande';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="form-page page-validate">
    <h1>Valider la commande</h1>
    <?php if ($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <p class="cart-summary">Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong> (Solde : <?= number_format($balance, 2, ',', ' ') ?> €)</p>
    <form method="post" action="<?= BASE ?>/cart/validate.php" class="form-card form-wide">
        <h2>Informations de facturation</h2>
        <label>Adresse</label>
        <input type="text" name="billing_address" required value="<?= htmlspecialchars($_POST['billing_address'] ?? '') ?>">
        <label>Ville</label>
        <input type="text" name="billing_city" required value="<?= htmlspecialchars($_POST['billing_city'] ?? '') ?>">
        <label>Code postal</label>
        <input type="text" name="billing_postal_code" required value="<?= htmlspecialchars($_POST['billing_postal_code'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Confirmer et payer</button>
    </form>
    <p><a href="<?= BASE ?>/cart.php">Retour au panier</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
