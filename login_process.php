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

    $sql = "SELECT id, username, email, password_hash, first_name, last_name, role, account_status
            FROM users
            WHERE email = :login OR username = :login
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':login', $login, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: login.php?status=error&message=' . urlencode('Invalid email/username or password.'));
        exit;
    }

    if ($user['account_status'] !== 'active') {
        header('Location: login.php?status=error&message=' . urlencode('This account is not currently active.'));
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id']       = (int) $user['id'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['first_name']    = $user['first_name'];
    $_SESSION['last_name']     = $user['last_name'];
    $_SESSION['role']          = $user['role'];
    $_SESSION['logged_in']     = true;

    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    header('Location: login.php?status=error&message=' . urlencode('Unable to sign in right now. Please try again.'));
    exit;
}
