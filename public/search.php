<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$q        = trim($_GET['q'] ?? '');
$maxPrice = $_GET['max_price'] ?? '';
$minRate  = $_GET['min_rating'] ?? '';

$sql = 'SELECT m.item_id, m.name AS item_name, m.price, m.available, m.image,
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
$mainClass = 'wide';

require __DIR__ . '/../includes/header.php';

$total = count($results);
?>

<h1 class="page-title">Find your next meal</h1>
<p class="page-sub">Explore campus food that fits your taste and budget.</p>

<form method="get" class="filter-form">
    <div class="field field-search">
        <input
            type="text"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Search meals by name"
            maxlength="100"
            aria-label="Search meals by name"
        >
    </div>

    <label class="field">
        Maximum price (PKR)
        <input
            type="number"
            name="max_price"
            value="<?= e((string) $maxPrice) ?>"
            placeholder="Any price"
            min="0"
            step="1"
        >
    </label>

    <label class="field">
        Minimum star rating
        <select name="min_rating">
            <option value="">All ratings</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option
                    value="<?= $i ?>"
                    <?= (string) $minRate === (string) $i ? 'selected' : '' ?>
                >
                    <?= $i ?>+ stars
                </option>
            <?php endfor; ?>
        </select>
    </label>

    <button type="submit" class="visually-hidden">Search</button>
</form>

<p class="result-count">
    <?= $total ?> meal<?= $total === 1 ? '' : 's' ?> around campus
</p>

<?php if (!$results): ?>

    <div class="empty-state">No matching meals found.</div>

<?php else: ?>

    <div class="tiles three">
        <?php foreach ($results as $row): ?>
            <a
                class="tile"
                href="/item.php?id=<?= (int) $row['item_id'] ?>"
            >
                <div class="tile-photo">
                    <img
                        src="<?= e(mealImage($row)) ?>"
                        alt="<?= e($row['item_name']) ?>"
                        loading="lazy"
                    >

                    <?php if (!(int) $row['available']): ?>
                        <span class="badge badge-off">Sold out</span>
                    <?php endif; ?>
                </div>

                <div class="tile-body">
                    <h3><?= e($row['item_name']) ?></h3>

                    <p class="muted">
                        <?= e($row['cafe_name']) ?>
                    </p>

                    <p class="price-row">
                        <strong>
                            PKR <?= e(number_format((float) $row['price'], 0)) ?>
                        </strong>

                        <span>
                            <?php if ($row['avg_rating'] !== null): ?>
                                &#9733;
                                <?= e((string) $row['avg_rating']) ?>
                                (<?= (int) $row['review_count'] ?>)
                            <?php else: ?>
                                No reviews yet
                            <?php endif; ?>
                        </span>
                    </p>

                    <span class="tile-link">View Meal &rarr;</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<script>
document
    .querySelectorAll('.filter-form select, .filter-form input[type=number]')
    .forEach(function (element) {
        element.addEventListener('change', function () {
            element.form.submit();
        });
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>