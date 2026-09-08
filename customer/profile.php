<?php
session_start(); require '../database/config.php'; 
require '../includes/auth.php'; 
requireCustomer();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>My Profile | Obeda Dormitories</title>
        <link rel="stylesheet" href="../assets/css/customer.css">
    </head
    ><body>
        <header class="customer-header">
            <a href="../index.php">
                <img src="../assets/images/logo-dark.svg" alt="Obeda Dormitories">
            </a><nav><a href="dashboard.php">Dashboard</a>
            <a href="viewing.php">Viewing</a>
            <a href="rooms.php">Rooms</a>
            <a href="application.php">My Applications</a>
            <a class="active" href="profile.php">Profile</a>
        </nav>
        <div>
            <span><?=h($_SESSION['first_name'])?></span>
            <a href="../logout.php">Logout</a>
        </div></header>
        <main class="customer-main narrow">
            <h1>My Profile</h1>
            <section class="panel profile">
                <p><strong>Name</strong><?=h($_SESSION['first_name'].' '.$_SESSION['last_name'])?>
            </p>
            <p>
                <strong>Username</strong><?=h($_SESSION['username'])?>
            </p>
            <p>
                <strong>Email</strong><?=h($_SESSION['email'])?>
            </p><p><strong>Account type</strong><?=h(ucfirst($_SESSION['role']))?></p>
        </section>
    </main>
</body>
</html>
