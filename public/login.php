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
$mainClass = 'account account-login';
require __DIR__ . '/../includes/header.php';
?>
<div class="account-grid">
    <section class="welcome">
        <h1>Welcome back.</h1>
        <p class="welcome-sub">Find your next campus meal.</p>
        <img class="welcome-photo" src="/img/meal-biryani.png" alt="Chicken biryani with raita on a plate">
    </section>

    <form method="post" class="form-card"><?= csrfField() ?>
        <h2>Login</h2>
        <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
        <label class="acct-field">
            <span class="acct-label">Email address</span>
            <input class="acct-input" type="email" name="email" value="<?= e($email) ?>" placeholder="you@university.edu" required maxlength="150" autocomplete="email">
        </label>
        <div class="acct-field">
            <label class="acct-label" for="password">Password</label>
            <div class="acct-input-wrap">
                <input id="password" type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                <button type="button" class="pw-toggle" hidden>Show</button>
            </div>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
        <p class="form-switch">New to FORK &amp; FIND? <a href="/signup.php">Signup &rarr;</a></p>
    </form>
</div>

<script>
// UI only: "Show / Hide" button for the password box (stays hidden when JavaScript is off).
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