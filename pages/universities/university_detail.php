<?php
$pageTitle = 'University Details - EduPool';
$pageDescription = 'Detailed information about the university including programs, colleges, and admission requirements.';

require_once __DIR__ . '/../../includes/header.php';

// Get university ID from URL
$universityId = (int)($_GET['id'] ?? 0);

if ($universityId <= 0) {
    redirect('/pages/universities/');
}

try {
    // Get university details
    $stmt = $pdo->prepare("SELECT * FROM universities WHERE id = ?");
    $stmt->execute([$universityId]);
    $university = $stmt->fetch();
    
    if (!$university) {
        redirect('/pages/universities/');
    }
    
    // Get affiliated colleges
    $stmt = $pdo->prepare("
        SELECT * FROM colleges 
        WHERE university_id = ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$universityId]);
    $colleges = $stmt->fetchAll();
    
    // Get offered courses
    $stmt = $pdo->prepare("
        SELECT c.* FROM courses c
        INNER JOIN university_courses uc ON c.id = uc.course_id
        WHERE uc.university_id = ?
        ORDER BY c.name ASC
    ");
    $stmt->execute([$universityId]);
    $courses = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("University detail error: " . $e->getMessage());
    redirect('/pages/universities/');
}

// Update page title with university name
$pageTitle = htmlspecialchars($university['name']) . ' - EduPool';
?>

<!-- University Header -->
<section class="university-header">
    <div class="university-banner">
        <img 
            src="<?php echo $university['image'] ? '/uploads/' . $university['image'] : 'https://placehold.co/1200x400?text=University+Campus+Aerial+View+with+Buildings'; ?>" 
            alt="<?php echo htmlspecialchars($university['name']); ?> campus"
            class="banner-image"
            onerror="this.src='https://placehold.co/1200x400?text=University+Campus+Aerial+View+with+Buildings'"
        >
        <div class="banner-overlay">
            <div class="container">
                <nav class="breadcrumb">
                    <a href="/">Home</a>
                    <span class="breadcrumb-separator">></span>
                    <a href="/pages/universities/">Universities</a>
                    <span class="breadcrumb-separator">></span>
                    <span><?php echo htmlspecialchars($university['name']); ?></span>
                </nav>
                
                <div class="university-info">
                    <h1 class="university-name"><?php echo htmlspecialchars($university['name']); ?></h1>
                    <?php if ($university['established_year']): ?>
                        <p class="university-established">Established in <?php echo $university['established_year']; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- University Content -->
<section class="university-content section">
    <div class="container">
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- About Section -->
                <div class="content-card animate-fade-in-up">
                    <h2>About <?php echo htmlspecialchars($university['name']); ?></h2>
                    <?php if ($university['description']): ?>
                        <p class="university-description"><?php echo nl2br(htmlspecialchars($university['description'])); ?></p>
                    <?php else: ?>
                        <p class="university-description">Information about this university will be updated soon.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Affiliated Colleges -->
                <?php if (!empty($colleges)): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Affiliated Colleges (<?php echo count($colleges); ?>)</h2>
                        <div class="colleges-grid">
                            <?php foreach ($colleges as $college): ?>
                                <div class="college-item card hover-lift">
                                    <div class="college-image">
                                        <img 
                                            src="<?php echo $college['image'] ? '/uploads/' . $college['image'] : 'https://placehold.co/300x200?text=College+Building+Modern+Architecture'; ?>" 
                                            alt="<?php echo htmlspecialchars($college['name']); ?>"
                                            onerror="this.src='https://placehold.co/300x200?text=College+Building+Modern+Architecture'"
                                        >
                                    </div>
                                    <div class="college-info">
                                        <h3><?php echo htmlspecialchars($college['name']); ?></h3>
                                        <p><?php echo truncateText($college['description'], 100); ?></p>
                                        <?php if ($college['location']): ?>
                                            <p class="college-location">📍 <?php echo htmlspecialchars($college['location']); ?></p>
                                        <?php endif; ?>
                                        <a href="/pages/colleges/college_detail.php?id=<?php echo $college['id']; ?>" 
                                           class="btn btn-outline btn-sm">View College</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Offered Courses -->
                <?php if (!empty($courses)): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Courses Offered (<?php echo count($courses); ?>)</h2>
                        <div class="courses-grid">
                            <?php foreach ($courses as $course): ?>
                                <div class="course-item card hover-lift">
                                    <div class="course-header">
                                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                                        <?php if ($course['duration']): ?>
                                            <span class="course-duration"><?php echo htmlspecialchars($course['duration']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($course['syllabus']): ?>
                                        <p class="course-syllabus"><?php echo truncateText($course['syllabus'], 150); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($course['career_paths']): ?>
                                        <div class="course-careers">
                                            <strong>Career Opportunities:</strong>
                                            <p><?php echo truncateText($course['career_paths'], 100); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="/pages/courses/course_detail.php?id=<?php echo $course['id']; ?>" 
                                       class="btn btn-primary btn-sm">View Course Details</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Quick Info -->
                <div class="sidebar-card">
                    <h3>Quick Information</h3>
                    <div class="info-list">
                        <?php if ($university['established_year']): ?>
                            <div class="info-item">
                                <span class="info-label">Established:</span>
                                <span class="info-value"><?php echo $university['established_year']; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="info-label">Affiliated Colleges:</span>
                            <span class="info-value"><?php echo count($colleges); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Courses Offered:</span>
                            <span class="info-value"><?php echo count($courses); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Related Links -->
                <div class="sidebar-card">
                    <h3>Explore More</h3>
                    <div class="related-links">
                        <a href="/pages/universities/" class="related-link">All Universities</a>
                        <a href="/pages/colleges/" class="related-link">Browse Colleges</a>
                        <a href="/pages/courses/" class="related-link">View All Courses</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="#" class="related-link">Save to Favorites</a>
                        <?php else: ?>
                            <a href="/register.php" class="related-link">Create Account</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* University Header */
.university-header {
    position: relative;
    margin-bottom: 2rem;
}

.university-banner {
    position: relative;
    height: 400px;
    overflow: hidden;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(37, 99, 235, 0.7), rgba(37, 99, 235, 0.9));
    display: flex;
    align-items: center;
    color: var(--white);
}

.university-info {
    text-align: center;
    margin-top: 2rem;
}

.university-name {
    font-size: 3rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.5rem;
}

.university-established {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb a:hover {
    color: var(--white);
}

.breadcrumb-separator {
    color: rgba(255, 255, 255, 0.6);
}

/* Content Layout */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.content-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
}

.content-card h2 {
    color: var(--primary-blue);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--light-blue);
}

.university-description {
    font-size: 1.125rem;
    line-height: 1.7;
    color: var(--gray);
}

/* Colleges Grid */
.colleges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.college-item {
    overflow: hidden;
}

.college-image {
    height: 150px;
    overflow: hidden;
}

.college-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.college-item:hover .college-image img {
    transform: scale(1.05);
}

.college-info {
    padding: 1rem;
}

.college-info h3 {
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
}

.college-location {
    color: var(--gray);
    font-size: 0.9rem;
    margin: 0.5rem 0;
}

/* Courses Grid */
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.course-item {
    padding: 1.5rem;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.course-header h3 {
    color: var(--dark-blue);
    margin: 0;
    flex: 1;
}

.course-duration {
    background: var(--light-blue);
    color: var(--primary-blue);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.course-syllabus {
    color: var(--gray);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.course-careers {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
}

.course-careers strong {
    color: var(--dark-blue);
}

.course-careers p {
    margin: 0.5rem 0 0 0;
    color: var(--gray);
    font-size: 0.9rem;
}

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
}

.sidebar-card h3 {
    color: var(--primary-blue);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border-color);
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    color: var(--gray);
    font-size: 0.9rem;
}

.info-value {
    color: var(--dark-blue);
    font-weight: 600;
}

.related-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.related-link {
    padding: 0.75rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
    color: var(--primary-blue);
    text-decoration: none;
    transition: var(--transition);
    text-align: center;
}

.related-link:hover {
    background: var(--light-blue);
    color: var(--dark-blue);
}

/* Responsive */
@media (max-width: 768px) {
    .university-name {
        font-size: 2rem;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .colleges-grid,
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .course-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .course-duration {
        align-self: flex-start;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
