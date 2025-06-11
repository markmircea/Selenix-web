<?php
// templates-api.php
// API endpoint to serve templates data and track downloads

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get templates
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'active';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $whereConditions = ['status = ?'];
    $params = [$status];
    
    if ($category && $category !== 'all') {
        $whereConditions[] = 'category = ?';
        $params[] = $category;
    }
    
    if ($search) {
        $whereConditions[] = '(title LIKE ? OR description LIKE ? OR JSON_SEARCH(tags, "one", ?) IS NOT NULL)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $search;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get templates
    $sql = "SELECT * FROM templates $whereClause ORDER BY featured DESC, downloads DESC, created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse JSON tags for each template
    foreach ($templates as &$template) {
        $template['tags'] = json_decode($template['tags'] ?? '[]');
        $template['featured'] = (bool)$template['featured'];
        $template['premium'] = (bool)$template['premium'];
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM templates $whereClause";
    $countParams = array_slice($params, 0, -2); // Remove limit and offset
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch()['total'];
    
    // Get categories with counts
    $categoriesStmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM templates 
        WHERE status = 'active' 
        GROUP BY category 
        ORDER BY count DESC
    ");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'templates' => $templates,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total
        ],
        'categories' => $categories
    ]);

} elseif ($method === 'POST') {
    // Handle template download tracking
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'track_download') {
        $templateId = (int)($input['template_id'] ?? 0);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if ($templateId > 0) {
            try {
                // Insert download record
                $stmt = $pdo->prepare("
                    INSERT INTO template_downloads (template_id, ip_address, user_agent) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$templateId, $ipAddress, $userAgent]);
                
                // Increment download count
                $updateStmt = $pdo->prepare("UPDATE templates SET downloads = downloads + 1 WHERE id = ?");
                $updateStmt->execute([$templateId]);
                
                // Get updated template info
                $templateStmt = $pdo->prepare("SELECT title, downloads FROM templates WHERE id = ?");
                $templateStmt->execute([$templateId]);
                $template = $templateStmt->fetch();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Download tracked successfully',
                    'template' => $template
                ]);
                
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to track download'
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid template ID'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>