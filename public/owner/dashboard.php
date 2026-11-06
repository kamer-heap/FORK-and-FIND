<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);

// NEW read-only queries for the redesigned dashboard (counts + the 2 latest reviews).
$menuCount   = 0;
$reviewCount = 0;
$recent      = [];
if ($cafe) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Menu_Items WHERE cafe_id = :cafe');
    $stmt->execute([':cafe' => $cafe['cafe_id']]);
    $menuCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM Reviews r JOIN Menu_Items m ON m.item_id = r.item_id WHERE m.cafe_id = :cafe'
    );
    $stmt->execute([':cafe' => $cafe['cafe_id']]);
    $reviewCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT m.name AS item_name, r.rating, r.comment, r.date, u.name AS reviewer
         FROM Reviews r
         JOIN Menu_Items m ON m.item_id = r.item_id
         JOIN Users u ON u.user_id = r.user_id
         WHERE m.cafe_id = :cafe
         ORDER BY r.date DESC
         LIMIT 2'
    );
    $stmt->execute([':cafe' => $cafe['cafe_id']]);
    $recent = $stmt->fetchAll();
}

$pageTitle = 'Owner dashboard';
$mainClass = 'account owner-page';
$extraCss  = 'manage.css';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap ow-wrap">
    <div class="rf-intro">
        <p class="ow-eyebrow">OWNER WORKSPACE</p>
        <h1>Your Cafeteria</h1>
        <p class="ow-sub">A simple place to keep your cafeteria up to date.</p>
    </div>

<?php if (!$cafe): ?>
    <div class="mr-empty">
        <h2>No cafeteria yet</h2>
        <p>No cafeteria is assigned to your account yet. Ask an admin to assign one.</p>
    </div>
<?php else: ?>
    <section class="ow-overview">
        <div class="ow-identity">
            <h2 class="ow-cafe-name"><?= e($cafe['name']) ?></h2>
            <p class="ow-cafe-place"><?= e($cafe['department']) ?> &middot; <?= e($cafe['location']) ?></p>
            <p class="ow-cafe-time"><?= e($cafe['timings']) ?></p>
        </div>
        <a class="mr-btn" href="/owner/cafe_edit.php">Edit Cafeteria</a>
    </section>

    <div class="ow-stats">
        <div class="ow-stat">
            <p class="ow-stat-label">Menu items</p>
            <p class="ow-stat-num"><?= $menuCount ?></p>
        </div>
        <div class="ow-stat">
            <p class="ow-stat-label">Student reviews</p>
            <p class="ow-stat-num"><?= $reviewCount ?></p>
        </div>
    </div>

    <div class="ow-actions">
        <a class="mr-btn is-primary" href="/owner/menu.php">Manage Menu</a>
        <a class="mr-btn" href="/owner/reviews.php">View Reviews</a>
    </div>

    <section class="ow-section">
        <h2>Recent reviews</h2>
        <?php if (!$recent): ?>
            <div class="mr-empty">
                <h2>No reviews yet</h2>
                <p>Student reviews of your menu will show up here.</p>
            </div>
        <?php else: ?>
            <div class="ow-feed">
            <?php foreach ($recent as $rev): ?>
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
    </section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>