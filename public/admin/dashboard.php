<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$counts = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM Users WHERE role = 'owner' AND is_approved = 0) AS pending_owners,
        (SELECT COUNT(*) FROM Cafeterias) AS cafes,
        (SELECT COUNT(*) FROM Menu_Items) AS items,
        (SELECT COUNT(*) FROM Reviews) AS reviews"
)->fetch();

$pageTitle = 'Admin dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Admin dashboard</h1>
<div class="grid">
    <div class="card"><h2><?= (int) $counts['pending_owners'] ?></h2><p>Owners waiting for approval</p><a class="btn" href="/admin/owners.php">Review owners</a></div>
    <div class="card"><h2><?= (int) $counts['cafes'] ?></h2><p>Cafeterias</p><a class="btn" href="/admin/cafeterias.php">Manage cafeterias</a></div>
    <div class="card"><h2><?= (int) $counts['reviews'] ?></h2><p>Reviews (<?= (int) $counts['items'] ?> menu items)</p><a class="btn" href="/admin/reviews.php">Moderate reviews</a></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
