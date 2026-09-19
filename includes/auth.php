<?php
require_once __DIR__ . '/db.php';

session_start();

function is_logged_in(): bool
{
    return !empty($_SESSION['owner_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function attempt_login(string $username, string $password): bool
{
    $stmt = get_db()->prepare('SELECT id, password_hash FROM store_owner WHERE username = ?');
    $stmt->execute([$username]);
    $owner = $stmt->fetch();

    if ($owner && password_verify($password, $owner['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['owner_id'] = $owner['id'];
        $_SESSION['username'] = $username;
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

/** Basic CSRF token helpers, used on every form that changes data. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission. Go back and try again.');
    }
}
