<?php
/**
 * Selenix Blog Configuration
 * Database and application settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'aibrainl_selenixblog');
define('DB_USER', 'aibrainl_selenix');
define('DB_PASS', 'She-wolf11');
define('DB_PORT', '5432');

// Site Configuration
define('SITE_URL', 'https://selenix.io');
define('BLOG_URL', SITE_URL . '/blog');
define('ADMIN_URL', BLOG_URL . '/admin');

// Security
define('SECRET_KEY', 'selenix_blog_secret_2025');
define('SESSION_TIMEOUT', 3600 * 2); // 2 hours

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', BLOG_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('POSTS_PER_PAGE', 6);
define('ADMIN_POSTS_PER_PAGE', 10);

// Blog Settings
define('BLOG_TITLE', 'The Selenix Blog');
define('BLOG_DESCRIPTION', 'Insights, tutorials, and updates from the world of browser automation');

// Categories
$BLOG_CATEGORIES = [
    'tutorials' => 'Tutorials',
    'features' => 'Features', 
    'case-studies' => 'Case Studies',
    'automation' => 'Automation Tips',
    'news' => 'News',
    'guides' => 'Guides'
];

// Admin User (in production, store this securely)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('selenix2025!', PASSWORD_DEFAULT));

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Ensure uploads directory is writable
if (!is_writable(UPLOAD_DIR)) {
    error_log('Uploads directory is not writable: ' . UPLOAD_DIR);
}
?>
