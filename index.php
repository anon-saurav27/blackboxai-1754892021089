<?php
$pageTitle = 'EduPool - Educational Portal for Nepal';
$pageDescription = 'Find the best universities, colleges, and courses in Nepal. Your gateway to quality education.';

require_once __DIR__ . '/includes/header.php';

// Get featured universities, colleges, and courses
try {
    // Get featured universities (latest 3)
    $stmt = $pdo->query("SELECT * FROM universities ORDER BY created_at DESC LIMIT 3");
    $featuredUniversities = $stmt->fetchAll();
    
    // Get featured colleges (latest 6)
    $stmt = $pdo->query("
        SELECT c.*, u.name as university_name 
        FROM colleges c 
        LEFT JOIN universities u ON c.university_id = u.id 
        ORDER BY c.created_at DESC 
        LIMIT 6
    ");
    $featuredColleges = $stmt->fetchAll();
    
    // Get popular courses (latest 4)
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 4");
    $popularCourses = $stmt->fetchAll();
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM universities");
    $universitiesCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM colleges");
    $collegesCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
    $coursesCount = $stmt->fetch()['count'];
    
} catch (PDOException $e) {
    error_log("Homepage data fetch error: " . $e->getMessage());
    $featuredUniversities = $featuredColleges = $popularCourses = [];
    $universitiesCount = $collegesCount = $coursesCount = 0;
}

// Handle search
$searchResults = [];
$searchQuery = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = sanitizeInput($_GET['search']);
    $searchType = sanitizeInput($_GET['type'] ?? 'all');
    
    try {
        if ($searchType === 'all' || $searchType === 'universities') {
            $stmt = $pdo->prepare("SELECT 'university' as type, id, name, description FROM universities WHERE name LIKE ? OR description LIKE ?");
            $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
            $searchResults = array_merge($searchResults, $stmt->fetchAll());
        }
        
        if ($searchType === 'all' || $searchType === 'colleges') {
            $stmt = $pdo->prepare("SELECT 'college' as type, id, name, description FROM colleges WHERE name LIKE ? OR description LIKE ?");
            $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
            $searchResults = array_merge($searchResults, $stmt->fetchAll());
        }
        
        if ($searchType === 'all' || $searchType === 'courses') {
            $stmt = $pdo->prepare("SELECT 'course' as type, id, name, syllabus as description FROM courses WHERE name LIKE ? OR syllabus LIKE ?");
            $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
            $searchResults = array_merge($searchResults, $stmt->fetchAll());
        }
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background gradient-animated"></div>
    <div class="container">
        <div class="hero-content animate-fade-in-up">
            <h1 class="hero-title">Find Your Perfect Education Path in Nepal</h1>
            <p class="hero-subtitle">Discover universities, colleges, and courses that shape your future. Your gateway to quality education starts here.</p>
            
            <!-- Search Form -->
            <div class="hero-search animate-fade-in-up" id="search">
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <input 
                            type="text" 
                            name="search" 
                            class="search-input" 
                            placeholder="Search universities, colleges, or courses..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                        <select name="type" class="search-select">
                            <option value="all">All</option>
                            <option value="universities" <?php echo ($_GET['type'] ?? '') === 'universities' ? 'selected' : ''; ?>>Universities</option>
                            <option value="colleges" <?php echo ($_GET['type'] ?? '') === 'colleges' ? 'selected' : ''; ?>>Colleges</option>
                            <option value="courses" <?php echo ($_GET['type'] ?? '') === 'courses' ? 'selected' : ''; ?>>Courses</option>
                        </select>
                        <button type="submit" class="search-btn btn-animated">Search</button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Stats -->
            <div class="hero-stats animate-fade-in-up">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $universitiesCount; ?></span>
                    <span class="stat-label">Universities</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $collegesCount; ?></span>
                    <span class="stat-label">Colleges</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $coursesCount; ?></span>
                    <span class="stat-label">Courses</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<?php if (!empty($searchResults)): ?>
<section class="search-results-section section">
    <div class="container">
        <div class="section-title">
            <h2>Search Results</h2>
            <p>Found <?php echo count($searchResults); ?> results for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
        </div>
        
        <div class="search-results grid grid-2 stagger-animation">
            <?php foreach ($searchResults as $result): ?>
                <div class="search-result-card card hover-lift">
                    <div class="card-body">
                        <div class="result-type">
                            <span class="badge badge-<?php echo $result['type']; ?>">
                                <?php echo ucfirst($result['type']); ?>
                            </span>
                        </div>
                        <h3><?php echo htmlspecialchars($result['name']); ?></h3>
                        <p><?php echo truncateText($result['description'], 150); ?></p>
                        <a href="/pages/<?php echo $result['type'] === 'university' ? 'universities' : ($result['type'] === 'college' ? 'colleges' : 'courses'); ?>/<?php echo $result['type'] === 'university' ? 'university_detail' : ($result['type'] === 'college' ? 'college_detail' : 'course_detail'); ?>.php?id=<?php echo $result['id']; ?>" 
                           class="btn btn-outline">Learn More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Universities -->
<?php if (!empty($featuredUniversities)): ?>
<section class="featured-universities section">
    <div class="container">
        <div class="section-title">
            <h2>Featured Universities</h2>
            <p>Explore top universities in Nepal offering quality education</p>
        </div>
        
        <div class="universities-grid grid grid-3 stagger-animation">
            <?php foreach ($featuredUniversities as $university): ?>
                <div class="university-card card hover-lift">
                    <div class="card-image-container">
                        <img 
                            src="<?php echo $university['image'] ? '/uploads/' . $university['image'] : 'https://placehold.co/400x200?text=University+Campus+Building+with+Blue+Sky'; ?>" 
                            alt="<?php echo htmlspecialchars($university['name']); ?> campus"
                            class="card-image"
                            onerror="this.src='https://placehold.co/400x200?text=University+Campus+Building+with+Blue+Sky'"
                        >
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($university['name']); ?></h3>
                        <?php if ($university['established_year']): ?>
                            <p class="university-year">Established: <?php echo $university['established_year']; ?></p>
                        <?php endif; ?>
                        <p><?php echo truncateText($university['description'], 120); ?></p>
                        <a href="/pages/universities/university_detail.php?id=<?php echo $university['id']; ?>" 
                           class="btn btn-primary">Explore University</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/pages/universities/" class="btn btn-outline btn-lg">View All Universities</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Colleges -->
<?php if (!empty($featuredColleges)): ?>
<section class="featured-colleges section curved-section">
    <div class="container">
        <div class="section-title">
            <h2>Popular Colleges</h2>
            <p>Discover colleges offering diverse programs and opportunities</p>
        </div>
        
        <div class="colleges-grid grid grid-3 stagger-animation">
            <?php foreach ($featuredColleges as $college): ?>
                <div class="college-card card hover-lift">
                    <div class="card-image-container">
                        <img 
                            src="<?php echo $college['image'] ? '/uploads/' . $college['image'] : 'https://placehold.co/350x200?text=Modern+College+Campus+with+Students'; ?>" 
                            alt="<?php echo htmlspecialchars($college['name']); ?> campus"
                            class="card-image"
                            onerror="this.src='https://placehold.co/350x200?text=Modern+College+Campus+with+Students'"
                        >
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($college['name']); ?></h3>
                        <?php if ($college['university_name']): ?>
                            <p class="college-university">Affiliated with: <?php echo htmlspecialchars($college['university_name']); ?></p>
                        <?php endif; ?>
                        <p><?php echo truncateText($college['description'], 100); ?></p>
                        <a href="/pages/colleges/college_detail.php?id=<?php echo $college['id']; ?>" 
                           class="btn btn-secondary">View College</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/pages/colleges/" class="btn btn-outline btn-lg">View All Colleges</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Courses -->
<?php if (!empty($popularCourses)): ?>
<section class="popular-courses section">
    <div class="container">
        <div class="section-title">
            <h2>Popular Courses</h2>
            <p>Explore in-demand courses that lead to successful careers</p>
        </div>
        
        <div class="courses-grid grid grid-2 stagger-animation">
            <?php foreach ($popularCourses as $course): ?>
                <div class="course-card card hover-lift">
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                        <?php if ($course['duration']): ?>
                            <p class="course-duration">Duration: <?php echo htmlspecialchars($course['duration']); ?></p>
                        <?php endif; ?>
                        <p><?php echo truncateText($course['syllabus'], 150); ?></p>
                        <div class="course-meta">
                            <?php if ($course['career_paths']): ?>
                                <p><strong>Career Opportunities:</strong> <?php echo truncateText($course['career_paths'], 100); ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="/pages/courses/course_detail.php?id=<?php echo $course['id']; ?>" 
                           class="btn btn-primary">View Course Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/pages/courses/" class="btn btn-outline btn-lg">View All Courses</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- About Section -->
<section class="about-section section curved-section" id="about">
    <div class="container">
        <div class="about-content grid grid-2">
            <div class="about-text animate-fade-in-left">
                <h2>About EduPool</h2>
                <p>EduPool is Nepal's comprehensive educational portal designed to help students find the perfect educational path. We connect aspiring students with top universities, colleges, and courses across Nepal.</p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <h4>🎓 Comprehensive Database</h4>
                        <p>Access detailed information about universities, colleges, and courses in one place.</p>
                    </div>
                    <div class="feature-item">
                        <h4>🔍 Smart Search</h4>
                        <p>Find exactly what you're looking for with our advanced search and filtering options.</p>
                    </div>
                    <div class="feature-item">
                        <h4>📊 Updated Information</h4>
                        <p>Get the latest information about admission requirements, courses, and career opportunities.</p>
                    </div>
                </div>
                
                <div class="about-actions">
                    <a href="/register.php" class="btn btn-primary btn-lg">Join EduPool</a>
                    <a href="#search" class="btn btn-outline btn-lg">Start Searching</a>
                </div>
            </div>
            
            <div class="about-image animate-fade-in-right">
                <img 
                    src="https://placehold.co/600x400?text=Students+Studying+in+Modern+Library+Environment" 
                    alt="Students studying in modern library environment"
                    class="rounded-lg shadow-lg"
                    onerror="this.src='https://placehold.co/600x400?text=Educational+Excellence+in+Nepal'"
                >
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section gradient-animated">
    <div class="container">
        <div class="cta-content text-center animate-fade-in-up">
            <h2>Ready to Start Your Educational Journey?</h2>
            <p>Join thousands of students who have found their perfect educational path through EduPool.</p>
            <div class="cta-actions">
                <a href="/register.php" class="btn btn-white btn-lg btn-animated">Create Free Account</a>
                <a href="/pages/universities/" class="btn btn-outline btn-white btn-lg">Explore Universities</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section Styles */
.hero-section {
    position: relative;
    min-height: 80vh;
    display: flex;
    align-items: center;
    color: var(--white);
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue), var(--accent-blue));
    z-index: -1;
}

.hero-content {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--white);
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 3rem;
    color: rgba(255, 255, 255, 0.9);
}

.hero-search {
    margin-bottom: 3rem;
}

.search-input-group {
    display: flex;
    gap: 0.5rem;
    max-width: 600px;
    margin: 0 auto;
    background: var(--white);
    padding: 0.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
}

.search-input {
    flex: 1;
    border: none;
    padding: 1rem;
    font-size: 1rem;
    border-radius: var(--border-radius);
}

.search-select {
    border: none;
    padding: 1rem;
    font-size: 1rem;
    border-radius: var(--border-radius);
    background: var(--light-gray);
    min-width: 120px;
}

.search-btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: var(--border-radius);
    background: var(--primary-blue);
    color: var(--white);
    font-weight: 600;
    cursor: pointer;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
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

/* Card Styles */
.university-year,
.college-university,
.course-duration {
    color: var(--primary-blue);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.course-meta {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

/* About Section */
.about-features {
    margin: 2rem 0;
}

.feature-item {
    margin-bottom: 1.5rem;
}

.feature-item h4 {
    margin-bottom: 0.5rem;
    color: var(--primary-blue);
}

.about-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* CTA Section */
.cta-section {
    color: var(--white);
}

.cta-content h2 {
    color: var(--white);
    margin-bottom: 1rem;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

/* Search Results */
.search-results-section {
    background: var(--light-gray);
}

.result-type {
    margin-bottom: 1rem;
}

/* Badges */
.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-university {
    background: var(--light-blue);
    color: var(--primary-blue);
}

.badge-college {
    background: #dcfce7;
    color: #166534;
}

.badge-course {
    background: #fef3c7;
    color: #92400e;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .search-input-group {
        flex-direction: column;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .about-actions,
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
