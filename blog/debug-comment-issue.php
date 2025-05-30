<?php
// Complete Comment System Debug Script
require_once 'config.php';
require_once 'database.php';
require_once 'models.php';
require_once 'functions.php';

echo "<h1>Complete Comment System Debug</h1>";

// Test database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "<div style='color: green;'>✓ Database connection successful</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

// Check comments table structure
echo "<h2>Comments Table Structure</h2>";
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'comments' 
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll();
    
    if (empty($columns)) {
        echo "<div style='color: red;'>❌ Comments table does not exist!</div>";
        echo "<p>You may need to run the setup script or create the table manually.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['column_name']}</td>";
            echo "<td>{$column['data_type']}</td>";
            echo "<td>{$column['is_nullable']}</td>";
            echo "<td>" . ($column['column_default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error checking table structure: " . $e->getMessage() . "</div>";
}

// Test addComment function with detailed debugging
echo "<h2>Test Comment Insertion</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_insert'])) {
    $blogModel = new BlogModel();
    
    // Get a post to test with
    $posts = $blogModel->getPosts(1, null, 1);
    if (empty($posts)) {
        echo "<div style='color: red;'>No posts available for testing</div>";
    } else {
        $testPost = $posts[0];
        $testName = $_POST['test_name'] ?: 'Debug Test User';
        $testEmail = $_POST['test_email'] ?: 'debug@test.com';
        $testWebsite = $_POST['test_website'] ?: '';
        $testContent = $_POST['test_content'] ?: 'This is a test comment created at ' . date('Y-m-d H:i:s');
        
        echo "<h3>Testing Comment Insertion:</h3>";
        echo "<div><strong>Post:</strong> {$testPost['title']} (ID: {$testPost['id']})</div>";
        echo "<div><strong>Name:</strong> $testName</div>";
        echo "<div><strong>Email:</strong> $testEmail</div>";
        echo "<div><strong>Website:</strong> $testWebsite</div>";
        echo "<div><strong>Content:</strong> $testContent</div>";
        
        // Test the addComment function directly with error handling
        try {
            echo "<h4>Calling addComment function...</h4>";
            $commentId = $blogModel->addComment($testPost['id'], $testName, $testEmail, $testWebsite, $testContent);
            
            if ($commentId) {
                echo "<div style='color: green;'>✅ Comment added successfully! Comment ID: $commentId</div>";
                
                // Verify the comment was inserted
                $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
                $stmt->execute(['id' => $commentId]);
                $comment = $stmt->fetch();
                
                if ($comment) {
                    echo "<div style='color: green;'>✅ Comment verified in database</div>";
                    echo "<h4>Inserted Comment Data:</h4>";
                    echo "<pre>" . print_r($comment, true) . "</pre>";
                } else {
                    echo "<div style='color: red;'>❌ Comment not found in database after insertion</div>";
                }
            } else {
                echo "<div style='color: red;'>❌ addComment function returned false</div>";
                
                // Check if there was a database error
                $errorInfo = $pdo->errorInfo();
                if ($errorInfo[0] !== '00000') {
                    echo "<div style='color: red;'>Database Error: " . $errorInfo[2] . "</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Exception during comment insertion: " . $e->getMessage() . "</div>";
            echo "<div>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></div>";
        }
    }
}

// Show recent comments
echo "<h2>Recent Comments</h2>";
try {
    $stmt = $pdo->query("
        SELECT c.*, p.title as post_title 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC 
        LIMIT 10
    ");
    $comments = $stmt->fetchAll();
    
    echo "<div>Total comments in database: " . count($comments) . "</div>";
    
    if (!empty($comments)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Post</th><th>Name</th><th>Email</th><th>Content</th><th>Status</th><th>Created</th></tr>";
        foreach ($comments as $comment) {
            $status = $comment['is_approved'] ? 'Approved' : 'Pending';
            echo "<tr>";
            echo "<td>{$comment['id']}</td>";
            echo "<td>{$comment['post_title']}</td>";
            echo "<td>{$comment['name']}</td>";
            echo "<td>{$comment['email']}</td>";
            echo "<td>" . substr($comment['content'], 0, 50) . "...</td>";
            echo "<td>$status</td>";
            echo "<td>{$comment['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div>No comments found in database</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error fetching comments: " . $e->getMessage() . "</div>";
}

// Test form submission detection
echo "<h2>Test Form Submission Detection</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #e6f3ff; padding: 10px; border: 1px solid #0066cc;'>";
    echo "<strong>POST Request Detected!</strong><br>";
    echo "POST data: <pre>" . print_r($_POST, true) . "</pre>";
    echo "isset(\$_POST['submit_comment']): " . (isset($_POST['submit_comment']) ? 'Yes' : 'No') . "<br>";
    echo "Has required fields: " . ((isset($_POST['comment_name']) && isset($_POST['comment_email']) && isset($_POST['comment_content'])) ? 'Yes' : 'No') . "<br>";
    echo "</div>";
}

// Get available posts for testing
$blogModel = new BlogModel();
$posts = $blogModel->getPosts(1, null, 5);
?>

<!-- Test Form -->
<h2>Test Comment Insertion</h2>
<form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <div style="margin-bottom: 10px;">
        <label>Name:</label><br>
        <input type="text" name="test_name" value="Test User" style="width: 300px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Email:</label><br>
        <input type="email" name="test_email" value="test@example.com" style="width: 300px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Website (optional):</label><br>
        <input type="url" name="test_website" value="" style="width: 300px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Comment:</label><br>
        <textarea name="test_content" rows="3" style="width: 300px; padding: 5px;">This is a test comment to debug the comment system.</textarea>
    </div>
    
    <button type="submit" name="test_insert" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Insert Test Comment
    </button>
</form>

<h2>Test Form Submission Detection</h2>
<form method="POST" style="background: #f0f0f0; padding: 20px; border-radius: 5px;">
    <div style="margin-bottom: 10px;">
        <label for="comment_name">Name *</label><br>
        <input type="text" id="comment_name" name="comment_name" required style="width: 200px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label for="comment_email">Email *</label><br>
        <input type="email" id="comment_email" name="comment_email" required style="width: 200px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label for="comment_content">Comment *</label><br>
        <textarea id="comment_content" name="comment_content" rows="3" required style="width: 300px; padding: 5px;"></textarea>
    </div>
    
    <button type="submit" name="submit_comment" value="1" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Test Form Submission
    </button>
</form>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        line-height: 1.6;
    }
    
    h1, h2, h3 {
        color: #333;
        border-bottom: 2px solid #4f46e5;
        padding-bottom: 5px;
    }
    
    table {
        width: 100%;
        margin: 10px 0;
    }
    
    th, td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
    }
    
    th {
        background: #f0f0f0;
    }
    
    pre {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }
    
    label {
        font-weight: bold;
    }
</style>
