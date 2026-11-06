<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);
if (!$cafe) {
    setFlash('error', 'No cafeteria is assigned to your account.');
    redirect('/owner/dashboard.php');
}

// Same query that used to live on the dashboard (moved here, now also reading the photo).
$stmt = $pdo->prepare(
    'SELECT m.item_id, m.name, m.price, m.category, m.available, m.image,
            ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
     FROM Menu_Items m
     LEFT JOIN Reviews r ON r.item_id = m.item_id
     WHERE m.cafe_id = :cafe
     GROUP BY m.item_id
     ORDER BY m.category, m.name'
);
$stmt->execute([':cafe' => $cafe['cafe_id']]);
$items = $stmt->fetchAll();

$pageTitle = 'Manage menu';
$mainClass = 'account owner-page';
$extraCss  = 'manage.css';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap ow-wrap">
    <div class="rf-intro">
        <p class="ow-eyebrow">OWNER WORKSPACE</p>
        <h1>Manage Menu</h1>
        <p class="ow-sub"><?= e($cafe['name']) ?> &middot; <?= count($items) ?> menu item<?= count($items) === 1 ? '' : 's' ?></p>
        <a class="mr-btn is-primary" href="/owner/menu_form.php">Add Menu Item</a>
    </div>

    <section class="ow-section">
        <p class="ow-label">Your campus menu</p>
        <div class="om-table">
            <?php if (!$items): ?>
                <p class="om-empty">No items yet. Use "Add Menu Item" to create your first one.</p>
            <?php else: ?>
                <div class="om-head">
                    <span>Menu item</span><span>Price</span><span>Rating</span><span>Availability</span><span>Actions</span>
                </div>
                <?php foreach ($items as $item): ?>
                    <?php
                    $priceText  = 'PKR ' . number_format((float) $item['price'], 0);
                    $ratingText = $item['avg_rating'] !== null ? '★ ' . number_format((float) $item['avg_rating'], 1) : '';
                    ?>
                    <div class="om-row">
                        <div class="om-meal">
                            <img class="om-photo" src="<?= e(mealImage($item)) ?>" alt="">
                            <div class="om-name-wrap">
                                <span class="om-name"><?= e($item['name']) ?></span>
                                <span class="om-sub"><?= e($priceText) ?><?= $ratingText !== '' ? '&nbsp;&nbsp;&nbsp; ' . e($ratingText) : '' ?></span>
                            </div>
                        </div>
                        <div class="om-price"><?= e($priceText) ?></div>
                        <div class="om-rating"><?= $ratingText !== '' ? e($ratingText) : '–' ?></div>
                        <div class="om-avail">
                            <?php if ((int) $item['available']): ?>
                                <span class="om-badge is-on">Available</span>
                            <?php else: ?>
                                <span class="om-badge is-off">Sold out</span>
                            <?php endif; ?>
                        </div>
                        <div class="om-actions">
                            <div class="om-links">
                                <a class="ow-link" href="/owner/menu_form.php?id=<?= (int) $item['item_id'] ?>">Edit</a>
                                <span aria-hidden="true">&middot;</span>
                                <form method="post" action="/owner/menu_delete.php" onsubmit="return confirm('Delete this item and its reviews?');"><?= csrfField() ?>
                                    <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                    <button type="submit" class="ow-link">Delete</button>
                                </form>
                            </div>
                            <form method="post" action="/owner/menu_toggle.php"><?= csrfField() ?>
                                <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                <button type="submit" class="mr-btn"><?= (int) $item['available'] ? 'Mark sold out' : 'Mark available' ?></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>