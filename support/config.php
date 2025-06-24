<?php
/**
 * Database Configuration for Selenix Website
 * 
 * IMPORTANT: 
 * 1. Update these credentials with your actual database information
 * 2. Make sure this file is not accessible via web browser (place outside web root or use .htaccess)
 * 3. Use environment variables in production for better security
 */

// Database configuration
define('DB_HOST', 'localhost');           // Database host (usually localhost)
define('DB_NAME', 'aibrainl_selenix');     // Database name
define('DB_USER', 'aibrainl_selenix');    // Database username
define('DB_PASS', 'She-wolf11');    // Database password
define('DB_CHARSET', 'utf8mb4');


// Email configuration
define('SUPPORT_EMAIL', 'support@selenix.io');
define('FROM_EMAIL', 'support@selenix.io');
define('FROM_NAME', 'Selenix Support');

// Application settings
define('SITE_URL', 'https://selenix.io');
define('ADMIN_EMAIL', 'support@selenix.io');

/**
 * Create database connection
 * @return PDO Database connection
 */
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * @return bool True if connection successful
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
