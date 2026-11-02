<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);
if (!$cafe) {
    setFlash('error', 'No cafeteria is assigned to your account.');
    redirect('/owner/dashboard.php');
}

$stmt = $pdo->prepare(
    'SELECT m.name AS item_name, r.rating, r.comment, r.date, u.name AS reviewer
     FROM Reviews r
     JOIN Menu_Items m ON m.item_id = r.item_id
     JOIN Users u ON u.user_id = r.user_id
     WHERE m.cafe_id = :cafe
     ORDER BY r.date DESC'
);
$stmt->execute([':cafe' => $cafe['cafe_id']]);
$reviews = $stmt->fetchAll();

$pageTitle = 'My reviews';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Reviews for <?= e($cafe['name']) ?></h1>
<?php if (!$reviews): ?><p>No reviews yet.</p><?php endif; ?>
<?php foreach ($reviews as $rev): ?>
    <blockquote>
        <strong><?= e($rev['item_name']) ?></strong> - <?= (int) $rev['rating'] ?>/5
        by <?= e($rev['reviewer']) ?> <small><?= e($rev['date']) ?></small><br>
        <?= e($rev['comment']) ?>
    </blockquote>
<?php endforeach; ?>
<p><a href="/owner/dashboard.php">Back to dashboard</a></p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
