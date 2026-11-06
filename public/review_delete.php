<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requirePost();
requireRole('student');                          // only students have reviews to delete

$itemId = intOrNull($_POST['item_id'] ?? null);
if ($itemId === null) {
    http_response_code(400);
    exit('Bad request.');
}

$stmt = $pdo->prepare('SELECT cafe_id FROM Menu_Items WHERE item_id = :id');
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch();
if (!$item) {
    setFlash('error', 'That menu item does not exist.');
    redirect('/index.php');
}

// The user_id condition means a student can only delete their OWN review.
$stmt = $pdo->prepare('DELETE FROM Reviews WHERE item_id = :item AND user_id = :user');
$stmt->execute([':item' => $itemId, ':user' => $_SESSION['user_id']]);

setFlash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Your review was deleted.' : 'You have no review on this item.');
redirect('/my_reviews.php');                      // back to the student's own list