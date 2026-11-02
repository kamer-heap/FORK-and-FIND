<?php
// Small shared helpers (output escaping, redirects, flash messages, validation).

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Positive integer from POST/GET, or null if missing/invalid.
function intOrNull($value): ?int
{
    $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $int === false ? null : $int;
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

// Character count that works even if the php-mbstring extension is not installed.
function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : (int) preg_match_all('/./us', $value);
}
