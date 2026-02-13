<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Administration';
require_once __DIR__ . '/../includes/header.php';

$articles = [];
$result = $mysqli->query("
    SELECT a.id, a.name, a.price, a.publication_date, u.username
    FROM article a
    JOIN user u ON u.id = a.author_id
    ORDER BY a.publication_date DESC
");
if ($result) $articles = $result->fetch_all(MYSQLI_ASSOC);

$users = [];
$result = $mysqli->query("SELECT id, username, email, balance, role FROM user ORDER BY id");
if ($result) $users = $result->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-admin">
    <h1>Tableau de bord administrateur</h1>

    <section class="admin-section">
        <h2>Articles</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?= (int)$a['id'] ?></td>
                        <td><a href="<?= BASE ?>/detail.php?id=<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?></a></td>
                        <td><?= number_format($a['price'], 2, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($a['username']) ?></td>
                        <td><?= date('d/m/Y', strtotime($a['publication_date'])) ?></td>
                        <td>
                            <a href="<?= BASE ?>/edit.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
                            <form method="post" action="<?= BASE ?>/admin/delete_article.php" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                                <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="admin-section">
        <h2>Utilisateurs</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Solde</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><a href="<?= BASE ?>/account.php?user=<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['username']) ?></a></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= number_format($u['balance'], 2, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <a href="<?= BASE ?>/admin/edit_user.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
                            <?php if ((int)$u['id'] !== current_user_id()): ?>
                                <form method="post" action="<?= BASE ?>/admin/delete_user.php" style="display:inline;" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
