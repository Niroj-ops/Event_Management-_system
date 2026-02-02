<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_student();

$pdo = db();
$user = current_user();

$tab = $_GET['tab'] ?? 'upcoming';
$tab = in_array($tab, ['upcoming','past'], true) ? $tab : 'upcoming';

$where = ($tab === 'past') ? "e.start_datetime < NOW()" : "e.start_datetime >= NOW()";

$st = $pdo->prepare("
    SELECT e.*, d.name AS department_name, v.name AS venue_name, r.created_at AS registered_at,
           (SELECT COUNT(*) FROM event_registrations rr WHERE rr.event_id = e.id) AS reg_count
    FROM event_registrations r
    JOIN events e ON e.id = r.event_id
    JOIN departments d ON d.id = e.department_id
    LEFT JOIN venues v ON v.id = e.venue_id
    WHERE r.user_id = ? AND $where
    ORDER BY e.start_datetime " . ($tab === 'past' ? "DESC" : "ASC")
);
$st->execute([$user['id']]);
$events = $st->fetchAll();

$page_title = 'My Events';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>My Events</h1>
    <div class="tabs">
        <a class="tab <?= $tab==='upcoming'?'active':'' ?>" href="<?= e(url('/students/my_events.php?tab=upcoming')) ?>">Upcoming</a>
        <a class="tab <?= $tab==='past'?'active':'' ?>" href="<?= e(url('/students/my_events.php?tab=past')) ?>">Past</a>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2><?= $tab === 'past' ? 'Past Registrations' : 'Upcoming Registrations' ?></h2>
        <a class="btn" href="<?= e(url('/students/browse_events.php')) ?>">Browse Events</a>
    </div>

    <?php if (!$events): ?>
        <p class="muted">No events found in this section.</p>
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
                        $closed = ((int)$ev['is_closed'] === 1) || ($remaining === 0) || (strtotime($ev['start_datetime']) < time());
                    ?>
                        <tr>
                            <td>
                                <div class="t-strong"><?= e($ev['title']) ?></div>
                                <div class="muted small">Registered: <?= e(fmt_dt($ev['registered_at'])) ?></div>
                            </td>
                            <td><?= e($ev['department_name']) ?></td>
                            <td><?= e($ev['venue_name'] ?: 'TBA') ?></td>
                            <td><?= e(fmt_dt($ev['start_datetime'])) ?></td>
                            <td><?= e($remaining) ?> remaining / <?= e((int)$ev['capacity']) ?></td>
                            <td class="td-actions">
                                <?php if ($tab === 'upcoming'): ?>
                                    <form method="post" action="<?= e(url('/students/register_event.php')) ?>" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button class="btn small danger" type="submit">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
