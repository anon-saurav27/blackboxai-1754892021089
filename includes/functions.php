<?php
// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength
function validatePassword($password) {
    // At least 6 characters
    return strlen($password) >= 6;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Display success message
function showSuccess($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

// Display error message
function showError($message) {
    return '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
}

// Upload file function
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
    }
    
    // Check file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed'];
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileType;
    $uploadPath = $uploadDir . '/' . $newFileName;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Delete file function
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Format date
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

// Truncate text
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Generate slug from text
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Get base URL
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    return $protocol . '://' . $host . ($path === '/' ? '' : $path);
}

// Pagination function
function paginate($currentPage, $totalPages, $baseUrl) {
    $pagination = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-btn">Previous</a>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $pagination .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-btn">Next</a>';
    }
    
    $pagination .= '</div>';
    return $pagination;
}

// Search function
function searchQuery($query, $fields) {
    $conditions = [];
    $params = [];
    
    foreach ($fields as $field) {
        $conditions[] = "$field LIKE ?";
        $params[] = "%$query%";
    }
    
    return [
        'condition' => '(' . implode(' OR ', $conditions) . ')',
        'params' => $params
    ];
}

// Log activity
function logActivity($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?>
