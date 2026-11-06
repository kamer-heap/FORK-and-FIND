<?php
// Small helpers for cafeteria days, the "days · timings" text and photo uploads.

// The days an owner can pick from the dropdown.
function cafeDayOptions(): array
{
    return ['Monday–Friday', 'Monday–Saturday', 'Monday–Sunday', 'Saturday–Sunday'];
}

// "Monday–Friday · 8:00 AM - 5:00 PM", or just the timings when no days are set.
function cafeHours(array $cafe): string
{
    $days = trim((string) ($cafe['days'] ?? ''));
    return $days !== '' ? $days . ' · ' . $cafe['timings'] : (string) $cafe['timings'];
}

// Saves an uploaded JPG/PNG/WebP (max 2 MB) into public/uploads/ under a random name.
// Returns [file name or null, error message or null]. No file chosen = [null, null].
function saveUploadedImage(string $field): array
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The image could not be uploaded. Try a smaller file.'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'The image must be 2 MB or smaller.'];
    }

    // Check the real file content, not what the browser claims.
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $info  = @getimagesize($file['tmp_name']);
    if ($info === false || !isset($types[$info['mime']])) {
        return [null, 'Please choose a JPG, PNG or WebP image.'];
    }

    $dir = __DIR__ . '/../public/uploads';
    if (!is_dir($dir) || !is_writable($dir)) {
        return [null, 'The uploads folder is missing or not writable. Ask the site admin to fix it.'];
    }
    $name = bin2hex(random_bytes(12)) . '.' . $types[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return [null, 'The image could not be saved.'];
    }
    return [$name, null];
}

// Deletes an uploaded photo (only files that look like our own random names).
function deleteUploadedImage(?string $name): void
{
    if ($name && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $name)) {
        @unlink(__DIR__ . '/../public/uploads/' . $name);
    }
}