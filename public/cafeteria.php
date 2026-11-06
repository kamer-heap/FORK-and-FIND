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
    'SELECT m.item_id, m.name, m.price, m.category, m.available,
            ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
     FROM Menu_Items m
     LEFT JOIN Reviews r ON r.item_id = m.item_id
     WHERE m.cafe_id = :id
     GROUP BY m.item_id
     ORDER BY m.category, m.name'
);
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
    <a class="tile" href="#reviews">
        <div class="tile-photo"><img src="<?= e(mealImage($item)) ?>" alt="<?= e($item['name']) ?>" loading="lazy"></div>
        <div class="tile-body">
            <h3><?= e($item['name']) ?></h3>
            <p class="muted"><?= e($item['category']) ?></p>
            <p class="price-row"><strong>PKR <?= e(number_format((float) $item['price'], 0)) ?></strong>
                <span><?= $item['avg_rating'] !== null ? '&#9733; ' . e((string) $item['avg_rating']) . ' (' . (int) $item['review_count'] . ')' : 'No reviews yet' ?></span></p>
            <span class="tile-link"><?= (int) $item['available'] ? '' : 'Sold out today &middot; ' ?>View Reviews &rarr;</span>
        </div>
    </a>
<?php endforeach; ?>
</div>

<div class="section-row" id="reviews">
    <h2 class="section-title">What students think</h2>
    <?php if ($isStudent && $items): ?>
        <button type="button" class="btn" id="add-review-btn">Add Review</button>
    <?php endif; ?>
</div>
<?php if (!isLoggedIn()): ?>
    <p class="page-sub small"><a href="/login.php">Log in</a> as a student to share your own experience.</p>
<?php elseif ($isStudent): ?>
    <p class="page-sub small">Share your own experience.</p>
<?php endif; ?>

<?php if ($isStudent && $items): ?>
<div class="review-panel" id="review-panel">
    <?php foreach ($items as $item): ?>
    <?php
        // The logged-in student's own review of this item (if any), to pre-fill the form.
        $mine = null;
        foreach ($reviewsByItem[$item['item_id']] ?? [] as $r) {
            if ((int) $r['user_id'] === (int) $_SESSION['user_id']) { $mine = $r; break; }
        }
    ?>
    <div class="review-item">
        <h3><?= e($item['name']) ?></h3>
        <form method="post" action="/review_add.php" class="inline-form"><?= csrfField() ?>
            <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
            <select name="rating" required>
                <option value="">Rating</option>
                <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= $mine && (int) $mine['rating'] === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?>
            </select>
            <input type="text" name="comment" placeholder="Your review (optional)" maxlength="500" value="<?= e($mine['comment'] ?? '') ?>">
            <button type="submit"><?= $mine ? 'Update my review' : 'Submit review' ?></button>
        </form>
        <?php if ($mine): ?>
        <form method="post" action="/review_delete.php" class="inline-form"><?= csrfField() ?>
            <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
            <button type="submit" class="danger">Delete my review</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
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

<script>
// UI only: the "Add Review" button shows/hides the review forms (they stay visible if JavaScript is off).
(function () {
    var btn = document.getElementById('add-review-btn'), panel = document.getElementById('review-panel');
    if (!btn || !panel) return;
    panel.hidden = true;
    btn.addEventListener('click', function () { panel.hidden = !panel.hidden; });
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>