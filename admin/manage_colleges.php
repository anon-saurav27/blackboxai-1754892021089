<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage Colleges';
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
                $name = sanitizeInput($_POST['name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $map_link = sanitizeInput($_POST['map_link'] ?? '');
                $university_id = (int)($_POST['university_id'] ?? 0);
                $website_url = sanitizeInput($_POST['website_url'] ?? '');
                
                if (empty($name)) {
                    $error = 'College name is required.';
                } elseif ($university_id <= 0) {
                    $error = 'Please select an affiliated university.';
                } else {
                    try {
                        // Handle image upload
                        $imageName = '';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadFile($_FILES['image'], '../uploads');
                            if ($uploadResult['success']) {
                                $imageName = $uploadResult['filename'];
                            } else {
                                $error = $uploadResult['message'];
                            }
                        }
                        
                        if (empty($error)) {
                            $stmt = $pdo->prepare("INSERT INTO colleges (name, description, location, map_link, image, university_id, website_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$name, $description, $location, $map_link, $imageName, $university_id, $website_url]);
                            $success = 'College added successfully!';
                            logActivity("College added: " . $name);
                        }
                    } catch (PDOException $e) {
                        error_log("Add college error: " . $e->getMessage());
                        $error = 'Failed to add college. Please try again.';
                    }
                }
                break;
                
            case 'edit':
                $id = (int)($_POST['id'] ?? 0);
                $name = sanitizeInput($_POST['name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $map_link = sanitizeInput($_POST['map_link'] ?? '');
                $university_id = (int)($_POST['university_id'] ?? 0);
                $website_url = sanitizeInput($_POST['website_url'] ?? '');
                
                if (empty($name) || $id <= 0) {
                    $error = 'Invalid data provided.';
                } elseif ($university_id <= 0) {
                    $error = 'Please select an affiliated university.';
                } else {
                    try {
                        // Get current image
                        $stmt = $pdo->prepare("SELECT image FROM colleges WHERE id = ?");
                        $stmt->execute([$id]);
                        $currentImage = $stmt->fetchColumn();
                        
                        $imageName = $currentImage;
                        
                        // Handle new image upload
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadFile($_FILES['image'], '../uploads');
                            if ($uploadResult['success']) {
                                // Delete old image
                                if ($currentImage && file_exists('../uploads/' . $currentImage)) {
                                    unlink('../uploads/' . $currentImage);
                                }
                                $imageName = $uploadResult['filename'];
                            } else {
                                $error = $uploadResult['message'];
                            }
                        }
                        
                        if (empty($error)) {
                            $stmt = $pdo->prepare("UPDATE colleges SET name = ?, description = ?, location = ?, map_link = ?, image = ?, university_id = ?, website_url = ? WHERE id = ?");
                            $stmt->execute([$name, $description, $location, $map_link, $imageName, $university_id, $website_url, $id]);
                            $success = 'College updated successfully!';
                            logActivity("College updated: " . $name);
                        }
                    } catch (PDOException $e) {
                        error_log("Update college error: " . $e->getMessage());
                        $error = 'Failed to update college. Please try again.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Invalid college ID.';
                } else {
                    try {
                        // Get image to delete
                        $stmt = $pdo->prepare("SELECT image FROM colleges WHERE id = ?");
                        $stmt->execute([$id]);
                        $image = $stmt->fetchColumn();
                        
                        // Delete college
                        $stmt = $pdo->prepare("DELETE FROM colleges WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        // Delete image file
                        if ($image && file_exists('../uploads/' . $image)) {
                            unlink('../uploads/' . $image);
                        }
                        
                        $success = 'College deleted successfully!';
                        logActivity("College deleted: ID " . $id);
                    } catch (PDOException $e) {
                        error_log("Delete college error: " . $e->getMessage());
                        $error = 'Failed to delete college. Please try again.';
                    }
                }
                break;
        }
    }
}

// Get colleges list with university names
try {
    $stmt = $pdo->query("SELECT c.*, u.name as university_name FROM colleges c LEFT JOIN universities u ON c.university_id = u.id ORDER BY c.name ASC");
    $colleges = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch colleges error: " . $e->getMessage());
    $colleges = [];
}

// Get college for editing
$editCollege = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM colleges WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editCollege = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Fetch college for edit error: " . $e->getMessage());
    }
}

// Get universities for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM universities ORDER BY name ASC");
    $universities = $stmt->fetchAll();
} catch (PDOException $e) {
    $universities = [];
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editCollege;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Colleges - EduPool</title>
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
                    <li><a href="/admin/manage_colleges.php" class="active">Colleges</a></li>
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
                    <h1 class="admin-title">Manage Colleges</h1>
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
                    <span>Colleges</span>
                </div>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Colleges Management</h2>
                    <p>Add, edit, and manage colleges in the system</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($showForm): ?>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3><?php echo $editCollege ? 'Edit College' : 'Add New College'; ?></h3>
                            <div class="admin-card-actions">
                                <a href="/admin/manage_colleges.php" class="btn btn-outline btn-sm">Cancel</a>
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <form method="POST" enctype="multipart/form-data" class="admin-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                                <input type="hidden" name="action" value="<?php echo $editCollege ? 'edit' : 'add'; ?>" />
                                <?php if ($editCollege): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCollege['id']; ?>" />
                                <?php endif; ?>
                                <div class="admin-form-group">
                                    <label for="name">College Name *</label>
                                    <input type="text" id="name" name="name" class="admin-form-input" value="<?php echo htmlspecialchars($editCollege['name'] ?? ''); ?>" required />
                                </div>
                                <div class="admin-form-group">
                                    <label for="university_id">Affiliated University *</label>
                                    <select id="university_id" name="university_id" class="admin-form-select" required>
                                        <option value="">Select University</option>
                                        <?php foreach ($universities as $university): ?>
                                            <option value="<?php echo $university['id']; ?>" <?php echo (isset($editCollege['university_id']) && $editCollege['university_id'] == $university['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($university['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="admin-form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="admin-form-textarea"><?php echo htmlspecialchars($editCollege['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="admin-form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" class="admin-form-input" value="<?php echo htmlspecialchars($editCollege['location'] ?? ''); ?>" />
                                </div>
                                <div class="admin-form-group">
                                    <label for="map_link">Map Link</label>
                                    <input type="url" id="map_link" name="map_link" class="admin-form-input" value="<?php echo htmlspecialchars($editCollege['map_link'] ?? ''); ?>" />
                                </div>
                                <div class="admin-form-group">
                                    <label for="website_url">Website URL</label>
                                    <input type="url" id="website_url" name="website_url" class="admin-form-input" value="<?php echo htmlspecialchars($editCollege['website_url'] ?? ''); ?>" />
                                </div>
                                <div class="admin-form-group">
                                    <label for="image">College Image</label>
                                    <input type="file" id="image" name="image" accept="image/*" />
                                    <?php if ($editCollege && $editCollege['image']): ?>
                                        <img src="../uploads/<?php echo $editCollege['image']; ?>" alt="Current Image" style="max-width: 150px; margin-top: 10px;" />
                                    <?php endif; ?>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="submit" class="btn btn-primary"><?php echo $editCollege ? 'Update College' : 'Add College'; ?></button>
                                    <a href="/admin/manage_colleges.php" class="btn btn-outline">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Colleges List (<?php echo count($colleges); ?>)</h3>
                        <div class="admin-card-actions">
                            <a href="/admin/manage_colleges.php?action=add" class="btn btn-primary">Add College</a>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($colleges)): ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>University</th>
                                        <th>Location</th>
                                        <th>Website</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($colleges as $college): ?>
                                        <tr>
                                            <td>
                                                <?php if ($college['image']): ?>
                                                    <img src="../uploads/<?php echo $college['image']; ?>" alt="<?php echo htmlspecialchars($college['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: var(--light-gray); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                                                        🏫
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($college['name']); ?></td>
                                            <td><?php echo htmlspecialchars($college['university_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($college['location'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($college['website_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($college['website_url']); ?>" target="_blank">Visit Site</a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <a href="/admin/manage_colleges.php?edit=<?php echo $college['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this college?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>" />
                                                        <input type="hidden" name="action" value="delete" />
                                                        <input type="hidden" name="id" value="<?php echo $college['id']; ?>" />
                                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: var(--error);">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center" style="padding: 3rem;">
                                <h4>No Colleges Found</h4>
                                <p class="text-gray">Start by adding your first college to the system.</p>
                                <a href="/admin/manage_colleges.php?action=add" class="btn btn-primary">Add First College</a>
                            </div>
                        <?php endif; ?>
                    </div>
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
