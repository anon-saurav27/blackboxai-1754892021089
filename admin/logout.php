<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (isAdmin()) {
    $admin = getCurrentAdmin();
    logActivity("Admin logout: " . ($admin['username'] ?? 'Unknown'));
    logoutAdmin();
}

// Destroy session completely
destroySession();

// Redirect to login page
redirect('/admin/login.php');
?>
