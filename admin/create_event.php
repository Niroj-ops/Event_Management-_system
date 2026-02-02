<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pdo = db();

// fetch departments and venues for dropdowns
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$venues = $pdo->query("SELECT id, name, location FROM venues ORDER BY name")->fetchAll();

$title = '';
$description = '';
$department_id = '';
$venue_id = '';
$start_datetime = '';
$end_datetime = '';
$capacity = '50';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $venue_id = (int)($_POST['venue_id'] ?? 0);
    $start_datetime = trim($_POST['start_datetime'] ?? '');
    $end_datetime = trim($_POST['end_datetime'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);

    if ($title === '' || $description === '' || !$department_id || $start_datetime === '' || $end_datetime === '') {
        flash_set('error', 'Please fill in all required fields.');
        redirect(url('/admin/create_event.php'));
    }

    if ($capacity < 1) {
        flash_set('error', 'Capacity must be at least 1.');
        redirect(url('/admin/create_event.php'));
    }

    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        flash_set('error', 'End time must be after start time.');
        redirect(url('/admin/create_event.php'));
    }

    // allow venue to be optional
    $venue_id = $venue_id > 0 ? $venue_id : null;

    $st = $pdo->prepare("INSERT INTO events (title, description, department_id, venue_id, start_datetime, end_datetime, capacity, is_closed, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    $st->execute([$title, $description, $department_id, $venue_id, $start_datetime, $end_datetime, $capacity]);

    flash_set('success', 'Event created successfully.');
    redirect(url('/admin/manage_events.php'));
}

$page_title = 'Create Event';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <h1>Create Event</h1>
    <div class="muted">Add a new department event and assign venue/capacity.</div>
</div>

<div class="card form-card wide">
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Event Title <span class="req">*</span></label>
                <input type="text" name="title" value="<?= e($title) ?>" required>
            </div>

            <div class="form-group">
                <label>Department <span class="req">*</span></label>
                <select name="department_id" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int)$d['id'] ?>" <?= ((int)$department_id === (int)$d['id']) ? 'selected' : '' ?>>
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
                        <option value="<?= (int)$v['id'] ?>" <?= ((int)$venue_id === (int)$v['id']) ? 'selected' : '' ?>>
                            <?= e($v['name']) ?><?= $v['location'] ? ' - ' . e($v['location']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Capacity <span class="req">*</span></label>
                <input type="number" name="capacity" min="1" value="<?= e($capacity) ?>" required>
            </div>

            <div class="form-group">
                <label>Start Date & Time <span class="req">*</span></label>
                <input type="datetime-local" name="start_datetime" value="<?= e($start_datetime) ?>" required>
            </div>

            <div class="form-group">
                <label>End Date & Time <span class="req">*</span></label>
                <input type="datetime-local" name="end_datetime" value="<?= e($end_datetime) ?>" required>
            </div>

            <div class="form-group form-group-full">
                <label>Description <span class="req">*</span></label>
                <textarea name="description" rows="5" required><?= e($description) ?></textarea>
            </div>
        </div>

        <div class="btn-row">
            <button class="btn primary" type="submit">Create Event</button>
            <a class="btn" href="<?= e(url('/admin/manage_events.php')) ?>">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
