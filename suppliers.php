<?php
// suppliers.php — list / create / edit / delete suppliers

require_once __DIR__ . '/config/auth.php';
requireLogin();

require_once __DIR__ . '/models/SupplierModel.php';

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errors = [];

// ----------------------------------------------------------------
// POST handlers
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['_action'] ?? '';

    $data = [
        'supplier_name'    => trim($_POST['supplier_name']    ?? ''),
        'phone_number'     => trim($_POST['phone_number']     ?? ''),
        'village_location' => trim($_POST['village_location'] ?? ''),
        'cooperative_name' => trim($_POST['cooperative_name'] ?? ''),
    ];

    if (empty($data['supplier_name']))    $errors[] = 'Supplier name is required.';
    if (empty($data['phone_number']))     $errors[] = 'Phone number is required.';
    if (empty($data['village_location'])) $errors[] = 'Village/location is required.';

    if ($postAction === 'create' && empty($errors)) {
        $newId = SupplierModel::create($data);
        header("Location: " . BASE_PATH . "/suppliers.php?created=1");
        exit;

    } elseif ($postAction === 'update' && empty($errors)) {
        $editId = (int) ($_POST['supplier_id'] ?? 0);
        SupplierModel::update($editId, $data);
        header("Location: " . BASE_PATH . "/suppliers.php?updated=1");
        exit;

    } elseif ($postAction === 'delete') {
        requireAdmin();
        $delId  = (int) ($_POST['supplier_id'] ?? 0);
        $result = SupplierModel::delete($delId);
        if ($result === true) {
            header('Location: ' . BASE_PATH . '/suppliers.php?deleted=1');
        } else {
            header('Location: ' . BASE_PATH . '/suppliers.php?delerror=' . urlencode($result));
        }
        exit;
    }

    // If we hit validation errors, stay on the form
    $action = ($postAction === 'create') ? 'new' : 'edit';
}

// ----------------------------------------------------------------
// Render
// ----------------------------------------------------------------
$pageTitle = match($action) {
    'new'  => 'Register Supplier',
    'edit' => 'Edit Supplier',
    default => 'Suppliers'
};
include __DIR__ . '/views/partials/header.php';

if ($action === 'list'):
    $suppliers = SupplierModel::getAll();
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end">
    <div>
        <h1>👤 Suppliers</h1>
        <p><?= count($suppliers) ?> supplier(s) registered</p>
    </div>
    <a href="<?= BASE_PATH ?>/suppliers.php?action=new" class="btn btn-primary">+ Register Supplier</a>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">Supplier registered successfully.</div>
<?php elseif (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Supplier updated successfully.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Supplier deleted.</div>
<?php elseif (isset($_GET['delerror'])): ?>
    <div class="alert alert-error"><?= e(urldecode($_GET['delerror'])) ?></div>
<?php endif; ?>

<?php if (empty($suppliers)): ?>
    <div class="card" style="text-align:center; padding:3rem">
        <p style="color:var(--muted); margin-bottom:1rem">No suppliers registered yet.</p>
        <a href="<?= BASE_PATH ?>/suppliers.php?action=new" class="btn btn-primary">Register First Supplier</a>
    </div>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Village / Location</th>
                <th>Cooperative</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($suppliers as $s): ?>
            <tr>
                <td class="mono"><?= $s['id'] ?></td>
                <td style="font-weight:600"><?= e($s['supplier_name']) ?></td>
                <td class="mono"><?= e($s['phone_number']) ?></td>
                <td><?= e($s['village_location']) ?></td>
                <td><?= e($s['cooperative_name'] ?: '—') ?></td>
                <td style="font-size:0.8rem; color:var(--muted)">
                    <?= date('d M Y', strtotime($s['created_at'])) ?>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="<?= BASE_PATH ?>/suppliers.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-amber">Edit</a>
                        <?php if (currentUser()['role'] === 'admin'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="supplier_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-confirm="Delete <?= e($s['supplier_name']) ?>?">
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

// ---- NEW / EDIT FORM ----
else:
    $supplier = ($action === 'edit') ? SupplierModel::getById($id) : null;
    if ($action === 'edit' && !$supplier) {
        echo '<div class="alert alert-error">Supplier not found.</div>';
        include __DIR__ . '/views/partials/footer.php';
        exit;
    }
    $old = !empty($errors) ? $_POST : ($supplier ?? []);
?>
<div class="page-header">
    <h1><?= $action === 'edit' ? '✏️ Edit Supplier' : '👤 Register New Supplier' ?></h1>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:700px">
    <form method="POST" action="<?= BASE_PATH ?>/suppliers.php" novalidate>
        <input type="hidden" name="_action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="supplier_id" value="<?= $id ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="supplier_name">Supplier Name *</label>
                <input type="text" id="supplier_name" name="supplier_name"
                       value="<?= e($old['supplier_name'] ?? '') ?>"
                       placeholder="Full name of the supplier" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number *</label>
                <input type="text" id="phone_number" name="phone_number"
                       value="<?= e($old['phone_number'] ?? '') ?>"
                       placeholder="e.g. +250 788 123 456" required>
            </div>

            <div class="form-group">
                <label for="village_location">Village / Location *</label>
                <input type="text" id="village_location" name="village_location"
                       value="<?= e($old['village_location'] ?? '') ?>"
                       placeholder="e.g. Musanze, Northern Province" required>
            </div>

            <div class="form-group">
                <label for="cooperative_name">Cooperative Name</label>
                <input type="text" id="cooperative_name" name="cooperative_name"
                       value="<?= e($old['cooperative_name'] ?? '') ?>"
                       placeholder="Optional — leave blank if none">
            </div>
        </div>

        <div class="btn-group" style="margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">
                <?= $action === 'edit' ? 'Update Supplier →' : 'Register Supplier →' ?>
            </button>
            <a href="<?= BASE_PATH ?>/suppliers.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php endif; ?>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
