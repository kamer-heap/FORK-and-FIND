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

$itemStmt = $pdo->prepare('SELECT cafe_id FROM Menu_Items WHERE item_id = :id');
$itemStmt->execute([':id' => $itemId]);
$item = $itemStmt->fetch();
if (!$item) {
    setFlash('error', 'Item not found.');
    redirect('/owner/menu.php');
}
if ((int) $item['cafe_id'] !== (int) $cafe['cafe_id']) {
    http_response_code(403);
    exit('403 Forbidden: access denied.');
}

$stmt = $pdo->prepare('DELETE FROM Menu_Items WHERE item_id = :id AND cafe_id = :cafe');
$stmt->execute([':id' => $itemId, ':cafe' => $cafe['cafe_id']]);

setFlash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Item deleted.' : 'Item not found.');
redirect('/owner/menu.php');