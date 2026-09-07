<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to your Obeda Dormitories account.">
    <title>Sign In | Obeda Dormitories</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <header class="login-header">
        <a class="header-logo" href="index.php" aria-label="Obeda Dormitories home">
            <img src="assets/images/logo-dark.svg" alt="Obeda Dormitories logo">
        </a>
        <a class="back-link" href="index.php">Back to home</a>
    </header>

    <main class="login-main">
        <section class="login-visual" aria-label="Obeda Dormitories welcome">
            <img src="assets/images/city-image.jpg" alt="Dumaguete City">
            <div class="visual-overlay"></div>
            <div class="visual-copy">
                <h1>WELCOME<br>BACK.</h1>
                <p>Access your Obeda Dormitories account to continue with your room rental or viewing request.</p>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <img class="brand" src="assets/images/brand-light.svg" alt="Obeda Dormitories">
                <h2>Sign in</h2>
                <p class="subtitle">Enter your account details below.</p>

                <?php if ($status === 'error' && $message): ?>
                    <div class="alert error" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php elseif ($status === 'success' && $message): ?>
                    <div class="alert success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="login_process.php" autocomplete="on">
                    <label for="login">Email or Username</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        required
                        maxlength="255"
                        autocomplete="username"
                        placeholder="Enter your email or username"
                    >

                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                    <button type="submit" class="login-button">Sign In</button>
                </form>

                <p class="account-note">Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
        </section>
    </main>
</body>
</html>
