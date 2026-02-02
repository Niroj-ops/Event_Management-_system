<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pdo = db();

$tab = $_GET['tab'] ?? 'upcoming';
$tab = in_array($tab, ['upcoming', 'past'], true) ? $tab : 'upcoming';

$where = ($tab === 'past') ? "e.start_datetime < NOW()" : "e.start_datetime >= NOW()";

$st = $pdo->prepare("
    SELECT e.*, d.name AS department_name, v.name AS venue_name,
           (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS reg_count
    FROM events e
    JOIN departments d ON d.id = e.department_id
    LEFT JOIN venues v ON v.id = e.venue_id
    WHERE $where
    ORDER BY e.start_datetime " . ($tab === 'past' ? "DESC" : "ASC")
);
$st->execute();
$events = $st->fetchAll();

$page_title = 'Manage Events';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>Manage Events</h1>
    <div class="tabs">
        <a class="tab <?= $tab==='upcoming'?'active':'' ?>" href="<?= e(url('/admin/manage_events.php?tab=upcoming')) ?>">Upcoming</a>
        <a class="tab <?= $tab==='past'?'active':'' ?>" href="<?= e(url('/admin/manage_events.php?tab=past')) ?>">Past</a>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2><?= $tab === 'past' ? 'Past Events' : 'Upcoming Events' ?></h2>
        <a class="btn primary" href="<?= e(url('/admin/create_event.php')) ?>">Create Event</a>
    </div>

    <?php if (!$events): ?>
        <p class="muted">No events found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Department</th>
                        <th>Venue</th>
                        <th>Start</th>
                        <th>Capacity</th>
                        <th>Registrations</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td>
                                <div class="t-strong"><?= e($ev['title']) ?></div>
                                <div class="muted small"><?= e(substr($ev['description'], 0, 70)) ?><?= strlen($ev['description'])>70?'...':'' ?></div>
                            </td>
                            <td><?= e($ev['department_name']) ?></td>
                            <td><?= e($ev['venue_name'] ?: 'TBA') ?></td>
                            <td><?= e(fmt_dt($ev['start_datetime'])) ?></td>
                            <td><?= e((int)$ev['capacity']) ?></td>
                            <td>
                                <?= e((int)$ev['reg_count']) ?>
                                <?php if ((int)$ev['is_closed'] === 1): ?>
                                    <span class="badge danger">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <a class="btn small" href="<?= e(url('/admin/edit_event.php?id=' . (int)$ev['id'])) ?>">Edit</a>
                                <a class="btn small" href="<?= e(url('/admin/view_registrations.php?event_id=' . (int)$ev['id'])) ?>">Registrations</a>
                                <a class="btn small danger" href="<?= e(url('/admin/delete_event.php?id=' . (int)$ev['id'])) ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
