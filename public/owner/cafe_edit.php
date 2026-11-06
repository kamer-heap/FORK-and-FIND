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
$days        = (string) ($cafe['days'] ?? '');
$description = (string) ($cafe['description'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $_) {
        $form[$field] = trim($_POST[$field] ?? '');
        if ($form[$field] === '' || textLength($form[$field]) > 150) {
            $errors[] = ucfirst($field) . ' is required (max 150 characters).';
        }
    }
    // NEW optional fields: days (from the dropdown list) and a short description.
    $days        = trim($_POST['days'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($days !== '' && !in_array($days, cafeDayOptions(), true)) {
        $errors[] = 'Please choose the days from the list.';
    }
    if (textLength($description) > 500) {
        $errors[] = 'Description can be at most 500 characters.';
    }

    // NEW optional photo upload (checked and saved by saveUploadedImage()).
    $newImage = null;
    if (!$errors) {
        [$newImage, $imageError] = saveUploadedImage('image');
        if ($imageError !== null) {
            $errors[] = $imageError;
        }
    }

    if (!$errors) {
        // WHERE owner_id = session user: an owner can only ever edit their own cafeteria.
        $sql = 'UPDATE Cafeterias SET name = :name, department = :department, location = :location, timings = :timings,
                       days = :days, description = :description'
             . ($newImage !== null ? ', image = :image' : '')
             . ' WHERE cafe_id = :cafe AND owner_id = :owner';
        $params = $form + [
            ':days'        => $days === '' ? null : $days,
            ':description' => $description === '' ? null : $description,
            ':cafe'        => $cafe['cafe_id'],
            ':owner'       => $_SESSION['user_id'],
        ];
        if ($newImage !== null) {
            $params[':image'] = $newImage;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($newImage !== null) {
            deleteUploadedImage($cafe['image'] ?? null);   // remove the old photo file
        }
        setFlash('success', 'Cafeteria info updated.');
        redirect('/owner/dashboard.php');
    }
}

$pageTitle = 'Edit cafeteria';
$mainClass = 'account owner-page';
$extraCss  = 'manage.css,form.css';
require __DIR__ . '/../../includes/header.php';
?>
<div class="rf-wrap ow-wrap">
    <div class="rf-intro">
        <p class="of-crumb">Cafeteria / Edit information</p>
        <h1>Edit Cafeteria</h1>
        <p class="ow-sub">Make sure students know where and when to find you.</p>
    </div>

    <form method="post" enctype="multipart/form-data" class="of-card"><?= csrfField() ?>
        <h2>Cafeteria information</h2>
        <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

        <div class="of-field">
            <label class="of-label" for="name">Cafeteria name</label>
            <input class="of-input" type="text" id="name" name="name" value="<?= e($form['name']) ?>" required maxlength="100" placeholder="e.g. Central Cafeteria">
        </div>
        <div class="of-field">
            <label class="of-label" for="department">Department</label>
            <input class="of-input" type="text" id="department" name="department" value="<?= e($form['department']) ?>" required maxlength="100" placeholder="e.g. Computer Science">
        </div>
        <div class="of-field">
            <label class="of-label" for="location">Location</label>
            <input class="of-input" type="text" id="location" name="location" value="<?= e($form['location']) ?>" required maxlength="150" placeholder="e.g. Student Centre, Main campus">
        </div>
        <div class="of-row">
            <div class="of-field">
                <label class="of-label" for="days">Days</label>
                <select class="of-select" id="days" name="days">
                    <option value="">Select days</option>
                    <?php foreach (cafeDayOptions() as $option): ?>
                        <option value="<?= e($option) ?>" <?= $days === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="of-field">
                <label class="of-label" for="timings">Opening hours</label>
                <input class="of-input" type="text" id="timings" name="timings" value="<?= e($form['timings']) ?>" required maxlength="100" placeholder="e.g. 8:00 AM - 5:00 PM">
            </div>
        </div>
        <div class="of-field">
            <label class="of-label" for="description">Description (optional)</label>
            <textarea class="of-textarea" id="description" name="description" maxlength="500" placeholder="A short description of your cafeteria."><?= e($description) ?></textarea>
        </div>

        <div class="of-field">
            <span class="of-label">Cafeteria image</span>
            <div class="of-image">
                <img class="of-preview" id="image-preview" src="<?= e(cafeImage($cafe)) ?>" alt="Current cafeteria image">
                <input class="visually-hidden of-file-input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <label class="mr-btn of-file" for="image">Change Image</label>
                <p class="of-hint" id="image-note">Current image &middot; <?= !empty($cafe['image']) ? e($cafe['image']) : 'default photo' ?>. JPG, PNG or WebP, up to 2 MB.</p>
            </div>
        </div>

        <div class="of-actions">
            <button type="submit" class="mr-btn is-primary">Save Changes</button>
            <a class="mr-btn" href="/owner/dashboard.php">Cancel</a>
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