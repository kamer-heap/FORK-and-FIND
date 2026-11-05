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
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($cafe['name']) ?></h1>
<p><?= e($cafe['department']) ?> | <?= e($cafe['location']) ?> | Timings: <?= e($cafe['timings']) ?></p>

<?php if (!$items): ?><p>This cafeteria has not added a menu yet.</p><?php endif; ?>

<?php foreach ($items as $item): ?>
<div class="card">
    <h3><?= e($item['name']) ?> <small>(<?= e($item['category']) ?>)</small></h3>
    <p>Rs <?= e(number_format((float) $item['price'], 0)) ?> |
       <?= (int) $item['available'] ? 'Available' : '<span class="muted">Sold out</span>' ?> |
       Rating: <?= $item['avg_rating'] !== null ? e((string) $item['avg_rating']) . ' / 5 (' . (int) $item['review_count'] . ' reviews)' : 'no reviews yet' ?></p>

    <?php foreach ($reviewsByItem[$item['item_id']] ?? [] as $rev): ?>
        <blockquote>
            <strong><?= e($rev['reviewer']) ?></strong> - <?= (int) $rev['rating'] ?>/5
            <small><?= e($rev['date']) ?></small><br>
            <?= e($rev['comment']) ?>
        </blockquote>
    <?php endforeach; ?>

    <?php if (currentRole() === 'student'): ?>
    <form method="post" action="/review_add.php" class="inline-form"><?= csrfField() ?>
        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
        <select name="rating" required>
            <option value="">Rating</option>
            <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?>
        </select>
        <input type="text" name="comment" placeholder="Your review (optional)" maxlength="500">
        <button type="submit">Submit review</button>
    </form>
    <?php elseif (!isLoggedIn()): ?>
        <p><small><a href="/login.php">Log in</a> as a student to review.</small></p>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
