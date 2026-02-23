<?php
require_once __DIR__ . '/includes/init.php';
// Home: public, no require_login
$page_title = 'Accueil';

// Optional sort
$sort = $_GET['sort'] ?? 'recent';
$order = "a.publication_date DESC";
if ($sort === 'price_asc') $order = "a.price ASC";
if ($sort === 'price_desc') $order = "a.price DESC";
if ($sort === 'name') $order = "a.name ASC";

$result = $mysqli->query("
    SELECT a.id, a.name, a.price, a.image_link, a.publication_date, a.author_id, u.username
    FROM article a
    JOIN user u ON u.id = a.author_id
    ORDER BY $order
");
$articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-home">
    <h1>Nos cailloux</h1>
    <p class="lead">Des pierres. De tailles et formes différentes. C'est tout. (Non, vraiment, c'est juste des cailloux.)</p>
    <div class="toolbar">
        <span>Trier :</span>
        <a href="<?= BASE ?>/index.php?sort=recent" class="<?= $sort === 'recent' ? 'active' : '' ?>">Plus récents</a>
        <a href="<?= BASE ?>/index.php?sort=price_asc" class="<?= $sort === 'price_asc' ? 'active' : '' ?>">Prix croissant</a>
        <a href="<?= BASE ?>/index.php?sort=price_desc" class="<?= $sort === 'price_desc' ? 'active' : '' ?>">Prix décroissant</a>
        <a href="<?= BASE ?>/index.php?sort=name" class="<?= $sort === 'name' ? 'active' : '' ?>">Nom</a>
    </div>
    <div class="article-grid">
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
                    <p class="meta">par <?= htmlspecialchars($a['username']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($articles)): ?>
        <p class="empty">Aucun caillou en vente pour le moment.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
