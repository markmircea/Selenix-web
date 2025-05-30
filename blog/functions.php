<?php
/**
 * Blog Helper Functions
 * Utility functions for the blog system
 */

/**
 * Sanitize and validate input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Format date for display
 */
function formatDate($timestamp, $format = 'F j, Y') {
    if (is_numeric($timestamp)) {
        return date($format, $timestamp);
    }
    return date($format, strtotime($timestamp));
}

/**
 * Format date as relative time (e.g., "2 days ago")
 */
function timeAgo($timestamp) {
    $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2419200) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($timestamp);
    }
}

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 150, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $truncated = substr($text, 0, $length);
    $lastSpace = strrpos($truncated, ' ');
    
    if ($lastSpace !== false) {
        $truncated = substr($truncated, 0, $lastSpace);
    }
    
    return $truncated . $suffix;
}

/**
 * Generate pagination links
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination">';
    $html .= '<ul class="pagination-list">';
    
    // Previous page
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . '?page=' . ($currentPage - 1);
        $html .= '<li><a href="' . $prevUrl . '" class="pagination-link prev">&laquo; Previous</a></li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=1" class="pagination-link">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $class = ($i == $currentPage) ? 'pagination-link active' : 'pagination-link';
        $url = $baseUrl . '?page=' . $i;
        $html .= '<li><a href="' . $url . '" class="' . $class . '">' . $i . '</a></li>';
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
        $html .= '<li><a href="' . $baseUrl . '?page=' . $totalPages . '" class="pagination-link">' . $totalPages . '</a></li>';
    }
    
    // Next page
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . '?page=' . ($currentPage + 1);
        $html .= '<li><a href="' . $nextUrl . '" class="pagination-link next">Next &raquo;</a></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Get category display name
 */
function getCategoryName($slug) {
    global $BLOG_CATEGORIES;
    return isset($BLOG_CATEGORIES[$slug]) ? $BLOG_CATEGORIES[$slug] : ucfirst($slug);
}

/**
 * Get category color class
 */
function getCategoryColor($category) {
    $colors = [
        'tutorials' => 'category-blue',
        'features' => 'category-purple',
        'case-studies' => 'category-green',
        'automation' => 'category-orange',
        'news' => 'category-red',
        'guides' => 'category-teal'
    ];
    
    return isset($colors[$category]) ? $colors[$category] : 'category-gray';
}

/**
 * Handle file upload
 */
function handleFileUpload($file, $allowedTypes = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds PHP upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $error = isset($errorMessages[$file['error']]) ? $errorMessages[$file['error']] : 'Unknown upload error';
        error_log('File upload error: ' . $error . ' (Code: ' . $file['error'] . ')');
        return ['success' => false, 'error' => $error];
    }
    
    $allowedTypes = $allowedTypes ?: ALLOWED_EXTENSIONS;
    $maxSize = MAX_FILE_SIZE;
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed size (' . round($maxSize/1024/1024, 1) . 'MB)'];
    }
    
    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    // Validate MIME type for additional security
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg', 
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    
    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid file type. Must be a valid image.'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    // Ensure upload directory exists and is writable
    if (!file_exists(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            return ['success' => false, 'error' => 'Could not create upload directory'];
        }
    }
    
    if (!is_writable(UPLOAD_DIR)) {
        return ['success' => false, 'error' => 'Upload directory is not writable'];
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Set proper file permissions
        chmod($filepath, 0644);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => UPLOAD_URL . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file to destination'];
}

/**
 * Delete uploaded file
 */
function deleteUploadedFile($filename) {
    if (empty($filename)) {
        return false;
    }
    
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Generate excerpt from content
 */
function generateExcerpt($content, $length = 160) {
    // Strip HTML tags
    $text = strip_tags($content);
    
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Truncate to specified length
    return truncateText($text, $length);
}

/**
 * Estimate reading time based on content
 */
function estimateReadingTime($content) {
    $wordCount = str_word_count(strip_tags($content));
    $averageWordsPerMinute = 200;
    $minutes = ceil($wordCount / $averageWordsPerMinute);
    
    return max(1, $minutes); // Minimum 1 minute
}

/**
 * Generate breadcrumb navigation
 */
function generateBreadcrumbs($items) {
    $html = '<nav class="breadcrumbs">';
    $html .= '<ol class="breadcrumb-list">';
    
    foreach ($items as $index => $item) {
        $isLast = ($index === array_key_last($items));
        
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check if user is admin (simple session-based check)
 */
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BLOG_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Login admin user
 */
function loginAdmin($username, $password) {
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        return true;
    }
    
    return false;
}

/**
 * Logout admin user
 */
function logoutAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['admin_login_time'])) {
        if (time() - $_SESSION['admin_login_time'] > SESSION_TIMEOUT) {
            logoutAdmin();
            return false;
        }
        
        // Update last activity time
        $_SESSION['admin_login_time'] = time();
    }
    
    return isAdmin();
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate meta tags for SEO
 */
function generateMetaTags($post) {
    $title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'] . ' - ' . BLOG_TITLE;
    $description = !empty($post['meta_description']) ? $post['meta_description'] : $post['excerpt'];
    $image = !empty($post['featured_image']) ? UPLOAD_URL . $post['featured_image'] : '';
    $url = BLOG_URL . '/post.php?slug=' . $post['slug'];
    
    $meta = '';
    $meta .= '<title>' . htmlspecialchars($title) . '</title>' . "\n";
    $meta .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
    
    // Open Graph meta tags
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
    $meta .= '<meta property="og:type" content="article">' . "\n";
    
    if ($image) {
        $meta .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
    }
    
    // Twitter Card meta tags
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
    
    if ($image) {
        $meta .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
    }
    
    return $meta;
}

/**
 * Log error message
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] ERROR: $message$contextStr" . PHP_EOL;
    
    error_log($logMessage, 3, __DIR__ . '/logs/error.log');
}

/**
 * Create logs directory if it doesn't exist
 */
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
?>
