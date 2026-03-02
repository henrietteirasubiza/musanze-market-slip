<?php
// users.php — admin-only user management

require_once __DIR__ . '/config/auth.php';
requireAdmin(); // only admins can access this page

require_once __DIR__ . '/models/UserModel.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'  => trim($_POST['username']  ?? ''),
        'password'  => $_POST['password']  ?? '',
        'role'      => in_array($_POST['role'] ?? '', ['admin','aggregator']) ? $_POST['role'] : 'aggregator',
        'full_name' => trim($_POST['full_name'] ?? ''),
    ];

    if (empty($data['username'])) $errors[] = 'Username is required.';
    if (empty($data['password'])) $errors[] = 'Password is required.';
    if (strlen($data['password']) < 6) $errors[] = 'Password must be at least 6 characters.';

    // Check for duplicate username
    if (empty($errors) && UserModel::findByUsername($data['username'])) {
        $errors[] = "Username '{$data['username']}' is already taken.";
    }

    if (empty($errors)) {
        UserModel::create($data);
        $success = "User '{$data['username']}' created successfully.";
    }
}

$users     = UserModel::getAll();
$pageTitle = 'User Management';
include __DIR__ . '/views/partials/header.php';
?>

<div class="page-header">
    <h1>🔑 User Management</h1>
    <p>Admin-only — create accounts for staff members</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:start">

    <!-- User list -->
    <div>
        <h2 style="font-size:1rem; margin-bottom:1rem; color:var(--green-dark)">Existing Users</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Since</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['full_name'] ?: '—') ?></td>
                        <td class="mono"><?= e($u['username']) ?></td>
                        <td>
                            <span class="badge-role <?= e($u['role']) ?>">
                                <?= e(ucfirst($u['role'])) ?>
                            </span>
                        </td>
                        <td style="font-size:0.78rem; color:var(--muted)">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create user form -->
    <div class="card">
        <h2 style="font-size:1rem; margin-bottom:1.25rem; color:var(--green-dark)">Add New User</h2>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="<?= BASE_PATH ?>/users.php" novalidate>
            <div class="form-group" style="margin-bottom:1rem">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="e.g. Jean Pierre">
            </div>
            <div class="form-group" style="margin-bottom:1rem">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username"
                       value="<?= e($_POST['username'] ?? '') ?>" placeholder="e.g. jean_p" required>
            </div>
            <div class="form-group" style="margin-bottom:1rem">
                <label for="password">Password * (min 6 chars)</label>
                <input type="password" id="password" name="password" placeholder="Set a strong password" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem">
                <label for="role">Role *</label>
                <select name="role" id="role">
                    <option value="aggregator" <?= ($_POST['role'] ?? '') === 'aggregator' ? 'selected' : '' ?>>Aggregator</option>
                    <option value="admin"      <?= ($_POST['role'] ?? '') === 'admin'      ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create User →</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
