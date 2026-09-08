<?php
session_start();
require '../database/config.php';
require '../includes/auth.php';
requireAdmin();

$pdo = getConnection();
$message = $_GET['message'] ?? null;
$statusMessage = $_GET['status'] ?? 'success';
$appStatuses = ['pending', 'approved', 'rejected', 'cancelled'];

function syncResidentRole(PDO $pdo, int $userId): void
{
    if (!$userId) return;
    $check = $pdo->prepare("SELECT COUNT(*) FROM room_applications WHERE user_id=:id AND status='approved'");
    $check->execute(['id'=>$userId]);
    $hasApproved = (int)$check->fetchColumn() > 0;
    $stmt = $pdo->prepare("UPDATE users SET role=:role WHERE id=:id AND role <> 'admin'");
    $stmt->execute(['role'=>$hasApproved ? 'resident' : 'applicant', 'id'=>$userId]);
}

function consumeRoomSlot(PDO $pdo, int $roomId): void
{
    $stmt = $pdo->prepare("UPDATE rooms SET available_slots=available_slots-1,status=CASE WHEN available_slots-1<=0 THEN 'full' ELSE 'available' END WHERE id=:id AND available_slots>0");
    $stmt->execute(['id'=>$roomId]);
    if ($stmt->rowCount() !== 1) throw new RuntimeException('The selected room has no available slots.');
}

function restoreRoomSlot(PDO $pdo, int $roomId): void
{
    $stmt = $pdo->prepare("UPDATE rooms SET available_slots=LEAST(capacity,available_slots+1),status=CASE WHEN status='maintenance' THEN 'maintenance' WHEN available_slots+1>=capacity THEN 'available' ELSE 'available' END WHERE id=:id");
    $stmt->execute(['id'=>$roomId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    try {
        $pdo->beginTransaction();
        $oldUserId = 0;
        if ($action === 'update' || $action === 'delete') {
            if (!$id) throw new RuntimeException('Invalid application ID.');
            $get = $pdo->prepare('SELECT * FROM room_applications WHERE id=:id FOR UPDATE');
            $get->execute(['id'=>$id]);
            $old = $get->fetch();
            if (!$old) throw new RuntimeException('Application not found.');
            $oldUserId = (int)$old['user_id'];
        }

        if ($action === 'create') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $roomId = (int)($_POST['room_id'] ?? 0);
            $moveIn = $_POST['move_in_date'] ?? '';
            $appStatus = $_POST['status'] ?? 'pending';
            $notes = trim($_POST['notes'] ?? '');
            if (!$userId || !$roomId || !in_array($appStatus,$appStatuses,true)) throw new RuntimeException('Please provide valid application details.');
            if ($appStatus === 'approved') consumeRoomSlot($pdo,$roomId);
            $ins = $pdo->prepare('INSERT INTO room_applications(user_id,room_id,move_in_date,status,notes) VALUES(:u,:r,:move,:status,:notes)');
            $ins->execute(['u'=>$userId,'r'=>$roomId,'move'=>$moveIn?:null,'status'=>$appStatus,'notes'=>$notes?:null]);
            syncResidentRole($pdo,$userId);
            $message='Room application created successfully.';
        } elseif ($action === 'update') {
            $newUserId = (int)($_POST['user_id'] ?? 0);
            $newRoomId = (int)($_POST['room_id'] ?? 0);
            $moveIn = $_POST['move_in_date'] ?? '';
            $newStatus = $_POST['status'] ?? 'pending';
            $notes = trim($_POST['notes'] ?? '');
            if (!$newUserId || !$newRoomId || !in_array($newStatus,$appStatuses,true)) throw new RuntimeException('Please provide valid application details.');

            $oldApproved = $old['status'] === 'approved';
            $newApproved = $newStatus === 'approved';
            $oldRoomId = (int)$old['room_id'];

            if ($oldApproved && (!$newApproved || $newRoomId !== $oldRoomId)) restoreRoomSlot($pdo,$oldRoomId);
            if ($newApproved && (!$oldApproved || $newRoomId !== $oldRoomId)) consumeRoomSlot($pdo,$newRoomId);

            $upd = $pdo->prepare('UPDATE room_applications SET user_id=:u,room_id=:r,move_in_date=:move,status=:status,notes=:notes WHERE id=:id');
            $upd->execute(['u'=>$newUserId,'r'=>$newRoomId,'move'=>$moveIn?:null,'status'=>$newStatus,'notes'=>$notes?:null,'id'=>$id]);
            syncResidentRole($pdo,$oldUserId);
            if ($newUserId !== $oldUserId) syncResidentRole($pdo,$newUserId);
            $message='Room application updated successfully.';
        } elseif ($action === 'delete') {
            if ($old['status'] === 'approved') restoreRoomSlot((int)$old['room_id']);
            $del = $pdo->prepare('DELETE FROM room_applications WHERE id=:id');
            $del->execute(['id'=>$id]);
            syncResidentRole($pdo,$oldUserId);
            $message='Room application deleted successfully.';
        }
        $pdo->commit();
        header('Location: room_applications.php?status=success&message=' . urlencode($message ?: 'Operation completed.'));
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header('Location: room_applications.php?status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

$editId = filter_input(INPUT_GET,'edit',FILTER_VALIDATE_INT) ?: 0;
$editRow = null;
if ($editId) {
    $stmt=$pdo->prepare('SELECT * FROM room_applications WHERE id=:id');
    $stmt->execute(['id'=>$editId]);
    $editRow=$stmt->fetch();
}
$users=$pdo->query("SELECT id,first_name,last_name,email FROM users WHERE role IN ('applicant','resident') ORDER BY first_name,last_name")->fetchAll();
$rooms=$pdo->query("SELECT r.id,r.room_number,r.room_type,r.monthly_rate,r.available_slots,r.capacity,r.status,d.location FROM rooms r JOIN dormitories d ON d.id=r.dormitory_id WHERE d.status='active' ORDER BY d.location,r.room_number")->fetchAll();
$rows=$pdo->query("SELECT ra.*,u.first_name,u.last_name,u.email,r.room_number,r.room_type,d.location FROM room_applications ra JOIN users u ON u.id=ra.user_id JOIN rooms r ON r.id=ra.room_id JOIN dormitories d ON d.id=r.dormitory_id ORDER BY FIELD(ra.status,'pending','approved','rejected','cancelled'),ra.created_at DESC")->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Room Applications | Admin</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<header class="admin-header"><a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a><div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div></header>
<div class="admin-layout"><aside><div class="side-title">ADMIN</div><nav><a href="dashboard.php">Dashboard</a><a href="viewing_requests.php">Viewing Requests</a><a class="active" href="room_applications.php">Room Applications</a><a href="rooms.php">Rooms</a><a href="dormitories.php">Dormitories</a><a href="users.php">Users</a></nav></aside>
<main class="admin-main"><h1>Room Applications</h1><?php if($message): ?><div class="alert <?= $statusMessage==='error'?'error':'success' ?>"><?= h($message) ?></div><?php endif; ?>
<div class="panel form-wide"><h2><?= $editRow?'Edit Room Application':'Create Room Application' ?></h2><form method="post" class="form-grid"><input type="hidden" name="action" value="<?= $editRow?'update':'create' ?>"><?php if($editRow): ?><input type="hidden" name="id" value="<?= h((string)$editRow['id']) ?>"><?php endif; ?>
<div><label>Customer</label><select name="user_id" required><option value="">Choose customer</option><?php foreach($users as $u): ?><option value="<?= h((string)$u['id']) ?>" <?= ((int)($editRow['user_id']??0)===(int)$u['id'])?'selected':'' ?>><?= h($u['first_name'].' '.$u['last_name'].' — '.$u['email']) ?></option><?php endforeach; ?></select></div>
<div><label>Room</label><select name="room_id" required><option value="">Choose room</option><?php foreach($rooms as $r): ?><option value="<?= h((string)$r['id']) ?>" <?= ((int)($editRow['room_id']??0)===(int)$r['id'])?'selected':'' ?>><?= h($r['location'].' / '.$r['room_number'].' — '.$r['room_type'].' — '.$r['available_slots'].' slots') ?></option><?php endforeach; ?></select></div>
<div><label>Move-in Date</label><input type="date" name="move_in_date" value="<?= h($editRow['move_in_date']??'') ?>"></div>
<div><label>Status</label><select name="status"><?php foreach($appStatuses as $v): ?><option value="<?= $v ?>" <?= (($editRow['status']??'pending')===$v)?'selected':'' ?>><?= ucfirst($v) ?></option><?php endforeach; ?></select></div>
<div><label>Notes</label><textarea name="notes"><?= h($editRow['notes']??'') ?></textarea></div>
<div class="form-actions"><button class="primary" type="submit"><?= $editRow?'Save Changes':'Create Application' ?></button><?php if($editRow): ?><a class="secondary-button" href="room_applications.php">Cancel Edit</a><?php endif; ?></div>
</form></div>
<div class="panel table-wrap"><h2>All Room Applications</h2><table><thead><tr><th>Customer</th><th>Room</th><th>Move-in</th><th>Notes</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= h($r['first_name'].' '.$r['last_name']) ?><br><small><?= h($r['email']) ?></small></td><td><?= h($r['location'].' / '.$r['room_number']) ?><br><small><?= h($r['room_type']) ?></small></td><td><?= h($r['move_in_date']?:'Not specified') ?></td><td><?= h($r['notes']?:'—') ?></td><td><span class="status <?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span></td><td class="action-cell"><a class="secondary-button" href="room_applications.php?edit=<?= h((string)$r['id']) ?>">Edit</a><form method="post" class="inline-form" onsubmit="return confirm('Delete this application? Approved applications restore one room slot.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h((string)$r['id']) ?>"><button class="danger-button" type="submit">Delete</button></form></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="6">No room applications yet.</td></tr><?php endif; ?></tbody></table></div>
</main></div></body></html>
