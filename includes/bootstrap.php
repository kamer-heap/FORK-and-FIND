<?php
// Every page starts with: require_once __DIR__ . '/../includes/bootstrap.php';
// Sets up the session, error handling, DB connection ($pdo) and shared functions.

ini_set('display_errors', '0');                // never show raw errors to users
set_exception_handler(function (Throwable $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('Something went wrong. Please try again.');
});

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../config/database.php';   // defines $pdo
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Every POST request must carry a valid CSRF token.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
}
