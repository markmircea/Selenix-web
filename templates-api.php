<?php
// templates-api.php
// Improved API endpoint to serve templates data and track downloads

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error logging function
function logError($message, $data = null) {
    $logEntry = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $logEntry .= " - Data: " . json_encode($data);
    }
    $logEntry .= "\n";
    
    // Log to file (make sure this directory exists and is writable)
    error_log($logEntry, 3, 'templates-api-errors.log');
    
    // Also log to PHP error log
    error_log("Templates API Error: " . $message);
}

// Database connection with better error handling
function getDbConnection() {
    $host = 'localhost';
    $username = 'aibrainl_selenix';
    $password = 'She-wolf11';
    $database = 'aibrainl_selenix';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create upload directory if it doesn't exist
        $uploadDir = 'uploads/templates/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        return $pdo;
    } catch (PDOException $e) {
        logError('Database connection failed', ['error' => $e->getMessage()]);
        return false;
    }
}

// Validate and sanitize input
function sanitizeInput($input, $type = 'string') {
    switch ($type) {
        case 'int':
            return (int) $input;
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        default:
            return trim(strip_tags($input));
    }
}

// Main API handler
function handleRequest() {
    $pdo = getDbConnection();
    if (!$pdo) {
        http_response_code(500);
        return ['success' => false, 'error' => 'Database connection failed'];
    }

    $method = $_SERVER['REQUEST_METHOD'];
    
    try {
        if ($method === 'GET') {
            return handleGetRequest($pdo);
        } elseif ($method === 'POST') {
            return handlePostRequest($pdo);
        } else {
            http_response_code(405);
            return ['success' => false, 'error' => 'Method not allowed'];
        }
    } catch (Exception $e) {
        logError('API Request Error', [
            'method' => $method,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        http_response_code(500);
        return ['success' => false, 'error' => 'Internal server error'];
    }
}

function handleGetRequest($pdo) {
    // Get and sanitize parameters
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
    $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'active';
    $limit = sanitizeInput($_GET['limit'] ?? 50, 'int');
    $offset = sanitizeInput($_GET['offset'] ?? 0, 'int');
    
    // Validate parameters
    $limit = max(1, min(100, $limit)); // Between 1 and 100
    $offset = max(0, $offset);
    
    $allowedStatuses = ['active', 'inactive', 'draft', 'all'];
    if (!in_array($status, $allowedStatuses)) {
        $status = 'active';
    }
    
    // Build query conditions
    $whereConditions = [];
    $params = [];
    
    if ($status !== 'all') {
        $whereConditions[] = 'status = ?';
        $params[] = $status;
    }
    
    if ($category && $category !== 'all') {
        $whereConditions[] = 'category = ?';
        $params[] = $category;
    }
    
    if ($search) {
        $whereConditions[] = '(title LIKE ? OR description LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get templates with proper SQL construction
    $sql = "SELECT * FROM templates $whereClause ORDER BY featured DESC, downloads DESC, created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll();
    
    // Process templates data
    foreach ($templates as &$template) {
        // Parse JSON tags safely
        $template['tags'] = json_decode($template['tags'] ?? '[]') ?: [];
        
        // Convert boolean fields
        $template['featured'] = (bool)$template['featured'];
        $template['premium'] = (bool)$template['premium'];
        
        // Ensure numeric fields are properly typed
        $template['id'] = (int)$template['id'];
        $template['downloads'] = (int)$template['downloads'];
        
        // Ensure new fields exist even if NULL in database
        $template['long_description'] = $template['long_description'] ?? '';
        $template['preview_image'] = $template['preview_image'] ?? '';
        $template['image_alt'] = $template['image_alt'] ?? '';
        
        // Clean up file paths for security
        if ($template['file_path'] && !filter_var($template['file_path'], FILTER_VALIDATE_URL)) {
            // If it's a local file, ensure it starts with uploads/
            if (!str_starts_with($template['file_path'], 'uploads/')) {
                $template['file_path'] = null;
            }
        }
    }
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM templates $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch()['total'];
    
    // Get categories with counts
    $categoriesStmt = $pdo->prepare("
        SELECT category, COUNT(*) as count 
        FROM templates 
        WHERE status = ? 
        GROUP BY category 
        ORDER BY count DESC
    ");
    $categoriesStmt->execute([$status === 'all' ? 'active' : $status]);
    $categories = $categoriesStmt->fetchAll();
    
    // Format category data
    foreach ($categories as &$cat) {
        $cat['count'] = (int)$cat['count'];
    }
    
    return [
        'success' => true,
        'templates' => $templates,
        'pagination' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total,
            'current_page' => floor($offset / $limit) + 1,
            'total_pages' => ceil($total / $limit)
        ],
        'categories' => $categories,
        'filters' => [
            'category' => $category ?: 'all',
            'search' => $search,
            'status' => $status
        ]
    ];
}

function handlePostRequest($pdo) {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid JSON input'];
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'track_download':
            return handleDownloadTracking($pdo, $input);
        
        case 'get_template':
            return handleGetTemplate($pdo, $input);
            
        default:
            http_response_code(400);
            return ['success' => false, 'error' => 'Invalid action'];
    }
}

function handleDownloadTracking($pdo, $input) {
    $templateId = sanitizeInput($input['template_id'] ?? 0, 'int');
    $email = sanitizeInput($input['email'] ?? '', 'email');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if ($templateId <= 0) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid template ID'];
    }
    
    try {
        // Check if template exists and is active
        $checkStmt = $pdo->prepare("SELECT id, title, downloads, status FROM templates WHERE id = ?");
        $checkStmt->execute([$templateId]);
        $template = $checkStmt->fetch();
        
        if (!$template) {
            http_response_code(404);
            return ['success' => false, 'error' => 'Template not found'];
        }
        
        if ($template['status'] !== 'active') {
            http_response_code(403);
            return ['success' => false, 'error' => 'Template not available for download'];
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert download record
        $insertStmt = $pdo->prepare("
            INSERT INTO template_downloads (template_id, email, ip_address, user_agent, download_time) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertStmt->execute([$templateId, $email ?: null, $ipAddress, $userAgent]);
        
        // Increment download count
        $updateStmt = $pdo->prepare("UPDATE templates SET downloads = downloads + 1 WHERE id = ?");
        $updateStmt->execute([$templateId]);
        
        // Get updated download count
        $newDownloads = (int)$template['downloads'] + 1;
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Download tracked successfully',
            'template' => [
                'id' => (int)$template['id'],
                'title' => $template['title'],
                'downloads' => $newDownloads
            ]
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Download tracking failed', [
            'template_id' => $templateId,
            'error' => $e->getMessage()
        ]);
        
        http_response_code(500);
        return ['success' => false, 'error' => 'Failed to track download'];
    }
}

function handleGetTemplate($pdo, $input) {
    $templateId = sanitizeInput($input['template_id'] ?? 0, 'int');
    
    if ($templateId <= 0) {
        http_response_code(400);
        return ['success' => false, 'error' => 'Invalid template ID'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM templates WHERE id = ? AND status = 'active'");
        $stmt->execute([$templateId]);
        $template = $stmt->fetch();
        
        if (!$template) {
            http_response_code(404);
            return ['success' => false, 'error' => 'Template not found'];
        }
        
        // Process template data
        $template['tags'] = json_decode($template['tags'] ?? '[]') ?: [];
        $template['featured'] = (bool)$template['featured'];
        $template['premium'] = (bool)$template['premium'];
        $template['id'] = (int)$template['id'];
        $template['downloads'] = (int)$template['downloads'];
        
        return [
            'success' => true,
            'template' => $template
        ];
        
    } catch (PDOException $e) {
        logError('Get template failed', [
            'template_id' => $templateId,
            'error' => $e->getMessage()
        ]);
        
        http_response_code(500);
        return ['success' => false, 'error' => 'Failed to get template'];
    }
}

// Execute the request
try {
    $response = handleRequest();
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    logError('Fatal API Error', ['error' => $e->getMessage()]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ], JSON_PRETTY_PRINT);
}
?>
