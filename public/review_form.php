<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireRole('student');                          // only students can review

$itemId = intOrNull($_GET['item'] ?? null);
if ($itemId === null) {
    http_response_code(404);
    exit('Meal not found.');
}

$stmt = $pdo->prepare(
    'SELECT m.*, c.name AS cafe_name
     FROM Menu_Items m
     JOIN Cafeterias c ON c.cafe_id = m.cafe_id
     WHERE m.item_id = :id'
);
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch();
if (!$item) {
    http_response_code(404);
    exit('Meal not found.');
}

// The student's own review of this meal (if any) pre-fills the form. Saving again updates it.
$stmt = $pdo->prepare('SELECT rating, comment FROM Reviews WHERE item_id = :item AND user_id = :user');
$stmt->execute([':item' => $itemId, ':user' => $_SESSION['user_id']]);
$mine    = $stmt->fetch() ?: null;
$rating  = $mine ? (int) $mine['rating'] : 0;
$comment = $mine ? (string) $mine['comment'] : '';

$pageTitle = 'Write a review';
$mainClass = 'account review-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="rf-wrap">
    <div class="rf-intro">
        <h1>Share your experience</h1>
        <p>Help other students find their next favourite meal.</p>
    </div>

    <form method="post" action="/review_add.php" class="rf-card"><?= csrfField() ?>
        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
        <div class="rf-meal">
            <img class="rf-photo" src="<?= e(mealImage($item)) ?>" alt="<?= e($item['name']) ?>">
            <div class="rf-meal-info">
                <h2><?= e($item['name']) ?></h2>
                <p class="rf-cafe"><?= e($item['cafe_name']) ?></p>
                <p class="rf-price">PKR <?= e(number_format((float) $item['price'], 0)) ?></p>
            </div>
        </div>

        <fieldset class="rf-rating">
            <legend class="acct-label">Your rating</legend>
            <div class="star-picker">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $rating === $i ? 'checked' : '' ?> required>
                    <label for="star<?= $i ?>"><span class="visually-hidden"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span></label>
                <?php endfor; ?>
            </div>
        </fieldset>
        <p class="acct-hint">Choose a rating from 1 to 5 stars.</p>

        <div class="rf-field">
            <label class="acct-label" for="comment">Your review</label>
            <textarea class="rf-textarea" id="comment" name="comment" maxlength="500" placeholder="How was the taste, portion size and value?"><?= e($comment) ?></textarea>
        </div>
        <p class="acct-hint">Up to 500 characters &middot; <span id="rf-count"><?= textLength($comment) ?></span> / 500</p>

        <button type="submit" class="btn rf-submit"><?= $mine ? 'Update Review' : 'Submit Review' ?></button>
    </form>

    <?php if ($mine): ?>
    <form method="post" action="/review_delete.php" class="rf-delete"><?= csrfField() ?>
        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
        <button type="submit">Delete my review</button>
    </form>
    <?php endif; ?>
</div>

<script>
// UI only: live character counter for the review box.
(function () {
    var box = document.getElementById('comment'), count = document.getElementById('rf-count');
    if (!box || !count) return;
    box.addEventListener('input', function () { count.textContent = box.value.length; });
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>