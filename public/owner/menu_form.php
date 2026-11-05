<?php
// One page for both "add item" (no id) and "edit item" (?id=...).
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);
if (!$cafe) {
    setFlash('error', 'No cafeteria is assigned to your account.');
    redirect('/owner/dashboard.php');
}

$itemId = null;
$form = ['name' => '', 'price' => '', 'category' => ''];

if (isset($_REQUEST['id'])) {
    $itemId = intOrNull($_REQUEST['id']);
    // Load only if the item belongs to THIS owner's cafeteria.
    $stmt = $pdo->prepare('SELECT item_id, name, price, category FROM Menu_Items WHERE item_id = :id AND cafe_id = :cafe');
    $stmt->execute([':id' => $itemId, ':cafe' => $cafe['cafe_id']]);
    $existing = $stmt->fetch();
    if ($itemId === null || !$existing) {
        http_response_code(404);
        exit('Menu item not found.');
    }
    $form = ['name' => $existing['name'], 'price' => $existing['price'], 'category' => $existing['category']];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name']     = trim($_POST['name'] ?? '');
    $form['price']    = trim($_POST['price'] ?? '');
    $form['category'] = trim($_POST['category'] ?? '');

    if ($form['name'] === '' || textLength($form['name']) > 100) {
        $errors[] = 'Item name is required (max 100 characters).';
    }
    if (!is_numeric($form['price']) || $form['price'] < 0 || $form['price'] > 100000) {
        $errors[] = 'Price must be a number between 0 and 100000.';
    }
    if ($form['category'] === '' || textLength($form['category']) > 50) {
        $errors[] = 'Category is required (max 50 characters).';
    }

    if (!$errors) {
        try {
            if ($itemId === null) {
                $stmt = $pdo->prepare('INSERT INTO Menu_Items (cafe_id, name, price, category) VALUES (:cafe, :name, :price, :category)');
                $stmt->execute([':cafe' => $cafe['cafe_id']] + [':name' => $form['name'], ':price' => $form['price'], ':category' => $form['category']]);
                setFlash('success', 'Item added.');
            } else {
                $stmt = $pdo->prepare('UPDATE Menu_Items SET name = :name, price = :price, category = :category WHERE item_id = :id AND cafe_id = :cafe');
                $stmt->execute([':name' => $form['name'], ':price' => $form['price'], ':category' => $form['category'], ':id' => $itemId, ':cafe' => $cafe['cafe_id']]);
                setFlash('success', 'Item updated.');
            }
            redirect('/owner/dashboard.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {              // duplicate (cafe_id, name)
                $errors[] = 'You already have an item with that name.';
            } else {
                throw $ex;
            }
        }
    }
}

$pageTitle = $itemId ? 'Edit menu item' : 'Add menu item';
require __DIR__ . '/../../includes/header.php';
?>
<h1><?= e($pageTitle) ?></h1>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form"><?= csrfField() ?>
    <?php if ($itemId): ?><input type="hidden" name="id" value="<?= (int) $itemId ?>"><?php endif; ?>
    <label>Item name <input type="text" name="name" value="<?= e((string) $form['name']) ?>" required maxlength="100"></label>
    <label>Price (Rs) <input type="number" name="price" value="<?= e((string) $form['price']) ?>" required min="0" step="1"></label>
    <label>Category <input type="text" name="category" value="<?= e((string) $form['category']) ?>" required maxlength="50" placeholder="e.g. Rice, Chinese, Drinks"></label>
    <button type="submit">Save</button>
    <a href="/owner/dashboard.php">Cancel</a>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
