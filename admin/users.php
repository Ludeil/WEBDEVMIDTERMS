<?php
session_start();
require '../database/config.php';
require '../includes/auth.php';
requireAdmin();

$pdo = getConnection();
$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? 'success';
$roles = ['applicant', 'resident', 'admin'];
$accountStatuses = ['active', 'inactive', 'suspended'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    try {
        if ($action === 'create') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'applicant';
            $accountStatus = $_POST['account_status'] ?? 'active';
            if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $firstName === '' || $lastName === '' || strlen($password) < 8 || !in_array($role, $roles, true) || !in_array($accountStatus, $accountStatuses, true)) {
                throw new RuntimeException('Please provide valid account information. Password must be at least 8 characters.');
            }
            $check = $pdo->prepare('SELECT id FROM users WHERE username=:username OR email=:email LIMIT 1');
            $check->execute(['username' => $username, 'email' => $email]);
            if ($check->fetch()) throw new RuntimeException('Username or email is already in use.');
            $stmt = $pdo->prepare('INSERT INTO users(username,email,password_hash,first_name,last_name,phone,role,account_status) VALUES(:username,:email,:password_hash,:first_name,:last_name,:phone,:role,:status)');
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone !== '' ? $phone : null,
                'role' => $role,
                'status' => $accountStatus,
            ]);
            $message = 'User created successfully.';
        } elseif ($action === 'update' && $id) {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'applicant';
            $accountStatus = $_POST['account_status'] ?? 'active';
            if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $firstName === '' || $lastName === '' || !in_array($role, $roles, true) || !in_array($accountStatus, $accountStatuses, true)) {
                throw new RuntimeException('Please provide valid account information.');
            }
            $check = $pdo->prepare('SELECT id FROM users WHERE (username=:username OR email=:email) AND id<>:id LIMIT 1');
            $check->execute(['username' => $username, 'email' => $email, 'id' => $id]);
            if ($check->fetch()) throw new RuntimeException('Username or email is already in use by another user.');

            $sql = 'UPDATE users SET username=:username,email=:email,first_name=:first_name,last_name=:last_name,phone=:phone,role=:role,account_status=:status';
            $params = ['username'=>$username,'email'=>$email,'first_name'=>$firstName,'last_name'=>$lastName,'phone'=>$phone !== '' ? $phone : null,'role'=>$role,'status'=>$accountStatus,'id'=>$id];
            if ($password !== '') {
                if (strlen($password) < 8) throw new RuntimeException('New password must be at least 8 characters.');
                $sql .= ', password_hash=:password_hash';
                $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ((int)($_SESSION['user_id'] ?? 0) === $id) {
                $_SESSION['first_name'] = $firstName;
                $_SESSION['role'] = $role;
            }
            $message = 'User updated successfully.';
        } elseif ($action === 'delete' && $id) {
            if ((int)($_SESSION['user_id'] ?? 0) === $id) throw new RuntimeException('You cannot delete the administrator account you are currently using.');
            $stmt = $pdo->prepare('DELETE FROM users WHERE id=:id AND role<>\'admin\'');
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() === 0) throw new RuntimeException('Administrator accounts are protected from deletion.');
            $message = 'User deleted successfully.';
        }
        header('Location: users.php?status=success&message=' . urlencode($message ?: 'Operation completed.'));
        exit;
    } catch (Throwable $e) {
        header('Location: users.php?status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$editRow = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=:id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch();
}
$rows = $pdo->query('SELECT id,username,email,first_name,last_name,phone,role,account_status,created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Users | Admin</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<header class="admin-header"><a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a><div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div></header>
<div class="admin-layout"><aside><div class="side-title">ADMIN</div><nav><a href="dashboard.php">Dashboard</a><a href="viewing_requests.php">Viewing Requests</a><a href="room_applications.php">Room Applications</a><a href="rooms.php">Rooms</a><a href="dormitories.php">Dormitories</a><a class="active" href="users.php">Users</a></nav></aside>
<main class="admin-main"><h1>Users</h1><?php if ($message): ?><div class="alert <?= $status === 'error' ? 'error' : 'success' ?>"><?= h($message) ?></div><?php endif; ?>
<div class="panel form-wide"><h2><?= $editRow ? 'Edit User' : 'Create User' ?></h2><form method="post" class="form-grid"><input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>"><?php if ($editRow): ?><input type="hidden" name="id" value="<?= h((string)$editRow['id']) ?>"><?php endif; ?>
<div><label>Username</label><input name="username" required value="<?= h($editRow['username'] ?? '') ?>"></div>
<div><label>Email</label><input type="email" name="email" required value="<?= h($editRow['email'] ?? '') ?>"></div>
<div><label>First Name</label><input name="first_name" required value="<?= h($editRow['first_name'] ?? '') ?>"></div>
<div><label>Last Name</label><input name="last_name" required value="<?= h($editRow['last_name'] ?? '') ?>"></div>
<div><label>Phone</label><input name="phone" value="<?= h($editRow['phone'] ?? '') ?>"></div>
<div><label>Password <?= $editRow ? '(leave blank to keep current)' : '' ?></label><input type="password" name="password" <?= $editRow ? '' : 'required' ?>></div>
<div><label>Role</label><select name="role"><?php foreach ($roles as $value): ?><option value="<?= $value ?>" <?= (($editRow['role'] ?? 'applicant') === $value) ? 'selected' : '' ?>><?= ucfirst($value) ?></option><?php endforeach; ?></select></div>
<div><label>Status</label><select name="account_status"><?php foreach ($accountStatuses as $value): ?><option value="<?= $value ?>" <?= (($editRow['account_status'] ?? 'active') === $value) ? 'selected' : '' ?>><?= ucfirst($value) ?></option><?php endforeach; ?></select></div>
<div class="form-actions"><button class="primary" type="submit"><?= $editRow ? 'Save Changes' : 'Create User' ?></button><?php if ($editRow): ?><a class="secondary-button" href="users.php">Cancel Edit</a><?php endif; ?></div>
</form></div>
<div class="panel table-wrap"><h2>All Users</h2><table><thead><tr><th>User</th><th>Contact</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= h($r['first_name'].' '.$r['last_name']) ?><br><small>@<?= h($r['username']) ?></small></td><td><?= h($r['email']) ?><br><small><?= h($r['phone'] ?: 'No phone') ?></small></td><td><?= h($r['role']) ?></td><td><span class="status <?= h($r['account_status']) ?>"><?= h(ucfirst($r['account_status'])) ?></span></td><td class="action-cell"><a class="secondary-button" href="users.php?edit=<?= h((string)$r['id']) ?>">Edit</a><?php if ($r['role'] !== 'admin'): ?><form method="post" class="inline-form" onsubmit="return confirm('Delete this user? Their related viewing/application records will also be removed by the database cascade.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h((string)$r['id']) ?>"><button class="danger-button" type="submit">Delete</button></form><?php else: ?><span class="protected-label">Protected</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</main></div></body></html>
