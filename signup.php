<?php
session_start();
require 'database/config.php';

$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $firstName === '' || $lastName === '' || $password === '') {
        $message = 'Please complete all required fields.';
        $status = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter a valid email address.';
        $status = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $status = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $status = 'error';
    } else {
        try {
            $pdo = getConnection();

            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1');
            $check->execute(['email' => $email, 'username' => $username]);

            if ($check->fetch()) {
                $message = 'That email or username is already registered.';
                $status = 'error';
            } else {
                $sql = 'INSERT INTO users (username, email, password_hash, first_name, last_name, phone)
                        VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone !== '' ? $phone : null,
                ]);

                header('Location: login.php?status=success&message=' . urlencode('Account created successfully. Please sign in.'));
                exit;
            }
        } catch (PDOException $e) {
            $message = 'Unable to create the account right now.';
            $status = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Obeda Dormitories</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body>
    <header class="signup-header">
        <a class="header-logo" href="index.php" aria-label="Obeda Dormitories home">
            <img src="assets/images/logo-dark.svg" alt="Obeda Dormitories logo">
        </a>
        <a class="back-link" href="login.php">Back to sign in</a>
    </header>

    <main class="signup-main">
        <section class="signup-form-card">
            <img class="brand signup-brand" src="assets/images/brand-light.svg" alt="Obeda Dormitories">
            <h1>Create your account</h1>
            <p class="subtitle">Create an account before renting a room or scheduling a viewing.</p>

            <?php if ($status === 'error' && $message): ?>
                <div class="alert error" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="signup.php" autocomplete="on">
                <div class="form-grid">
                    <div>
                        <label for="first_name">First Name *</label>
                        <input id="first_name" name="first_name" type="text" required maxlength="100" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="last_name">Last Name *</label>
                        <input id="last_name" name="last_name" type="text" required maxlength="100" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="username">Username *</label>
                        <input id="username" name="username" type="text" required maxlength="50" autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="tel" maxlength="30" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>

                <label for="email">Email *</label>
                <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label for="password">Password *</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">

                <label for="confirm_password">Confirm Password *</label>
                <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">

                <button type="submit" class="login-button">Create Account</button>
            </form>

            <p class="account-note">Already have an account? <a href="login.php">Sign in</a></p>
        </section>
    </main>
</body>
</html>