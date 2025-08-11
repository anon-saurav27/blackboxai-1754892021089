<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Admin Dashboard';
$currentAdmin = getCurrentAdmin();

// Get statistics
try {
    // Count universities
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM universities");
    $universitiesCount = $stmt->fetch()['count'];
    
    // Count colleges
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM colleges");
    $collegesCount = $stmt->fetch()['count'];
    
    // Count courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
    $coursesCount = $stmt->fetch()['count'];
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $stmt->fetch()['count'];
    
    // Get recent activities (last 10)
    $stmt = $pdo->query("
        SELECT 'university' as type, name, created_at FROM universities 
        UNION ALL 
        SELECT 'college' as type, name, created_at FROM colleges 
        UNION ALL 
        SELECT 'course' as type, name, created_at FROM courses 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $universitiesCount = $collegesCount = $coursesCount = $usersCount = 0;
    $recentActivities = [];
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
                        <a href="/admin/" class="active">
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
                        <a href="/admin/users.php">
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
                    <h1 class="admin-title">Dashboard</h1>
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
                </div>
                
                <!-- Page Header -->
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Dashboard Overview</h2>
                    <p class="admin-page-subtitle">Welcome to the EduPool administration panel</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid stagger-animation">
                    <div class="stat-card hover-lift">
                        <div class="stat-icon blue">🏛️</div>
                        <div class="stat-content">
                            <h3><?php echo $universitiesCount; ?></h3>
                            <p>Universities</p>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon green">🏫</div>
                        <div class="stat-content">
                            <h3><?php echo $collegesCount; ?></h3>
                            <p>Colleges</p>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon orange">📚</div>
                        <div class="stat-content">
                            <h3><?php echo $coursesCount; ?></h3>
                            <p>Courses</p>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon red">👥</div>
                        <div class="stat-content">
                            <h3><?php echo $usersCount; ?></h3>
                            <p>Registered Users</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Quick Actions</h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="grid grid-4">
                            <a href="/admin/manage_universities.php?action=add" class="btn btn-primary btn-lg hover-lift">
                                Add University
                            </a>
                            <a href="/admin/manage_colleges.php?action=add" class="btn btn-secondary btn-lg hover-lift">
                                Add College
                            </a>
                            <a href="/admin/manage_courses.php?action=add" class="btn btn-primary btn-lg hover-lift">
                                Add Course
                            </a>
                            <a href="../" target="_blank" class="btn btn-outline btn-lg hover-lift">
                                View Website
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Recent Activities</h3>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($recentActivities)): ?>
                            <div class="admin-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Date Added</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-<?php echo $activity['type']; ?>">
                                                        <?php echo ucfirst($activity['type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                                <td><?php echo formatDate($activity['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray">No recent activities found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">System Information</h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="grid grid-2">
                            <div>
                                <h4>Database Status</h4>
                                <p class="text-success">✓ Connected</p>
                                
                                <h4>PHP Version</h4>
                                <p><?php echo PHP_VERSION; ?></p>
                            </div>
                            <div>
                                <h4>Server Time</h4>
                                <p><?php echo date('Y-m-d H:i:s'); ?></p>
                                
                                <h4>Admin Session</h4>
                                <p class="text-success">✓ Active</p>
                            </div>
                        </div>
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
            
            // Mobile sidebar toggle
            if (window.innerWidth <= 768) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && 
                        !sidebar.contains(e.target) && 
                        !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('mobile-open');
                    }
                });
            }
            
            // Auto-refresh stats every 30 seconds
            setInterval(function() {
                // You can implement AJAX refresh here if needed
                console.log('Stats refresh interval');
            }, 30000);
        });
    </script>
    
    <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-university {
            background: var(--light-blue);
            color: var(--primary-blue);
        }
        
        .badge-college {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-course {
            background: #fef3c7;
            color: #92400e;
        }
        
        .text-success {
            color: var(--success);
        }
        
        .text-gray {
            color: var(--gray);
        }
    </style>
</body>
</html>
