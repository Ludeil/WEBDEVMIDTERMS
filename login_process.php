<?php
session_start();
require 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$login    = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
    header('Location: login.php?status=error&message=' . urlencode('Email/username and password are required.'));
    exit;
}

try {
    $pdo = getConnection();

    // Use two placeholders. With native PDO prepares, the same named
    // placeholder should not be reused for two separate parameter positions.
    $sql = "SELECT id, username, email, password_hash, first_name, last_name, role, account_status
            FROM users
            WHERE email = :login_email OR username = :login_username
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'login_email' => $login,
        'login_username' => $login,
    ]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: login.php?status=error&message=' . urlencode('Invalid email/username or password.'));
        exit;
    }

    if ($user['account_status'] !== 'active') {
        header('Location: login.php?status=error&message=' . urlencode('This account is not currently active.'));
        exit;
    }

    if (!in_array($user['role'], ['admin', 'applicant', 'resident'], true)) {
        header('Location: login.php?status=error&message=' . urlencode('This account has an invalid role.'));
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['logged_in']  = true;

    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: customer/dashboard.php');
    }
    exit;
} catch (PDOException $e) {
    header('Location: login.php?status=error&message=' . urlencode('Unable to sign in right now. Please try again.'));
    exit;
}
