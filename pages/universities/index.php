<?php
$pageTitle = 'Universities in Nepal - EduPool';
$pageDescription = 'Explore top universities in Nepal offering quality education across various fields. Find detailed information about programs, admission requirements, and more.';

require_once __DIR__ . '/../../includes/header.php';

// Pagination settings
$itemsPerPage = 9;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset = ($currentPage - 1) * $itemsPerPage;

// Search and filter parameters
$searchQuery = sanitizeInput($_GET['search'] ?? '');
$sortBy = sanitizeInput($_GET['sort'] ?? 'name');
$filterYear = (int)($_GET['year'] ?? 0);

// Build query conditions
$conditions = [];
$params = [];

if (!empty($searchQuery)) {
    $conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($filterYear > 0) {
    $conditions[] = "established_year = ?";
    $params[] = $filterYear;
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Valid sort options
$validSorts = ['name', 'established_year', 'created_at'];
$sortBy = in_array($sortBy, $validSorts) ? $sortBy : 'name';
$sortOrder = $sortBy === 'created_at' ? 'DESC' : 'ASC';

try {
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM universities $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Get universities
    $query = "SELECT * FROM universities $whereClause ORDER BY $sortBy $sortOrder LIMIT $itemsPerPage OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $universities = $stmt->fetchAll();
    
    // Get available years for filter
    $stmt = $pdo->query("SELECT DISTINCT established_year FROM universities WHERE established_year IS NOT NULL ORDER BY established_year DESC");
    $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Universities page error: " . $e->getMessage());
    $universities = [];
    $availableYears = [];
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
                <span>Universities</span>
            </nav>
            <h1 class="page-title">Universities in Nepal</h1>
            <p class="page-subtitle">Discover top universities offering quality education and diverse programs</p>
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
                            placeholder="Search universities..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                    </div>
                    
                    <div class="filter-group">
                        <select name="year" class="filter-select">
                            <option value="">All Years</option>
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo $filterYear == $year ? 'selected' : ''; ?>>
                                    Established <?php echo $year; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="sort-group">
                        <select name="sort" class="sort-select">
                            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="established_year" <?php echo $sortBy === 'established_year' ? 'selected' : ''; ?>>Established Year</option>
                            <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Recently Added</option>
                        </select>
                    </div>
                    
                    <div class="action-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="/pages/universities/" class="btn btn-outline">Clear</a>
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
                <h2>Universities Found</h2>
                <p>Showing <?php echo count($universities); ?> of <?php echo $totalItems; ?> universities</p>
            </div>
            
            <?php if (!empty($searchQuery) || $filterYear > 0): ?>
                <div class="active-filters">
                    <span class="filter-label">Active filters:</span>
                    <?php if (!empty($searchQuery)): ?>
                        <span class="filter-tag">
                            Search: "<?php echo htmlspecialchars($searchQuery); ?>"
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>" class="filter-remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if ($filterYear > 0): ?>
                        <span class="filter-tag">
                            Year: <?php echo $filterYear; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['year' => ''])); ?>" class="filter-remove">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Universities Grid -->
        <?php if (!empty($universities)): ?>
            <div class="universities-grid grid grid-3 stagger-animation">
                <?php foreach ($universities as $university): ?>
                    <div class="university-card card hover-lift">
                        <div class="card-image-container">
                            <img 
                                src="<?php echo $university['image'] ? '/uploads/' . $university['image'] : 'https://placehold.co/400x250?text=University+Campus+Building+Architecture'; ?>" 
                                alt="<?php echo htmlspecialchars($university['name']); ?> campus"
                                class="card-image"
                                onerror="this.src='https://placehold.co/400x250?text=University+Campus+Building+Architecture'"
                            >
                            <div class="card-overlay">
                                <a href="/pages/universities/university_detail.php?id=<?php echo $university['id']; ?>" 
                                   class="btn btn-white btn-sm">View Details</a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="university-header">
                                <h3 class="university-name">
                                    <a href="/pages/universities/university_detail.php?id=<?php echo $university['id']; ?>">
                                        <?php echo htmlspecialchars($university['name']); ?>
                                    </a>
                                </h3>
                                <?php if ($university['established_year']): ?>
                                    <span class="university-year">Est. <?php echo $university['established_year']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="university-description">
                                <?php echo truncateText($university['description'], 120); ?>
                            </p>
                            
                            <div class="university-meta">
                                <?php
                                // Get affiliated colleges count
                                try {
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM colleges WHERE university_id = ?");
                                    $stmt->execute([$university['id']]);
                                    $collegesCount = $stmt->fetch()['count'];
                                    
                                    // Get offered courses count
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM university_courses WHERE university_id = ?");
                                    $stmt->execute([$university['id']]);
                                    $coursesCount = $stmt->fetch()['count'];
                                } catch (PDOException $e) {
                                    $collegesCount = $coursesCount = 0;
                                }
                                ?>
                                <div class="meta-item">
                                    <span class="meta-icon">🏫</span>
                                    <span class="meta-text"><?php echo $collegesCount; ?> Colleges</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">📚</span>
                                    <span class="meta-text"><?php echo $coursesCount; ?> Courses</span>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <a href="/pages/universities/university_detail.php?id=<?php echo $university['id']; ?>" 
                                   class="btn btn-primary w-full">Explore University</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php
                    $baseUrl = '/pages/universities/';
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
                <div class="no-results-icon">🏛️</div>
                <h3>No Universities Found</h3>
                <p>We couldn't find any universities matching your criteria.</p>
                <div class="no-results-actions">
                    <a href="/pages/universities/" class="btn btn-primary">View All Universities</a>
                    <a href="/" class="btn btn-outline">Back to Homepage</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section gradient-animated">
    <div class="container">
        <div class="cta-content text-center animate-fade-in-up">
            <h2>Can't Find What You're Looking For?</h2>
            <p>Explore our comprehensive database of colleges and courses to find your perfect educational path.</p>
            <div class="cta-actions">
                <a href="/pages/colleges/" class="btn btn-white btn-lg">Browse Colleges</a>
                <a href="/pages/courses/" class="btn btn-outline btn-white btn-lg">View Courses</a>
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

/* University Cards */
.university-card {
    transition: var(--transition);
}

.card-image-container {
    position: relative;
    overflow: hidden;
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(37, 99, 235, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.university-card:hover .card-overlay {
    opacity: 1;
}

.university-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.university-name {
    margin: 0;
    flex: 1;
}

.university-name a {
    color: var(--dark-blue);
    text-decoration: none;
    transition: var(--transition);
}

.university-name a:hover {
    color: var(--primary-blue);
}

.university-year {
    background: var(--light-blue);
    color: var(--primary-blue);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.university-description {
    color: var(--gray);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.university-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
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
    
    .university-header {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .university-year {
        align-self: flex-start;
    }
    
    .university-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .no-results-actions,
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
