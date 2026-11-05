<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);

$items = [];
if ($cafe) {
    $stmt = $pdo->prepare(
        'SELECT m.item_id, m.name, m.price, m.category, m.available,
                ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.review_id) AS review_count
         FROM Menu_Items m
         LEFT JOIN Reviews r ON r.item_id = m.item_id
         WHERE m.cafe_id = :cafe
         GROUP BY m.item_id
         ORDER BY m.category, m.name'
    );
    $stmt->execute([':cafe' => $cafe['cafe_id']]);
    $items = $stmt->fetchAll();
}

$pageTitle = 'Owner dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Owner dashboard</h1>

<?php if (!$cafe): ?>
    <div class="card"><p>No cafeteria is assigned to your account yet. Ask an admin to assign one.</p></div>
<?php else: ?>
    <div class="card">
        <h2><?= e($cafe['name']) ?></h2>
        <p><?= e($cafe['department']) ?> | <?= e($cafe['location']) ?> | Timings: <?= e($cafe['timings']) ?></p>
        <a class="btn" href="/owner/cafe_edit.php">Edit info and timings</a>
        <a class="btn" href="/owner/reviews.php">See my reviews</a>
    </div>

    <h2>Menu</h2>
    <table>
        <thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['name']) ?></td>
                <td><?= e($item['category']) ?></td>
                <td>Rs <?= e(number_format((float) $item['price'], 0)) ?></td>
                <td><?= $item['avg_rating'] !== null ? e((string) $item['avg_rating']) . ' (' . (int) $item['review_count'] . ')' : '-' ?></td>
                <td><?= (int) $item['available'] ? 'Available' : 'Sold out' ?></td>
                <td class="actions">
                    <form method="post" action="/owner/menu_toggle.php"><?= csrfField() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <button type="submit"><?= (int) $item['available'] ? 'Mark sold out' : 'Mark available' ?></button>
                    </form>
                    <a class="btn" href="/owner/menu_form.php?id=<?= (int) $item['item_id'] ?>">Edit</a>
                    <form method="post" action="/owner/menu_delete.php" onsubmit="return confirm('Delete this item and its reviews?');"><?= csrfField() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <button type="submit" class="danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$items): ?><p>No items yet.</p><?php endif; ?>
    <p><a class="btn" href="/owner/menu_form.php">+ Add menu item</a></p>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
