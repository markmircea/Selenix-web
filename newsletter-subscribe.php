<?php
/**
 * Newsletter Subscription Handler
 * Standalone script to handle newsletter subscriptions from any page
 * Uses the same PostgreSQL database as the blog system
 */

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Get email from POST data
    $email = trim($_POST['email'] ?? '');
    $source = trim($_POST['source'] ?? 'download'); // Track where subscription came from
    
    // Validate email
    if (empty($email)) {
        $response['message'] = 'Email is required';
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit;
    }
    
    // Connect to the blog PostgreSQL database
    if (!file_exists('blog/config.php')) {
        throw new Exception('Blog configuration not found');
    }
    
    require_once 'blog/config.php';
    
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Insert or update newsletter subscription
    $stmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, is_active, subscribed_at) 
        VALUES (:email, true, CURRENT_TIMESTAMP) 
        ON CONFLICT (email) 
        DO UPDATE SET 
            is_active = true, 
            unsubscribed_at = NULL,
            subscribed_at = CASE 
                WHEN newsletter_subscribers.is_active = false THEN CURRENT_TIMESTAMP 
                ELSE newsletter_subscribers.subscribed_at 
            END
    ");
    
    $stmt->execute(['email' => $email]);
    
    // Log the subscription
    error_log("Newsletter subscription: $email (source: $source)");
    
    $response['success'] = true;
    $response['message'] = 'Successfully subscribed to newsletter';
    
} catch (PDOException $e) {
    error_log("Newsletter subscription database error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    $response['message'] = 'An error occurred while processing subscription';
}

echo json_encode($response);
exit;
?>