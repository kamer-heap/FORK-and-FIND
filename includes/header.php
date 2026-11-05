<?php
// Expects $pageTitle (optional). Included after bootstrap.php.
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Fork & Find') ?> | Fork &amp; Find</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="/index.php">Fork &amp; Find</a>
    <nav>
        <a href="/index.php">Cafeterias</a>
        <a href="/search.php">Search meals</a>
        <?php if (isLoggedIn()): ?>
            <?php if (currentRole() === 'owner'): ?><a href="/owner/dashboard.php">My cafeteria</a><?php endif; ?>
            <?php if (currentRole() === 'admin'): ?><a href="/admin/dashboard.php">Admin</a><?php endif; ?>
            <span class="who"><?= e($_SESSION['name']) ?> (<?= e(currentRole()) ?>)</span>
            <form method="post" action="/logout.php" class="logout"><?= csrfField() ?><button type="submit">Logout</button></form>
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/signup.php">Sign up</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
