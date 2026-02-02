<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Run this page ONCE after importing database.sql.
// It creates a default admin user if it does not exist.

$pdo = db();

$email = 'admin@university.local';
$pass = 'Admin@123';
$name = 'System Admin';

$chk = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$chk->execute([$email]);
$exists = $chk->fetch();

if (!$exists) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $ins = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,department_id,created_at) VALUES (?,?,?,?,NULL,NOW())");
    $ins->execute([$name, $email, $hash, 'admin']);
    flash_set('success', 'Admin created. Email: admin@university.local Password: Admin@123');
} else {
    flash_set('success', 'Admin already exists. You can login using admin@university.local');
}

redirect(url('/auth/login.php'));
