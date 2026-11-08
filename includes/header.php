<?php
// Expects $pageTitle (optional), $mainClass (optional) and $extraCss (optional, e.g. 'manage.css,form.css'). Included after bootstrap.php.
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Fork & Find') ?> | Fork &amp; Find</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/css/style.css">
    <?php foreach (array_filter(explode(',', (string) ($extraCss ?? ''))) as $css): ?><link rel="stylesheet" href="/css/<?= e(trim($css)) ?>">
    <?php endforeach; ?>
</head>
<body>
<header class="site-header">
    <a class="brand" href="/index.php">
        <?php require __DIR__ . '/brand.php'; ?>
        <span class="brand-name">FORK &amp; FIND</span>
    </a>
    <nav>
        <a href="/index.php#cafeterias">Cafeterias</a>
        <a href="/search.php">Find meals</a>
        <a href="/search.php?max_price=200">Under PKR 200</a>
        <?php if (isLoggedIn()): ?>
            <?php if (currentRole() === 'owner'): ?><a href="/owner/dashboard.php">My cafeteria</a><?php endif; ?>
            <?php if (currentRole() === 'admin'): ?><a href="/admin/dashboard.php">Admin</a><?php endif; ?>
            <?php if (currentRole() === 'student'): ?>
                <a class="who" href="/student/my_reviews.php" title="My reviews"><?= e($_SESSION['name']) ?> (<?= e(currentRole()) ?>)</a>
            <?php else: ?>
                <span class="who"><?= e($_SESSION['name']) ?> (<?= e(currentRole()) ?>)</span>
            <?php endif; ?>
            <form method="post" action="/logout.php" class="logout"><?= csrfField() ?><button type="submit">Logout</button></form>
        <?php else: ?>
            <a href="/signup.php">Sign up</a>
            <a class="btn nav-login" href="/login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="<?= e($mainClass ?? 'container') ?>">
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>