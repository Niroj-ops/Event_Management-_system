<?php
// includes/auth_check.php
require_once __DIR__ . '/helpers.php';

function require_login() {
    if (!is_logged_in()) {
        flash_set('error', 'Please login first.');
        redirect(url('/auth/login.php'));
    }
}

function require_admin() {
    require_login();
    $u = current_user();
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo "<h3>Forbidden</h3><p>Admins only.</p>";
        exit;
    }
}

function require_student() {
    require_login();
    $u = current_user();
    if (($u['role'] ?? '') !== 'student') {
        http_response_code(403);
        echo "<h3>Forbidden</h3><p>Students only.</p>";
        exit;
    }
}
