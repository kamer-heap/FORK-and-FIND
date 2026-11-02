<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);
if (!$cafe) {
    setFlash('error', 'No cafeteria is assigned to your account.');
    redirect('/owner/dashboard.php');
}

$errors = [];
$form = [
    'name'       => $cafe['name'],
    'department' => $cafe['department'],
    'location'   => $cafe['location'],
    'timings'    => $cafe['timings'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $_) {
        $form[$field] = trim($_POST[$field] ?? '');
        if ($form[$field] === '' || textLength($form[$field]) > 150) {
            $errors[] = ucfirst($field) . ' is required (max 150 characters).';
        }
    }
    if (!$errors) {
        // WHERE owner_id = session user: an owner can only ever edit their own cafeteria.
        $stmt = $pdo->prepare(
            'UPDATE Cafeterias SET name = :name, department = :department, location = :location, timings = :timings
             WHERE cafe_id = :cafe AND owner_id = :owner'
        );
        $stmt->execute($form + [':cafe' => $cafe['cafe_id'], ':owner' => $_SESSION['user_id']]);
        setFlash('success', 'Cafeteria info updated.');
        redirect('/owner/dashboard.php');
    }
}

$pageTitle = 'Edit cafeteria';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Edit cafeteria info</h1>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form">
    <label>Name <input type="text" name="name" value="<?= e($form['name']) ?>" required maxlength="100"></label>
    <label>Department <input type="text" name="department" value="<?= e($form['department']) ?>" required maxlength="100"></label>
    <label>Location <input type="text" name="location" value="<?= e($form['location']) ?>" required maxlength="150"></label>
    <label>Timings <input type="text" name="timings" value="<?= e($form['timings']) ?>" required maxlength="100" placeholder="e.g. 8:00 AM - 5:00 PM"></label>
    <button type="submit">Save</button>
    <a href="/owner/dashboard.php">Cancel</a>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
