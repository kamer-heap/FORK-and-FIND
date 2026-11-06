<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requirePost();
requireRole('owner');

$itemId = intOrNull($_POST['item_id'] ?? null);
$cafe   = ownerCafe($pdo);
if ($itemId === null || !$cafe) {
    http_response_code(400);
    exit('Bad request.');
}

// NOT available flips 1 <-> 0. The cafe_id condition blocks touching another owner's items.
$stmt = $pdo->prepare('UPDATE Menu_Items SET available = 1 - available WHERE item_id = :id AND cafe_id = :cafe');
$stmt->execute([':id' => $itemId, ':cafe' => $cafe['cafe_id']]);

setFlash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Availability updated.' : 'Item not found.');
redirect('/owner/menu.php');