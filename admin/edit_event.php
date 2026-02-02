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

$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$venues = $pdo->query("SELECT id, name, location FROM venues ORDER BY name")->fetchAll();

$st = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
$st->execute([$id]);
$event = $st->fetch();
if (!$event) {
    flash_set('error', 'Event not found.');
    redirect(url('/admin/manage_events.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $venue_id = (int)($_POST['venue_id'] ?? 0);
    $start_datetime = trim($_POST['start_datetime'] ?? '');
    $end_datetime = trim($_POST['end_datetime'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $is_closed = isset($_POST['is_closed']) ? 1 : 0;

    if ($title === '' || $description === '' || !$department_id || $start_datetime === '' || $end_datetime === '') {
        flash_set('error', 'Please fill in all required fields.');
        redirect(url('/admin/edit_event.php?id=' . $id));
    }

    if ($capacity < 1) {
        flash_set('error', 'Capacity must be at least 1.');
        redirect(url('/admin/edit_event.php?id=' . $id));
    }

    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        flash_set('error', 'End time must be after start time.');
        redirect(url('/admin/edit_event.php?id=' . $id));
    }

    $venue_id = $venue_id > 0 ? $venue_id : null;

    // if registrations already exceed new capacity, close it
    $stCount = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?");
    $stCount->execute([$id]);
    $reg_count = (int)$stCount->fetchColumn();
    if ($reg_count >= $capacity) {
        $is_closed = 1;
    }

    $stUp = $pdo->prepare("UPDATE events SET title=?, description=?, department_id=?, venue_id=?, start_datetime=?, end_datetime=?, capacity=?, is_closed=? WHERE id=?");
    $stUp->execute([$title, $description, $department_id, $venue_id, $start_datetime, $end_datetime, $capacity, $is_closed, $id]);

    flash_set('success', 'Event updated.');
    redirect(url('/admin/manage_events.php'));
}

// for form display
function dt_local($mysql_dt) {
    if (!$mysql_dt) return '';
    $t = strtotime($mysql_dt);
    return $t ? date('Y-m-d\TH:i', $t) : '';
}

$page_title = 'Edit Event';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>Edit Event</h1>
    <div class="muted">Update details, capacity and venue.</div>
</div>

<div class="card form-card wide">
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Event Title <span class="req">*</span></label>
                <input type="text" name="title" value="<?= e($event['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Department <span class="req">*</span></label>
                <select name="department_id" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int)$d['id'] ?>" <?= ((int)$event['department_id'] === (int)$d['id']) ? 'selected' : '' ?>>
                            <?= e($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Venue (optional)</label>
                <select name="venue_id">
                    <option value="">-- TBA --</option>
                    <?php foreach ($venues as $v): ?>
                        <option value="<?= (int)$v['id'] ?>" <?= ((int)$event['venue_id'] === (int)$v['id']) ? 'selected' : '' ?>>
                            <?= e($v['name']) ?><?= $v['location'] ? ' - ' . e($v['location']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Capacity <span class="req">*</span></label>
                <input type="number" name="capacity" min="1" value="<?= e((int)$event['capacity']) ?>" required>
            </div>

            <div class="form-group">
                <label>Start Date & Time <span class="req">*</span></label>
                <input type="datetime-local" name="start_datetime" value="<?= e(dt_local($event['start_datetime'])) ?>" required>
            </div>

            <div class="form-group">
                <label>End Date & Time <span class="req">*</span></label>
                <input type="datetime-local" name="end_datetime" value="<?= e(dt_local($event['end_datetime'])) ?>" required>
            </div>

            <div class="form-group form-group-full">
                <label>Description <span class="req">*</span></label>
                <textarea name="description" rows="5" required><?= e($event['description']) ?></textarea>
            </div>

            <div class="form-group form-group-full">
                <label class="checkbox">
                    <input type="checkbox" name="is_closed" value="1" <?= ((int)$event['is_closed'] === 1) ? 'checked' : '' ?>>
                    Manually close registration for this event
                </label>
                <div class="muted small">If the event is full, the system will close it automatically.</div>
            </div>
        </div>

        <div class="btn-row">
            <button class="btn primary" type="submit">Save Changes</button>
            <a class="btn" href="<?= e(url('/admin/manage_events.php')) ?>">Back</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
