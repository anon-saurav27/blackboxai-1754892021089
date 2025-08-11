<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if admin is logged in
function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to require user login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Function to require admin login
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit();
    }
}

// Function to get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}

// Function to get current admin info
function getCurrentAdmin() {
    if (!isAdmin()) {
        return null;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching admin: " . $e->getMessage());
        return null;
    }
}

// Function to login user
function loginUser($userId) {
    $_SESSION['user_id'] = $userId;
    session_regenerate_id(true);
}

// Function to login admin
function loginAdmin($adminId) {
    $_SESSION['admin_id'] = $adminId;
    session_regenerate_id(true);
}

// Function to logout user
function logoutUser() {
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

// Function to logout admin
function logoutAdmin() {
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}

// Function to destroy session completely
function destroySession() {
    session_destroy();
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
