<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    $u = current_user();
    if ($u['role'] === 'admin') redirect(url('/admin/dashboard.php'));
    redirect(url('/students/dashboard.php'));
}

$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        flash_set('error', 'Please enter email and password.');
        redirect(url('/auth/login.php'));
    }

    $pdo = db();
    $st = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    $user = $st->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash_set('error', 'Invalid email or password.');
        redirect(url('/auth/login.php'));
    }

    // login ok - store in session (only what we need)
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    flash_set('success', 'Welcome, ' . $user['full_name'] . '!');
    if ($user['role'] === 'admin') redirect(url('/admin/dashboard.php'));
    redirect(url('/students/dashboard.php'));
}

$page_title = 'Login';
include __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Login</h1>
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="btn primary" type="submit">Login</button>
    </form>
    <p class="muted small">No account? <a href="<?= e(url('/auth/register.php')) ?>">Register as student</a></p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
