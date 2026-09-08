<?php

session_start();

require '../database/config.php';
require '../includes/auth.php';

requireCustomer();

$pdo = getConnection();

$message = null;
$status = null;
$selected = (int) ($_GET['room_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $room = (int) ($_POST['room_id'] ?? 0);
    $move = $_POST['move_in_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($room) {

        $check = $pdo->prepare(
            "SELECT id
             FROM room_applications
             WHERE user_id = :u
             AND room_id = :r
             AND status IN ('pending', 'approved')
             LIMIT 1"
        );

        $check->execute([
            'u' => $_SESSION['user_id'],
            'r' => $room
        ]);

        if ($check->fetch()) {

            $status = 'error';
            $message = 'You already have an active application for this room.';

        } else {

            $s = $pdo->prepare(
                "SELECT id
                 FROM rooms
                 WHERE id = :id
                 AND status = 'available'
                 AND available_slots > 0"
            );

            $s->execute([
                'id' => $room
            ]);

            if (!$s->fetch()) {

                $status = 'error';
                $message = 'That room is no longer available.';

            } else {

                $ins = $pdo->prepare(
                    'INSERT INTO room_applications(
                        user_id,
                        room_id,
                        move_in_date,
                        notes
                    ) VALUES(
                        :u,
                        :r,
                        :move,
                        :notes
                    )'
                );

                $ins->execute([
                    'u' => $_SESSION['user_id'],
                    'r' => $room,
                    'move' => $move ?: null,
                    'notes' => $notes ?: null
                ]);

                header(
                    'Location: dashboard.php?status=success&message=' .
                    urlencode(
                        'Room application submitted for administrator review.'
                    )
                );

                exit;
            }
        }

    } else {

        $status = 'error';
        $message = 'Please choose a room.';
    }
}

$rooms = $pdo->query(
    "SELECT
        r.id,
        r.room_number,
        r.room_type,
        r.monthly_rate,
        r.available_slots,
        d.location
     FROM rooms r
     JOIN dormitories d
        ON d.id = r.dormitory_id
     WHERE r.status = 'available'
     AND r.available_slots > 0
     AND d.status = 'active'
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

    <title>
        Room Application | Obeda Dormitories
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/customer.css"
    >

</head>

<body>

<header class="customer-header">

    <a href="../index.php">

        <img
            src="../assets/images/logo-dark.svg"
            alt="Obeda Dormitories"
        >

    </a>

    <nav>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="viewing.php">
            Viewing
        </a>

        <a href="rooms.php">
            Rooms
        </a>

        <a
            class="active"
            href="application.php"
        >
            My Applications
        </a>

        <a href="profile.php">
            Profile
        </a>

    </nav>

    <div>

        <span>
            <?= h($_SESSION['first_name']) ?>
        </span>

        <a href="../logout.php">
            Logout
        </a>

    </div>

</header>

<main class="customer-main narrow">

    <h1>
        Apply for a Room
    </h1>

    <?php if ($message): ?>

        <div class="alert error">
            <?= h($message) ?>
        </div>

    <?php endif; ?>

    <section class="panel">

        <form method="post">

            <label>
                Room *
            </label>

            <select
                name="room_id"
                required
            >

                <option value="">
                    Choose a room
                </option>

                <?php foreach ($rooms as $r): ?>

                    <option
                        value="<?= h((string) $r['id']) ?>"
                        <?= (
                            $selected === $r['id']
                            ? 'selected'
                            : ''
                        ) ?>
                    >
                        <?= h($r['location']) ?>
                        —
                        Room <?= h($r['room_number']) ?>
                        —
                        <?= h(
                            str_replace(
                                '_',
                                ' ',
                                $r['room_type']
                            )
                        ) ?>
                        —
                        ₱<?= number_format(
                            (float) $r['monthly_rate'],
                            2
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <label>
                Preferred Move-in Date
            </label>

            <input
                type="date"
                name="move_in_date"
            >

            <label>
                Notes
            </label>

            <textarea
                name="notes"
                placeholder="Optional message for the administrator"
            ></textarea>

            <button
                class="button"
                type="submit"
            >
                Submit Application
            </button>

        </form>

    </section>

</main>

</body>

</html>

