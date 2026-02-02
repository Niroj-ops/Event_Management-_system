<?php
// includes/app.php
// Helpers to make links/CSS work on user-directory servers (case-sensitive)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Compute the base path for this application.
 * Works when deployed under /event_management/ or /~user/event_management/.
 */
function base_path(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $needle = '/event_management/';
    $pos = strpos($script, $needle);
    if ($pos !== false) {
        return substr($script, 0, $pos) . $needle;
    }
    return $needle;
}

/** Build an app URL for a relative path. */
function url(string $path = ''): string {
    return base_path() . ltrim($path, '/');
}

/** Redirect to an app path. */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}
