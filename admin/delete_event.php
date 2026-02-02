<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    flash_set('error', 'Event not found.');
    redirect(url('/admin/manage_events.php'));
}

$st = $pdo->prepare("SELECT e.*, d.name AS department_name FROM events e JOIN departments d ON d.id=e.department_id WHERE e.id=? LIMIT 1");
$st->execute([$id]);
$event = $st->fetch();

if (!$event) {
    flash_set('error', 'Event not found.');
    redirect(url('/admin/manage_events.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $stDel = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stDel->execute([$id]);

    flash_set('success', 'Event deleted.');
    redirect(url('/admin/manage_events.php'));
}

$page_title = 'Delete Event';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h1>Delete Event</h1>
    <p>Are you sure you want to delete this event?</p>

    <div class="info-box">
        <div class="t-strong"><?= e($event['title']) ?></div>
        <div class="muted"><?= e($event['department_name']) ?> • <?= e(fmt_dt($event['start_datetime'])) ?></div>
    </div>

    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="btn-row">
            <button class="btn danger" type="submit">Yes, Delete</button>
            <a class="btn" href="<?= e(url('/admin/manage_events.php')) ?>">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
