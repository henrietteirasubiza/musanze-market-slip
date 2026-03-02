<?php
// index.php — Home / landing page

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/models/OrderModel.php';
require_once __DIR__ . '/models/SupplierModel.php';

$stats     = OrderModel::getTodayStats();
$pageTitle = 'Home';
include __DIR__ . '/views/partials/header.php';
?>

<div class="hero">
    <h1>Welcome back, <?= e(currentUser()['name'] ?: currentUser()['username']) ?> 👋</h1>
    <p>Manage your cooperative's orders, suppliers, and daily activity — all in one place.</p>
    <div class="btn-group">
        <a href="<?= BASE_PATH ?>/orders.php?action=new" class="btn btn-amber">+ New Order</a>
        <a href="<?= BASE_PATH ?>/dashboard.php" class="btn btn-outline" style="border-color:rgba(255,255,255,0.5); color:#fff">View Dashboard</a>
    </div>
</div>

<div class="quick-links">
    <a href="<?= BASE_PATH ?>/orders.php?action=new" class="quick-card">
        <div class="qc-icon">📦</div>
        <div>
            <h3>Create Order</h3>
            <p>Record a new supplier order with auto-total</p>
        </div>
    </a>
    <a href="<?= BASE_PATH ?>/suppliers.php?action=new" class="quick-card">
        <div class="qc-icon">👤</div>
        <div>
            <h3>Register Supplier</h3>
            <p>Add a new supplier to the system</p>
        </div>
    </a>
    <a href="<?= BASE_PATH ?>/orders.php" class="quick-card">
        <div class="qc-icon">📋</div>
        <div>
            <h3>All Orders</h3>
            <p>Browse, edit, and print receipts</p>
        </div>
    </a>
    <a href="<?= BASE_PATH ?>/dashboard.php" class="quick-card">
        <div class="qc-icon">📊</div>
        <div>
            <h3>Dashboard</h3>
            <p>Today's totals and recent activity</p>
        </div>
    </a>
    <a href="<?= BASE_PATH ?>/suppliers.php" class="quick-card">
        <div class="qc-icon">🤝</div>
        <div>
            <h3>Suppliers</h3>
            <p><?= SupplierModel::count() ?> registered so far</p>
        </div>
    </a>
    <?php if (currentUser()['role'] === 'admin'): ?>
    <a href="<?= BASE_PATH ?>/users.php" class="quick-card">
        <div class="qc-icon">🔑</div>
        <div>
            <h3>User Management</h3>
            <p>Add or manage admin &amp; aggregator accounts</p>
        </div>
    </a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
