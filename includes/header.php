<?php
// includes/header.php
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'University Event Management') ?></title>
    <link rel="stylesheet" href="<?= e(url('/css/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <div class="brand">
            <a href="<?= e(url('/index.php')) ?>" class="brand-link">University Event Portal</a>
            <div class="brand-sub">Department Events & Registrations</div>
        </div>

        <nav class="nav">
            <a href="<?= e(url('/index.php')) ?>">Home</a>

            <?php if (is_logged_in()): ?>
                <?php if (current_user()['role'] === 'admin'): ?>
                    <a href="<?= e(url('/admin/dashboard.php')) ?>">Admin Dashboard</a>
                    <a href="<?= e(url('/admin/manage_events.php')) ?>">Manage Events</a>
                <?php else: ?>
                    <a href="<?= e(url('/students/dashboard.php')) ?>">Student Dashboard</a>
                    <a href="<?= e(url('/students/browse_events.php')) ?>">Browse Events</a>
                    <a href="<?= e(url('/students/my_events.php')) ?>">My Events</a>
                <?php endif; ?>
                <a href="<?= e(url('/auth/logout.php')) ?>" class="nav-right">Logout</a>
            <?php else: ?>
                <a href="<?= e(url('/auth/login.php')) ?>" class="nav-right">Login</a>
                <a href="<?= e(url('/auth/register.php')) ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <?php if ($msg = flash_get('success')): ?>
        <div class="alert success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash_get('error')): ?>
        <div class="alert danger"><?= e($msg) ?></div>
    <?php endif; ?>
