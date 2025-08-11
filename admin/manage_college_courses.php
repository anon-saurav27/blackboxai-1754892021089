<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage College Courses';
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
            case 'add':
                $college_id = (int)($_POST['college_id'] ?? 0);
                $course_id = (int)($_POST['course_id'] ?? 0);
                $program_level = sanitizeInput($_POST['program_level'] ?? '');
                
                if ($college_id <= 0 || $course_id <= 0 || empty($program_level)) {
                    $error = 'All fields are required.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO college_courses (college_id, course_id, program_level) VALUES (?, ?, ?)");
                        $stmt->execute([$college_id, $course_id, $program_level]);
                        $success = 'Course linked to college successfully.';
                        logActivity("Linked course ID $course_id to college ID $college_id with program level $program_level");
                    } catch (PDOException $e) {
                        error_log("Add college_course error: " . $e->getMessage());
                        $error = 'Failed to link course to college. It may already exist.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Invalid ID.';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM college_courses WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = 'Course unlinked from college successfully.';
                        logActivity("Unlinked college_course ID $id");
                    } catch (PDOException $e) {
                        error_log("Delete college_course error: " . $e->getMessage());
                        $error = 'Failed to unlink course from college.';
                    }
                }
                break;
        }
    }
}

// Get list of colleges
try {
    $stmt = $pdo->query("SELECT id, name FROM colleges ORDER BY name ASC");
    $colleges = $stmt->fetchAll();
} catch (PDOException $e) {
    $colleges = [];
}

// Get list of courses
try {
    $stmt = $pdo->query("SELECT id, name FROM courses ORDER BY name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}

// Get existing college_courses with details
try {
    $stmt = $pdo->query("
        SELECT cc.id, c.name as college_name, co.name as course_name, cc.program_level
        FROM college_courses cc
        JOIN colleges c ON cc.college_id = c.id
        JOIN courses co ON cc.course_id = co.id
        ORDER BY c.name, co.name
    ");
    $collegeCourses = $stmt->fetchAll();
} catch (PDOException $e) {
    $collegeCourses = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage College Courses - EduPool</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/responsive.css" />
    <link rel="stylesheet" href="../assets/css/animations.css" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-logo">EduPool</h2>
                <p class="sidebar-tagline">Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="/admin/">Dashboard</a></li>
                    <li><a href="/admin/manage_universities.php">Universities</a></li>
                    <li><a href="/admin/manage_colleges.php">Colleges</a></li>
                    <li><a href="/admin/manage_courses.php">Courses</a></li>
                    <li><a href="/admin/manage_users.php">Users</a></li>
                    <li><a href="../" target="_blank">View Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main" id="adminMain">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
                    <h1 class="admin-title">Manage College Courses</h1>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user">
                        <span>Welcome, <?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                    </div>
                    <a href="/admin/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </header>
            <div class="admin-content">
                <div class="admin-breadcrumb">
                    <a href="/admin/">Dashboard</a> >
                    <span>College Courses</span>
                </div>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Assign Courses to Colleges</h2>
                    <p>Link courses to colleges with program levels</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <div class="admin-card">
                    <h3>Assign Course to College</h3>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                        <input type="hidden" name="action" value="add" />
                        <div class="admin-form-grid">
                            <div class="admin-form-group">
                                <label for="college_id">College *</label>
                                <select id="college_id" name="college_id" class="admin-form-select" required>
                                    <option value="">Select College</option>
                                    <?php foreach ($colleges as $college): ?>
                                        <option value="<?php echo $college['id']; ?>"><?php echo htmlspecialchars($college['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label for="course_id">Course *</label>
                                <select id="course_id" name="course_id" class="admin-form-select" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label for="program_level">Program Level *</label>
                                <select id="program_level" name="program_level" class="admin-form-select" required>
                                    <option value="">Select Level</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Bachelor">Bachelor</option>
                                    <option value="Master">Master</option>
                                    <option value="PhD">PhD</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign Course</button>
                    </form>
                </div>
                <div class="admin-card">
                    <h3>Assigned Courses</h3>
                    <?php if (!empty($collegeCourses)): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>College</th>
                                    <th>Course</th>
                                    <th>Program Level</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($collegeCourses as $cc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cc['college_name']); ?></td>
                                        <td><?php echo htmlspecialchars($cc['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($cc['program_level']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Unassign this course?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                                                <input type="hidden" name="action" value="delete" />
                                                <input type="hidden" name="id" value="<?php echo $cc['id']; ?>" />
                                                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Unassign</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No courses assigned to colleges yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
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
