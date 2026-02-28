<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . getAdminBase() . 'index.php');
        exit;
    }
}

function getAdminBase(): string {
    $script = $_SERVER['SCRIPT_NAME'];
    if (strpos($script, '/admin/') !== false) {
        return preg_replace('#/[^/]+$#', '/', $script);
    }
    return '/admin/';
}

function adminLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
