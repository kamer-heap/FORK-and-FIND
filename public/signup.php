<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect(homeFor(currentRole()));
}

$errors = [];
$name = $email = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    if ($name === '' || textLength($name) > 100) {
        $errors[] = 'Name is required (max 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || textLength($email) > 150) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    // Only these two roles can self-register. Admin is never created from the browser.
    if (!in_array($role, ['student', 'owner'], true)) {
        $errors[] = 'Invalid role.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT 1 FROM Users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        $isApproved = $role === 'owner' ? 0 : 1;   // owners wait for admin approval
        $stmt = $pdo->prepare(
            'INSERT INTO Users (name, email, password, role, is_approved)
             VALUES (:name, :email, :password, :role, :approved)'
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role'     => $role,
            ':approved' => $isApproved,
        ]);
        setFlash('success', $role === 'owner'
            ? 'Account created. An admin must approve it before you can log in.'
            : 'Account created. You can log in now.');
        redirect('/login.php');
    }
}

$pageTitle = 'Sign up';
require __DIR__ . '/../includes/header.php';
?>
<h1>Sign up</h1>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form">
    <label>Name <input type="text" name="name" value="<?= e($name) ?>" required maxlength="100"></label>
    <label>Email <input type="email" name="email" value="<?= e($email) ?>" required maxlength="150"></label>
    <label>Password (min 8 characters) <input type="password" name="password" required minlength="8"></label>
    <label>I am a
        <select name="role">
            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="owner" <?= $role === 'owner' ? 'selected' : '' ?>>Cafeteria owner</option>
        </select>
    </label>
    <button type="submit">Create account</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
