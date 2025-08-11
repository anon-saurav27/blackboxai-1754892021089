<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage Courses';
$currentAdmin = getCurrentAdmin();
$error = '';
$success = '';

// Fetch universities for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM universities ORDER BY name ASC");
    $universities = $stmt->fetchAll();
} catch (PDOException $e) {
    $universities = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'add':
                $name = sanitizeInput($_POST['name'] ?? '');
                $duration = sanitizeInput($_POST['duration'] ?? '');
                $university_id = (int)($_POST['university_id'] ?? 0);
                $syllabus = sanitizeInput($_POST['syllabus'] ?? '');
                $eligibility = sanitizeInput($_POST['eligibility'] ?? '');
                $career_paths = sanitizeInput($_POST['career_paths'] ?? '');
                $required_documents = sanitizeInput($_POST['required_documents'] ?? '');
                
                if (empty($name)) {
                    $error = 'Course name is required.';
                } elseif ($university_id <= 0) {
                    $error = 'Please select a university for this course.';
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // Insert course
                        $stmt = $pdo->prepare("INSERT INTO courses (name, duration, syllabus, eligibility, career_paths, required_documents) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $duration, $syllabus, $eligibility, $career_paths, $required_documents]);
                        $course_id = $pdo->lastInsertId();
                        
                        // Link course with university
                        $stmt = $pdo->prepare("INSERT INTO university_courses (university_id, course_id) VALUES (?, ?)");
                        $stmt->execute([$university_id, $course_id]);
                        
                        $pdo->commit();
                        
                        $success = 'Course added successfully!';
                        logActivity("Course added: " . $name);
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        error_log("Add course error: " . $e->getMessage());
                        $error = 'Failed to add course. Please try again.';
                    }
                }
                break;
                
            case 'edit':
                $id = (int)($_POST['id'] ?? 0);
                $name = sanitizeInput($_POST['name'] ?? '');
                $duration = sanitizeInput($_POST['duration'] ?? '');
                $syllabus = sanitizeInput($_POST['syllabus'] ?? '');
                $eligibility = sanitizeInput($_POST['eligibility'] ?? '');
                $career_paths = sanitizeInput($_POST['career_paths'] ?? '');
                $required_documents = sanitizeInput($_POST['required_documents'] ?? '');
                
                if (empty($name) || $id <= 0) {
                    $error = 'Invalid data provided.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE courses SET name = ?, duration = ?, syllabus = ?, eligibility = ?, career_paths = ?, required_documents = ? WHERE id = ?");
                        $stmt->execute([$name, $duration, $syllabus, $eligibility, $career_paths, $required_documents, $id]);
                        $success = 'Course updated successfully!';
                        logActivity("Course updated: " . $name);
                    } catch (PDOException $e) {
                        error_log("Update course error: " . $e->getMessage());
                        $error = 'Failed to update course. Please try again.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Invalid course ID.';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = 'Course deleted successfully!';
                        logActivity("Course deleted: ID " . $id);
                    } catch (PDOException $e) {
                        error_log("Delete course error: " . $e->getMessage());
                        $error = 'Failed to delete course. Please try again.';
                    }
                }
                break;
        }
    }
}

// Get courses list with university info
try {
    $stmt = $pdo->query("
        SELECT c.*, u.id as university_id, u.name as university_name, uc.id as uc_id
        FROM courses c
        LEFT JOIN university_courses uc ON c.id = uc.course_id
        LEFT JOIN universities u ON uc.university_id = u.id
        ORDER BY c.name ASC
    ");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch courses error: " . $e->getMessage());
    $courses = [];
}

// Get course for editing
$editCourse = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editCourse = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Fetch course for edit error: " . $e->getMessage());
    }
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editCourse;
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
                        <a href="/admin/manage_courses.php" class="active">
                            <span class="nav-icon">📚</span>
                            <span class="nav-text">Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/manage_users.php">
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
                    <h1 class="admin-title">Manage Courses</h1>
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
                    <span>Courses</span>
                </div>
                
                <!-- Page Header -->
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Courses Management</h2>
                    <p class="admin-page-subtitle">Add, edit, and manage courses in the system</p>
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
                
                <!-- Add/Edit Form -->
                <?php if ($showForm): ?>
                    <div class="admin-card animate-fade-in-up">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">
                                <?php echo $editCourse ? 'Edit Course' : 'Add New Course'; ?>
                            </h3>
                            <div class="admin-card-actions">
                                <a href="/admin/manage_courses.php" class="btn btn-outline btn-sm">Cancel</a>
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <form method="POST" class="admin-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="<?php echo $editCourse ? 'edit' : 'add'; ?>">
                                <?php if ($editCourse): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCourse['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="admin-form-group">
                                    <label for="university_id" class="admin-form-label">University *</label>
                                    <select id="university_id" name="university_id" class="admin-form-select" required>
                                        <option value="">Select University</option>
                                        <?php foreach ($universities as $university): ?>
                                            <option value="<?php echo $university['id']; ?>" <?php echo (isset($editCourse['university_id']) && $editCourse['university_id'] == $university['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($university['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="name" class="admin-form-label">Course Name *</label>
                                    <input 
                                        type="text" 
                                        id="name" 
                                        name="name" 
                                        class="admin-form-input" 
                                        value="<?php echo htmlspecialchars($editCourse['name'] ?? ''); ?>"
                                        required
                                        placeholder="Enter course name"
                                    >
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="duration" class="admin-form-label">Duration</label>
                                    <input 
                                        type="text" 
                                        id="duration" 
                                        name="duration" 
                                        class="admin-form-input" 
                                        value="<?php echo htmlspecialchars($editCourse['duration'] ?? ''); ?>"
                                        placeholder="e.g., 4 Years, 2 Years"
                                    >
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="syllabus" class="admin-form-label">Syllabus</label>
                                    <textarea 
                                        id="syllabus" 
                                        name="syllabus" 
                                        class="admin-form-input admin-form-textarea"
                                        placeholder="Enter course syllabus"
                                    ><?php echo htmlspecialchars($editCourse['syllabus'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="eligibility" class="admin-form-label">Eligibility</label>
                                    <textarea 
                                        id="eligibility" 
                                        name="eligibility" 
                                        class="admin-form-input admin-form-textarea"
                                        placeholder="Enter eligibility criteria"
                                    ><?php echo htmlspecialchars($editCourse['eligibility'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="career_paths" class="admin-form-label">Career Paths</label>
                                    <textarea 
                                        id="career_paths" 
                                        name="career_paths" 
                                        class="admin-form-input admin-form-textarea"
                                        placeholder="Enter career opportunities"
                                    ><?php echo htmlspecialchars($editCourse['career_paths'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="required_documents" class="admin-form-label">Required Documents</label>
                                    <textarea 
                                        id="required_documents" 
                                        name="required_documents" 
                                        class="admin-form-input admin-form-textarea"
                                        placeholder="Enter required documents"
                                    ><?php echo htmlspecialchars($editCourse['required_documents'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="admin-form-actions">
                                    <button type="submit" class="btn btn-primary btn-animated">
                                        <?php echo $editCourse ? 'Update Course' : 'Add Course'; ?>
                                    </button>
                                    <a href="/admin/manage_courses.php" class="btn btn-outline">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Courses List -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Courses List (<?php echo count($courses); ?>)</h3>
                        <div class="admin-card-actions">
                            <a href="/admin/manage_courses.php?action=add" class="btn btn-primary">Add Course</a>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($courses)): ?>
                            <div class="admin-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>University</th>
                                            <th>Name</th>
                                            <th>Duration</th>
                                            <th>Syllabus</th>
                                            <th>Eligibility</th>
                                            <th>Career Paths</th>
                                            <th>Required Documents</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course['university_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                                <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                                <td><?php echo truncateText($course['syllabus'], 50); ?></td>
                                                <td><?php echo truncateText($course['eligibility'], 50); ?></td>
                                                <td><?php echo truncateText($course['career_paths'], 50); ?></td>
                                                <td><?php echo truncateText($course['required_documents'], 50); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="/admin/manage_courses.php?edit=<?php echo $course['id']; ?>" 
                                                           class="btn btn-outline btn-sm">Edit</a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center" style="padding: 3rem;">
                                <h4>No Courses Found</h4>
                                <p class="text-gray">Start by adding your first course to the system.</p>
                                <a href="/admin/manage_courses.php?action=add" class="btn btn-primary">Add First Course</a>
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
