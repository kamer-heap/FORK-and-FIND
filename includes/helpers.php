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

// CSRF protection: every POST form carries a secret token that other websites cannot guess.
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('403 Forbidden: invalid or missing security token. Go back, reload the page and try again.');
    }
}


// ---- UI helpers (redesign): pictures only, no effect on the application logic ----

// Cafeteria picture. Uses an uploaded file if the row has an 'image' value,
// otherwise one of the three default cafe pictures (chosen by cafe_id).
function cafeImage(array $cafe): string
{
    if (!empty($cafe['image'])) {
        return '/uploads/' . rawurlencode((string) $cafe['image']);
    }
    $defaults = ['cafe-central.png', 'cafe-engineering.png', 'cafe-garden.png'];
    return '/img/' . $defaults[((int) ($cafe['cafe_id'] ?? 1) - 1) % 3];
}

// Meal picture. Uses an uploaded file if the row has an 'image' value,
// otherwise a neutral placeholder for legacy records that still need an image.
function mealImage(array $item): string
{
    if (!empty($item['image'])) {
        return '/uploads/' . rawurlencode((string) $item['image']);
    }
    return '/img/placeholder-meal.svg';
}
