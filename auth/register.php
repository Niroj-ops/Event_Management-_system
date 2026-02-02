<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    $u = current_user();
    if ($u['role'] === 'admin') redirect(url('/admin/dashboard.php'));
    redirect(url('/students/dashboard.php'));
}

$full_name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if ($full_name === '' || $email === '' || $password === '') {
        flash_set('error', 'All fields are required.');
        redirect(url('/auth/register.php'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Please enter a valid email.');
        redirect(url('/auth/register.php'));
    }

    if (strlen($password) < 6) {
        flash_set('error', 'Password must be at least 6 characters.');
        redirect(url('/auth/register.php'));
    }

    if ($password !== $password2) {
        flash_set('error', 'Passwords do not match.');
        redirect(url('/auth/register.php'));
    }

    $pdo = db();

    // check if email already exists
    $st = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) {
        flash_set('error', 'This email is already registered. Please login.');
        redirect(url('/auth/login.php'));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $st = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'student', NOW())");
    $st->execute([$full_name, $email, $hash]);

    flash_set('success', 'Registration successful. Please login.');
    redirect(url('/auth/login.php'));
}

$page_title = 'Student Registration';
include __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Student Registration</h1>
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= e($full_name) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="password2" required>

        <button class="btn primary" type="submit">Create Account</button>
    </form>
    <p class="muted small">Already have an account? <a href="<?= e(url('/auth/login.php')) ?>">Login</a></p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
