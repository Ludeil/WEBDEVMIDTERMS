<?php

function requireLogin(): void
{
    if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
        header('Location: ../login.php?status=error&message=' . urlencode('Please sign in to continue.'));
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ../customer/dashboard.php?status=error&message=' . urlencode('Administrator access is required.'));
        exit;
    }
}

function requireCustomer(): void
{
    requireLogin();

    if (!in_array($_SESSION['role'] ?? '', ['applicant', 'resident'], true)) {
        header('Location: ../admin/dashboard.php?status=error&message=' . urlencode('Customer access is required.'));
        exit;
    }
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
