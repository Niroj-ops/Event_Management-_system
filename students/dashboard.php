<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_student();

$pdo = db();
$user = current_user();

// my registrations count
$st = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id=?");
$st->execute([$user['id']]);
$my_total = (int)$st->fetchColumn();

// upcoming registrations count
$st = $pdo->prepare("
    SELECT COUNT(*)
    FROM event_registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id=? AND e.start_datetime >= NOW()
");
$st->execute([$user['id']]);
$my_upcoming = (int)$st->fetchColumn();

// next 5 upcoming events (for browse)
$st = $pdo->prepare("
    SELECT e.*, d.name AS department_name, v.name AS venue_name,
           (SELECT COUNT(*) FROM event_registrations rr WHERE rr.event_id = e.id) AS reg_count,
           (SELECT COUNT(*) FROM event_registrations r2 WHERE r2.event_id = e.id AND r2.user_id = ?) AS is_registered
    FROM events e
    JOIN departments d ON d.id = e.department_id
    LEFT JOIN venues v ON v.id = e.venue_id
    WHERE e.start_datetime >= NOW()
    ORDER BY e.start_datetime ASC
    LIMIT 5
");
$st->execute([$user['id']]);
$events = $st->fetchAll();

$page_title = 'Student Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>Student Dashboard</h1>
    <div class="muted">Welcome, <?= e($user['full_name']) ?>. Browse and register for upcoming events.</div>
</div>

<div class="stats">
    <div class="stat card">
        <div class="stat-num"><?= e($my_total) ?></div>
        <div class="stat-label">My Total Registrations</div>
    </div>
    <div class="stat card">
        <div class="stat-num"><?= e($my_upcoming) ?></div>
        <div class="stat-label">My Upcoming Events</div>
    </div>
    <div class="stat card">
        <div class="stat-num"><?= e(date('Y')) ?></div>
        <div class="stat-label">Academic Year</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Upcoming Events</h2>
        <div class="btn-row">
            <a class="btn primary" href="<?= e(url('/students/browse_events.php')) ?>">Browse All</a>
            <a class="btn" href="<?= e(url('/students/my_events.php')) ?>">My Events</a>
        </div>
    </div>

    <?php if (!$events): ?>
        <p class="muted">No upcoming events at the moment.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Department</th>
                        <th>Start</th>
                        <th>Seats</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev):
                        $remaining = max(0, (int)$ev['capacity'] - (int)$ev['reg_count']);
                        $closed = ((int)$ev['is_closed'] === 1) || ($remaining === 0);
                    ?>
                        <tr>
                            <td>
                                <div class="t-strong"><?= e($ev['title']) ?></div>
                                <div class="muted small"><?= e($ev['venue_name'] ?: 'Venue TBA') ?></div>
                            </td>
                            <td><?= e($ev['department_name']) ?></td>
                            <td><?= e(fmt_dt($ev['start_datetime'])) ?></td>
                            <td>
                                <?= e($remaining) ?> remaining
                                <?php if ($closed): ?><span class="badge danger">Closed</span><?php else: ?><span class="badge">Open</span><?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <?php if ((int)$ev['is_registered'] > 0): ?>
                                    <form method="post" action="<?= e(url('/students/register_event.php')) ?>" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button class="btn small danger" type="submit">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('/students/register_event.php')) ?>" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                        <input type="hidden" name="action" value="register">
                                        <button class="btn small primary" type="submit" <?= $closed ? 'disabled' : '' ?>>Register</button>
                                    </form>
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
