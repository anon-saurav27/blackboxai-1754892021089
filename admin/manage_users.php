<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage Users';
$currentAdmin = getCurrentAdmin();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Invalid user ID.';
                } else {
                    try {
                        // Delete user
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        $success = 'User deleted successfully!';
                        logActivity("User deleted: ID " . $id);
                    } catch (PDOException $e) {
                        error_log("Delete user error: " . $e->getMessage());
                        $error = 'Failed to delete user. Please try again.';
                    }
                }
                break;
        }
    }
}

// Get users list
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch users error: " . $e->getMessage());
    $users = [];
}
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
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-logo">EduPool</h2>
                <p class="sidebar-tagline">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="/admin/">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/manage_universities.php">
                            <span class="nav-icon">🏛️</span>
                            <span class="nav-text">Universities</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/manage_colleges.php">
                            <span class="nav-icon">🏫</span>
                            <span class="nav-text">Colleges</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/manage_courses.php">
                            <span class="nav-icon">📚</span>
                            <span class="nav-text">Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/manage_users.php" class="active">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="../" target="_blank">
                            <span class="nav-icon">🌐</span>
                            <span class="nav-text">View Site</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main" id="adminMain">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
                    <h1 class="admin-title">Manage Users</h1>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user">
                        <span>Welcome, <?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                    </div>
                    <a href="/admin/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Breadcrumb -->
                <div class="admin-breadcrumb">
                    <a href="/admin/">Dashboard</a>
                    <span class="breadcrumb-separator">></span>
                    <span>Users</span>
                </div>
                
                <!-- Page Header -->
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Users Management</h2>
                    <p class="admin-page-subtitle">View and manage registered users</p>
                </div>
                
                <!-- Alerts -->
                <?php if ($error): ?>
                    <div class="alert alert-error animate-shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success animate-fade-in">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Users List -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Users List (<?php echo count($users); ?>)</h3>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($users)): ?>
                            <div class="admin-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Profile Picture</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Registered On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($user['profile_picture']): ?>
                                                        <img src="../uploads/<?php echo $user['profile_picture']; ?>" 
                                                             alt="<?php echo htmlspecialchars($user['username']); ?>"
                                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                                    <?php else: ?>
                                                        <div style="width: 50px; height: 50px; background: var(--light-gray); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gray); font-weight: 700;">
                                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center" style="padding: 3rem;">
                                <h4>No Users Found</h4>
                                <p class="text-gray">No registered users found in the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');
            });
        });
    </script>
</body>
</html>
