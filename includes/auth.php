<?php
// Authentication and role-based authorization. All checks happen on the server.

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in first.');
        redirect('/login.php');
    }
}

function requireRole(string $role): void
{
    requireLogin();
    if (currentRole() !== $role) {
        http_response_code(403);
        exit('403 Forbidden: you do not have access to this page.');
    }
}

function loginUser(array $user): void
{
    session_regenerate_id(true);               // prevents session fixation
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['role']    = $user['role'];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function homeFor(?string $role): string
{
    return match ($role) {
        'admin' => '/admin/dashboard.php',
        'owner' => '/owner/dashboard.php',
        default => '/index.php',
    };
}

// Returns the cafeteria row that belongs to the logged-in owner (or null).
function ownerCafe(PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM Cafeterias WHERE owner_id = :uid');
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
