
<?php

session_start();

require '../database/config.php';
require '../includes/auth.php';

requireAdmin();

$pdo = getConnection();

$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? 'success';

$viewStatuses = [
    'pending',
    'confirmed',
    'completed',
    'cancelled'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

    try {

        if ($action === 'create') {

            $userId = (int) ($_POST['user_id'] ?? 0);
            $dormitoryId = (int) ($_POST['dormitory_id'] ?? 0);
            $date = $_POST['preferred_date'] ?? '';
            $time = $_POST['preferred_time'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            $requestStatus = $_POST['status'] ?? 'pending';

            if (
                !$userId ||
                !$dormitoryId ||
                !$date ||
                !$time ||
                !in_array(
                    $requestStatus,
                    $viewStatuses,
                    true
                )
            ) {
                throw new RuntimeException(
                    'Please complete all required viewing request fields.'
                );
            }

            $stmt = $pdo->prepare(
                'INSERT INTO viewing_requests(
                    user_id,
                    dormitory_id,
                    preferred_date,
                    preferred_time,
                    status,
                    notes
                ) VALUES(
                    :u,
                    :d,
                    :date,
                    :time,
                    :status,
                    :notes
                )'
            );

            $stmt->execute([
                'u' => $userId,
                'd' => $dormitoryId,
                'date' => $date,
                'time' => $time,
                'status' => $requestStatus,
                'notes' => $notes !== '' ? $notes : null
            ]);

            $message = 'Viewing request created successfully.';

        } elseif ($action === 'update' && $id) {

            $userId = (int) ($_POST['user_id'] ?? 0);
            $dormitoryId = (int) ($_POST['dormitory_id'] ?? 0);
            $date = $_POST['preferred_date'] ?? '';
            $time = $_POST['preferred_time'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            $requestStatus = $_POST['status'] ?? 'pending';

            if (
                !$userId ||
                !$dormitoryId ||
                !$date ||
                !$time ||
                !in_array(
                    $requestStatus,
                    $viewStatuses,
                    true
                )
            ) {
                throw new RuntimeException(
                    'Please complete all required viewing request fields.'
                );
            }

            $stmt = $pdo->prepare(
                'UPDATE viewing_requests
                 SET
                    user_id = :u,
                    dormitory_id = :d,
                    preferred_date = :date,
                    preferred_time = :time,
                    status = :status,
                    notes = :notes
                 WHERE id = :id'
            );

            $stmt->execute([
                'u' => $userId,
                'd' => $dormitoryId,
                'date' => $date,
                'time' => $time,
                'status' => $requestStatus,
                'notes' => $notes !== '' ? $notes : null,
                'id' => $id
            ]);

            $message = 'Viewing request updated successfully.';

        } elseif ($action === 'delete' && $id) {

            $stmt = $pdo->prepare(
                'DELETE FROM viewing_requests
                 WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id
            ]);

            $message = 'Viewing request deleted successfully.';
        }

        header(
            'Location: viewing_requests.php?status=success&message=' .
            urlencode(
                $message ?: 'Operation completed.'
            )
        );

        exit;

    } catch (Throwable $e) {

        header(
            'Location: viewing_requests.php?status=error&message=' .
            urlencode($e->getMessage())
        );

        exit;
    }
}

$editId = filter_input(
    INPUT_GET,
    'edit',
    FILTER_VALIDATE_INT
) ?: 0;

$editRow = null;

if ($editId) {

    $stmt = $pdo->prepare(
        'SELECT *
         FROM viewing_requests
         WHERE id = :id'
    );

    $stmt->execute([
        'id' => $editId
    ]);

    $editRow = $stmt->fetch();
}

$users = $pdo->query(
    "SELECT
        id,
        username,
        first_name,
        last_name,
        email
     FROM users
     WHERE role IN ('applicant', 'resident')
     ORDER BY first_name, last_name"
)->fetchAll();

$dorms = $pdo->query(
    "SELECT
        id,
        name,
        location
     FROM dormitories
     WHERE status = 'active'
     ORDER BY location, name"
)->fetchAll();

$rows = $pdo->query(
    "SELECT
        vr.*,
        u.first_name,
        u.last_name,
        u.email,
        d.name AS dormitory_name,
        d.location
     FROM viewing_requests vr
     JOIN users u
        ON u.id = vr.user_id
     JOIN dormitories d
        ON d.id = vr.dormitory_id
     ORDER BY
        vr.preferred_date DESC,
        vr.preferred_time DESC,
        vr.created_at DESC"
)->fetchAll();

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Viewing Requests | Admin
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<header class="admin-header">

    <a
        href="../index.php"
        class="brand-link"
    >

        <img
            src="../assets/images/logo-dark.svg"
            alt="Obeda Dormitories"
        >

    </a>

    <div>

        <span>
            Administrator
        </span>

        <a
            class="logout"
            href="../logout.php"
        >
            Logout
        </a>

    </div>

</header>

<div class="admin-layout">

    <aside>

        <div class="side-title">
            ADMIN
        </div>

        <nav>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a
                class="active"
                href="viewing_requests.php"
            >
                Viewing Requests
            </a>

            <a href="room_applications.php">
                Room Applications
            </a>

            <a href="rooms.php">
                Rooms
            </a>

            <a href="dormitories.php">
                Dormitories
            </a>

            <a href="users.php">
                Users
            </a>

        </nav>

    </aside>

    <main class="admin-main">

        <h1>
            Viewing Requests
        </h1>

        <?php if ($message): ?>

            <div
                class="alert <?= $status === 'error' ? 'error' : 'success' ?>"
            >
                <?= h($message) ?>
            </div>

        <?php endif; ?>

        <div class="panel form-wide">

            <h2>
                <?= $editRow
                    ? 'Edit Viewing Request'
                    : 'Create Viewing Request'
                ?>
            </h2>

            <form
                method="post"
                class="form-grid"
            >

                <input
                    type="hidden"
                    name="action"
                    value="<?= $editRow ? 'update' : 'create' ?>"
                >

                <?php if ($editRow): ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= h((string) $editRow['id']) ?>"
                    >

                <?php endif; ?>

                <div>

                    <label>
                        Customer
                    </label>

                    <select
                        name="user_id"
                        required
                    >

                        <option value="">
                            Choose customer
                        </option>

                        <?php foreach ($users as $u): ?>

                            <option
                                value="<?= h((string) $u['id']) ?>"
                                <?= (
                                    (int) ($editRow['user_id'] ?? 0)
                                    === (int) $u['id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= h(
                                    $u['first_name'] . ' ' .
                                    $u['last_name'] . ' — ' .
                                    $u['email']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label>
                        Dormitory
                    </label>

                    <select
                        name="dormitory_id"
                        required
                    >

                        <option value="">
                            Choose dormitory
                        </option>

                        <?php foreach ($dorms as $d): ?>

                            <option
                                value="<?= h((string) $d['id']) ?>"
                                <?= (
                                    (int) ($editRow['dormitory_id'] ?? 0)
                                    === (int) $d['id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= h(
                                    $d['location'] . ' — ' .
                                    $d['name']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label>
                        Preferred Date
                    </label>

                    <input
                        type="date"
                        name="preferred_date"
                        required
                        value="<?= h(
                            $editRow['preferred_date'] ?? ''
                        ) ?>"
                    >

                </div>

                <div>

                    <label>
                        Preferred Time
                    </label>

                    <input
                        type="time"
                        name="preferred_time"
                        required
                        value="<?= h(
                            isset($editRow['preferred_time'])
                                ? substr(
                                    $editRow['preferred_time'],
                                    0,
                                    5
                                )
                                : ''
                        ) ?>"
                    >

                </div>

                <div>

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <?php foreach ($viewStatuses as $v): ?>

                            <option
                                value="<?= $v ?>"
                                <?= (
                                    ($editRow['status'] ?? 'pending')
                                    === $v
                                ) ? 'selected' : '' ?>
                            >
                                <?= ucfirst($v) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label>
                        Notes
                    </label>

                    <textarea name="notes"><?= h(
                        $editRow['notes'] ?? ''
                    ) ?></textarea>

                </div>

                <div class="form-actions">

                    <button
                        class="primary"
                        type="submit"
                    >
                        <?= $editRow
                            ? 'Save Changes'
                            : 'Create Request'
                        ?>
                    </button>

                    <?php if ($editRow): ?>

                        <a
                            class="secondary-button"
                            href="viewing_requests.php"
                        >
                            Cancel Edit
                        </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>

        <div class="panel table-wrap">

            <h2>
                All Viewing Requests
            </h2>

            <table>

                <thead>

                    <tr>

                        <th>
                            Customer
                        </th>

                        <th>
                            Location
                        </th>

                        <th>
                            Date / Time
                        </th>

                        <th>
                            Notes
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <td>

                                <?= h(
                                    $r['first_name'] . ' ' .
                                    $r['last_name']
                                ) ?>

                                <br>

                                <small>
                                    <?= h($r['email']) ?>
                                </small>

                            </td>

                            <td>

                                <?= h($r['location']) ?>

                                <br>

                                <small>
                                    <?= h($r['dormitory_name']) ?>
                                </small>

                            </td>

                            <td>

                                <?= h($r['preferred_date']) ?>

                                <br>

                                <?= h(
                                    substr(
                                        $r['preferred_time'],
                                        0,
                                        5
                                    )
                                ) ?>

                            </td>

                            <td>

                                <?= h(
                                    $r['notes'] ?: '—'
                                ) ?>

                            </td>

                            <td>

                                <span
                                    class="status <?= h($r['status']) ?>"
                                >
                                    <?= h(
                                        ucfirst($r['status'])
                                    ) ?>
                                </span>

                            </td>

                            <td class="action-cell">

                                <a
                                    class="secondary-button"
                                    href="viewing_requests.php?edit=<?= h(
                                        (string) $r['id']
                                    ) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    class="inline-form"
                                    onsubmit="return confirm('Delete this viewing request?');"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= h(
                                            (string) $r['id']
                                        ) ?>"
                                    >

                                    <button
                                        class="danger-button"
                                        type="submit"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$rows): ?>

                        <tr>

                            <td colspan="6">
                                No viewing requests yet.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

</body>

</html>

