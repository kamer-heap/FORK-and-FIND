<?php
// One page for both "add item" (no id) and "edit item" (?id=...).
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('owner');

$cafe = ownerCafe($pdo);
if (!$cafe) {
    setFlash('error', 'No cafeteria is assigned to your account.');
    redirect('/owner/dashboard.php');
}

$itemId   = null;
$existing = null;
$form = ['name' => '', 'price' => '', 'category' => '', 'description' => ''];

if (isset($_REQUEST['id'])) {
    $itemId = intOrNull($_REQUEST['id']);
    $stmt = $pdo->prepare('SELECT item_id, cafe_id, name, price, category, description, image FROM Menu_Items WHERE item_id = :id');
    $stmt->execute([':id' => $itemId]);
    $existing = $stmt->fetch();
    if ($itemId === null || !$existing) {
        http_response_code(404);
        exit('Menu item not found.');
    }
    if ((int) $existing['cafe_id'] !== (int) $cafe['cafe_id']) {
        http_response_code(403);
        exit('403 Forbidden: access denied.');
    }
    $form = [
        'name'        => $existing['name'],
        'price'       => $existing['price'],
        'category'    => $existing['category'],
        'description' => (string) ($existing['description'] ?? ''),
    ];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name']        = trim($_POST['name'] ?? '');
    $form['price']       = trim($_POST['price'] ?? '');
    $form['category']    = trim($_POST['category'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');

    if ($form['name'] === '' || textLength($form['name']) > 100) {
        $errors[] = 'Item name is required (max 100 characters).';
    }
    if (!is_numeric($form['price']) || $form['price'] <= 0 || $form['price'] > 100000) {
        $errors[] = 'Price must be greater than 0 and at most 100000.';
    }
    if ($form['category'] === '' || textLength($form['category']) > 50) {
        $errors[] = 'Category is required (max 50 characters).';
    }
    if (textLength($form['description']) > 500) {
        $errors[] = 'Description can be at most 500 characters.';
    }

    // A meal must have an image. Existing images may be retained when editing.
    $newImage = null;
    if (!$errors) {
        [$newImage, $imageError] = saveUploadedImage('image');
        if ($imageError !== null) {
            $errors[] = $imageError;
        } elseif ($newImage === null && ($itemId === null || empty($existing['image']))) {
            $errors[] = 'A food image is required.';
        }
    }

    if (!$errors) {
        $description = $form['description'] === '' ? null : $form['description'];
        try {
            if ($itemId === null) {
                $stmt = $pdo->prepare('INSERT INTO Menu_Items (cafe_id, name, price, category, description, image) VALUES (:cafe, :name, :price, :category, :description, :image)');
                $stmt->execute([
                    ':cafe'        => $cafe['cafe_id'],
                    ':name'        => $form['name'],
                    ':price'       => $form['price'],
                    ':category'    => $form['category'],
                    ':description' => $description,
                    ':image'       => $newImage,
                ]);
                setFlash('success', 'Item added.');
            } else {
                $sql = 'UPDATE Menu_Items SET name = :name, price = :price, category = :category, description = :description'
                     . ($newImage !== null ? ', image = :image' : '')
                     . ' WHERE item_id = :id AND cafe_id = :cafe';
                $params = [
                    ':name'        => $form['name'],
                    ':price'       => $form['price'],
                    ':category'    => $form['category'],
                    ':description' => $description,
                    ':id'          => $itemId,
                    ':cafe'        => $cafe['cafe_id'],
                ];
                if ($newImage !== null) {
                    $params[':image'] = $newImage;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                if ($newImage !== null) {
                    deleteUploadedImage($existing['image'] ?? null);   // remove the old photo file
                }
                setFlash('success', 'Item updated.');
            }
            redirect('/owner/menu.php');
        } catch (PDOException $ex) {
            deleteUploadedImage($newImage);                  // do not keep a photo for an item that was not saved
            if ($ex->getCode() === '23000') {              // duplicate (cafe_id, name)
                $errors[] = 'You already have an item with that name.';
            } else {
                throw $ex;
            }
        }
    }
}

$pageTitle = $itemId ? 'Edit menu item' : 'Add menu item';
$mainClass = 'account owner-page';
$extraCss  = 'manage.css,form.css';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap ow-wrap">
    <div class="rf-intro">
        <p class="of-crumb"><?= $itemId ? 'Menu / Edit item' : 'Menu / Add item' ?></p>
        <h1><?= $itemId ? 'Edit Menu Item' : 'Add Menu Item' ?></h1>
        <p class="ow-sub"><?= $itemId ? 'Update the details students see for this meal.' : 'Add a meal, set its price and help students discover it.' ?></p>
    </div>

    <form method="post" enctype="multipart/form-data" class="of-card"><?= csrfField() ?>
        <?php if ($itemId): ?><input type="hidden" name="id" value="<?= (int) $itemId ?>"><?php endif; ?>
        <h2>Meal information</h2>
        <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

        <div class="of-field">
            <label class="of-label" for="name">Food name</label>
            <input class="of-input" type="text" id="name" name="name" value="<?= e((string) $form['name']) ?>" required maxlength="100" placeholder="e.g. Chicken Biryani">
        </div>
        <div class="of-field">
            <label class="of-label" for="price">Price (PKR)</label>
            <input class="of-input" type="number" id="price" name="price" value="<?= e((string) $form['price']) ?>" required min="0.01" step="0.01" placeholder="e.g. 250">
        </div>
        <div class="of-field">
            <label class="of-label" for="category">Category</label>
            <input class="of-input" type="text" id="category" name="category" value="<?= e((string) $form['category']) ?>" required maxlength="50" placeholder="e.g. Rice, Chinese, Drinks">
        </div>
        <div class="of-field">
            <label class="of-label" for="description">Description (optional)</label>
            <textarea class="of-textarea" id="description" name="description" maxlength="500" placeholder="A short description of the meal."><?= e((string) $form['description']) ?></textarea>
        </div>

        <div class="of-field">
            <span class="of-label">Food image<?= !$existing || empty($existing['image']) ? '' : ' (optional)' ?></span>
            <div class="of-image">
                <?php if ($existing): ?>
                    <img class="of-preview" id="image-preview" src="<?= e(mealImage($existing)) ?>" alt="Current meal image">
                <?php endif; ?>
                <input class="visually-hidden of-file-input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"<?= !$existing || empty($existing['image']) ? ' required' : '' ?>>
                <label class="mr-btn of-file" for="image"><?= $existing ? 'Change Image' : 'Choose Image' ?></label>
                <p class="of-hint" id="image-note">JPG, PNG or WebP, up to 2 MB. A food image is required.</p>
            </div>
        </div>

        <div class="of-actions">
            <button type="submit" class="mr-btn is-primary">Save Item</button>
            <a class="mr-btn" href="/owner/menu.php">Cancel</a>
        </div>
    </form>
</div>
<script>
// UI only: show the chosen file name and a preview before saving.
(function () {
    var input = document.getElementById('image'), note = document.getElementById('image-note'), prev = document.getElementById('image-preview');
    if (!input) return;
    input.addEventListener('change', function () {
        var f = input.files && input.files[0];
        if (!f) return;
        if (note) note.textContent = 'Selected \u00b7 ' + f.name;
        if (prev) prev.src = URL.createObjectURL(f);
    });
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>