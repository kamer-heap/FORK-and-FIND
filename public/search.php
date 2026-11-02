<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$q        = trim($_GET['q'] ?? '');
$maxPrice = $_GET['max_price'] ?? '';
$minRate  = $_GET['min_rating'] ?? '';

$sql = 'SELECT m.item_id, m.name AS item_name, m.price, m.available,
               c.cafe_id, c.name AS cafe_name, c.department,
               ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
        FROM Menu_Items m
        JOIN Cafeterias c ON c.cafe_id = m.cafe_id
        LEFT JOIN Reviews r ON r.item_id = m.item_id
        WHERE 1 = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND m.name LIKE :q';
    $params[':q'] = '%' . $q . '%';
}
if ($maxPrice !== '' && is_numeric($maxPrice) && $maxPrice >= 0) {
    $sql .= ' AND m.price <= :max_price';
    $params[':max_price'] = $maxPrice;
}

$sql .= ' GROUP BY m.item_id, c.cafe_id';

if ($minRate !== '' && ctype_digit($minRate) && $minRate >= 1 && $minRate <= 5) {
    $sql .= ' HAVING AVG(r.rating) >= :min_rating';
    $params[':min_rating'] = (int) $minRate;
}
$sql .= ' ORDER BY avg_rating DESC, m.price ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

$pageTitle = 'Search meals';
require __DIR__ . '/../includes/header.php';
?>
<h1>Search meals</h1>
<form method="get" class="inline-form">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Meal name, e.g. biryani" maxlength="100">
    <input type="number" name="max_price" value="<?= e((string) $maxPrice) ?>" placeholder="Max price (Rs)" min="0" step="1">
    <select name="min_rating">
        <option value="">Any rating</option>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>" <?= (string) $minRate === (string) $i ? 'selected' : '' ?>><?= $i ?>+ stars</option>
        <?php endfor; ?>
    </select>
    <button type="submit">Search</button>
</form>

<?php if (!$results): ?>
    <p>No matching meals found.</p>
<?php else: ?>
<table>
    <thead><tr><th>Meal</th><th>Cafeteria</th><th>Price</th><th>Rating</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($results as $row): ?>
        <tr>
            <td><?= e($row['item_name']) ?></td>
            <td><a href="/cafeteria.php?id=<?= (int) $row['cafe_id'] ?>"><?= e($row['cafe_name']) ?></a>
                <small>(<?= e($row['department']) ?>)</small></td>
            <td>Rs <?= e(number_format((float) $row['price'], 0)) ?></td>
            <td><?= $row['avg_rating'] !== null ? e((string) $row['avg_rating']) . ' (' . (int) $row['review_count'] . ')' : '-' ?></td>
            <td><?= (int) $row['available'] ? 'Available' : '<span class="muted">Sold out</span>' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
