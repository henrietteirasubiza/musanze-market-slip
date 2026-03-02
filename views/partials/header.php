<?php
// views/partials/header.php
// Call this at the top of every page: include __DIR__ . '/partials/header.php';
// $pageTitle should be set before including.
$pageTitle = $pageTitle ?? 'Order Management System';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — AgriOrder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/style.css">
</head>
<body>

<nav class="navbar">
    <a class="brand" href="<?= BASE_PATH ?>/index.php">🌾 AgriOrder</a>

    <!-- quick filter for navigation links -->
    <input type="text" class="nav-filter" placeholder="Filter…" aria-label="Filter navigation">

    <ul class="nav-links">
        <li><a href="<?= BASE_PATH ?>/index.php">Home</a></li>
        <li><a href="<?= BASE_PATH ?>/dashboard.php">Dashboard</a></li>
        <li><a href="<?= BASE_PATH ?>/orders.php">Orders</a></li>
        <li><a href="<?= BASE_PATH ?>/suppliers.php">Suppliers</a></li>
        <?php if ($user['role'] === 'admin'): ?>
        <li><a href="<?= BASE_PATH ?>/users.php">Users</a></li>
        <?php endif; ?>
    </ul>
    <div class="nav-user">
        <span class="badge-role <?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span>
        <span><?= e($user['name'] ?: $user['username']) ?></span>
        <a href="<?= BASE_PATH ?>/logout.php" class="btn btn-sm btn-outline">Logout</a>
    </div>
</nav>

<main class="container">
