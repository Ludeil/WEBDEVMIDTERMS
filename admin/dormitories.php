<?php
session_start();
require '../database/config.php';
require '../includes/auth.php';
requireAdmin();

$pdo = getConnection();
$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $recordStatus = $_POST['status'] ?? 'active';
            if ($name === '' || $location === '' || !in_array($recordStatus, ['active', 'inactive'], true)) {
                throw new RuntimeException('Name, location, and a valid status are required.');
            }
            $stmt = $pdo->prepare('INSERT INTO dormitories(name,location,address,description,status) VALUES(:name,:location,:address,:description,:status)');
            $stmt->execute([
                'name' => $name,
                'location' => $location,
                'address' => $address !== '' ? $address : null,
                'description' => $description !== '' ? $description : null,
                'status' => $recordStatus,
            ]);
            $message = 'Dormitory added successfully.';
        } elseif ($action === 'update' && $id) {
            $name = trim($_POST['name'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $recordStatus = $_POST['status'] ?? 'active';
            if ($name === '' || $location === '' || !in_array($recordStatus, ['active', 'inactive'], true)) {
                throw new RuntimeException('Name, location, and a valid status are required.');
            }
            $stmt = $pdo->prepare('UPDATE dormitories SET name=:name, location=:location, address=:address, description=:description, status=:status WHERE id=:id');
            $stmt->execute([
                'name' => $name,
                'location' => $location,
                'address' => $address !== '' ? $address : null,
                'description' => $description !== '' ? $description : null,
                'status' => $recordStatus,
                'id' => $id,
            ]);
            $message = 'Dormitory updated successfully.';
        } elseif ($action === 'delete' && $id) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE dormitory_id=:id');
            $check->execute(['id' => $id]);
            if ((int)$check->fetchColumn() > 0) {
                throw new RuntimeException('This dormitory has rooms assigned to it. Move/delete those rooms first, then delete the dormitory.');
            }
            $stmt = $pdo->prepare('DELETE FROM dormitories WHERE id=:id');
            $stmt->execute(['id' => $id]);
            $message = 'Dormitory deleted successfully.';
        }
        header('Location: dormitories.php?status=success&message=' . urlencode($message ?: 'Operation completed.'));
        exit;
    } catch (Throwable $e) {
        header('Location: dormitories.php?status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$editRow = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM dormitories WHERE id=:id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch();
}
$rows = $pdo->query('SELECT * FROM dormitories ORDER BY location, name')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dormitories | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<header class="admin-header">
    <a href="../index.php" class="brand-link"><img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories"></a>
    <div><span>Administrator</span><a class="logout" href="../logout.php">Logout</a></div>
</header>
<div class="admin-layout">
    <aside>
        <div class="side-title">ADMIN</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="viewing_requests.php">Viewing Requests</a>
            <a href="room_applications.php">Room Applications</a>
            <a href="rooms.php">Rooms</a>
            <a class="active" href="dormitories.php">Dormitories</a>
            <a href="users.php">Users</a>
        </nav>
    </aside>
    <main class="admin-main">
        <h1>Dormitories</h1>
        <?php if ($message): ?><div class="alert <?= $status === 'error' ? 'error' : 'success' ?>"><?= h($message) ?></div><?php endif; ?>

        <div class="two-col">
            <section class="panel">
                <h2><?= $editRow ? 'Edit Dormitory' : 'Add Dormitory' ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
                    <?php if ($editRow): ?><input type="hidden" name="id" value="<?= h((string)$editRow['id']) ?>"><?php endif; ?>
                    <label>Name</label>
                    <input name="name" required value="<?= h($editRow['name'] ?? '') ?>">
                    <label>Location</label>
                    <input name="location" required value="<?= h($editRow['location'] ?? '') ?>">
                    <label>Address</label>
                    <input name="address" value="<?= h($editRow['address'] ?? '') ?>">
                    <label>Description</label>
                    <textarea name="description"><?= h($editRow['description'] ?? '') ?></textarea>
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['active','inactive'] as $value): ?>
                            <option value="<?= $value ?>" <?= (($editRow['status'] ?? 'active') === $value) ? 'selected' : '' ?>><?= ucfirst($value) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="primary" type="submit"><?= $editRow ? 'Save Changes' : 'Add Dormitory' ?></button>
                    <?php if ($editRow): ?><a class="secondary-button" href="dormitories.php">Cancel Edit</a><?php endif; ?>
                </form>
            </section>

            <section class="panel table-wrap">
                <h2>Existing Locations</h2>
                <table>
                    <thead><tr><th>Name</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= h($r['name']) ?></td>
                            <td><?= h($r['location']) ?></td>
                            <td><span class="status <?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span></td>
                            <td class="action-cell">
                                <a class="secondary-button" href="dormitories.php?edit=<?= h((string)$r['id']) ?>">Edit</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Delete this dormitory? This is only allowed when it has no rooms.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= h((string)$r['id']) ?>">
                                    <button class="danger-button" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; if (!$rows): ?>
                        <tr><td colspan="4">No dormitories have been added yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</div>
</body>
</html>
