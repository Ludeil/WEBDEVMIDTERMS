<?php

session_start();

require '../database/config.php';
require '../includes/auth.php';

requireAdmin();

$pdo = getConnection();

$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? 'success';

$types = [
    '4_person' => '4 person',
    'female_exclusive' => 'Female exclusive',
    'other' => 'Other'
];

$statuses = [
    'available',
    'full',
    'maintenance',
    'inactive'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    try {

        if ($action === 'create' || ($action === 'update' && $id)) {

            $dormitoryId = (int) ($_POST['dormitory_id'] ?? 0);
            $roomNumber = trim($_POST['room_number'] ?? '');
            $roomType = $_POST['room_type'] ?? '4_person';
            $capacity = max(1, (int) ($_POST['capacity'] ?? 4));
            $rate = max(0, (float) ($_POST['monthly_rate'] ?? 0));
            $slots = max(
                0,
                min(
                    $capacity,
                    (int) ($_POST['available_slots'] ?? 0)
                )
            );

            $roomStatus = $_POST['status']
                ?? ($slots > 0 ? 'available' : 'full');

            if (
                !$dormitoryId ||
                $roomNumber === '' ||
                !isset($types[$roomType]) ||
                !in_array($roomStatus, $statuses, true)
            ) {
                throw new RuntimeException(
                    'Please provide valid room details.'
                );
            }

            $checkDorm = $pdo->prepare(
                "SELECT id
                 FROM dormitories
                 WHERE id = :id
                 AND status = 'active'"
            );

            $checkDorm->execute([
                'id' => $dormitoryId
            ]);

            if (!$checkDorm->fetch()) {
                throw new RuntimeException(
                    'Please choose an active dormitory.'
                );
            }

            if ($action === 'create') {

                $stmt = $pdo->prepare(
                    'INSERT INTO rooms(
                        dormitory_id,
                        room_number,
                        room_type,
                        capacity,
                        monthly_rate,
                        available_slots,
                        status
                    ) VALUES(
                        :d,
                        :n,
                        :t,
                        :c,
                        :r,
                        :s,
                        :status
                    )'
                );

                $stmt->execute([
                    'd' => $dormitoryId,
                    'n' => $roomNumber,
                    't' => $roomType,
                    'c' => $capacity,
                    'r' => $rate,
                    's' => $slots,
                    'status' => $roomStatus
                ]);

                $message = 'Room added successfully.';

            } else {

                $stmt = $pdo->prepare(
                    'UPDATE rooms
                     SET
                        dormitory_id = :d,
                        room_number = :n,
                        room_type = :t,
                        capacity = :c,
                        monthly_rate = :r,
                        available_slots = :s,
                        status = :status
                     WHERE id = :id'
                );

                $stmt->execute([
                    'd' => $dormitoryId,
                    'n' => $roomNumber,
                    't' => $roomType,
                    'c' => $capacity,
                    'r' => $rate,
                    's' => $slots,
                    'status' => $roomStatus,
                    'id' => $id
                ]);

                $message = 'Room updated successfully.';
            }

        } elseif ($action === 'delete' && $id) {

            $check = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM room_applications
                 WHERE room_id = :id'
            );

            $check->execute([
                'id' => $id
            ]);

            if ((int) $check->fetchColumn() > 0) {

                throw new RuntimeException(
                    'This room has application records. ' .
                    'Cancel/delete those applications first, ' .
                    'or mark the room inactive instead.'
                );
            }

            $stmt = $pdo->prepare(
                'DELETE FROM rooms
                 WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id
            ]);

            $message = 'Room deleted successfully.';
        }

        header(
            'Location: rooms.php?status=success&message=' .
            urlencode($message ?: 'Operation completed.')
        );

        exit;

    } catch (Throwable $e) {

        header(
            'Location: rooms.php?status=error&message=' .
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
         FROM rooms
         WHERE id = :id'
    );

    $stmt->execute([
        'id' => $editId
    ]);

    $editRow = $stmt->fetch();
}

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
        r.*,
        d.name AS dormitory_name,
        d.location
     FROM rooms r
     JOIN dormitories d
        ON d.id = r.dormitory_id
     ORDER BY
        d.location,
        r.room_number"
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

    <title>Rooms | Admin</title>

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

            <a href="viewing_requests.php">
                Viewing Requests
            </a>

            <a href="room_applications.php">
                Room Applications
            </a>

            <a
                class="active"
                href="rooms.php"
            >
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
            Rooms
        </h1>

        <?php if ($message): ?>

            <div
                class="alert <?= $status === 'error' ? 'error' : 'success' ?>"
            >
                <?= h($message) ?>
            </div>

        <?php endif; ?>

        <div class="two-col">

            <section class="panel">

                <h2>
                    <?= $editRow ? 'Edit Room' : 'Add Room' ?>
                </h2>

                <form method="post">

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
                                    $d['location'] . ' — ' . $d['name']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <label>
                        Room Number
                    </label>

                    <input
                        name="room_number"
                        required
                        value="<?= h($editRow['room_number'] ?? '') ?>"
                    >

                    <label>
                        Type
                    </label>

                    <select name="room_type">

                        <?php foreach ($types as $value => $label): ?>

                            <option
                                value="<?= h($value) ?>"
                                <?= (
                                    ($editRow['room_type'] ?? '4_person')
                                    === $value
                                ) ? 'selected' : '' ?>
                            >
                                <?= h($label) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <label>
                        Capacity
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        min="1"
                        value="<?= h(
                            (string) ($editRow['capacity'] ?? 4)
                        ) ?>"
                    >

                    <label>
                        Monthly Rate
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="monthly_rate"
                        min="0"
                        value="<?= h(
                            (string) ($editRow['monthly_rate'] ?? 0)
                        ) ?>"
                    >

                    <label>
                        Available Slots
                    </label>

                    <input
                        type="number"
                        name="available_slots"
                        min="0"
                        value="<?= h(
                            (string) ($editRow['available_slots'] ?? 0)
                        ) ?>"
                    >

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <?php foreach ($statuses as $value): ?>

                            <option
                                value="<?= $value ?>"
                                <?= (
                                    ($editRow['status'] ?? 'available')
                                    === $value
                                ) ? 'selected' : '' ?>
                            >
                                <?= ucfirst($value) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button
                        class="primary"
                        type="submit"
                    >
                        <?= $editRow
                            ? 'Save Changes'
                            : 'Add Room'
                        ?>
                    </button>

                    <?php if ($editRow): ?>

                        <a
                            class="secondary-button"
                            href="rooms.php"
                        >
                            Cancel Edit
                        </a>

                    <?php endif; ?>

                </form>

            </section>

            <section class="panel table-wrap">

                <h2>
                    Existing Rooms
                </h2>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Location
                            </th>

                            <th>
                                Room
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Slots
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
                                    <?= h($r['location']) ?>
                                </td>

                                <td>
                                    <?= h($r['room_number']) ?>
                                </td>

                                <td>
                                    <?= h(
                                        $types[$r['room_type']]
                                        ?? $r['room_type']
                                    ) ?>
                                </td>

                                <td>
                                    <?= h(
                                        (string) $r['available_slots']
                                    ) ?>

                                    /

                                    <?= h(
                                        (string) $r['capacity']
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
                                        href="rooms.php?edit=<?= h(
                                            (string) $r['id']
                                        ) ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        class="inline-form"
                                        onsubmit="return confirm('Delete this room? It must have no application records.');"
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
                                    No rooms have been added yet.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </section>

        </div>

    </main>

</div>

</body>

</html>

