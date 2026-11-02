<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// All cafeterias with their average item rating.
$cafes = $pdo->query(
    'SELECT c.cafe_id, c.name, c.department, c.location, c.timings,
            COUNT(DISTINCT m.item_id) AS item_count,
            ROUND(AVG(r.rating), 1) AS avg_rating
     FROM Cafeterias c
     LEFT JOIN Menu_Items m ON m.cafe_id = c.cafe_id
     LEFT JOIN Reviews r ON r.item_id = m.item_id
     GROUP BY c.cafe_id
     ORDER BY c.name'
)->fetchAll();

$pageTitle = 'Cafeterias';
require __DIR__ . '/../includes/header.php';
?>
<h1>Campus cafeterias</h1>
<form action="/search.php" method="get" class="inline-form">
    <input type="text" name="q" placeholder="Search a meal, e.g. biryani" maxlength="100">
    <button type="submit">Search</button>
</form>
<?php if (!$cafes): ?>
    <p>No cafeterias yet.</p>
<?php endif; ?>
<div class="grid">
<?php foreach ($cafes as $c): ?>
    <div class="card">
        <h2><a href="/cafeteria.php?id=<?= (int) $c['cafe_id'] ?>"><?= e($c['name']) ?></a></h2>
        <p><?= e($c['department']) ?> | <?= e($c['location']) ?></p>
        <p>Timings: <?= e($c['timings']) ?></p>
        <p><?= (int) $c['item_count'] ?> items |
           Rating: <?= $c['avg_rating'] !== null ? e((string) $c['avg_rating']) . ' / 5' : 'no reviews yet' ?></p>
    </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
