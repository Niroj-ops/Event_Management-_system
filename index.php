<?php
require_once __DIR__ . '/includes/helpers.php';

$page_title = 'University Event Portal';
include __DIR__ . '/includes/header.php';
?>

<div class="hero card">
    <h1>Welcome to the University Event Portal</h1>
    <p class="muted">
        Browse and register for department events, or manage events if you are an admin.
        This is a simple Core PHP project made for a university assessment.
    </p>

    <?php if (!is_logged_in()): ?>
        <div class="btn-row">
            <a class="btn primary" href="<?= e(url('/auth/login.php')) ?>">Login</a>
            <a class="btn" href="<?= e(url('/auth/register.php')) ?>">Register as Student</a>
        </div>
        <div class="note">
            <strong>Demo accounts (from database.sql):</strong><br>
            Admin: admin@university.edu / Admin@123<br>
            Student: student1@university.edu / Student@123
        </div>
    <?php else: ?>
        <?php if (current_user()['role'] === 'admin'): ?>
            <div class="btn-row">
                <a class="btn primary" href="<?= e(url('/admin/dashboard.php')) ?>">Go to Admin Dashboard</a>
                <a class="btn" href="<?= e(url('/admin/create_event.php')) ?>">Create Event</a>
            </div>
        <?php else: ?>
            <div class="btn-row">
                <a class="btn primary" href="<?= e(url('/students/browse_events.php')) ?>">Browse Events</a>
                <a class="btn" href="<?= e(url('/students/my_events.php')) ?>">My Events</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="grid-2">
    <div class="card">
        <h2>What you can do</h2>
        <ul class="list">
            <li>Students can browse upcoming events and register</li>
            <li>Registration closes automatically when an event is full</li>
            <li>Admins can create, edit, delete events and view registrations</li>
            <li>Venues and departments are linked properly (foreign keys)</li>
        </ul>
    </div>

    <div class="card">
        <h2>System rules</h2>
        <ul class="list">
            <li>No duplicate registrations allowed</li>
            <li>Remaining seats are shown on event lists</li>
            <li>Past events are separated from upcoming events</li>
            <li>All database access uses PDO prepared statements</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
