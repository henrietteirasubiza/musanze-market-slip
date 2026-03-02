<?php
// orders.php — list / create / edit / view / delete orders
// Uses action= query param to route between sub-views

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/models/OrderModel.php';
require_once __DIR__ . '/models/SupplierModel.php';

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errors  = [];
$success = '';

// ----------------------------------------------------------------
// POST handlers
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['_action'] ?? '';

    // --- Save new order ---
    if ($postAction === 'create') {
        $data = [
            'supplier_id'     => (int) ($_POST['supplier_id'] ?? 0),
            'quantity'        => (float) ($_POST['quantity'] ?? 0),
            'unit'            => in_array($_POST['unit'] ?? '', ['kg','sacks']) ? $_POST['unit'] : 'kg',
            'unit_price'      => (float) ($_POST['unit_price'] ?? 0),
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
        ];

        if ($data['supplier_id'] <= 0)    $errors[] = 'Please select a supplier.';
        if ($data['quantity'] <= 0)       $errors[] = 'Quantity must be greater than zero.';
        if ($data['unit_price'] <= 0)     $errors[] = 'Unit price must be greater than zero.';
        if (empty($data['pickup_location'])) $errors[] = 'Pickup location is required.';

        if (empty($errors)) {
            $newId = OrderModel::create($data, currentUser()['id']);
            header("Location: " . BASE_PATH . "/orders.php?action=view&id={$newId}&created=1");
            exit;
        }
        $action = 'new'; // fall back to form with errors

    // --- Update existing order ---
    } elseif ($postAction === 'update') {
        $editId = (int) ($_POST['order_id'] ?? 0);
        $data = [
            'supplier_id'     => (int) ($_POST['supplier_id'] ?? 0),
            'quantity'        => (float) ($_POST['quantity'] ?? 0),
            'unit'            => in_array($_POST['unit'] ?? '', ['kg','sacks']) ? $_POST['unit'] : 'kg',
            'unit_price'      => (float) ($_POST['unit_price'] ?? 0),
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
        ];

        if ($data['supplier_id'] <= 0)    $errors[] = 'Please select a supplier.';
        if ($data['quantity'] <= 0)       $errors[] = 'Quantity must be greater than zero.';
        if ($data['unit_price'] <= 0)     $errors[] = 'Unit price must be greater than zero.';
        if (empty($data['pickup_location'])) $errors[] = 'Pickup location is required.';

        if (empty($errors)) {
            OrderModel::update($editId, $data);
            header("Location: " . BASE_PATH . "/orders.php?action=view&id={$editId}&updated=1");
            exit;
        }
        $action = 'edit';
        $id = $editId;

    // --- Delete ---
    } elseif ($postAction === 'delete') {
        requireAdmin(); // only admins can delete
        $delId = (int) ($_POST['order_id'] ?? 0);
        OrderModel::delete($delId);
        header('Location: ' . BASE_PATH . '/orders.php?deleted=1');
        exit;
    }
}

// ----------------------------------------------------------------
// Render
// ----------------------------------------------------------------
$pageTitle = match($action) {
    'new'  => 'New Order',
    'edit' => 'Edit Order',
    'view' => 'Order Receipt',
    default => 'Orders'
};
include __DIR__ . '/views/partials/header.php';

// ---- LIST ----
if ($action === 'list'):
    $orders = OrderModel::getAll();
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end">
    <div>
        <h1>📦 Orders</h1>
        <p><?= count($orders) ?> total orders on record</p>
    </div>
    <a href="<?= BASE_PATH ?>/orders.php?action=new" class="btn btn-primary">+ New Order</a>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Order deleted successfully.</div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="card" style="text-align:center; padding:3rem">
        <p style="color:var(--muted); margin-bottom:1rem">No orders yet.</p>
        <a href="<?= BASE_PATH ?>/orders.php?action=new" class="btn btn-primary">Create First Order</a>
    </div>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#ID</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Pickup</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td class="mono"><?= $o['id'] ?></td>
                <td><?= e($o['supplier_name']) ?></td>
                <td class="mono"><?= $o['quantity'] ?> <?= e($o['unit']) ?></td>
                <td class="mono">RWF <?= number_format($o['unit_price'], 0) ?></td>
                <td class="mono" style="font-weight:700; color:var(--green-dark)">
                    RWF <?= number_format($o['total_amount'], 0) ?>
                </td>
                <td><?= e($o['pickup_location']) ?></td>
                <td style="font-size:0.8rem; color:var(--muted)">
                    <?= date('d M Y', strtotime($o['created_at'])) ?>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="<?= BASE_PATH ?>/orders.php?action=view&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">View</a>
                        <a href="<?= BASE_PATH ?>/orders.php?action=edit&id=<?= $o['id'] ?>" class="btn btn-sm btn-amber">Edit</a>
                        <?php if (currentUser()['role'] === 'admin'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-confirm="Delete order #<?= $o['id'] ?>? This cannot be undone.">
                                Del
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php

// ---- NEW ORDER FORM ----
elseif ($action === 'new'):
    $suppliers = SupplierModel::getAll();
    $old = $_POST; // re-populate on validation fail
?>
<div class="page-header">
    <h1>📦 Create New Order</h1>
    <p>Fill in the details below. The total is calculated for you automatically.</p>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card">
    <form method="POST" action="<?= BASE_PATH ?>/orders.php" novalidate>
        <input type="hidden" name="_action" value="create">
        <div class="form-grid">

            <div class="form-group full">
                <label for="supplier_id">Supplier *</label>
                <select name="supplier_id" id="supplier_id" required>
                    <option value="">— Select a supplier —</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= ($old['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['supplier_name']) ?> — <?= e($s['village_location']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($suppliers)): ?>
                    <small style="color:var(--danger)">
                        No suppliers yet. <a href="<?= BASE_PATH ?>/suppliers.php?action=new">Register one first →</a>
                    </small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                       value="<?= e($old['quantity'] ?? '') ?>" placeholder="e.g. 500" required>
            </div>

            <div class="form-group">
                <label for="unit">Unit *</label>
                <select name="unit" id="unit">
                    <option value="kg"    <?= ($old['unit'] ?? '') === 'kg'    ? 'selected' : '' ?>>Kilograms (kg)</option>
                    <option value="sacks" <?= ($old['unit'] ?? '') === 'sacks' ? 'selected' : '' ?>>Sacks</option>
                </select>
            </div>

            <div class="form-group">
                <label for="unit_price">Unit Price (RWF) *</label>
                <input type="number" id="unit_price" name="unit_price" step="0.01" min="0.01"
                       value="<?= e($old['unit_price'] ?? '') ?>" placeholder="e.g. 350" required>
            </div>

            <div class="form-group">
                <label>Auto-Computed Total</label>
                <div id="total_preview" class="total-preview">RWF 0</div>
            </div>

            <div class="form-group full">
                <label for="pickup_location">Pickup Location *</label>
                <input type="text" id="pickup_location" name="pickup_location"
                       value="<?= e($old['pickup_location'] ?? '') ?>"
                       placeholder="e.g. Kigali Warehouse, Gate 3" required>
            </div>

        </div>
        <div class="btn-group" style="margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">Save Order →</button>
            <a href="<?= BASE_PATH ?>/orders.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php

// ---- EDIT ORDER FORM ----
elseif ($action === 'edit'):
    $order     = OrderModel::getById($id);
    $suppliers = SupplierModel::getAll();
    if (!$order) { echo '<div class="alert alert-error">Order not found.</div>'; include __DIR__ . '/views/partials/footer.php'; exit; }
    $old = !empty($errors) ? $_POST : $order; // use POST data if we had validation errors
?>
<div class="page-header">
    <h1>✏️ Edit Order #<?= $id ?></h1>
    <p>Update the order details below.</p>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card">
    <form method="POST" action="<?= BASE_PATH ?>/orders.php" novalidate>
        <input type="hidden" name="_action" value="update">
        <input type="hidden" name="order_id" value="<?= $id ?>">
        <div class="form-grid">

            <div class="form-group full">
                <label for="supplier_id">Supplier *</label>
                <select name="supplier_id" id="supplier_id" required>
                    <option value="">— Select a supplier —</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= $old['supplier_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['supplier_name']) ?> — <?= e($s['village_location']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                       value="<?= e($old['quantity']) ?>" required>
            </div>

            <div class="form-group">
                <label for="unit">Unit *</label>
                <select name="unit" id="unit">
                    <option value="kg"    <?= $old['unit'] === 'kg'    ? 'selected' : '' ?>>Kilograms (kg)</option>
                    <option value="sacks" <?= $old['unit'] === 'sacks' ? 'selected' : '' ?>>Sacks</option>
                </select>
            </div>

            <div class="form-group">
                <label for="unit_price">Unit Price (RWF) *</label>
                <input type="number" id="unit_price" name="unit_price" step="0.01" min="0.01"
                       value="<?= e($old['unit_price']) ?>" required>
            </div>

            <div class="form-group">
                <label>Auto-Computed Total</label>
                <div id="total_preview" class="total-preview">
                    RWF <?= number_format($old['quantity'] * $old['unit_price'], 0) ?>
                </div>
            </div>

            <div class="form-group full">
                <label for="pickup_location">Pickup Location *</label>
                <input type="text" id="pickup_location" name="pickup_location"
                       value="<?= e($old['pickup_location']) ?>" required>
            </div>

        </div>
        <div class="btn-group" style="margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">Update Order →</button>
            <a href="<?= BASE_PATH ?>/orders.php?action=view&id=<?= $id ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php

// ---- VIEW / RECEIPT ----
elseif ($action === 'view'):
    $order = OrderModel::getById($id);
    if (!$order) { echo '<div class="alert alert-error">Order not found.</div>'; include __DIR__ . '/views/partials/footer.php'; exit; }
?>
<div class="page-header no-print" style="display:flex; justify-content:space-between; align-items:flex-end">
    <div>
        <h1>🧾 Order Receipt</h1>
        <p>Order #<?= $id ?></p>
    </div>
    <div class="btn-group">
        <button id="print-btn" class="btn btn-primary">🖨️ Print Receipt</button>
        <a href="<?= BASE_PATH ?>/orders.php?action=edit&id=<?= $id ?>" class="btn btn-amber no-print">Edit</a>
        <a href="<?= BASE_PATH ?>/orders.php" class="btn btn-outline no-print">← Back</a>
    </div>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success no-print">Order saved successfully! Here's your receipt.</div>
<?php elseif (isset($_GET['updated'])): ?>
    <div class="alert alert-success no-print">Order updated successfully.</div>
<?php endif; ?>

<div class="receipt">
    <div class="receipt-header">
        <h2>🌾 AgriOrder</h2>
        <p>Official Order Receipt</p>
        <p style="font-family:'DM Mono',monospace; font-size:0.75rem; margin-top:0.3rem; color:var(--muted)">
            #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
        </p>
    </div>

    <div class="receipt-row">
        <span class="rk">Supplier Name</span>
        <span class="rv"><?= e($order['supplier_name']) ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Phone</span>
        <span class="rv"><?= e($order['phone_number']) ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Cooperative</span>
        <span class="rv"><?= e($order['cooperative_name'] ?: '—') ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Pickup Location</span>
        <span class="rv"><?= e($order['pickup_location']) ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Quantity</span>
        <span class="rv"><?= $order['quantity'] ?> <?= e($order['unit']) ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Unit Price</span>
        <span class="rv">RWF <?= number_format($order['unit_price'], 0) ?></span>
    </div>
    <div class="receipt-row">
        <span class="rk">Date</span>
        <span class="rv"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
    </div>

    <div class="receipt-total">
        <span>TOTAL AMOUNT</span>
        <span class="rt-val">RWF <?= number_format($order['total_amount'], 0) ?></span>
    </div>

    <p style="text-align:center; font-size:0.75rem; color:var(--muted); margin-top:1.25rem">
        Thank you — AgriOrder System
    </p>
</div>

<?php endif; ?>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
