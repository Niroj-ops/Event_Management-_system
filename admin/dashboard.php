<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pdo = db();

// quick stats
$total_events = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_regs = (int)$pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
$total_students = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();

// upcoming events (next 8)
$st = $pdo->prepare("
    SELECT e.*, d.name AS department_name, v.name AS venue_name,
           (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS reg_count
    FROM events e
    JOIN departments d ON d.id = e.department_id
    LEFT JOIN venues v ON v.id = e.venue_id
    WHERE e.start_datetime >= NOW()
    ORDER BY e.start_datetime ASC
    LIMIT 8
");
$st->execute();
$events = $st->fetchAll();

$page_title = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>Admin Dashboard</h1>
    <div class="muted">Manage department events and view student registrations.</div>
</div>

<div class="stats">
    <div class="stat card">
        <div class="stat-num"><?= e($total_events) ?></div>
        <div class="stat-label">Total Events</div>
    </div>
    <div class="stat card">
        <div class="stat-num"><?= e($total_regs) ?></div>
        <div class="stat-label">Total Registrations</div>
    </div>
    <div class="stat card">
        <div class="stat-num"><?= e($total_students) ?></div>
        <div class="stat-label">Student Accounts</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Upcoming Events</h2>
        <div class="btn-row">
            <a class="btn primary" href="<?= e(url('/admin/create_event.php')) ?>">Create Event</a>
            <a class="btn" href="<?= e(url('/admin/manage_events.php')) ?>">Manage All</a>
        </div>
    </div>

    <?php if (!$events): ?>
        <p class="muted">No upcoming events found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Department</th>
                        <th>Venue</th>
                        <th>Start</th>
                        <th>Seats</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): 
                        $remaining = max(0, (int)$ev['capacity'] - (int)$ev['reg_count']);
                    ?>
                        <tr>
                            <td>
                                <div class="t-strong"><?= e($ev['title']) ?></div>
                                <div class="muted small"><?= e(substr($ev['description'], 0, 70)) ?><?= strlen($ev['description'])>70?'...':'' ?></div>
                            </td>
                            <td><?= e($ev['department_name']) ?></td>
                            <td><?= e($ev['venue_name'] ?: 'TBA') ?></td>
                            <td><?= e(fmt_dt($ev['start_datetime'])) ?></td>
                            <td>
                                <?= e($remaining) ?> remaining
                                <?php if ((int)$ev['is_closed'] === 1): ?>
                                    <span class="badge danger">Closed</span>
                                <?php elseif ($remaining === 0): ?>
                                    <span class="badge danger">Full</span>
                                <?php else: ?>
                                    <span class="badge">Open</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <a class="btn small" href="<?= e(url('/admin/edit_event.php?id=' . (int)$ev['id'])) ?>">Edit</a>
                                <a class="btn small" href="<?= e(url('/admin/view_registrations.php?event_id=' . (int)$ev['id'])) ?>">Registrations</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
