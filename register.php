<?php
$pageTitle = 'Register - Join EduPool';
$pageDescription = 'Create your free EduPool account to access personalized education recommendations and save your favorite universities and courses.';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!validatePassword($password)) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username or email already exists. Please choose different ones.';
            } else {
                // Handle profile picture upload
                $profilePicture = '';
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['profile_picture'], 'uploads');
                    if ($uploadResult['success']) {
                        $profilePicture = $uploadResult['filename'];
                    } else {
                        $error = $uploadResult['message'];
                    }
                }
                
                if (empty($error)) {
                    // Create user account
                    $hashedPassword = hashPassword($password);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashedPassword, $profilePicture]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    // Log the user in
                    loginUser($userId);
                    logActivity("User registration successful: " . $username);
                    
                    $success = 'Account created successfully! Welcome to EduPool.';
                    
                    // Redirect after a short delay
                    header("refresh:2;url=/");
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Registration failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card animate-scale-in">
                <div class="auth-header">
                    <h1>Join EduPool</h1>
                    <p>Create your free account to discover the best educational opportunities in Nepal</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error animate-shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success animate-fade-in">
                        <?php echo $success; ?>
                        <p><small>Redirecting to homepage...</small></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">Username *</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                required 
                                autofocus
                                placeholder="Choose a unique username"
                                pattern="[a-zA-Z0-9_]{3,20}"
                                title="Username must be 3-20 characters long and contain only letters, numbers, and underscores"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                required
                                placeholder="your.email@example.com"
                            >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                required
                                placeholder="Create a strong password"
                                minlength="6"
                            >
                            <small class="form-help">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                required
                                placeholder="Repeat your password"
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_picture" class="form-label">Profile Picture (Optional)</label>
                        <div class="file-upload-area">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-input">
                            <div class="file-upload-label">
                                <div class="upload-icon">📷</div>
                                <div class="upload-text">
                                    <span class="upload-title">Choose Profile Picture</span>
                                    <span class="upload-subtitle">PNG, JPG up to 5MB</span>
                                </div>
                            </div>
                            <div class="file-preview" id="filePreview" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg w-full btn-animated">
                            Create Account
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="/login.php" class="text-primary">Sign in here</a></p>
                    <p><a href="/" class="text-gray">← Back to Homepage</a></p>
                </div>
            </div>
            
            <!-- Benefits Section -->
            <div class="auth-benefits animate-fade-in-right">
                <h3>Why Join EduPool?</h3>
                <div class="benefit-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">🎯</div>
                        <div class="benefit-content">
                            <h4>Personalized Recommendations</h4>
                            <p>Get course and university suggestions based on your interests and goals.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">💾</div>
                        <div class="benefit-content">
                            <h4>Save Favorites</h4>
                            <p>Bookmark universities, colleges, and courses for easy access later.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">📊</div>
                        <div class="benefit-content">
                            <h4>Track Applications</h4>
                            <p>Keep track of your application status and important deadlines.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">🔔</div>
                        <div class="benefit-content">
                            <h4>Get Updates</h4>
                            <p>Receive notifications about new courses, admission dates, and opportunities.</p>
                        </div>
                    </div>
                </div>
                
                <div class="trust-indicators">
                    <p class="trust-text">Trusted by thousands of students across Nepal</p>
                    <div class="trust-stats">
                        <span>🎓 <?php echo $universitiesCount ?? 0; ?>+ Universities</span>
                        <span>🏫 <?php echo $collegesCount ?? 0; ?>+ Colleges</span>
                        <span>📚 <?php echo $coursesCount ?? 0; ?>+ Courses</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Auth Section Styles */
.auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, var(--light-blue), var(--white));
    padding: 2rem 0;
}

.auth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    max-width: 1200px;
    margin: 0 auto;
    align-items: center;
}

.auth-card {
    background: var(--white);
    padding: 3rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2.5rem;
    color: var(--primary-blue);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: var(--gray);
    font-size: 1.125rem;
}

.auth-form {
    margin-bottom: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray);
}

.file-upload-area {
    position: relative;
    border: 2px dashed var(--border-color);
    border-radius: var(--border-radius);
    padding: 2rem;
    text-align: center;
    transition: var(--transition);
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: var(--primary-blue);
    background: var(--light-blue);
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.upload-icon {
    font-size: 2rem;
}

.upload-title {
    display: block;
    font-weight: 600;
    color: var(--dark-gray);
}

.upload-subtitle {
    display: block;
    font-size: 0.875rem;
    color: var(--gray);
}

.file-preview {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-preview img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.auth-footer {
    text-align: center;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.auth-footer p {
    margin-bottom: 0.5rem;
}

/* Benefits Section */
.auth-benefits {
    padding: 2rem;
}

.auth-benefits h3 {
    font-size: 1.75rem;
    color: var(--primary-blue);
    margin-bottom: 2rem;
}

.benefit-list {
    margin-bottom: 2rem;
}

.benefit-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.benefit-icon {
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    background: var(--light-blue);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.benefit-content h4 {
    color: var(--dark-blue);
    margin-bottom: 0.25rem;
}

.benefit-content p {
    color: var(--gray);
    font-size: 0.9rem;
}

.trust-indicators {
    padding: 1.5rem;
    background: var(--white);
    border-radius: var(--border-radius);
    text-align: center;
}

.trust-text {
    color: var(--gray);
    margin-bottom: 1rem;
    font-weight: 500;
}

.trust-stats {
    display: flex;
    justify-content: space-around;
    font-size: 0.875rem;
    color: var(--primary-blue);
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .auth-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .auth-card {
        padding: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .auth-header h1 {
        font-size: 2rem;
    }
    
    .trust-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const fileInput = document.getElementById('profile_picture');
    const filePreview = document.getElementById('filePreview');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    // File upload preview
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <div>
                        <div style="font-weight: 500;">${file.name}</div>
                        <div style="font-size: 0.875rem; color: var(--gray);">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                    </div>
                `;
                filePreview.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        } else {
            filePreview.style.display = 'none';
        }
    });
    
    // Password confirmation validation
    function validatePasswords() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
            confirmPasswordInput.classList.add('error');
        } else {
            confirmPasswordInput.setCustomValidity('');
            confirmPasswordInput.classList.remove('error');
        }
    }
    
    passwordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('input[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            
            // Shake animation for errors
            const card = document.querySelector('.auth-card');
            card.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                card.style.animation = '';
            }, 500);
        }
    });
    
    // Real-time validation
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error') && this.value.trim()) {
                this.classList.remove('error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
