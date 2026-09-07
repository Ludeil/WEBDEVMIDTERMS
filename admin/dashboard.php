<?php
session_start();
require '../database/config.php';
require '../includes/auth.php';
requireAdmin();

$pdo = getConnection();
$pendingViewings = (int)$pdo->query("SELECT COUNT(*) FROM viewing_requests WHERE status = 'pending'")->fetchColumn();
$pendingApplications = (int)$pdo->query("SELECT COUNT(*) FROM room_applications WHERE status = 'pending'")->fetchColumn();
$activeCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('applicant','resident') AND account_status = 'active'")->fetchColumn();
$availableRooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available' AND available_slots > 0")->fetchColumn();
$status = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard | Obeda Dormitories</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<header class="admin-header"><a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a><div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div></header>
<div class="admin-layout"><aside><div class="side-title">ADMIN</div><nav><a class="active" href="dashboard.php">Dashboard</a><a href="viewing_requests.php">Viewing Requests</a><a href="room_applications.php">Room Applications</a><a href="rooms.php">Rooms</a><a href="dormitories.php">Dormitories</a><a href="users.php">Users</a></nav></aside><main class="admin-main">
<h1>Dashboard</h1><?php if($message): ?><div class="alert <?= $status === 'error' ? 'error' : 'success' ?>"><?= h($message) ?></div><?php endif; ?>
<div class="welcome">Welcome, <?= h($_SESSION['first_name'] ?? 'Administrator') ?>.</div>
<div class="stats"><div><strong><?= $pendingViewings ?></strong><span>Pending Viewings</span></div><div><strong><?= $pendingApplications ?></strong><span>Pending Applications</span></div><div><strong><?= $activeCustomers ?></strong><span>Active Customers</span></div><div><strong><?= $availableRooms ?></strong><span>Available Rooms</span></div></div>
<section class="panel"><h2>Management</h2><div class="quick-links"><a href="viewing_requests.php">Manage viewing requests</a><a href="room_applications.php">Manage room applications</a><a href="rooms.php">Manage rooms</a><a href="users.php">Manage customer accounts</a></div></section>
</main></div></body></html>
