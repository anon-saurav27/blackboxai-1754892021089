<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

$baseURL = getBaseURL();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - EduPool' : 'EduPool - Educational Portal for Nepal'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Find the best universities, colleges, and courses in Nepal. Your gateway to quality education.'; ?>">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $baseURL; ?>/assets/images/favicon.ico">
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <!-- Logo -->
                <div class="nav-logo">
                    <a href="<?php echo $baseURL; ?>/">
                        <h2 class="logo-text">EduPool</h2>
                        <span class="logo-tagline">Educational Portal</span>
                    </a>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <div class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <!-- Navigation Menu -->
                <div class="nav-menu" id="navMenu">
                    <ul class="nav-links">
                        <li><a href="<?php echo $baseURL; ?>/" class="nav-link">Home</a></li>
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle">Explore</a>
                            <div class="dropdown-menu">
                                <a href="<?php echo $baseURL; ?>/pages/universities/" class="dropdown-link">Universities</a>
                                <a href="<?php echo $baseURL; ?>/pages/colleges/" class="dropdown-link">Colleges</a>
                                <a href="<?php echo $baseURL; ?>/pages/courses/" class="dropdown-link">Courses</a>
                            </div>
                        </li>
                        <li><a href="<?php echo $baseURL; ?>/#search" class="nav-link">Search</a></li>
                        <li><a href="<?php echo $baseURL; ?>/#about" class="nav-link">About</a></li>
                    </ul>
                    
                    <!-- User Authentication -->
                    <div class="nav-auth">
                        <?php if ($currentUser): ?>
                            <div class="user-menu dropdown">
                                <div class="user-profile dropdown-toggle">
                                    <?php if ($currentUser['profile_picture']): ?>
                                        <img src="<?php echo $baseURL; ?>/uploads/<?php echo $currentUser['profile_picture']; ?>" 
                                             alt="Profile" class="profile-img">
                                    <?php else: ?>
                                        <div class="profile-avatar"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                    <span class="username"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                                </div>
                                <div class="dropdown-menu">
                                    <a href="<?php echo $baseURL; ?>/profile.php" class="dropdown-link">My Profile</a>
                                    <a href="/logout.php" class="dropdown-link">Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="auth-buttons">
                                <a href="<?php echo $baseURL; ?>/login.php" class="btn btn-outline">Login</a>
                                <a href="<?php echo $baseURL; ?>/register.php" class="btn btn-primary">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
