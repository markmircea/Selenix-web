<?php
// debug-templates-api.php
// Simple debug version to check what's wrong

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo "Testing API...\n";

// Test 1: Basic PHP
echo "✅ PHP is working\n";

// Test 2: Database connection
try {
    $host = 'localhost';
    $username = 'aibrainl_selenix';
    $password = 'She-wolf11';
    $database = 'aibrainl_selenix';
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Test 3: Check if templates table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'templates'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Templates table exists\n";
        
        // Test 4: Count templates
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM templates");
        $count = $countStmt->fetch()['count'];
        echo "✅ Found {$count} templates in database\n";
        
        // Test 5: Get sample template
        $sampleStmt = $pdo->query("SELECT id, title, status FROM templates LIMIT 1");
        $sample = $sampleStmt->fetch();
        if ($sample) {
            echo "✅ Sample template: ID={$sample['id']}, Title='{$sample['title']}', Status='{$sample['status']}'\n";
        } else {
            echo "⚠️ No templates found in database\n";
        }
        
    } else {
        echo "❌ Templates table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 6: Check file permissions
$uploadDir = 'uploads/templates/';
if (is_dir($uploadDir)) {
    echo "✅ Upload directory exists\n";
    if (is_writable($uploadDir)) {
        echo "✅ Upload directory is writable\n";
    } else {
        echo "⚠️ Upload directory is not writable\n";
    }
} else {
    echo "⚠️ Upload directory does not exist\n";
}

// Return JSON response
echo json_encode([
    'success' => true,
    'message' => 'Debug completed - check output above',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
