<?php
// includes/helpers.php
if (session_status() === PHP_SESSION_NONE) {
    // basic session settings
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// simple escape helper
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// base url so links work even if folder name changes
function base_url() {
    // try to detect the project root folder so links work from /auth, /admin, /students pages too
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $project = basename(dirname(__DIR__)); // should be: event_management

    $needle = '/' . $project . '/';
    $pos = strpos($script, $needle);

    // example script: /~np03cy4s250018/event_management/auth/login.php
    // we want base url: /~np03cy4s250018/event_management
    if ($pos !== false) {
        return substr($script, 0, $pos + strlen('/' . $project));
    }

    // fallback (works on localhost when project is in web root)
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return ($dir === '' || $dir === '/') ? '' : $dir;
}

function url($path) {
    if ($path === '' || $path[0] !== '/') $path = '/' . $path;
    return base_url() . $path;
}

function redirect($to) {
    header("Location: " . $to);
    exit;
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo "<h3>Method Not Allowed</h3>";
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check($token) {
    $ok = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    if (!$ok) {
        http_response_code(400);
        echo "<h3>Invalid Request</h3><p>CSRF token mismatch.</p>";
        exit;
    }
}

function flash_set($key, $msg) {
    $_SESSION['flash'][$key] = $msg;
}

function flash_get($key) {
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return '';
}

// small helper for date formatting
function fmt_dt($dt) {
    if (!$dt) return '';
    try {
        $d = new DateTime($dt);
        return $d->format('d M Y, H:i');
    } catch (Exception $e) {
        return $dt;
    }
}
