<?php
// TEMPLATE. Copy this file to config/database.php and put your real values there.
// config/database.php is in .gitignore, so your password never reaches GitHub.

$dbHost = 'localhost';
$dbName = 'fork_and_find';
$dbUser = 'fork_user';
$dbPass = 'CHANGE_ME';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database unavailable. Please try again later.');
}
