<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_student();

require_post();
csrf_check($_POST['csrf_token'] ?? '');

$pdo = db();
$user = current_user();

$event_id = (int)($_POST['event_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$event_id || !in_array($action, ['register','cancel'], true)) {
    flash_set('error', 'Invalid request.');
    redirect(url('/students/browse_events.php'));
}

if ($action === 'cancel') {
    $st = $pdo->prepare("DELETE FROM event_registrations WHERE event_id=? AND user_id=?");
    $st->execute([$event_id, $user['id']]);

    // if event was closed because full, reopen if there is space now
    $stEv = $pdo->prepare("SELECT capacity, is_closed, start_datetime FROM events WHERE id=? LIMIT 1");
    $stEv->execute([$event_id]);
    $ev = $stEv->fetch();
    if ($ev && strtotime($ev['start_datetime']) >= time()) {
        $stCount = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id=?");
        $stCount->execute([$event_id]);
        $count = (int)$stCount->fetchColumn();
        if ($count < (int)$ev['capacity']) {
            $pdo->prepare("UPDATE events SET is_closed=0 WHERE id=?")->execute([$event_id]);
        }
    }

    flash_set('success', 'Registration cancelled.');
    redirect(url('/students/my_events.php'));
}

// REGISTER action
try {
    $pdo->beginTransaction();

    // lock the event row so capacity checks are safer
    $stEv = $pdo->prepare("SELECT * FROM events WHERE id=? FOR UPDATE");
    $stEv->execute([$event_id]);
    $ev = $stEv->fetch();

    if (!$ev) {
        $pdo->rollBack();
        flash_set('error', 'Event not found.');
        redirect(url('/students/browse_events.php'));
    }

    if ((int)$ev['is_closed'] === 1) {
        $pdo->rollBack();
        flash_set('error', 'Registration is closed for this event.');
        redirect(url('/students/browse_events.php'));
    }

    if (strtotime($ev['start_datetime']) < time()) {
        $pdo->rollBack();
        flash_set('error', 'This event is already in the past.');
        redirect(url('/students/browse_events.php?tab=past'));
    }

    // prevent duplicate registration
    $stDup = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id=? AND user_id=? LIMIT 1");
    $stDup->execute([$event_id, $user['id']]);
    if ($stDup->fetch()) {
        $pdo->rollBack();
        flash_set('error', 'You are already registered for this event.');
        redirect(url('/students/my_events.php'));
    }

    // check capacity
    $stCount = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id=?");
    $stCount->execute([$event_id]);
    $count = (int)$stCount->fetchColumn();

    if ($count >= (int)$ev['capacity']) {
        // close it for everyone if full
        $pdo->prepare("UPDATE events SET is_closed=1 WHERE id=?")->execute([$event_id]);

        $pdo->commit();
        flash_set('error', 'Sorry, this event is full.');
        redirect(url('/students/browse_events.php'));
    }

    // insert registration
    $stIns = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stIns->execute([$event_id, $user['id']]);

    // if it becomes full after insert, close it
    $count_after = $count + 1;
    if ($count_after >= (int)$ev['capacity']) {
        $pdo->prepare("UPDATE events SET is_closed=1 WHERE id=?")->execute([$event_id]);
    }

    $pdo->commit();

    flash_set('success', 'Successfully registered.');
    redirect(url('/students/my_events.php'));
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_set('error', 'Something went wrong. Please try again.');
    redirect(url('/students/browse_events.php'));
}
