<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect(homeFor(currentRole()));
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, name, password, role, is_approved FROM Users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Same message for "no such user" and "wrong password" (do not leak which one).
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } elseif (!(int) $user['is_approved']) {
            $errors[] = 'Your owner account is waiting for admin approval.';
        } else {
            loginUser($user);
            redirect(homeFor($user['role']));
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<h1>Login</h1>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form">
    <label>Email <input type="email" name="email" value="<?= e($email) ?>" required maxlength="150"></label>
    <label>Password <input type="password" name="password" required></label>
    <button type="submit">Log in</button>
</form>
<p>No account? <a href="/signup.php">Sign up</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
