<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$cafeId = intOrNull($_GET['id'] ?? null);
if ($cafeId === null) {
    http_response_code(404);
    exit('Cafeteria not found.');
}

$stmt = $pdo->prepare('SELECT * FROM Cafeterias WHERE cafe_id = :id');
$stmt->execute([':id' => $cafeId]);
$cafe = $stmt->fetch();
if (!$cafe) {
    http_response_code(404);
    exit('Cafeteria not found.');
}

$stmt = $pdo->prepare(
    'SELECT m.item_id, m.name, m.price, m.category, m.available, m.image,
            ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
     FROM Menu_Items m
     LEFT JOIN Reviews r ON r.item_id = m.item_id
     WHERE m.cafe_id = :id
     GROUP BY m.item_id
     ORDER BY m.category, m.name'
);                                                                    // CHANGED: m.image
$stmt->execute([':id' => $cafeId]);
$items = $stmt->fetchAll();

// Reviews for all items of this cafeteria, grouped by item in PHP.
$stmt = $pdo->prepare(
    'SELECT r.review_id, r.item_id, r.rating, r.comment, r.date, u.name AS reviewer, r.user_id
     FROM Reviews r
     JOIN Menu_Items m ON m.item_id = r.item_id
     JOIN Users u ON u.user_id = r.user_id
     WHERE m.cafe_id = :id
     ORDER BY r.date DESC'
);
$stmt->execute([':id' => $cafeId]);
$reviewsByItem = [];
foreach ($stmt->fetchAll() as $rev) {
    $reviewsByItem[$rev['item_id']][] = $rev;
}

$pageTitle = $cafe['name'];
$mainClass = 'wide';
require __DIR__ . '/../includes/header.php';

// UI only: names of the items, and one flat list of all reviews (newest first) for the "What students think" section.
$itemNames = [];
foreach ($items as $it) {
    $itemNames[$it['item_id']] = $it['name'];
}
$allReviews = [];
foreach ($reviewsByItem as $list) {
    foreach ($list as $rev) {
        $allReviews[] = $rev;
    }
}
usort($allReviews, function ($a, $b) {
    return strcmp((string) $b['date'], (string) $a['date']);
});
$isStudent = currentRole() === 'student';
?>
<nav class="crumbs"><a href="/index.php">Cafeterias</a> / <span><?= e($cafe['name']) ?></span></nav>

<img class="cafe-banner" src="<?= e(cafeImage($cafe)) ?>" alt="<?= e($cafe['name']) ?>">

<div class="cafe-head">
    <div>
        <h1 class="page-title"><?= e($cafe['name']) ?></h1>
        <p class="cafe-place"><?= e($cafe['department']) ?> &middot; <?= e($cafe['location']) ?></p>
    </div>
    <div class="hours">
        <span class="hours-label">Opening hours</span>
        <?php if (!empty($cafe['days'])): ?><span><?= e($cafe['days']) ?></span><?php endif; ?>
        <span><?= e($cafe['timings']) ?></span>
    </div>
</div>

<h2 class="section-title">What's on the menu?</h2>
<?php if (!$items): ?><div class="empty-state">This cafeteria has not added a menu yet.</div><?php endif; ?>
<div class="tiles two">
<?php foreach ($items as $item): ?>
    <article class="tile">
        <div class="tile-photo"><img src="<?= e(mealImage($item)) ?>" alt="<?= e($item['name']) ?>" loading="lazy"></div>
        <div class="tile-body">
            <h3><?= e($item['name']) ?></h3>
            <p class="muted"><?= e($item['category']) ?></p>
            <p class="price-row"><strong>PKR <?= e(number_format((float) $item['price'], 0)) ?></strong>
                <span><?= $item['avg_rating'] !== null ? '&#9733; ' . e((string) $item['avg_rating']) . ' (' . (int) $item['review_count'] . ')' : 'No reviews yet' ?></span></p>
            <div class="tile-actions">
                <a class="tile-link" href="#reviews"><?= (int) $item['available'] ? '' : 'Sold out today &middot; ' ?>View Reviews &rarr;</a>
                <?php if ($isStudent): ?><a class="tile-link" href="/review_form.php?item=<?= (int) $item['item_id'] ?>">Write a review &rarr;</a><?php endif; ?>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>

<div class="section-row" id="reviews">
    <h2 class="section-title">What students think</h2>
</div>
<?php if (!isLoggedIn()): ?>
    <p class="page-sub small"><a href="/login.php">Log in</a> as a student to share your own experience.</p>
<?php elseif ($isStudent): ?>
    <p class="page-sub small">Share your own experience: choose &ldquo;Write a review&rdquo; on a meal above.</p>
<?php endif; ?>

<?php if (!$allReviews): ?><div class="empty-state">No reviews yet.</div><?php endif; ?>
<?php foreach ($allReviews as $rev): ?>
    <article class="review-card">
        <strong><?= e($rev['reviewer']) ?></strong>
        <span class="review-item-name"><?= e($itemNames[$rev['item_id']] ?? '') ?></span>
        <span class="stars" aria-label="<?= (int) $rev['rating'] ?> out of 5"><?= str_repeat('&#9733;', (int) $rev['rating']) . str_repeat('&#9734;', 5 - (int) $rev['rating']) ?> <b><?= number_format((float) $rev['rating'], 1) ?></b></span>
        <?php if (trim((string) $rev['comment']) !== ''): ?><p class="review-text"><?= e($rev['comment']) ?></p><?php endif; ?>
        <small class="review-date"><?= e(date('j F Y', strtotime((string) $rev['date']))) ?></small>
    </article>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>