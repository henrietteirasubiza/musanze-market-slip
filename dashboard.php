<?php
// dashboard.php — key stats + recent orders

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/models/OrderModel.php';
require_once __DIR__ . '/models/SupplierModel.php';

$stats          = OrderModel::getTodayStats();
$recentOrders   = OrderModel::getRecent(5);
$totalSuppliers = SupplierModel::count();

$pageTitle = 'Dashboard';
include __DIR__ . '/views/partials/header.php';
?>

<div class="page-header">
    <h1>📊 Dashboard</h1>
    <p>Today is <?= date('l, F j, Y') ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-body">
            <div class="label">Orders Today</div>
            <div class="value"><?= $stats['total_orders'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-body">
            <div class="label">Value Today</div>
            <div class="value">RWF <?= number_format($stats['total_value'], 0) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-body">
            <div class="label">Total Suppliers</div>
            <div class="value"><?= $totalSuppliers ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗓️</div>
        <div class="stat-body">
            <div class="label">Avg. Order Value</div>
            <div class="value">
                <?php
                $avg = $stats['total_orders'] > 0
                    ? $stats['total_value'] / $stats['total_orders']
                    : 0;
                echo 'RWF ' . number_format($avg, 0);
                ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem">
        <h2 style="font-size:1rem; color:var(--green-dark)">📋 Recent Orders</h2>
        <a href="/orders.php" class="btn btn-sm btn-outline">View All</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p style="color:var(--muted); text-align:center; padding:2rem 0">
            No orders yet today. <a href="/orders.php?action=new">Create the first one →</a>
        </p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Pickup</th>
                    <th>Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td class="mono"><?= $o['id'] ?></td>
                    <td><?= e($o['supplier_name']) ?></td>
                    <td class="mono"><?= $o['quantity'] ?> <?= e($o['unit']) ?></td>
                    <td class="mono">RWF <?= number_format($o['unit_price'], 0) ?></td>
                    <td class="mono" style="font-weight:700; color:var(--green-dark)">
                        RWF <?= number_format($o['total_amount'], 0) ?>
                    </td>
                    <td><?= e($o['pickup_location']) ?></td>
                    <td style="color:var(--muted); font-size:0.8rem">
                        <?= date('H:i', strtotime($o['created_at'])) ?>
                    </td>
                    <td>
                        <a href="/orders.php?action=view&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">Receipt</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
