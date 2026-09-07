<?php
session_start(); require '../database/config.php'; require '../includes/auth.php'; requireAdmin();
$pdo = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $allowed = ['confirmed' => 'confirmed', 'completed' => 'completed', 'cancelled' => 'cancelled', 'pending' => 'pending'];
    if ($id && isset($allowed[$action])) {
        $stmt = $pdo->prepare('UPDATE viewing_requests SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $allowed[$action], 'id' => $id]);
    }
    header('Location: viewing_requests.php?message=' . urlencode('Viewing request updated.')); exit;
}
$rows = $pdo->query("SELECT vr.*, u.username, u.first_name, u.last_name, u.email, d.name AS dormitory_name, d.location FROM viewing_requests vr JOIN users u ON u.id=vr.user_id JOIN dormitories d ON d.id=vr.dormitory_id ORDER BY FIELD(vr.status,'pending','confirmed','completed','cancelled'), vr.preferred_date, vr.preferred_time DESC")->fetchAll();
$message = $_GET['message'] ?? null;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Viewing Requests | Admin</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<header class="admin-header"><a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a><div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div></header><div class="admin-layout"><aside><div class="side-title">ADMIN</div><nav><a href="dashboard.php">Dashboard</a><a class="active" href="viewing_requests.php">Viewing Requests</a><a href="room_applications.php">Room Applications</a><a href="rooms.php">Rooms</a><a href="dormitories.php">Dormitories</a><a href="users.php">Users</a></nav></aside><main class="admin-main"><h1>Viewing Requests</h1><?php if($message): ?><div class="alert success"><?=h($message)?></div><?php endif; ?><div class="panel table-wrap"><table><thead><tr><th>Customer</th><th>Location</th><th>Date / Time</th><th>Notes</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?=h($r['first_name'].' '.$r['last_name'])?><br><small><?=h($r['email'])?></small></td><td><?=h($r['location'])?></td><td><?=h($r['preferred_date'])?><br><?=h(substr($r['preferred_time'],0,5))?></td><td><?=h($r['notes'] ?: '—')?></td><td><span class="status <?=h($r['status'])?>"><?=h(ucfirst($r['status']))?></span></td><td><form method="post" class="inline-actions"><input type="hidden" name="id" value="<?=h((string)$r['id'])?>"><select name="action"><option value="confirmed">Confirm</option><option value="completed">Complete</option><option value="cancelled">Cancel</option><option value="pending">Set Pending</option></select><button>Update</button></form></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="6">No viewing requests yet.</td></tr><?php endif; ?></tbody></table></div></main></div></body></html>
