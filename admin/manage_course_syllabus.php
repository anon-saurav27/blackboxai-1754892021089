<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage Course Syllabus';
$currentAdmin = getCurrentAdmin();
$error = '';
$success = '';

// Get university_course_id from GET
$uc_id = (int)($_GET['uc_id'] ?? 0);
if ($uc_id <= 0) {
    header('Location: /admin/manage_courses.php');
    exit;
}

// Fetch course and university info
try {
    $stmt = $pdo->prepare("
        SELECT uc.id as uc_id, c.name as course_name, u.name as university_name
        FROM university_courses uc
        JOIN courses c ON uc.course_id = c.id
        JOIN universities u ON uc.university_id = u.id
        WHERE uc.id = ?
    ");
    $stmt->execute([$uc_id]);
    $ucInfo = $stmt->fetch();
    if (!$ucInfo) {
        header('Location: /admin/manage_courses.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Fetch university_course info error: " . $e->getMessage());
    header('Location: /admin/manage_courses.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        try {
            if ($action === 'add_group') {
                $label = sanitizeInput($_POST['label'] ?? '');
                if (empty($label)) {
                    $error = 'Group label is required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO course_syllabus_groups (university_course_id, label) VALUES (?, ?)");
                    $stmt->execute([$uc_id, $label]);
                    $success = 'Syllabus group added successfully.';
                }
            } elseif ($action === 'delete_group') {
                $group_id = (int)($_POST['group_id'] ?? 0);
                if ($group_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM course_syllabus_groups WHERE id = ?");
                    $stmt->execute([$group_id]);
                    $success = 'Syllabus group deleted successfully.';
                }
            } elseif ($action === 'add_item') {
                $group_id = (int)($_POST['group_id'] ?? 0);
                $subject_name = sanitizeInput($_POST['subject_name'] ?? '');
                $credit_hours = (int)($_POST['credit_hours'] ?? 0);
                if ($group_id <= 0 || empty($subject_name) || $credit_hours <= 0) {
                    $error = 'All fields are required for adding a syllabus item.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO course_syllabus_items (group_id, subject_name, credit_hours) VALUES (?, ?, ?)");
                    $stmt->execute([$group_id, $subject_name, $credit_hours]);
                    $success = 'Syllabus item added successfully.';
                }
            } elseif ($action === 'delete_item') {
                $item_id = (int)($_POST['item_id'] ?? 0);
                if ($item_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM course_syllabus_items WHERE id = ?");
                    $stmt->execute([$item_id]);
                    $success = 'Syllabus item deleted successfully.';
                }
            }
        } catch (PDOException $e) {
            error_log("Syllabus management error: " . $e->getMessage());
            $error = 'Operation failed. Please try again.';
        }
    }
}

// Fetch syllabus groups and items
try {
    $stmt = $pdo->prepare("SELECT * FROM course_syllabus_groups WHERE university_course_id = ? ORDER BY id ASC");
    $stmt->execute([$uc_id]);
    $groups = $stmt->fetchAll();
    
    foreach ($groups as &$group) {
        $stmt = $pdo->prepare("SELECT * FROM course_syllabus_items WHERE group_id = ? ORDER BY id ASC");
        $stmt->execute([$group['id']]);
        $group['items'] = $stmt->fetchAll();
        
        $group['total_credit'] = array_sum(array_column($group['items'], 'credit_hours'));
    }
} catch (PDOException $e) {
    error_log("Fetch syllabus groups/items error: " . $e->getMessage());
    $groups = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Syllabus - <?php echo htmlspecialchars($ucInfo['course_name']); ?> @ <?php echo htmlspecialchars($ucInfo['university_name']); ?> - EduPool</title>
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
                    <li><a href="/admin/manage_courses.php" class="active">Courses</a></li>
                    <li><a href="/admin/manage_users.php">Users</a></li>
                    <li><a href="../" target="_blank">View Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main" id="adminMain">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
                    <h1 class="admin-title">Manage Syllabus</h1>
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
                    <a href="/admin/manage_courses.php">Courses</a> >
                    <span>Manage Syllabus</span>
                </div>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Syllabus for <?php echo htmlspecialchars($ucInfo['course_name']); ?> @ <?php echo htmlspecialchars($ucInfo['university_name']); ?></h2>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <div class="admin-card">
                    <h3>Add New Syllabus Group</h3>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                        <input type="hidden" name="action" value="add_group" />
                        <input type="hidden" name="uc_id" value="<?php echo $uc_id; ?>" />
                        <div class="admin-form-group">
                            <label for="label">Group Label (Year or Semester)</label>
                            <input type="text" id="label" name="label" class="admin-form-input" required placeholder="e.g., Year I, Year I Sem I" />
                        </div>
                        <button type="submit" class="btn btn-primary">Add Group</button>
                    </form>
                </div>
                <?php foreach ($groups as $group): ?>
                    <div class="admin-card">
                        <h3><?php echo htmlspecialchars($group['label']); ?> (Total Credit Hours: <?php echo $group['total_credit']; ?>)</h3>
                        <form method="POST" class="admin-form" style="margin-bottom: 1rem;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                            <input type="hidden" name="action" value="add_item" />
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>" />
                            <div class="admin-form-grid">
                                <div class="admin-form-group">
                                    <label for="subject_name_<?php echo $group['id']; ?>">Subject Name</label>
                                    <input type="text" id="subject_name_<?php echo $group['id']; ?>" name="subject_name" class="admin-form-input" required />
                                </div>
                                <div class="admin-form-group">
                                    <label for="credit_hours_<?php echo $group['id']; ?>">Credit Hours</label>
                                    <input type="number" id="credit_hours_<?php echo $group['id']; ?>" name="credit_hours" class="admin-form-input" min="1" required />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </form>
                        <?php if (!empty($group['items'])): ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Credit Hours</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['subject_name']); ?></td>
                                            <td><?php echo $item['credit_hours']; ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this subject?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                                                    <input type="hidden" name="action" value="delete_item" />
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Delete this entire group?');" style="margin-top: 1rem;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                            <input type="hidden" name="action" value="delete_group" />
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>" />
                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Delete Group</button>
                        </form>
                    </div>
                <?php endforeach; ?>
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
