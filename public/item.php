<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$itemId = intOrNull($_GET['id'] ?? null);

if ($itemId === null) {
    http_response_code(404);
    exit('Meal not found.');
}

$stmt = $pdo->prepare(
    'SELECT m.item_id, m.name, m.price, m.category, m.available, m.image,
            c.cafe_id, c.name AS cafe_name, c.department,
            c.location, c.timings,
            stats.avg_rating, stats.review_count
     FROM Menu_Items m
     JOIN Cafeterias c ON c.cafe_id = m.cafe_id
     LEFT JOIN (
         SELECT item_id,
                ROUND(AVG(rating), 1) AS avg_rating,
                COUNT(review_id) AS review_count
         FROM Reviews
         GROUP BY item_id
     ) stats ON stats.item_id = m.item_id
     WHERE m.item_id = :item_id'
);

$stmt->execute([':item_id' => $itemId]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Meal not found.');
}

$stmt = $pdo->prepare(
    'SELECT r.review_id, r.rating, r.comment, r.date,
            u.name AS reviewer, r.user_id
     FROM Reviews r
     JOIN Users u ON u.user_id = r.user_id
     WHERE r.item_id = :item_id
     ORDER BY r.date DESC'
);

$stmt->execute([':item_id' => $itemId]);
$reviews = $stmt->fetchAll();

$pageTitle = $item['name'];
$mainClass = 'wide';

require __DIR__ . '/../includes/header.php';

$isStudent = currentRole() === 'student';
?>

<nav class="crumbs">
    <a href="/index.php">Cafeterias</a> /
    <a href="/cafeteria.php?id=<?= (int) $item['cafe_id'] ?>">
        <?= e($item['cafe_name']) ?>
    </a> /
    <span><?= e($item['name']) ?></span>
</nav>

<article class="item-details">
    <img
        class="cafe-banner"
        src="<?= e(mealImage($item)) ?>"
        alt="<?= e($item['name']) ?>"
    >

    <h1 class="page-title"><?= e($item['name']) ?></h1>

    <p class="muted"><?= e($item['category']) ?></p>

    <p class="price-row">
        <strong>PKR <?= e(number_format((float) $item['price'], 0)) ?></strong>
    </p>

    <p>
        Cafeteria:
        <a href="/cafeteria.php?id=<?= (int) $item['cafe_id'] ?>">
            <?= e($item['cafe_name']) ?>
        </a>
    </p>

    <p>
        <?= e($item['department']) ?> &middot;
        <?= e($item['location']) ?>
    </p>

    <p>
        <?= (int) $item['available']
            ? 'Available today'
            : 'Sold out today' ?>
    </p>

    <p>
        <?php if ($item['avg_rating'] !== null): ?>
            &#9733; <?= e((string) $item['avg_rating']) ?>
            (<?= (int) $item['review_count'] ?> reviews)
        <?php else: ?>
            No reviews yet
        <?php endif; ?>
    </p>

    <?php if ($isStudent): ?>
        <a
            class="tile-link"
            href="/student/review_form.php?item=<?= (int) $item['item_id'] ?>"
        >
            Write a review &rarr;
        </a>
    <?php endif; ?>
</article>

<section id="reviews">
    <h2 class="section-title">Reviews for this meal</h2>

    <?php if (!$reviews): ?>
        <p class="empty-state">No reviews yet.</p>
    <?php endif; ?>

    <?php foreach ($reviews as $review): ?>
        <article class="review-card">
            <strong><?= e($review['reviewer']) ?></strong>

            <span class="stars">
                <?= str_repeat('&#9733;', (int) $review['rating']) ?>
                <?= str_repeat('&#9734;', 5 - (int) $review['rating']) ?>
            </span>

            <?php if (trim((string) $review['comment']) !== ''): ?>
                <p class="review-text"><?= e($review['comment']) ?></p>
            <?php endif; ?>

            <small class="review-date">
                <?= e(date('j F Y', strtotime((string) $review['date']))) ?>
            </small>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>