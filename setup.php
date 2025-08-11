<?php
// EduPool Setup Script
// This script creates a SQLite database for demonstration purposes

$dbFile = __DIR__ . '/edupool.db';

try {
    // Create SQLite database
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $sql = "
    -- Admin table for admin authentication
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Universities table
    CREATE TABLE IF NOT EXISTS universities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        established_year INTEGER,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Colleges table
    CREATE TABLE IF NOT EXISTS colleges (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        location TEXT,
        map_link TEXT,
        image TEXT,
        university_id INTEGER,
        website_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE SET NULL
    );

    -- Courses table
    CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        duration TEXT,
        syllabus TEXT,
        eligibility TEXT,
        career_paths TEXT,
        required_documents TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Junction table for university-course relationships
    CREATE TABLE IF NOT EXISTS university_courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        university_id INTEGER NOT NULL,
        course_id INTEGER NOT NULL,
        FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(university_id, course_id)
    );

    -- Junction table for college-course relationships
    CREATE TABLE IF NOT EXISTS college_courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        college_id INTEGER NOT NULL,
        course_id INTEGER NOT NULL,
        program_level TEXT NOT NULL CHECK(program_level IN ('Diploma', 'Bachelor', 'Master', 'PhD')),
        FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(college_id, course_id)
    );

    -- Users table for student registration
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        profile_picture TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Create indexes for better performance
    CREATE INDEX IF NOT EXISTS idx_universities_name ON universities(name);
    CREATE INDEX IF NOT EXISTS idx_colleges_name ON colleges(name);
    CREATE INDEX IF NOT EXISTS idx_colleges_university ON colleges(university_id);
    CREATE INDEX IF NOT EXISTS idx_courses_name ON courses(name);
    CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
    CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
    ";
    
    $pdo->exec($sql);
    
    // Insert default admin user (password: admin123)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);

    // Insert additional admin user (username: superadmin, password: supersecret)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute(['superadmin', password_hash('supersecret', PASSWORD_DEFAULT)]);
    
    // Insert sample data
    $universities = [
        ['Tribhuvan University', 'The oldest and largest university in Nepal, established in 1959. It offers a wide range of undergraduate and graduate programs.', 1959, 'tu_banner.jpg'],
        ['Kathmandu University', 'A modern autonomous university established in 1991, known for its quality education and research programs.', 1991, 'ku_banner.jpg'],
        ['Pokhara University', 'Established in 1997, focusing on science, technology, and management education with practical approach.', 1997, 'pu_banner.jpg']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO universities (name, description, established_year, image) VALUES (?, ?, ?, ?)");
    foreach ($universities as $uni) {
        $stmt->execute($uni);
    }
    
    $colleges = [
        ['Pulchowk Campus', 'Institute of Engineering under Tribhuvan University, premier engineering college in Nepal.', 'Lalitpur, Nepal', 'https://maps.google.com?q=Pulchowk+Campus+Lalitpur', 'pulchowk.jpg', 1, 'https://pcampus.edu.np'],
        ['Kathmandu University School of Engineering', 'Leading engineering school with modern facilities and industry connections.', 'Dhulikhel, Nepal', 'https://maps.google.com?q=KU+School+Engineering+Dhulikhel', 'ku_soe.jpg', 2, 'https://soe.ku.edu.np'],
        ['Pokhara University School of Business', 'Business school offering MBA and BBA programs with practical approach.', 'Pokhara, Nepal', 'https://maps.google.com?q=PU+School+Business+Pokhara', 'pu_sob.jpg', 3, 'https://sob.pu.edu.np']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO colleges (name, description, location, map_link, image, university_id, website_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($colleges as $college) {
        $stmt->execute($college);
    }
    
    $courses = [
        ['Computer Engineering', '4 Years', 'Programming Fundamentals, Data Structures, Computer Networks, Database Systems, Software Engineering, Web Development, Mobile App Development, Artificial Intelligence, Machine Learning', '+2 Science with Physics and Mathematics, Minimum 60% marks', 'Software Developer, System Administrator, Network Engineer, Data Scientist, AI Engineer, Web Developer, Mobile App Developer', 'Academic Transcripts, Character Certificate, Citizenship Certificate, Passport Size Photos'],
        ['Business Administration (BBA)', '4 Years', 'Principles of Management, Marketing Management, Financial Management, Human Resource Management, Operations Management, Business Communication, Entrepreneurship, Strategic Management', '+2 in any stream with minimum 50% marks', 'Business Manager, Marketing Executive, HR Manager, Financial Analyst, Entrepreneur, Consultant, Project Manager', 'Academic Transcripts, Character Certificate, Citizenship Certificate, Passport Size Photos'],
        ['Master of Business Administration (MBA)', '2 Years', 'Advanced Management, Strategic Planning, Leadership, International Business, Digital Marketing, Financial Analysis, Operations Research, Business Analytics', "Bachelor's degree in any field with minimum 50% marks", 'CEO, General Manager, Business Consultant, Investment Banker, Management Consultant, Director, Senior Manager', "Bachelor's Degree Certificate, Academic Transcripts, Work Experience Certificate, Character Certificate"]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO courses (name, duration, syllabus, eligibility, career_paths, required_documents) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($courses as $course) {
        $stmt->execute($course);
    }
    
    // Link courses with universities and colleges
    $universityCourses = [
        [1, 1], [1, 2], [2, 1], [2, 3], [3, 2], [3, 3]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO university_courses (university_id, course_id) VALUES (?, ?)");
    foreach ($universityCourses as $uc) {
        $stmt->execute($uc);
    }
    
    $collegeCourses = [
        [1, 1, 'Bachelor'], [2, 1, 'Bachelor'], [3, 2, 'Bachelor'], [3, 3, 'Master']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO college_courses (college_id, course_id, program_level) VALUES (?, ?, ?)");
    foreach ($collegeCourses as $cc) {
        $stmt->execute($cc);
    }
    
    // Create demo user
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['demo', 'demo@edupool.com', password_hash('demo123', PASSWORD_DEFAULT)]);
    
    echo "✅ Database setup completed successfully!\n";
    echo "📊 Sample data inserted\n";
    echo "👤 Admin credentials: admin / admin123\n";
    echo "👤 Demo user: demo@edupool.com / demo123\n";
    echo "🌐 Visit: http://localhost:8000\n";
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
}
?>
