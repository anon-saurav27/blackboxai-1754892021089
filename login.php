<?php
$pageTitle = 'Login - EduPool';
$pageDescription = 'Sign in to your EduPool account to access personalized education recommendations and manage your profile.';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginField = sanitizeInput($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($loginField) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Check user credentials - allow login with either username or email
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$loginField, $loginField]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Login successful
                loginUser($user['id']);
                logActivity("User login successful: " . $user['username']);
                
                // Handle remember me functionality
                if ($remember) {
                    // Set a longer session lifetime (30 days)
                    ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60);
                }
                
                // Redirect to intended page or homepage
                $redirectTo = $_GET['redirect'] ?? '/';
                redirect($redirectTo);
            } else {
                $error = 'Invalid username/email or password.';
                logActivity("Failed user login attempt: " . $loginField);
            }
        } catch (PDOException $e) {
            error_log("User login error: " . $e->getMessage());
            $error = 'Login failed. Please try again.';
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
                    <h1>Welcome Back</h1>
                    <p>Sign in to your EduPool account to continue your educational journey</p>
                </div>
                
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
                
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="login" class="form-label">Username or Email</label>
                        <input 
                            type="text" 
                            id="login" 
                            name="login" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($loginField ?? ''); ?>"
                            required 
                            autofocus
                            placeholder="Enter your username or email"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input-group">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                required
                                placeholder="Enter your password"
                            >
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <span class="toggle-icon">👁️</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" class="checkbox-input">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Remember me</span>
                        </label>
                        
                        <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg w-full btn-animated">
                            Sign In
                        </button>
                    </div>
                </form>
                
                <div class="auth-divider">
                    <span>or</span>
                </div>
                
                <div class="social-login">
                    <p class="social-text">Quick access for demo:</p>
                    <div class="demo-credentials">
                        <div class="demo-item">
                            <strong>Demo User (Email):</strong>
                            <span>demo@edupool.com / demo123</span>
                        </div>
                        <div class="demo-item">
                            <strong>Demo User (Username):</strong>
                            <span>demo / demo123</span>
                        </div>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="/register.php" class="text-primary">Create one here</a></p>
                    <p><a href="/" class="text-gray">← Back to Homepage</a></p>
                </div>
            </div>
            
            <!-- Login Benefits -->
            <div class="auth-benefits animate-fade-in-right">
                <h3>Access Your Dashboard</h3>
                <div class="benefit-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">🎯</div>
                        <div class="benefit-content">
                            <h4>Personalized Experience</h4>
                            <p>Get recommendations tailored to your educational goals and interests.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">📋</div>
                        <div class="benefit-content">
                            <h4>Saved Items</h4>
                            <p>Access your bookmarked universities, colleges, and courses instantly.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">📊</div>
                        <div class="benefit-content">
                            <h4>Application Tracking</h4>
                            <p>Monitor your application progress and important deadlines.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">🔔</div>
                        <div class="benefit-content">
                            <h4>Instant Notifications</h4>
                            <p>Stay updated with the latest opportunities and admission news.</p>
                        </div>
                    </div>
                </div>
                
                <div class="feature-highlight">
                    <div class="highlight-card">
                        <h4>🚀 New Feature</h4>
                        <p>AI-powered course recommendations based on your profile and career goals.</p>
                    </div>
                </div>
                
                <div class="security-note">
                    <div class="security-icon">🔒</div>
                    <div class="security-text">
                        <h5>Your data is secure</h5>
                        <p>We use industry-standard encryption to protect your personal information.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Auth Section Styles (extending from register.php) */
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

.password-input-group {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: var(--gray);
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--primary-blue);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
}

.checkbox-input {
    display: none;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-radius: 3px;
    position: relative;
    transition: var(--transition);
}

.checkbox-input:checked + .checkbox-custom {
    background: var(--primary-blue);
    border-color: var(--primary-blue);
}

.checkbox-input:checked + .checkbox-custom::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--white);
    font-size: 12px;
    font-weight: bold;
}

.forgot-link {
    color: var(--primary-blue);
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
}

.forgot-link:hover {
    text-decoration: underline;
}

.auth-divider {
    text-align: center;
    margin: 2rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--border-color);
}

.auth-divider span {
    background: var(--white);
    padding: 0 1rem;
    color: var(--gray);
    font-size: 0.9rem;
}

.social-login {
    margin-bottom: 2rem;
}

.social-text {
    text-align: center;
    color: var(--gray);
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.demo-credentials {
    background: var(--light-gray);
    padding: 1rem;
    border-radius: var(--border-radius);
    text-align: center;
}

.demo-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.demo-item:last-child {
    margin-bottom: 0;
}

.demo-item strong {
    color: var(--dark-blue);
}

.demo-item span {
    color: var(--gray);
    font-family: monospace;
    background: var(--white);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: var(--transition);
}

.demo-item span:hover {
    background: var(--light-blue);
    color: var(--primary-blue);
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

.feature-highlight {
    margin-bottom: 2rem;
}

.highlight-card {
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    color: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    text-align: center;
}

.highlight-card h4 {
    color: var(--white);
    margin-bottom: 0.5rem;
}

.security-note {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    background: var(--white);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--success);
}

.security-icon {
    font-size: 1.5rem;
    color: var(--success);
}

.security-text h5 {
    color: var(--dark-blue);
    margin-bottom: 0.25rem;
}

.security-text p {
    color: var(--gray);
    font-size: 0.85rem;
    margin: 0;
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
    
    .auth-header h1 {
        font-size: 2rem;
    }
    
    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .demo-item {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    
    // Password visibility toggle
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('.toggle-icon');
        icon.textContent = type === 'password' ? '👁️' : '🙈';
    });
    
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
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
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
    
    // Demo credentials auto-fill
    const demoItems = document.querySelectorAll('.demo-item span');
    demoItems.forEach(item => {
        item.addEventListener('click', function() {
            const credentials = this.textContent.split(' / ');
            document.getElementById('login').value = credentials[0];
            document.getElementById('password').value = credentials[1];
        });
        
        item.title = 'Click to auto-fill';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
