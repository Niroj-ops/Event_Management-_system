<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('/students/my_events.php'));
csrf_check($_POST['csrf'] ?? null);

$pdo = db();
$user = current_user();
$reg_id = (int)($_POST['reg_id'] ?? 0);

if ($reg_id <= 0) {
    flash_set('error', 'Invalid request.');
    redirect(url('/students/my_events.php'));
}

$del = $pdo->prepare("DELETE FROM event_registrations WHERE id=? AND user_id=?");
$del->execute([$reg_id, (int)$user['id']]);

flash_set('success', 'Registration cancelled.');
redirect(url('/students/my_events.php'));
