<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pdo = db();

$event_id = (int)($_GET['event_id'] ?? 0);

// event list dropdown
$events = $pdo->query("SELECT id, title, start_datetime FROM events ORDER BY start_datetime DESC")->fetchAll();

$selected_event = null;
$registrations = [];

if ($event_id) {
    $stEv = $pdo->prepare("
        SELECT e.*, d.name AS department_name, v.name AS venue_name,
               (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS reg_count
        FROM events e
        JOIN departments d ON d.id = e.department_id
        LEFT JOIN venues v ON v.id = e.venue_id
        WHERE e.id = ? LIMIT 1
    ");
    $stEv->execute([$event_id]);
    $selected_event = $stEv->fetch();

    if ($selected_event) {
        $st = $pdo->prepare("
            SELECT r.created_at AS registered_at, u.full_name, u.email
            FROM event_registrations r
            JOIN users u ON u.id = r.user_id
            WHERE r.event_id = ?
            ORDER BY r.created_at DESC
        ");
        $st->execute([$event_id]);
        $registrations = $st->fetchAll();
    }
}

$page_title = 'View Registrations';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>View Registrations</h1>
    <div class="muted">See which students registered for each event.</div>
</div>

<div class="card form-card wide">
    <form method="get" action="">
        <label>Select Event</label>
        <select name="event_id" onchange="this.form.submit()">
            <option value="">-- Choose an event --</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= (int)$ev['id'] ?>" <?= ((int)$event_id === (int)$ev['id']) ? 'selected' : '' ?>>
                    <?= e($ev['title']) ?> (<?= e(date('d M Y', strtotime($ev['start_datetime']))) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <noscript><button class="btn" type="submit">View</button></noscript>
    </form>
</div>

<?php if ($selected_event): ?>
    <?php
        $remaining = max(0, (int)$selected_event['capacity'] - (int)$selected_event['reg_count']);
    ?>
    <div class="card">
        <div class="card-head">
            <h2><?= e($selected_event['title']) ?></h2>
            <a class="btn" href="<?= e(url('/admin/edit_event.php?id=' . (int)$selected_event['id'])) ?>">Edit Event</a>
        </div>

        <div class="meta-row">
            <div><span class="meta-label">Department:</span> <?= e($selected_event['department_name']) ?></div>
            <div><span class="meta-label">Venue:</span> <?= e($selected_event['venue_name'] ?: 'TBA') ?></div>
            <div><span class="meta-label">Start:</span> <?= e(fmt_dt($selected_event['start_datetime'])) ?></div>
            <div><span class="meta-label">Seats:</span> <?= e($remaining) ?> remaining / <?= e((int)$selected_event['capacity']) ?></div>
        </div>

        <?php if (!$registrations): ?>
            <p class="muted">No one has registered yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $r): ?>
                            <tr>
                                <td><?= e($r['full_name']) ?></td>
                                <td><?= e($r['email']) ?></td>
                                <td><?= e(fmt_dt($r['registered_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
