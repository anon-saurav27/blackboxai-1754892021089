<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    logActivity("User logout: " . ($user['username'] ?? 'Unknown'));
    logoutUser();
}

// Destroy session completely
destroySession();

// Redirect to homepage with logout message
redirect('/?logout=1');
?>
