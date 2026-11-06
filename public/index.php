<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// All cafeterias with their average item rating (unchanged query).
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

// NEW (read-only): the 3 best-rated meals, for the "Good meals" section.
$topMeals = $pdo->query(
    'SELECT m.item_id, m.name, m.price, c.cafe_id, c.name AS cafe_name,
            ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
     FROM Menu_Items m
     JOIN Cafeterias c ON c.cafe_id = m.cafe_id
     JOIN Reviews r ON r.item_id = m.item_id
     GROUP BY m.item_id, c.cafe_id
     ORDER BY avg_rating DESC, review_count DESC, m.price ASC
     LIMIT 3'
)->fetchAll();

// "Popular" badge goes to the best-rated cafeteria.
$popularId = null;
$best = 0;
foreach ($cafes as $c) {
    if ($c['avg_rating'] !== null && (float) $c['avg_rating'] > $best) {
        $best = (float) $c['avg_rating'];
        $popularId = (int) $c['cafe_id'];
    }
}

$pageTitle = 'Cafeterias';
$mainClass = 'home';
require __DIR__ . '/../includes/header.php';

$pin = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>';
$clock = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
?>
<section class="hero">
    <svg class="deco hero-hat" width="64" height="58" viewBox="0 0 54 50" fill="none" stroke="#a9c29b" stroke-width="1.8" aria-hidden="true"><path d="M14 30c-7-1-10-9-5-14 3-3 7-3 9-2 1-6 8-9 14-6 3 1 5 4 5 6 5-2 11 2 10 8-1 4-4 6-8 6v13H14z"/><path d="M14 38h28"/></svg><svg class="deco hero-leaf1" width="34" height="34" viewBox="0 0 24 24" fill="#c9dcb0" aria-hidden="true"><path d="M3 21C2 10 9 3 21 3c1 11-6 18-18 18z"/></svg><svg class="deco hero-leaf2" width="30" height="30" viewBox="0 0 24 24" fill="#c9dcb0" aria-hidden="true"><path d="M3 21C2 10 9 3 21 3c1 11-6 18-18 18z"/></svg>
    <div class="hero-text">
        <span class="pill"><svg width="20" height="16" viewBox="0 0 24 18" fill="currentColor" aria-hidden="true"><path d="M12 0 0 6l12 6 9-4.5V15h2V6zM5 10.5v4c0 1.7 3.1 3.5 7 3.5s7-1.8 7-3.5v-4L12 14z"/></svg>Made for campus life</span>
        <h1>Good food.<br>Near campus.</h1>
        <svg class="swoosh" width="270" height="22" viewBox="0 0 270 22" fill="none" aria-hidden="true"><path d="M3 16c40-9 100-12 150-8 20 1 40 3 55 6M170 8c25-2 55 0 70 6" stroke="#2d6a45" stroke-width="3" stroke-linecap="round"/></svg>
        <p class="lead">Discover campus meals, explore cafeterias, and find something delicious within your budget.</p>
        <form action="/search.php" method="get" class="hero-search">
            <svg class="s-ico" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
            <input type="text" name="q" placeholder="What are you craving?" maxlength="100" aria-label="Search a meal">
            <button type="submit">Find Meals</button>
        </form>
        <ul class="hero-points">
            <li><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6z"/></svg><div><strong>Quick Search</strong><span>Find food nearby</span></div></li>
            <li><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg><div><strong>Explore Cafeterias</strong><span>Discover options</span></div></li>
            <li><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3 6.5 7 .8-5.2 4.8 1.4 7L12 17.6 5.8 21l1.4-7L2 9.3l7-.8z"/></svg><div><strong>Read Reviews</strong><span>See what others say</span></div></li>
        </ul>
    </div>
    <img class="hero-img" src="/img/hero.png" alt="Chicken biryani with raita">
</section>

<section class="block">
    <div class="title-row"><h2 class="block-title">Explore Campus Cafeterias</h2><svg class="deco-hat" width="54" height="50" viewBox="0 0 54 50" fill="none" stroke="#9db98f" stroke-width="2" aria-hidden="true"><path d="M14 30c-7-1-10-9-5-14 3-3 7-3 9-2 1-6 8-9 14-6 3 1 5 4 5 6 5-2 11 2 10 8-1 4-4 6-8 6v13H14z"/><path d="M14 38h28"/></svg></div>
    <?php if (!$cafes): ?><p>No cafeterias yet.</p><?php endif; ?>
    <div class="tiles">
    <?php foreach ($cafes as $c): ?>
        <a class="tile" href="/cafeteria.php?id=<?= (int) $c['cafe_id'] ?>">
            <div class="tile-photo">
                <img src="<?= e(cafeImage($c)) ?>" alt="<?= e($c['name']) ?>" loading="lazy">
                <?php if ((int) $c['cafe_id'] === $popularId): ?><span class="badge">Popular</span><?php endif; ?>
            </div>
            <div class="tile-body">
                <h3><?= e($c['name']) ?></h3>
                <p><?= $pin ?> <?= e($c['department']) ?> | <?= e($c['location']) ?></p>
                <p><?= $clock ?> <?= e($c['timings']) ?></p>
                <p class="tile-meta"><?= (int) $c['item_count'] ?> items |
                    <?= $c['avg_rating'] !== null ? '&#9733; ' . e((string) $c['avg_rating']) : 'no reviews yet' ?></p>
                <span class="tile-link">View Menu &rarr;</span>
            </div>
        </a>
    <?php endforeach; ?>
    </div>
</section>

<?php if ($topMeals): ?>
<section class="block">
    <div class="title-row"><h2 class="block-title">Good meals. Student-friendly prices.</h2><svg class="deco-leaf" width="30" height="30" viewBox="0 0 24 24" fill="#c9dcb0" aria-hidden="true"><path d="M3 21C2 10 9 3 21 3c1 11-6 18-18 18z"/></svg></div>
    <p class="block-sub">Everyday favourites that fit your budget.</p>
    <div class="tiles">
    <?php foreach ($topMeals as $m): ?>
        <a class="tile" href="/cafeteria.php?id=<?= (int) $m['cafe_id'] ?>">
            <div class="tile-photo"><img src="<?= e(mealImage($m)) ?>" alt="<?= e($m['name']) ?>" loading="lazy"></div>
            <div class="tile-body">
                <h3><?= e($m['name']) ?></h3>
                <p class="muted"><?= e($m['cafe_name']) ?></p>
                <p class="price-row"><strong>PKR <?= e(number_format((float) $m['price'], 0)) ?></strong>
                    <span>&#9733; <?= e((string) $m['avg_rating']) ?> (<?= (int) $m['review_count'] ?>)</span></p>
                <span class="tile-link">View details &rarr;</span>
            </div>
        </a>
    <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
