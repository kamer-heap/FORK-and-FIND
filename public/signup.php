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
    $confirm  = $_POST['confirm_password'] ?? '';
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
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
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
$mainClass = 'account account-signup';
require __DIR__ . '/../includes/header.php';
?>
<div class="account-grid">
    <section class="welcome">
        <h1>Join FORK &amp; FIND</h1>
        <p class="welcome-sub">Discover campus meals and share your food experiences.</p>
        <img class="welcome-photo" src="/img/meal-wrap.png" alt="Grilled chicken wrap with fries on a plate">
    </section>

    <form method="post" class="form-card"><?= csrfField() ?>
        <h2>Create your account</h2>
        <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
        <label class="acct-field">
            <span class="acct-label">Full name</span>
            <input class="acct-input" type="text" name="name" value="<?= e($name) ?>" placeholder="Your full name" required maxlength="100" autocomplete="name">
        </label>
        <label class="acct-field">
            <span class="acct-label">Email address</span>
            <input class="acct-input" type="email" name="email" value="<?= e($email) ?>" placeholder="you@university.edu" required maxlength="150" autocomplete="email">
        </label>
        <div class="acct-field">
            <label class="acct-label" for="password">Password</label>
            <div class="acct-input-wrap">
                <input id="password" type="password" name="password" placeholder="Enter password" required minlength="8" autocomplete="new-password">
                <button type="button" class="pw-toggle" hidden>Show</button>
            </div>
        </div>
        <p class="acct-hint">Use at least 8 characters.</p>
        <div class="acct-field">
            <label class="acct-label" for="confirm_password">Confirm password</label>
            <div class="acct-input-wrap">
                <input id="confirm_password" type="password" name="confirm_password" placeholder="Re-enter password" required minlength="8" autocomplete="new-password">
                <button type="button" class="pw-toggle" hidden>Show</button>
            </div>
        </div>
        <label class="acct-field">
            <span class="acct-label">I am a</span>
            <select class="acct-input acct-select" name="role">
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="owner" <?= $role === 'owner' ? 'selected' : '' ?>>Cafeteria owner</option>
            </select>
        </label>
        <button type="submit" class="btn btn-block">Signup</button>
        <p class="form-switch">Already have an account? <a href="/login.php">Login &rarr;</a></p>
    </form>
</div>

<script>
// UI only: "Show / Hide" buttons for the password boxes (they stay hidden when JavaScript is off).
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    var input = btn.parentNode.querySelector('input');
    btn.hidden = false;
    btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
    });
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>