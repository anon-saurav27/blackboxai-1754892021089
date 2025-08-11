<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Manage Universities';
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
                $established_year = (int)($_POST['established_year'] ?? 0);
                
                if (empty($name)) {
                    $error = 'University name is required.';
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
                            $stmt = $pdo->prepare("INSERT INTO universities (name, description, established_year, image) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$name, $description, $established_year, $imageName]);
                            $success = 'University added successfully!';
                            logActivity("University added: " . $name);
                        }
                    } catch (PDOException $e) {
                        error_log("Add university error: " . $e->getMessage());
                        $error = 'Failed to add university. Please try again.';
                    }
                }
                break;
                
            case 'edit':
                $id = (int)($_POST['id'] ?? 0);
                $name = sanitizeInput($_POST['name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $established_year = (int)($_POST['established_year'] ?? 0);
                
                if (empty($name) || $id <= 0) {
                    $error = 'Invalid data provided.';
                } else {
                    try {
                        // Get current image
                        $stmt = $pdo->prepare("SELECT image FROM universities WHERE id = ?");
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
                            $stmt = $pdo->prepare("UPDATE universities SET name = ?, description = ?, established_year = ?, image = ? WHERE id = ?");
                            $stmt->execute([$name, $description, $established_year, $imageName, $id]);
                            $success = 'University updated successfully!';
                            logActivity("University updated: " . $name);
                        }
                    } catch (PDOException $e) {
                        error_log("Update university error: " . $e->getMessage());
                        $error = 'Failed to update university. Please try again.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Invalid university ID.';
                } else {
                    try {
                        // Get image to delete
                        $stmt = $pdo->prepare("SELECT image FROM universities WHERE id = ?");
                        $stmt->execute([$id]);
                        $image = $stmt->fetchColumn();
                        
                        // Delete university
                        $stmt = $pdo->prepare("DELETE FROM universities WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        // Delete image file
                        if ($image && file_exists('../uploads/' . $image)) {
                            unlink('../uploads/' . $image);
                        }
                        
                        $success = 'University deleted successfully!';
                        logActivity("University deleted: ID " . $id);
                    } catch (PDOException $e) {
                        error_log("Delete university error: " . $e->getMessage());
                        $error = 'Failed to delete university. Please try again.';
                    }
                }
                break;
        }
    }
}

// Get universities list
try {
    $stmt = $pdo->query("SELECT * FROM universities ORDER BY name ASC");
    $universities = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch universities error: " . $e->getMessage());
    $universities = [];
}

// Get university for editing
$editUniversity = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM universities WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editUniversity = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Fetch university for edit error: " . $e->getMessage());
    }
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editUniversity;
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
                        <a href="/admin/manage_universities.php" class="active">
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
                    <h1 class="admin-title">Manage Universities</h1>
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
                    <span>Universities</span>
                </div>
                
                <!-- Page Header -->
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Universities Management</h2>
                    <p class="admin-page-subtitle">Add, edit, and manage universities in the system</p>
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
                                <?php echo $editUniversity ? 'Edit University' : 'Add New University'; ?>
                            </h3>
                            <div class="admin-card-actions">
                                <a href="/admin/manage_universities.php" class="btn btn-outline btn-sm">Cancel</a>
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <form method="POST" enctype="multipart/form-data" class="admin-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="<?php echo $editUniversity ? 'edit' : 'add'; ?>">
                                <?php if ($editUniversity): ?>
                                    <input type="hidden" name="id" value="<?php echo $editUniversity['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="admin-form-grid">
                                    <div class="admin-form-group">
                                        <label for="name" class="admin-form-label">University Name *</label>
                                        <input 
                                            type="text" 
                                            id="name" 
                                            name="name" 
                                            class="admin-form-input" 
                                            value="<?php echo htmlspecialchars($editUniversity['name'] ?? ''); ?>"
                                            required
                                            placeholder="Enter university name"
                                        >
                                    </div>
                                    
                                    <div class="admin-form-group">
                                        <label for="established_year" class="admin-form-label">Established Year</label>
                                        <input 
                                            type="number" 
                                            id="established_year" 
                                            name="established_year" 
                                            class="admin-form-input" 
                                            value="<?php echo $editUniversity['established_year'] ?? ''; ?>"
                                            min="1800" 
                                            max="<?php echo date('Y'); ?>"
                                            placeholder="e.g., 1959"
                                        >
                                    </div>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="description" class="admin-form-label">Description</label>
                                    <textarea 
                                        id="description" 
                                        name="description" 
                                        class="admin-form-input admin-form-textarea"
                                        placeholder="Enter university description"
                                    ><?php echo htmlspecialchars($editUniversity['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="image" class="admin-form-label">University Image</label>
                                    <div class="file-upload">
                                        <input type="file" id="image" name="image" accept="image/*">
                                        <label for="image" class="file-upload-label">
                                            <span>📁</span>
                                            <span>Choose university image</span>
                                        </label>
                                    </div>
                                    <?php if ($editUniversity && $editUniversity['image']): ?>
                                        <div class="file-preview">
                                            <img src="../uploads/<?php echo $editUniversity['image']; ?>" alt="Current image">
                                            <div class="file-info">
                                                <div class="file-name">Current Image</div>
                                                <div class="file-size"><?php echo $editUniversity['image']; ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="admin-form-actions">
                                    <button type="submit" class="btn btn-primary btn-animated">
                                        <?php echo $editUniversity ? 'Update University' : 'Add University'; ?>
                                    </button>
                                    <a href="/admin/manage_universities.php" class="btn btn-outline">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Universities List -->
                <div class="admin-card animate-fade-in-up">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Universities List (<?php echo count($universities); ?>)</h3>
                        <div class="admin-card-actions">
                            <a href="/admin/manage_universities.php?action=add" class="btn btn-primary">Add University</a>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($universities)): ?>
                            <div class="admin-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Established</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($universities as $university): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($university['image']): ?>
                                                        <img src="../uploads/<?php echo $university['image']; ?>" 
                                                             alt="<?php echo htmlspecialchars($university['name']); ?>"
                                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                    <?php else: ?>
                                                        <div style="width: 50px; height: 50px; background: var(--light-gray); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                                                            🏛️
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($university['name']); ?></strong>
                                                </td>
                                                <td><?php echo $university['established_year'] ?: 'N/A'; ?></td>
                                                <td><?php echo truncateText($university['description'], 100); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="/admin/manage_universities.php?edit=<?php echo $university['id']; ?>" 
                                                           class="btn btn-outline btn-sm">Edit</a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this university?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $university['id']; ?>">
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
                                <h4>No Universities Found</h4>
                                <p class="text-gray">Start by adding your first university to the system.</p>
                                <a href="/admin/manage_universities.php?action=add" class="btn btn-primary">Add First University</a>
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
            
            // File upload preview
            const fileInput = document.getElementById('image');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Create or update preview
                            let preview = document.querySelector('.file-preview');
                            if (!preview) {
                                preview = document.createElement('div');
                                preview.className = 'file-preview';
                                fileInput.parentNode.appendChild(preview);
                            }
                            
                            preview.innerHTML = `
                                <img src="${e.target.result}" alt="Preview">
                                <div class="file-info">
                                    <div class="file-name">${file.name}</div>
                                    <div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                                </div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
