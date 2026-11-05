<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = intOrNull($_POST['review_id'] ?? null);
    if ($reviewId !== null) {
        // Hard delete (PENDING decision: hard vs soft delete).
        $stmt = $pdo->prepare('DELETE FROM Reviews WHERE review_id = :id');
        $stmt->execute([':id' => $reviewId]);
        setFlash('success', 'Review deleted.');
    } else {
        setFlash('error', 'Invalid request.');
    }
    redirect('/admin/reviews.php');
}

$reviews = $pdo->query(
    'SELECT r.review_id, r.rating, r.comment, r.date, u.name AS reviewer,
            m.name AS item_name, c.name AS cafe_name
     FROM Reviews r
     JOIN Users u ON u.user_id = r.user_id
     JOIN Menu_Items m ON m.item_id = r.item_id
     JOIN Cafeterias c ON c.cafe_id = m.cafe_id
     ORDER BY r.date DESC'
)->fetchAll();

$pageTitle = 'Moderate reviews';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Moderate reviews</h1>
<?php if (!$reviews): ?><p>No reviews yet.</p><?php endif; ?>
<?php foreach ($reviews as $rev): ?>
    <blockquote>
        <strong><?= e($rev['item_name']) ?></strong> @ <?= e($rev['cafe_name']) ?> - <?= (int) $rev['rating'] ?>/5
        by <?= e($rev['reviewer']) ?> <small><?= e($rev['date']) ?></small><br>
        <?= e($rev['comment']) ?>
        <form method="post" onsubmit="return confirm('Delete this review?');"><?= csrfField() ?>
            <input type="hidden" name="review_id" value="<?= (int) $rev['review_id'] ?>">
            <button type="submit" class="danger">Delete</button>
        </form>
    </blockquote>
<?php endforeach; ?>
<p><a href="/admin/dashboard.php">Back</a></p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
