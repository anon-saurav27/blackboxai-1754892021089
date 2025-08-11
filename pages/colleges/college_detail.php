<?php
$pageTitle = 'College Details - EduPool';
$pageDescription = 'Detailed information about the college including programs, facilities, and admission requirements.';

require_once __DIR__ . '/../../includes/header.php';

// Get college ID from URL
$collegeId = (int)($_GET['id'] ?? 0);

if ($collegeId <= 0) {
    redirect('/pages/colleges/');
}

try {
    // Get college details with university info
    $stmt = $pdo->prepare("
        SELECT c.*, u.name as university_name, u.id as university_id
        FROM colleges c
        LEFT JOIN universities u ON c.university_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$collegeId]);
    $college = $stmt->fetch();
    
    if (!$college) {
        redirect('/pages/colleges/');
    }
    
    // Get available courses with program levels
    $stmt = $pdo->prepare("
        SELECT c.*, cc.program_level 
        FROM courses c
        INNER JOIN college_courses cc ON c.id = cc.course_id
        WHERE cc.college_id = ?
        ORDER BY cc.program_level, c.name ASC
    ");
    $stmt->execute([$collegeId]);
    $courses = $stmt->fetchAll();
    
    // Group courses by program level
    $coursesByLevel = [];
    foreach ($courses as $course) {
        $coursesByLevel[$course['program_level']][] = $course;
    }
    
} catch (PDOException $e) {
    error_log("College detail error: " . $e->getMessage());
    redirect('/pages/colleges/');
}

// Update page title with college name
$pageTitle = htmlspecialchars($college['name']) . ' - EduPool';
?>

<!-- College Header -->
<section class="college-header">
    <div class="college-banner">
        <img 
            src="<?php echo $college['image'] ? '/uploads/' . $college['image'] : 'https://placehold.co/1200x400?text=Modern+College+Campus+with+Students+and+Buildings'; ?>" 
            alt="<?php echo htmlspecialchars($college['name']); ?> campus"
            class="banner-image"
            onerror="this.src='https://placehold.co/1200x400?text=Modern+College+Campus+with+Students+and+Buildings'"
        >
        <div class="banner-overlay">
            <div class="container">
                <nav class="breadcrumb">
                    <a href="/">Home</a>
                    <span class="breadcrumb-separator">></span>
                    <a href="/pages/colleges/">Colleges</a>
                    <span class="breadcrumb-separator">></span>
                    <span><?php echo htmlspecialchars($college['name']); ?></span>
                </nav>
                
                <div class="college-info">
                    <h1 class="college-name"><?php echo htmlspecialchars($college['name']); ?></h1>
                    <?php if ($college['university_name']): ?>
                        <p class="college-university">
                            Affiliated with 
                            <a href="/pages/universities/university_detail.php?id=<?php echo $college['university_id']; ?>">
                                <?php echo htmlspecialchars($college['university_name']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- College Content -->
<section class="college-content section">
    <div class="container">
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- About Section -->
                <div class="content-card animate-fade-in-up">
                    <h2>About <?php echo htmlspecialchars($college['name']); ?></h2>
                    <?php if ($college['description']): ?>
                        <p class="college-description"><?php echo nl2br(htmlspecialchars($college['description'])); ?></p>
                    <?php else: ?>
                        <p class="college-description">Information about this college will be updated soon.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Programs Offered -->
                <?php if (!empty($coursesByLevel)): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Programs Offered</h2>
                        
                        <?php foreach ($coursesByLevel as $level => $levelCourses): ?>
                            <div class="program-level">
                                <h3 class="level-title"><?php echo htmlspecialchars($level); ?> Programs</h3>
                                <div class="courses-grid">
                                    <?php foreach ($levelCourses as $course): ?>
                                        <div class="course-item card hover-lift">
                                            <div class="course-header">
                                                <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                                                <div class="course-badges">
                                                    <span class="level-badge level-<?php echo strtolower($course['program_level']); ?>">
                                                        <?php echo htmlspecialchars($course['program_level']); ?>
                                                    </span>
                                                    <?php if ($course['duration']): ?>
                                                        <span class="duration-badge">
                                                            <?php echo htmlspecialchars($course['duration']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($course['syllabus']): ?>
                                                <p class="course-syllabus"><?php echo truncateText($course['syllabus'], 120); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($course['eligibility']): ?>
                                                <div class="course-eligibility">
                                                    <strong>Eligibility:</strong>
                                                    <p><?php echo truncateText($course['eligibility'], 80); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="course-actions">
                                                <a href="/pages/courses/course_detail.php?id=<?php echo $course['id']; ?>" 
                                                   class="btn btn-primary btn-sm">View Details</a>
                                                <a href="#" class="btn btn-outline btn-sm">Apply Now</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Location & Contact -->
                <?php if ($college['location'] || $college['map_link'] || $college['website_url']): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Location & Contact</h2>
                        <div class="location-info">
                            <?php if ($college['location']): ?>
                                <div class="location-item">
                                    <span class="location-icon">📍</span>
                                    <div class="location-details">
                                        <strong>Address:</strong>
                                        <p><?php echo htmlspecialchars($college['location']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="location-actions">
                                <?php if ($college['map_link']): ?>
                                    <a href="<?php echo htmlspecialchars($college['map_link']); ?>" 
                                       target="_blank" class="btn btn-outline">
                                        🗺️ View on Map
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($college['website_url']): ?>
                                    <a href="<?php echo htmlspecialchars($college['website_url']); ?>" 
                                       target="_blank" class="btn btn-primary">
                                        🌐 Visit Website
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary w-full">Apply Now</a>
                        <?php if ($college['website_url']): ?>
                            <a href="<?php echo htmlspecialchars($college['website_url']); ?>" 
                               target="_blank" class="btn btn-outline w-full">Visit Website</a>
                        <?php endif; ?>
                        <?php if (isLoggedIn()): ?>
                            <a href="#" class="btn btn-outline w-full">Save to Favorites</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Info -->
                <div class="sidebar-card">
                    <h3>College Information</h3>
                    <div class="info-list">
                        <?php if ($college['university_name']): ?>
                            <div class="info-item">
                                <span class="info-label">Affiliated University:</span>
                                <span class="info-value">
                                    <a href="/pages/universities/university_detail.php?id=<?php echo $college['university_id']; ?>">
                                        <?php echo htmlspecialchars($college['university_name']); ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="info-label">Programs Available:</span>
                            <span class="info-value"><?php echo count($courses); ?></span>
                        </div>
                        
                        <?php if (!empty($coursesByLevel)): ?>
                            <div class="info-item">
                                <span class="info-label">Program Levels:</span>
                                <span class="info-value"><?php echo implode(', ', array_keys($coursesByLevel)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Related Links -->
                <div class="sidebar-card">
                    <h3>Explore More</h3>
                    <div class="related-links">
                        <a href="/pages/colleges/" class="related-link">All Colleges</a>
                        <a href="/pages/universities/" class="related-link">Browse Universities</a>
                        <a href="/pages/courses/" class="related-link">View All Courses</a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="/register.php" class="related-link">Create Account</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* College Header */
.college-header {
    position: relative;
    margin-bottom: 2rem;
}

.college-banner {
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

.college-info {
    text-align: center;
    margin-top: 2rem;
}

.college-name {
    font-size: 3rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.5rem;
}

.college-university {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

.college-university a {
    color: var(--white);
    text-decoration: underline;
    transition: var(--transition);
}

.college-university a:hover {
    color: rgba(255, 255, 255, 0.8);
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

.college-description {
    font-size: 1.125rem;
    line-height: 1.7;
    color: var(--gray);
}

/* Program Levels */
.program-level {
    margin-bottom: 2rem;
}

.program-level:last-child {
    margin-bottom: 0;
}

.level-title {
    color: var(--dark-blue);
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    background: var(--light-blue);
    border-radius: var(--border-radius);
    font-size: 1.25rem;
}

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

.course-header h4 {
    color: var(--dark-blue);
    margin: 0;
    flex: 1;
}

.course-badges {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    align-items: flex-end;
}

.level-badge,
.duration-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.level-badge {
    color: var(--white);
}

.level-diploma { background: var(--warning); }
.level-bachelor { background: var(--primary-blue); }
.level-master { background: var(--success); }
.level-phd { background: var(--error); }

.duration-badge {
    background: var(--light-gray);
    color: var(--gray);
}

.course-syllabus {
    color: var(--gray);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.course-eligibility {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
}

.course-eligibility strong {
    color: var(--dark-blue);
}

.course-eligibility p {
    margin: 0.5rem 0 0 0;
    color: var(--gray);
    font-size: 0.9rem;
}

.course-actions {
    display: flex;
    gap: 0.5rem;
}

/* Location Info */
.location-info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.location-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.location-icon {
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.location-details strong {
    color: var(--dark-blue);
}

.location-details p {
    margin: 0.25rem 0 0 0;
    color: var(--gray);
}

.location-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
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

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    color: var(--gray);
    font-size: 0.9rem;
}

.info-value {
    color: var(--dark-blue);
    font-weight: 600;
}

.info-value a {
    color: var(--primary-blue);
    text-decoration: none;
    transition: var(--transition);
}

.info-value a:hover {
    text-decoration: underline;
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
    .college-name {
        font-size: 2rem;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .course-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .course-badges {
        align-items: flex-start;
        flex-direction: row;
        gap: 0.5rem;
    }
    
    .course-actions {
        flex-direction: column;
    }
    
    .location-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
