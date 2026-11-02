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

$stmt = $pdo->prepare('DELETE FROM Menu_Items WHERE item_id = :id AND cafe_id = :cafe');
$stmt->execute([':id' => $itemId, ':cafe' => $cafe['cafe_id']]);

setFlash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Item deleted.' : 'Item not found.');
redirect('/owner/dashboard.php');
