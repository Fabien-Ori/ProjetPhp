<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$error = '';
$success = '';

// Répertoire d'upload
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Configuration des uploads
$max_upload_size = 5 * 1024 * 1024; // 5 MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float) str_replace(',', '.', $_POST['price']) : 0;
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $image_link = '';
    
    if (!$name || !$description) {
        $error = 'Nom et description requis.';
    } elseif ($price <= 0) {
        $error = 'Le prix doit être strictement positif.';
    } elseif ($quantity < 0) {
        $error = 'La quantité en stock ne peut pas être négative.';
    } else {
        // Traitement de l'image
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Erreur lors de l\'upload: ' . $file['error'];
            } elseif ($file['size'] > $max_upload_size) {
                $error = 'L\'image est trop volumineux (max ' . ($max_upload_size / 1024 / 1024) . ' MB).';
            } else {
                // Vérifier le type MIME
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mime, $allowed_mimes)) {
                    $error = 'Type de fichier non autorisé. Acceptés: JPG, PNG, GIF, WebP.';
                } else {
                    // Générer un nom sécurisé
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed_extensions)) {
                        $error = 'Extension non autorisée.';
                    } else {
                        $filename = 'art_' . uniqid() . '.' . $ext;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $image_link = 'uploads/' . $filename;
                        } else {
                            $error = 'Erreur lors de la sauvegarde de l\'image.';
                        }
                    }
                }
            }
        }
        
        if (!$error) {
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
            $error = 'Erreur lors de la création de l\'article.';
        }
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
