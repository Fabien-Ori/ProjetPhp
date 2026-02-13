<?php
if (!defined('SITE_NAME')) define('SITE_NAME', 'Cailloux & Cie');
if (!defined('BASE')) define('BASE', '');
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= BASE ?>/index.php" class="logo"><?= SITE_NAME ?></a>
        <nav class="nav-main">
            <a href="<?= BASE ?>/index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Accueil</a>
            <?php if ($user): ?>
                <a href="<?= BASE ?>/sell.php" class="<?= $current_page === 'sell' ? 'active' : '' ?>">Vendre</a>
                <a href="<?= BASE ?>/cart.php" class="<?= $current_page === 'cart' ? 'active' : '' ?>">Panier</a>
                <a href="<?= BASE ?>/account.php" class="<?= $current_page === 'account' ? 'active' : '' ?>">Mon compte</a>
                <?php if (is_admin()): ?>
                    <a href="<?= BASE ?>/admin/">Admin</a>
                <?php endif; ?>
                <span class="user-balance"><?= number_format($user['balance'], 2, ',', ' ') ?> €</span>
                <a href="<?= BASE ?>/logout.php" class="btn btn-outline">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE ?>/login.php" class="<?= $current_page === 'login' ? 'active' : '' ?>">Connexion</a>
                <a href="<?= BASE ?>/register.php" class="<?= $current_page === 'register' ? 'active' : '' ?>">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content">
