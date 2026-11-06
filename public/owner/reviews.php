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

$pageTitle = 'Student reviews';
$mainClass = 'account owner-page';
$extraCss  = 'manage.css';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap ow-wrap">
    <div class="rf-intro">
        <p class="ow-eyebrow">OWNER WORKSPACE</p>
        <h1>Student Reviews</h1>
        <p class="ow-sub">See what students are saying about your menu.</p>
    </div>

    <div class="ow-summary">
        <p class="ow-summary-main"><?= e($cafe['name']) ?> &middot; <?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></p>
        <p class="ow-summary-note">Reviews are shared by students. Only administrators can moderate them.</p>
    </div>

    <?php if (!$reviews): ?>
        <div class="mr-empty">
            <h2>No reviews yet</h2>
            <p>Student reviews of your menu will show up here.</p>
        </div>
    <?php else: ?>
        <div class="mr-list">
        <?php foreach ($reviews as $rev): ?>
            <?php
            $rating = max(0, min(5, (int) $rev['rating']));
            $when   = strtotime((string) $rev['date']);
            ?>
            <article class="mr-card ow-card">
                <p class="mr-by"><?= e($rev['reviewer']) ?></p>
                <p class="mr-meal"><?= e($rev['item_name']) ?></p>
                <p class="mr-stars" aria-label="<?= $rating ?> out of 5 stars"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?>&nbsp;&nbsp;<?= number_format($rating, 1) ?></p>
                <?php if (trim((string) $rev['comment']) !== ''): ?>
                    <p class="mr-text"><?= e($rev['comment']) ?></p>
                <?php endif; ?>
                <?php if ($when): ?>
                    <p class="mr-date"><?= e(date('j F Y', $when)) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>