<?php
$pageTitle = 'Courses in Nepal - EduPool';
$pageDescription = 'Explore courses offered by universities and colleges in Nepal. Find detailed information about syllabus, eligibility, career opportunities, and admission requirements.';

require_once __DIR__ . '/../../includes/header.php';

// Pagination settings
$itemsPerPage = 8;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset = ($currentPage - 1) * $itemsPerPage;

// Search and filter parameters
$searchQuery = sanitizeInput($_GET['search'] ?? '');
$sortBy = sanitizeInput($_GET['sort'] ?? 'name');
$filterDuration = sanitizeInput($_GET['duration'] ?? '');

// Build query conditions
$conditions = [];
$params = [];

if (!empty($searchQuery)) {
    $conditions[] = "(name LIKE ? OR syllabus LIKE ? OR career_paths LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if (!empty($filterDuration)) {
    $conditions[] = "duration LIKE ?";
    $params[] = "%$filterDuration%";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Valid sort options
$validSorts = ['name', 'duration', 'created_at'];
$sortBy = in_array($sortBy, $validSorts) ? $sortBy : 'name';
$sortOrder = $sortBy === 'created_at' ? 'DESC' : 'ASC';

try {
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM courses $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Get courses
    $query = "SELECT * FROM courses $whereClause ORDER BY $sortBy $sortOrder LIMIT $itemsPerPage OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    // Get available durations for filter
    $stmt = $pdo->query("SELECT DISTINCT duration FROM courses WHERE duration IS NOT NULL AND duration != '' ORDER BY duration ASC");
    $availableDurations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Courses page error: " . $e->getMessage());
    $courses = [];
    $availableDurations = [];
    $totalItems = 0;
    $totalPages = 0;
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content animate-fade-in-up">
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span class="breadcrumb-separator">></span>
                <span>Courses</span>
            </nav>
            <h1 class="page-title">Courses in Nepal</h1>
            <p class="page-subtitle">Explore courses that lead to successful careers and bright futures</p>
        </div>
    </div>
</section>

<!-- Search and Filter Section -->
<section class="search-filter-section">
    <div class="container">
        <div class="search-filter-card animate-fade-in-up">
            <form method="GET" class="search-filter-form">
                <div class="search-filter-row">
                    <div class="search-group">
                        <input 
                            type="text" 
                            name="search" 
                            class="search-input" 
                            placeholder="Search courses, careers, or subjects..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                    </div>
                    
                    <div class="filter-group">
                        <select name="duration" class="filter-select">
                            <option value="">All Durations</option>
                            <?php foreach ($availableDurations as $duration): ?>
                                <option value="<?php echo htmlspecialchars($duration); ?>" <?php echo $filterDuration === $duration ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($duration); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="sort-group">
                        <select name="sort" class="sort-select">
                            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="duration" <?php echo $sortBy === 'duration' ? 'selected' : ''; ?>>Duration</option>
                            <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Recently Added</option>
                        </select>
                    </div>
                    
                    <div class="action-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="/pages/courses/" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="results-section section">
    <div class="container">
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-info">
                <h2>Courses Found</h2>
                <p>Showing <?php echo count($courses); ?> of <?php echo $totalItems; ?> courses</p>
            </div>
            
            <?php if (!empty($searchQuery) || !empty($filterDuration)): ?>
                <div class="active-filters">
                    <span class="filter-label">Active filters:</span>
                    <?php if (!empty($searchQuery)): ?>
                        <span class="filter-tag">
                            Search: "<?php echo htmlspecialchars($searchQuery); ?>"
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>" class="filter-remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filterDuration)): ?>
                        <span class="filter-tag">
                            Duration: <?php echo htmlspecialchars($filterDuration); ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['duration' => ''])); ?>" class="filter-remove">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Courses Grid -->
        <?php if (!empty($courses)): ?>
            <div class="courses-grid grid grid-2 stagger-animation">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card card hover-lift">
                        <div class="course-header">
                            <div class="course-title-section">
                                <h3 class="course-name">
                                    <a href="/pages/courses/course_detail.php?id=<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </a>
                                </h3>
                                <?php if ($course['duration']): ?>
                                    <span class="course-duration"><?php echo htmlspecialchars($course['duration']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="course-body">
                            <?php if ($course['syllabus']): ?>
                                <div class="course-syllabus">
                                    <h4>Course Overview</h4>
                                    <p><?php echo truncateText($course['syllabus'], 150); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($course['career_paths']): ?>
                                <div class="course-careers">
                                    <h4>Career Opportunities</h4>
                                    <p><?php echo truncateText($course['career_paths'], 120); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($course['eligibility']): ?>
                                <div class="course-eligibility">
                                    <h4>Eligibility</h4>
                                    <p><?php echo truncateText($course['eligibility'], 100); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-footer">
                            <div class="course-meta">
                                <?php
                                // Get universities and colleges offering this course
                                try {
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM university_courses WHERE course_id = ?");
                                    $stmt->execute([$course['id']]);
                                    $universitiesCount = $stmt->fetch()['count'];
                                    
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM college_courses WHERE course_id = ?");
                                    $stmt->execute([$course['id']]);
                                    $collegesCount = $stmt->fetch()['count'];
                                } catch (PDOException $e) {
                                    $universitiesCount = $collegesCount = 0;
                                }
                                ?>
                                <div class="meta-item">
                                    <span class="meta-icon">🏛️</span>
                                    <span class="meta-text"><?php echo $universitiesCount; ?> Universities</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">🏫</span>
                                    <span class="meta-text"><?php echo $collegesCount; ?> Colleges</span>
                                </div>
                            </div>
                            
                            <div class="course-actions">
                                <a href="/pages/courses/course_detail.php?id=<?php echo $course['id']; ?>" 
                                   class="btn btn-primary">View Course Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php
                    $baseUrl = '/pages/courses/';
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                    ?>
                    
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $currentPage - 1; ?><?php echo $queryString; ?>" 
                               class="pagination-btn">← Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $i; ?><?php echo $queryString; ?>" 
                               class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $currentPage + 1; ?><?php echo $queryString; ?>" 
                               class="pagination-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pagination-info">
                        Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- No Results -->
            <div class="no-results animate-fade-in">
                <div class="no-results-icon">📚</div>
                <h3>No Courses Found</h3>
                <p>We couldn't find any courses matching your criteria.</p>
                <div class="no-results-actions">
                    <a href="/pages/courses/" class="btn btn-primary">View All Courses</a>
                    <a href="/" class="btn btn-outline">Back to Homepage</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Categories -->
<section class="categories-section section">
    <div class="container">
        <div class="section-title">
            <h2>Popular Course Categories</h2>
            <p>Explore courses by popular fields of study</p>
        </div>
        
        <div class="categories-grid grid grid-4 stagger-animation">
            <div class="category-card card hover-lift">
                <div class="category-icon">💻</div>
                <h3>Engineering & Technology</h3>
                <p>Computer Science, Engineering, IT, and Technology courses</p>
                <a href="/pages/courses/?search=engineering" class="btn btn-outline btn-sm">Explore</a>
            </div>
            
            <div class="category-card card hover-lift">
                <div class="category-icon">💼</div>
                <h3>Business & Management</h3>
                <p>MBA, BBA, Management, and Business Administration courses</p>
                <a href="/pages/courses/?search=business" class="btn btn-outline btn-sm">Explore</a>
            </div>
            
            <div class="category-card card hover-lift">
                <div class="category-icon">🏥</div>
                <h3>Health & Medicine</h3>
                <p>Medical, Nursing, Pharmacy, and Healthcare courses</p>
                <a href="/pages/courses/?search=medical" class="btn btn-outline btn-sm">Explore</a>
            </div>
            
            <div class="category-card card hover-lift">
                <div class="category-icon">🎨</div>
                <h3>Arts & Humanities</h3>
                <p>Literature, Arts, Social Sciences, and Humanities courses</p>
                <a href="/pages/courses/?search=arts" class="btn btn-outline btn-sm">Explore</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section gradient-animated">
    <div class="container">
        <div class="cta-content text-center animate-fade-in-up">
            <h2>Ready to Start Your Learning Journey?</h2>
            <p>Discover the perfect course that aligns with your career goals and interests.</p>
            <div class="cta-actions">
                <a href="/register.php" class="btn btn-white btn-lg">Create Account</a>
                <a href="/pages/universities/" class="btn btn-outline btn-white btn-lg">Browse Universities</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    color: var(--white);
    padding: 4rem 0 2rem;
}

.page-header-content {
    text-align: center;
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

.page-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--white);
}

.page-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    max-width: 600px;
    margin: 0 auto;
}

/* Search and Filter */
.search-filter-section {
    padding: 2rem 0;
    background: var(--light-gray);
}

.search-filter-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
}

.search-filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.search-group,
.filter-group,
.sort-group {
    display: flex;
    flex-direction: column;
}

.search-input,
.filter-select,
.sort-select {
    padding: 0.875rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.search-input:focus,
.filter-select:focus,
.sort-select:focus {
    border-color: var(--primary-blue);
    outline: none;
}

.action-group {
    display: flex;
    gap: 0.5rem;
}

/* Results */
.results-section {
    padding: 3rem 0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.results-info h2 {
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
}

.results-info p {
    color: var(--gray);
    margin: 0;
}

.active-filters {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-label {
    font-size: 0.9rem;
    color: var(--gray);
}

.filter-tag {
    background: var(--light-blue);
    color: var(--primary-blue);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-remove {
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
}

.filter-remove:hover {
    color: var(--dark-blue);
}

/* Course Cards */
.course-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.course-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid var(--border-color);
}

.course-title-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.course-name {
    margin: 0;
    flex: 1;
}

.course-name a {
    color: var(--dark-blue);
    text-decoration: none;
    transition: var(--transition);
}

.course-name a:hover {
    color: var(--primary-blue);
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

.course-body {
    padding: 1.5rem;
    flex: 1;
}

.course-body h4 {
    color: var(--dark-blue);
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.course-body p {
    color: var(--gray);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.course-syllabus,
.course-careers,
.course-eligibility {
    margin-bottom: 1rem;
}

.course-footer {
    padding: 1rem 1.5rem;
    background: var(--light-gray);
    border-top: 1px solid var(--border-color);
}

.course-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--gray);
}

.meta-icon {
    font-size: 1rem;
}

.course-actions {
    display: flex;
    gap: 0.5rem;
}

/* Categories Section */
.categories-section {
    background: var(--light-gray);
}

.categories-grid {
    margin-top: 2rem;
}

.category-card {
    text-align: center;
    padding: 2rem 1.5rem;
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-5px);
}

.category-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.category-card h3 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
}

.category-card p {
    color: var(--gray);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-results h3 {
    color: var(--dark-blue);
    margin-bottom: 1rem;
}

.no-results p {
    color: var(--gray);
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.no-results-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Pagination */
.pagination-container {
    margin-top: 3rem;
    text-align: center;
}

.pagination-info {
    margin-top: 1rem;
    color: var(--gray);
    font-size: 0.9rem;
}

/* CTA Section */
.cta-section {
    color: var(--white);
    margin-top: 4rem;
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

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .search-filter-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .active-filters {
        width: 100%;
    }
    
    .course-title-section {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .course-duration {
        align-self: flex-start;
    }
    
    .course-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .course-actions {
        flex-direction: column;
    }
    
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .no-results-actions,
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 480px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
