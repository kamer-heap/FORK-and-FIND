<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$errors = [];
$form = ['name' => '', 'department' => '', 'location' => '', 'timings' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        foreach ($form as $field => $_) {
            $form[$field] = trim($_POST[$field] ?? '');
            if ($form[$field] === '' || textLength($form[$field]) > 150) {
                $errors[] = ucfirst($field) . ' is required (max 150 characters).';
            }
        }
        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO Cafeterias (name, department, location, timings) VALUES (:name, :department, :location, :timings)');
            $stmt->execute($form);
            setFlash('success', 'Cafeteria added.');
            redirect('/admin/cafeterias.php');
        }
    } elseif ($action === 'assign') {
        $cafeId  = intOrNull($_POST['cafe_id'] ?? null);
        $ownerId = $_POST['owner_id'] === '' ? 'none' : intOrNull($_POST['owner_id'] ?? null);
        if ($cafeId === null || $ownerId === null) {
            setFlash('error', 'Invalid request.');
        } elseif ($ownerId === 'none') {
            $pdo->prepare('UPDATE Cafeterias SET owner_id = NULL WHERE cafe_id = :c')->execute([':c' => $cafeId]);
            setFlash('success', 'Owner removed from cafeteria.');
        } else {
            // Must be an approved owner who does not already run another cafeteria.
            $stmt = $pdo->prepare("SELECT 1 FROM Users WHERE user_id = :u AND role = 'owner' AND is_approved = 1");
            $stmt->execute([':u' => $ownerId]);
            if (!$stmt->fetch()) {
                setFlash('error', 'That user is not an approved owner.');
            } else {
                try {
                    $pdo->prepare('UPDATE Cafeterias SET owner_id = :u WHERE cafe_id = :c')->execute([':u' => $ownerId, ':c' => $cafeId]);
                    setFlash('success', 'Owner assigned.');
                } catch (PDOException $ex) {
                    if ($ex->getCode() !== '23000') { throw $ex; }
                    setFlash('error', 'That owner already runs another cafeteria.');
                }
            }
        }
        redirect('/admin/cafeterias.php');
    } elseif ($action === 'delete') {
        $cafeId = intOrNull($_POST['cafe_id'] ?? null);
        if ($cafeId !== null) {
            $pdo->prepare('DELETE FROM Cafeterias WHERE cafe_id = :c')->execute([':c' => $cafeId]);
            setFlash('success', 'Cafeteria deleted.');
        }
        redirect('/admin/cafeterias.php');
    }
}

$cafes  = $pdo->query('SELECT c.*, u.name AS owner_name FROM Cafeterias c LEFT JOIN Users u ON u.user_id = c.owner_id ORDER BY c.name')->fetchAll();
$owners = $pdo->query("SELECT user_id, name FROM Users WHERE role = 'owner' AND is_approved = 1 ORDER BY name")->fetchAll();

$pageTitle = 'Manage cafeterias';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Manage cafeterias</h1>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<table>
    <thead><tr><th>Name</th><th>Department</th><th>Location</th><th>Owner</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($cafes as $c): ?>
        <tr>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['department']) ?></td>
            <td><?= e($c['location']) ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="assign">
                    <input type="hidden" name="cafe_id" value="<?= (int) $c['cafe_id'] ?>">
                    <select name="owner_id">
                        <option value="">(no owner)</option>
                        <?php foreach ($owners as $o): ?>
                            <option value="<?= (int) $o['user_id'] ?>" <?= (int) $c['owner_id'] === (int) $o['user_id'] ? 'selected' : '' ?>><?= e($o['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Save</button>
                </form>
            </td>
            <td>
                <form method="post" onsubmit="return confirm('Delete this cafeteria, its menu and reviews?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="cafe_id" value="<?= (int) $c['cafe_id'] ?>">
                    <button type="submit" class="danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Add cafeteria</h2>
<form method="post" class="card form">
    <input type="hidden" name="action" value="add">
    <label>Name <input type="text" name="name" value="<?= e($form['name']) ?>" required maxlength="100"></label>
    <label>Department <input type="text" name="department" value="<?= e($form['department']) ?>" required maxlength="100"></label>
    <label>Location <input type="text" name="location" value="<?= e($form['location']) ?>" required maxlength="150"></label>
    <label>Timings <input type="text" name="timings" value="<?= e($form['timings']) ?>" required maxlength="100"></label>
    <button type="submit">Add</button>
</form>
<p><a href="/admin/dashboard.php">Back</a></p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
