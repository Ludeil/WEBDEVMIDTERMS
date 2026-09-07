<?php
session_start(); require '../database/config.php'; require '../includes/auth.php'; requireAdmin();
$pdo = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if ($id && in_array($action, ['approved','rejected','cancelled','pending'], true)) {
        try {
            $pdo->beginTransaction();
            $get = $pdo->prepare("SELECT ra.id, ra.user_id, ra.room_id, ra.status, r.available_slots FROM room_applications ra JOIN rooms r ON r.id = ra.room_id WHERE ra.id = :id FOR UPDATE");
            $get->execute(['id' => $id]);
            $app = $get->fetch();

            if (!$app) {
                throw new RuntimeException('Application not found.');
            }

            // Only a pending application consumes a slot when approved.
            if ($action === 'approved') {
                if ($app['status'] === 'approved') {
                    throw new RuntimeException('Application is already approved.');
                }
                if ((int)$app['available_slots'] <= 0) {
                    throw new RuntimeException('The selected room has no available slots.');
                }

                $check = $pdo->prepare("SELECT id FROM room_applications WHERE user_id = :user_id AND status = 'approved' AND id <> :id LIMIT 1");
                $check->execute(['user_id' => $app['user_id'], 'id' => $id]);
                if ($check->fetch()) {
                    throw new RuntimeException('This customer already has an approved room application.');
                }

                $update = $pdo->prepare("UPDATE room_applications SET status='approved' WHERE id=:id");
                $update->execute(['id' => $id]);
                $room = $pdo->prepare("UPDATE rooms SET available_slots = available_slots - 1, status = CASE WHEN available_slots - 1 <= 0 THEN 'full' ELSE 'available' END WHERE id = :room_id AND available_slots > 0");
                $room->execute(['room_id' => $app['room_id']]);
                $resident = $pdo->prepare("UPDATE users SET role='resident' WHERE id=:user_id AND role='applicant'");
                $resident->execute(['user_id' => $app['user_id']]);
            } elseif ($action === 'pending') {
                // Re-opening an application after approval would require restoring a room slot,
                // so do not allow an approved application to be silently returned to pending.
                if ($app['status'] !== 'pending') {
                    throw new RuntimeException('Only pending applications can remain pending.');
                }
            } else {
                $update = $pdo->prepare('UPDATE room_applications SET status=:status WHERE id=:id');
                $update->execute(['status' => $action, 'id' => $id]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header('Location: room_applications.php?status=error&message=' . urlencode($e->getMessage()));
            exit;
        }
    }

    header('Location: room_applications.php?message=' . urlencode('Room application updated.'));
    exit;
}
$rows = $pdo->query("SELECT ra.*, u.first_name, u.last_name, u.email, r.room_number, r.room_type, d.location FROM room_applications ra JOIN users u ON u.id=ra.user_id JOIN rooms r ON r.id=ra.room_id JOIN dormitories d ON d.id=r.dormitory_id ORDER BY FIELD(ra.status,'pending','approved','rejected','cancelled'), ra.created_at DESC")->fetchAll();
$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? 'success';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Room Applications | Admin</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body><header class="admin-header"><a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a><div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div></header><div class="admin-layout"><aside><div class="side-title">ADMIN</div><nav><a href="dashboard.php">Dashboard</a><a href="viewing_requests.php">Viewing Requests</a><a class="active" href="room_applications.php">Room Applications</a><a href="rooms.php">Rooms</a><a href="dormitories.php">Dormitories</a><a href="users.php">Users</a></nav></aside><main class="admin-main"><h1>Room Applications</h1><?php if($message): ?><div class="alert <?= $status === 'error' ? 'error' : 'success' ?>"><?=h($message)?></div><?php endif; ?><div class="panel table-wrap"><table><thead><tr><th>Customer</th><th>Room</th><th>Move-in</th><th>Notes</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?=h($r['first_name'].' '.$r['last_name'])?><br><small><?=h($r['email'])?></small></td><td><?=h($r['location'])?> / <?=h($r['room_number'])?><br><small><?=h($r['room_type'])?></small></td><td><?=h($r['move_in_date'] ?: 'Not specified')?></td><td><?=h($r['notes'] ?: '—')?></td><td><span class="status <?=h($r['status'])?>"><?=h(ucfirst($r['status']))?></span></td><td><form method="post" class="inline-actions"><input type="hidden" name="id" value="<?=h((string)$r['id'])?>"><select name="action"><option value="approved">Approve</option><option value="rejected">Reject</option><option value="cancelled">Cancel</option><option value="pending">Set Pending</option></select><button>Update</button></form></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="6">No room applications yet.</td></tr><?php endif; ?></tbody></table></div></main></div></body></html>
