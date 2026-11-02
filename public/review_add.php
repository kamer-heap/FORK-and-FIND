<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requirePost();
requireRole('student');                          // only students can review

$itemId  = intOrNull($_POST['item_id'] ?? null);
$rating  = intOrNull($_POST['rating'] ?? null);
$comment = trim($_POST['comment'] ?? '');

if ($itemId === null || $rating === null || $rating > 5 || textLength($comment) > 500) {
    setFlash('error', 'Invalid review. Choose a rating from 1 to 5.');
    redirect('/index.php');
}

$stmt = $pdo->prepare('SELECT cafe_id FROM Menu_Items WHERE item_id = :id');
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch();
if (!$item) {
    setFlash('error', 'That menu item does not exist.');
    redirect('/index.php');
}

// One review per student per item: a second submit updates the first (PENDING decision).
$stmt = $pdo->prepare(
    'INSERT INTO Reviews (item_id, user_id, rating, comment)
     VALUES (:item, :user, :rating, :comment)
     ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), date = CURRENT_TIMESTAMP'
);
$stmt->execute([
    ':item'    => $itemId,
    ':user'    => $_SESSION['user_id'],
    ':rating'  => $rating,
    ':comment' => $comment,
]);

setFlash('success', 'Thanks! Your review was saved.');
redirect('/cafeteria.php?id=' . (int) $item['cafe_id']);
