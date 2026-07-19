<?php
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    if ($admin === null) {
        $stmt = db()->prepare('SELECT id, username, nama FROM admin_users WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
    }
    return $admin;
}

/** Rate limit sederhana berbasis session, dipakai di checkout & login */
function rate_limit_ok(string $key, int $seconds = 10): bool
{
    $now = time();
    $last = $_SESSION['rl_' . $key] ?? 0;
    if ($now - $last < $seconds) {
        return false;
    }
    $_SESSION['rl_' . $key] = $now;
    return true;
}
