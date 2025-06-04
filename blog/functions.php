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
    if (empty($timestamp)) {
        return 'Unknown date';
    }
    
    if (is_numeric($timestamp)) {
        // Convert to integer to avoid float-string to int conversion warnings
        $timestamp = (int)$timestamp;
        return date($format, $timestamp);
    }
    
    $converted = strtotime($timestamp);
    if ($converted === false) {
        return 'Invalid date';
    }
    
    return date($format, $converted);
}

/**
 * Format date as relative time (e.g., "2 days ago")
 */
function timeAgo($timestamp) {
    if (empty($timestamp)) {
        return 'Unknown time';
    }
    
    $time = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
    
    if ($time === false || $time === null) {
        return 'Invalid time';
    }
    
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
        header('Location: ' . BLOG_URL . '/admin-login.php');
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
 * Enhanced sanitizeInput function for AI-generated content
 * This function handles different types of content appropriately
 */
function sanitizeInputEnhanced($data, $preserveHtml = false) {
    if ($preserveHtml) {
        // For HTML content, only trim and remove dangerous scripts
        $data = trim($data);
        
        // Remove potentially dangerous elements but preserve formatting
        $data = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $data);
        $data = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i', '', $data);
        $data = preg_replace('/on\w+="[^"]*"/i', '', $data); // Remove event handlers
        
        return $data;
    } else {
        // For regular input, use the existing method
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Clean AI-generated content for display
 */
function cleanAIContent($content) {
    // First, decode any HTML entities that might have been double-encoded
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove any JSON-like artifacts that might be left over
    $content = preg_replace('/\{[^}]*"title"[^}]*\}/i', '', $content);
    $content = preg_replace('/\{[^}]*"content"[^}]*\}/i', '', $content);
    $content = preg_replace('/\{[^}]*"excerpt"[^}]*\}/i', '', $content);
    
    // Clean up any escaped quotes
    $content = str_replace('\\"', '"', $content);
    $content = str_replace('\\\\', '\\', $content);
    
    // Remove any leftover HTML entities that look malformed
    $content = preg_replace('/&amp;&amp;&quot;/', '"', $content);
    $content = preg_replace('/&quot;/', '"', $content);
    $content = preg_replace('/&amp;/', '&', $content);
    
    // Ensure proper paragraph structure
    if (!preg_match('/<p[^>]*>/', $content)) {
        $content = wpautop($content);
    }
    
    return $content;
}

/**
 * WordPress-style autop function for converting line breaks to paragraphs
 */
function wpautop($pee, $br = true) {
    $pre_tags = array();
    
    if (trim($pee) === '') {
        return '';
    }
    
    $pee = $pee . "\n";
    
    if (strpos($pee, '<pre') !== false) {
        $pee_parts = explode('</pre>', $pee);
        $last_pee = array_pop($pee_parts);
        $pee = '';
        $i = 0;
        
        foreach ($pee_parts as $pee_part) {
            $start = strpos($pee_part, '<pre');
            
            if ($start === false) {
                $pee .= $pee_part;
                continue;
            }
            
            $name = "<pre wp-pre-tag-$i></pre>";
            $pre_tags[$name] = substr($pee_part, $start) . '</pre>';
            
            $pee .= substr($pee_part, 0, $start) . $name;
            $i++;
        }
        
        $pee .= $last_pee;
    }
    
    $pee = preg_replace('|<br\s*/?\>\s*<br\s*/?>|', "\n\n", $pee);
    
    $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
    
    $pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n\n$1", $pee);
    $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
    $pee = str_replace(array("\r\n", "\r"), "\n", $pee);
    
    if (strpos($pee, '<object') !== false) {
        $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee);
        $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
    }
    
    $pee = preg_replace("/\n\n+/", "\n\n", $pee);
    $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
    $pee = '';
    
    foreach ($pees as $tinkle) {
        $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
    }
    
    $pee = preg_replace('|<p>\s*</p>|', '', $pee);
    $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
    $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
    $pee = preg_replace("!<p>(<li.+?)</p>!", "$1", $pee);
    $pee = preg_replace('!<p><blockquote([^>]*)>!i', "<blockquote$1><p>", $pee);
    $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
    $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
    $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
    
    if ($br) {
        $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', function($matches) {
            return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
        }, $pee);
        $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);
        $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
    }
    
    $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
    $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
    $pee = preg_replace("|\n</p>$|", '</p>', $pee);
    
    if (!empty($pre_tags)) {
        $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
    }
    
    return $pee;
}

/**
 * Debug function to log AI content processing
 */
function debugAIContent($content, $stage = 'unknown') {
    error_log("AI Content Debug [$stage]: " . substr($content, 0, 200) . '...');
    error_log("AI Content Length [$stage]: " . strlen($content));
}

/**
 * Create logs directory if it doesn't exist
 */
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
?>
