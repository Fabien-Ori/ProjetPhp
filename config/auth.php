<?php
/**
 * Auth helpers: session start, require login, require admin, current user.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get current logged-in user ID or null.
 */
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user row (id, username, email, balance, role, ...) or null.
 */
function current_user() {
    if (!isset($_SESSION['user_id'])) return null;
    global $mysqli;
    $id = (int) $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT id, username, email, balance, profile_photo, role FROM user WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $r ?: null;
}

/**
 * Check if current user is admin.
 */
function is_admin() {
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'admin';
}

/**
 * Require login: redirect to /login if not logged in. Optionally redirect back after login.
 */
function require_login() {
    if (current_user_id() === null) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . (defined('BASE') ? BASE : '') . '/login.php');
        exit;
    }
}

/**
 * Require admin: redirect to home (or login) if not admin.
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ' . (defined('BASE') ? BASE : '') . '/index.php');
        exit;
    }
}

/**
 * Login user by ID (e.g. after register).
 */
function login_user($userId) {
    $_SESSION['user_id'] = (int) $userId;
}

/**
 * Logout.
 */
function logout_user() {
    unset($_SESSION['user_id']);
    session_destroy();
    session_start();
}
