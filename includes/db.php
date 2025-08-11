<?php
// Database configuration
$dbFile = __DIR__ . '/../edupool.db';

try {
    // Create PDO connection to SQLite
    $pdo = new PDO(
        "sqlite:$dbFile",
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Enable foreign key constraints for SQLite
    $pdo->exec("PRAGMA foreign_keys = ON");
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    
    // If database doesn't exist, show setup message
    if (!file_exists($dbFile)) {
        die("Database not found. Please run setup.php first to create the database.");
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Function to test database connection
function testConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>
