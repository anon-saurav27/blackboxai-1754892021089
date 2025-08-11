<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    redirect('/admin/');
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Check admin credentials
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && verifyPassword($password, $admin['password'])) {
                // Login successful
                loginAdmin($admin['id']);
                logActivity("Admin login successful: " . $username);
                redirect('/admin/');
            } else {
                $error = 'Invalid username or password.';
                logActivity("Failed admin login attempt: " . $username);
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - EduPool</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-login animate-fade-in">
        <div class="admin-login-card animate-scale-in">
            <div class="admin-login-header">
                <h1 class="admin-login-logo">EduPool</h1>
                <p class="admin-login-subtitle">Admin Panel Access</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error animate-shake">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-login-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input admin-form-input" 
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required 
                        autofocus
                        placeholder="Enter your username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input admin-form-input" 
                        required
                        placeholder="Enter your password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg w-full btn-animated">
                        Login to Admin Panel
                    </button>
                </div>
            </form>
            
            <div class="admin-login-footer">
                <p class="text-center">
                    <a href="../" class="text-primary">← Back to Main Site</a>
                </p>
                <p class="text-center mt-2">
                    <small class="text-gray">
                        Default credentials: admin / admin123
                    </small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
