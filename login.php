<?php
// login.php — handles GET (show form) and POST (process login)

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/UserModel.php';

// If already logged in, go straight to dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (empty($username) || empty($password)) {
        $error = 'Please fill in both username and password.';
    } else {
        $user = UserModel::login($username, $password);
        if ($user) {
            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: ' . BASE_PATH . '/dashboard.php');
            exit;
        } else {
            $error = 'Incorrect username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AgriOrder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h2>🌾 market slip order</h2>
        <p class="subtitle">Cooperative market slip order</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/login.php" novalidate>
            <div class="form-group" style="margin-bottom:1rem">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= e($_POST['username'] ?? '') ?>"
                       placeholder="Enter your username" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">
                Sign In →
            </button>
        </form>

        <p style="margin-top:1rem; font-size:0.78rem; color:var(--muted); text-align:center">
            Default: <strong>admin</strong> / <strong>password</strong> — change after first login
        </p>
    </div>
</div>
<script src="<?= BASE_PATH ?>/public/js/app.js"></script>
</body>
</html>
