<?php
$pageTitle = 'Course Details - EduPool';
$pageDescription = 'Detailed information about the course including syllabus, eligibility, career opportunities, and admission requirements.';

require_once __DIR__ . '/../../includes/header.php';

// Get course ID from URL
$courseId = (int)($_GET['id'] ?? 0);

if ($courseId <= 0) {
    redirect('/pages/courses/');
}

try {
    // Get course details
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        redirect('/pages/courses/');
    }
    
    // Get universities offering this course
    $stmt = $pdo->prepare("
        SELECT u.* FROM universities u
        INNER JOIN university_courses uc ON u.id = uc.university_id
        WHERE uc.course_id = ?
        ORDER BY u.name ASC
    ");
    $stmt->execute([$courseId]);
    $universities = $stmt->fetchAll();
    
    // Get colleges offering this course
    $stmt = $pdo->prepare("
        SELECT c.*, cc.program_level, u.name as university_name
        FROM colleges c
        INNER JOIN college_courses cc ON c.id = cc.college_id
        LEFT JOIN universities u ON c.university_id = u.id
        WHERE cc.course_id = ?
        ORDER BY cc.program_level, c.name ASC
    ");
    $stmt->execute([$courseId]);
    $colleges = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Course detail error: " . $e->getMessage());
    redirect('/pages/courses/');
}

// Update page title with course name
$pageTitle = htmlspecialchars($course['name']) . ' - EduPool';
?>

<!-- Course Header -->
<section class="course-header">
    <div class="course-banner gradient-animated">
        <div class="container">
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span class="breadcrumb-separator">></span>
                <a href="/pages/courses/">Courses</a>
                <span class="breadcrumb-separator">></span>
                <span><?php echo htmlspecialchars($course['name']); ?></span>
            </nav>
            
            <div class="course-info">
                <h1 class="course-name"><?php echo htmlspecialchars($course['name']); ?></h1>
                <?php if ($course['duration']): ?>
                    <p class="course-duration">Duration: <?php echo htmlspecialchars($course['duration']); ?></p>
                <?php endif; ?>
                
                <div class="course-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($universities); ?></span>
                        <span class="stat-label">Universities</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($colleges); ?></span>
                        <span class="stat-label">Colleges</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Course Content -->
<section class="course-content section">
    <div class="container">
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Course Overview -->
                <div class="content-card animate-fade-in-up">
                    <h2>Course Overview</h2>
                    <?php if ($course['syllabus']): ?>
                        <div class="course-overview">
                            <p><?php echo nl2br(htmlspecialchars($course['syllabus'])); ?></p>
                        </div>
                    <?php else: ?>
                        <p>Course overview will be updated soon.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Syllabus -->
                <?php if ($course['syllabus']): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Detailed Syllabus</h2>
                        <div class="syllabus-content">
                            <?php
                            // Split syllabus by commas and create a structured layout
                            $syllabusItems = array_map('trim', explode(',', $course['syllabus']));
                            if (count($syllabusItems) > 1):
                            ?>
                                <div class="syllabus-grid">
                                    <?php foreach ($syllabusItems as $index => $item): ?>
                                        <div class="syllabus-item">
                                            <div class="syllabus-number"><?php echo $index + 1; ?></div>
                                            <div class="syllabus-text"><?php echo htmlspecialchars($item); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p><?php echo nl2br(htmlspecialchars($course['syllabus'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Career Opportunities -->
                <?php if ($course['career_paths']): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Career Opportunities</h2>
                        <div class="career-content">
                            <?php
                            $careerPaths = array_map('trim', explode(',', $course['career_paths']));
                            if (count($careerPaths) > 1):
                            ?>
                                <div class="career-grid">
                                    <?php foreach ($careerPaths as $career): ?>
                                        <div class="career-item">
                                            <span class="career-icon">💼</span>
                                            <span class="career-title"><?php echo htmlspecialchars($career); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p><?php echo nl2br(htmlspecialchars($course['career_paths'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Eligibility & Requirements -->
                <div class="content-card animate-fade-in-up">
                    <h2>Eligibility & Requirements</h2>
                    <div class="requirements-content">
                        <?php if ($course['eligibility']): ?>
                            <div class="requirement-section">
                                <h3>Eligibility Criteria</h3>
                                <p><?php echo nl2br(htmlspecialchars($course['eligibility'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($course['required_documents']): ?>
                            <div class="requirement-section">
                                <h3>Required Documents</h3>
                                <?php
                                $documents = array_map('trim', explode(',', $course['required_documents']));
                                if (count($documents) > 1):
                                ?>
                                    <ul class="documents-list">
                                        <?php foreach ($documents as $document): ?>
                                            <li><?php echo htmlspecialchars($document); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p><?php echo nl2br(htmlspecialchars($course['required_documents'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Universities Offering This Course -->
                <?php if (!empty($universities)): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Universities Offering This Course</h2>
                        <div class="institutions-grid">
                            <?php foreach ($universities as $university): ?>
                                <div class="institution-item card hover-lift">
                                    <div class="institution-image">
                                        <img 
                                            src="<?php echo $university['image'] ? '/uploads/' . $university['image'] : 'https://placehold.co/200x120?text=University+Building'; ?>" 
                                            alt="<?php echo htmlspecialchars($university['name']); ?>"
                                            onerror="this.src='https://placehold.co/200x120?text=University+Building'"
                                        >
                                    </div>
                                    <div class="institution-info">
                                        <h4><?php echo htmlspecialchars($university['name']); ?></h4>
                                        <?php if ($university['established_year']): ?>
                                            <p class="institution-year">Est. <?php echo $university['established_year']; ?></p>
                                        <?php endif; ?>
                                        <p><?php echo truncateText($university['description'], 80); ?></p>
                                        <a href="/pages/universities/university_detail.php?id=<?php echo $university['id']; ?>" 
                                           class="btn btn-outline btn-sm">View University</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Colleges Offering This Course -->
                <?php if (!empty($colleges)): ?>
                    <div class="content-card animate-fade-in-up">
                        <h2>Colleges Offering This Course</h2>
                        <div class="institutions-grid">
                            <?php foreach ($colleges as $college): ?>
                                <div class="institution-item card hover-lift">
                                    <div class="institution-image">
                                        <img 
                                            src="<?php echo $college['image'] ? '/uploads/' . $college['image'] : 'https://placehold.co/200x120?text=College+Campus'; ?>" 
                                            alt="<?php echo htmlspecialchars($college['name']); ?>"
                                            onerror="this.src='https://placehold.co/200x120?text=College+Campus'"
                                        >
                                        <div class="program-level-badge">
                                            <?php echo htmlspecialchars($college['program_level']); ?>
                                        </div>
                                    </div>
                                    <div class="institution-info">
                                        <h4><?php echo htmlspecialchars($college['name']); ?></h4>
                                        <?php if ($college['university_name']): ?>
                                            <p class="institution-university">
                                                Affiliated with <?php echo htmlspecialchars($college['university_name']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p><?php echo truncateText($college['description'], 80); ?></p>
                                        <a href="/pages/colleges/college_detail.php?id=<?php echo $college['id']; ?>" 
                                           class="btn btn-outline btn-sm">View College</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3>Interested in This Course?</h3>
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary w-full">Apply Now</a>
                        <a href="#" class="btn btn-outline w-full">Download Brochure</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="#" class="btn btn-outline w-full">Save to Favorites</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Course Info -->
                <div class="sidebar-card">
                    <h3>Course Information</h3>
                    <div class="info-list">
                        <?php if ($course['duration']): ?>
                            <div class="info-item">
                                <span class="info-label">Duration:</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['duration']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="info-label">Available at:</span>
                            <span class="info-value"><?php echo count($universities); ?> Universities</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Colleges offering:</span>
                            <span class="info-value"><?php echo count($colleges); ?> Colleges</span>
                        </div>
                        
                        <?php if (!empty($colleges)): ?>
                            <?php
                            $levels = array_unique(array_column($colleges, 'program_level'));
                            ?>
                            <div class="info-item">
                                <span class="info-label">Program Levels:</span>
                                <span class="info-value"><?php echo implode(', ', $levels); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Related Links -->
                <div class="sidebar-card">
                    <h3>Explore More</h3>
                    <div class="related-links">
                        <a href="/pages/courses/" class="related-link">All Courses</a>
                        <a href="/pages/universities/" class="related-link">Browse Universities</a>
                        <a href="/pages/colleges/" class="related-link">View Colleges</a>
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
/* Course Header */
.course-header {
    position: relative;
    margin-bottom: 2rem;
}

.course-banner {
    padding: 4rem 0;
    color: var(--white);
}

.course-info {
    text-align: center;
    margin-top: 2rem;
}

.course-name {
    font-size: 3rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.5rem;
}

.course-duration {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 2rem 0;
}

.course-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--white);
}

.stat-label {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
}

.breadcrumb {
    display: flex;
    justify-content: center;
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

.course-overview {
    font-size: 1.125rem;
    line-height: 1.7;
    color: var(--gray);
}

/* Syllabus */
.syllabus-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.syllabus-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.syllabus-item:hover {
    background: var(--light-blue);
}

.syllabus-number {
    width: 30px;
    height: 30px;
    background: var(--primary-blue);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.syllabus-text {
    color: var(--dark-gray);
    font-weight: 500;
}

/* Career Opportunities */
.career-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.career-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.career-item:hover {
    background: var(--light-blue);
    transform: translateY(-2px);
}

.career-icon {
    font-size: 1.5rem;
}

.career-title {
    color: var(--dark-blue);
    font-weight: 600;
}

/* Requirements */
.requirements-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.requirement-section h3 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
}

.documents-list {
    list-style: none;
    padding: 0;
}

.documents-list li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
    color: var(--gray);
}

.documents-list li::before {
    content: '📄';
    position: absolute;
    left: 0;
    top: 0.5rem;
}

/* Institutions Grid */
.institutions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.institution-item {
    overflow: hidden;
}

.institution-image {
    position: relative;
    height: 120px;
    overflow: hidden;
}

.institution-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.institution-item:hover .institution-image img {
    transform: scale(1.05);
}

.program-level-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: var(--primary-blue);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.institution-info {
    padding: 1rem;
}

.institution-info h4 {
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
}

.institution-year,
.institution-university {
    color: var(--primary-blue);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
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
    .course-name {
        font-size: 2rem;
    }
    
    .course-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .syllabus-grid,
    .career-grid,
    .institutions-grid {
        grid-template-columns: 1fr;
    }
    
    .syllabus-item,
    .career-item {
        flex-direction: column;
        text-align: center;
    }
    
    .syllabus-number {
        align-self: center;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
