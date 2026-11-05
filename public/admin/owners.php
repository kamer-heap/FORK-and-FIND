<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intOrNull($_POST['user_id'] ?? null);
    $action = $_POST['action'] ?? '';

    if ($userId !== null && $action === 'approve') {
        $stmt = $pdo->prepare("UPDATE Users SET is_approved = 1 WHERE user_id = :id AND role = 'owner'");
        $stmt->execute([':id' => $userId]);
        setFlash('success', 'Owner approved.');
    } elseif ($userId !== null && $action === 'reject') {
        // Only pending owners can be rejected (deleted) from here.
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = :id AND role = 'owner' AND is_approved = 0");
        $stmt->execute([':id' => $userId]);
        setFlash('success', 'Owner request removed.');
    } else {
        setFlash('error', 'Invalid request.');
    }
    redirect('/admin/owners.php');
}

$owners = $pdo->query(
    "SELECT u.user_id, u.name, u.email, u.is_approved, c.name AS cafe_name
     FROM Users u LEFT JOIN Cafeterias c ON c.owner_id = u.user_id
     WHERE u.role = 'owner'
     ORDER BY u.is_approved, u.name"
)->fetchAll();

$pageTitle = 'Owner approvals';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Owner accounts</h1>
<?php if (!$owners): ?><p>No owner accounts yet.</p><?php endif; ?>
<table>
    <thead><tr><th>Name</th><th>Email</th><th>Cafeteria</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($owners as $o): ?>
        <tr>
            <td><?= e($o['name']) ?></td>
            <td><?= e($o['email']) ?></td>
            <td><?= e($o['cafe_name'] ?? 'not assigned') ?></td>
            <td><?= (int) $o['is_approved'] ? 'Approved' : '<strong>Pending</strong>' ?></td>
            <td class="actions">
            <?php if (!(int) $o['is_approved']): ?>
                <form method="post"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int) $o['user_id'] ?>"><input type="hidden" name="action" value="approve"><button type="submit">Approve</button></form>
                <form method="post" onsubmit="return confirm('Remove this request?');"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int) $o['user_id'] ?>"><input type="hidden" name="action" value="reject"><button type="submit" class="danger">Reject</button></form>
            <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p><a href="/admin/dashboard.php">Back</a></p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
