<?php
// -----------------------------------------------
// auth.php — session management and role checks
// -----------------------------------------------

// make sure BASE_PATH (and DB helper) are available for redirects
require_once __DIR__ . '/database.php';

session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . BASE_PATH . '/dashboard.php?error=no_access');
        exit;
    }
}

function currentUser(): array {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'username' => $_SESSION['username']  ?? '',
        'role'     => $_SESSION['user_role'] ?? '',
        'name'     => $_SESSION['full_name'] ?? '',
    ];
}

// Sanitise output — always escape before printing to HTML
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
