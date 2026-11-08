<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('student');                          // only students have "my reviews"

// NEW read-only query: all reviews written by the logged-in student, newest first.
$stmt = $pdo->prepare(
    'SELECT r.item_id, r.rating, r.comment, r.date,
            m.name AS item_name, c.name AS cafe_name
     FROM Reviews r
     JOIN Menu_Items m ON m.item_id = r.item_id
     JOIN Cafeterias c ON c.cafe_id = m.cafe_id
     WHERE r.user_id = :user
     ORDER BY r.date DESC'
);
$stmt->execute([':user' => $_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

$pageTitle = 'My reviews';
$mainClass = 'account review-page';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap mr-wrap">
    <div class="rf-intro">
        <h1>My Reviews</h1>
        <p>Your campus food experiences, in one place.</p>
    </div>

    <?php if (!$reviews): ?>
        <div class="mr-empty">
            <h2>You haven't rated anything yet</h2>
            <p>Try a meal on campus and tell other students what you think.</p>
            <a class="btn" href="/search.php">Find meals</a>
        </div>
    <?php else: ?>
        <div class="mr-list">
        <?php foreach ($reviews as $rev): ?>
            <?php
            $rating = max(0, min(5, (int) $rev['rating']));
            $when   = strtotime((string) $rev['date']);
            ?>
            <section class="mr-group">
                <article class="mr-card">
                    <p class="mr-by"><?= e($_SESSION['name']) ?> &middot; You</p>
                    <p class="mr-meal"><?= e($rev['item_name']) ?> &middot; <?= e($rev['cafe_name']) ?></p>
                    <p class="mr-stars" aria-label="<?= $rating ?> out of 5 stars"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?>&nbsp;&nbsp;<?= number_format($rating, 1) ?></p>
                    <?php if (trim((string) $rev['comment']) !== ''): ?>
                        <p class="mr-text"><?= e($rev['comment']) ?></p>
                    <?php endif; ?>
                    <?php if ($when): ?>
                        <p class="mr-date"><?= e(date('j F Y', $when)) ?></p>
                    <?php endif; ?>
                </article>
                <div class="mr-actions">
                    <!-- Edit = the existing review form, which pre-fills and updates the review -->
                    <a class="mr-btn" href="/student/review_form.php?item=<?= (int) $rev['item_id'] ?>">Edit Review</a>
                    <form method="post" action="/student/review_delete.php" onsubmit="return confirm('Delete this review?');"><?= csrfField() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $rev['item_id'] ?>">
                        <button type="submit" class="mr-btn">Delete</button>
                    </form>
                </div>
            </section>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>