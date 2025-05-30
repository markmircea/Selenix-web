<?php
// Enhanced Debug Script for Comment Issues
require_once 'config.php';
require_once 'database.php';
require_once 'models.php';
require_once 'functions.php';

echo "<h1>Enhanced Comment System Debug</h1>";

// Test database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "<div style='color: green;'>✓ Database connection successful</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

echo "<h2>Current Timezone Information</h2>";
echo "<div><strong>PHP Timezone:</strong> " . date_default_timezone_get() . "</div>";
echo "<div><strong>Current PHP Time:</strong> " . date('Y-m-d H:i:s') . "</div>";
echo "<div><strong>Current Database Time:</strong> ";
try {
    $stmt = $pdo->query("SELECT CURRENT_TIMESTAMP");
    echo $stmt->fetchColumn();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</div>";

echo "<h2>Comments Table Analysis</h2>";
try {
    // Get all comments with their actual boolean values
    $stmt = $pdo->query("
        SELECT 
            id, name, email, content, 
            is_approved, 
            CASE WHEN is_approved THEN 'TRUE' ELSE 'FALSE' END as is_approved_text,
            created_at,
            EXTRACT(EPOCH FROM created_at) as created_timestamp
        FROM comments 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $comments = $stmt->fetchAll();
    
    if (!empty($comments)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Content</th><th>is_approved (raw)</th><th>is_approved (text)</th><th>Created At</th><th>Time Ago</th></tr>";
        foreach ($comments as $comment) {
            $timeAgo = timeAgo(strtotime($comment['created_at']));
            echo "<tr>";
            echo "<td>{$comment['id']}</td>";
            echo "<td>{$comment['name']}</td>";
            echo "<td>{$comment['email']}</td>";
            echo "<td>" . substr($comment['content'], 0, 30) . "...</td>";
            echo "<td style='background: #f0f0f0;'>" . var_export($comment['is_approved'], true) . "</td>";
            echo "<td>{$comment['is_approved_text']}</td>";
            echo "<td>{$comment['created_at']}</td>";
            echo "<td>$timeAgo</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div>No comments found</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

echo "<h2>Test Boolean Handling</h2>";
try {
    // Test how PostgreSQL returns boolean values
    $stmt = $pdo->query("SELECT true as test_true, false as test_false");
    $result = $stmt->fetch();
    
    echo "<div><strong>PostgreSQL true value:</strong> " . var_export($result['test_true'], true) . "</div>";
    echo "<div><strong>PostgreSQL false value:</strong> " . var_export($result['test_false'], true) . "</div>";
    echo "<div><strong>true === 't':</strong> " . (($result['test_true'] === 't') ? 'Yes' : 'No') . "</div>";
    echo "<div><strong>false === 'f':</strong> " . (($result['test_false'] === 'f') ? 'Yes' : 'No') . "</div>";
    echo "<div><strong>true === true:</strong> " . (($result['test_true'] === true) ? 'Yes' : 'No') . "</div>";
    echo "<div><strong>false === false:</strong> " . (($result['test_false'] === false) ? 'Yes' : 'No') . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

echo "<h2>Test Comment Filtering</h2>";
$blogModel = new BlogModel();

// Test approved comments count
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE is_approved = true");
    $approvedCount = $stmt->fetchColumn();
    echo "<div><strong>Approved comments (direct query):</strong> $approvedCount</div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE is_approved = false");
    $pendingCount = $stmt->fetchColumn();
    echo "<div><strong>Pending comments (direct query):</strong> $pendingCount</div>";
    
    // Test getComments function
    $posts = $blogModel->getPosts(1, null, 1);
    if (!empty($posts)) {
        $testPost = $posts[0];
        $frontendComments = $blogModel->getComments($testPost['id']);
        echo "<div><strong>Frontend comments for post '{$testPost['title']}':</strong> " . count($frontendComments) . "</div>";
        
        if (!empty($frontendComments)) {
            echo "<div style='margin-left: 20px;'>";
            foreach ($frontendComments as $comment) {
                echo "<div>- {$comment['name']}: " . substr($comment['content'], 0, 50) . "...</div>";
            }
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

// Test form submission for bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_bulk'])) {
    echo "<h2>Bulk Action Test Results</h2>";
    echo "<div style='background: #e6f3ff; padding: 10px; border: 1px solid #0066cc;'>";
    echo "<strong>POST Data Received:</strong><br>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_POST['comment_ids'])) {
        echo "<strong>Comment IDs array:</strong><br>";
        foreach ($_POST['comment_ids'] as $index => $id) {
            echo "[$index] => $id<br>";
        }
    } else {
        echo "<strong style='color: red;'>No comment_ids received!</strong><br>";
    }
    echo "</div>";
}

// Test timezone conversion
echo "<h2>Timezone Test</h2>";
if (!empty($comments)) {
    $testComment = $comments[0];
    echo "<div><strong>Raw created_at:</strong> {$testComment['created_at']}</div>";
    echo "<div><strong>strtotime result:</strong> " . strtotime($testComment['created_at']) . "</div>";
    echo "<div><strong>timeAgo result:</strong> " . timeAgo(strtotime($testComment['created_at'])) . "</div>";
    echo "<div><strong>Current time:</strong> " . time() . "</div>";
    echo "<div><strong>Difference in seconds:</strong> " . (time() - strtotime($testComment['created_at'])) . "</div>";
}
?>

<h2>Test Bulk Action Form</h2>
<form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <p>This form simulates selecting multiple comments for bulk actions:</p>
    
    <div style="margin-bottom: 10px;">
        <label><input type="checkbox" name="comment_ids[]" value="1"> Comment ID 1</label>
    </div>
    <div style="margin-bottom: 10px;">
        <label><input type="checkbox" name="comment_ids[]" value="2"> Comment ID 2</label>
    </div>
    <div style="margin-bottom: 10px;">
        <label><input type="checkbox" name="comment_ids[]" value="3"> Comment ID 3</label>
    </div>
    
    <button type="submit" name="test_bulk" value="1" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Test Bulk Form Submission
    </button>
</form>

<h2>Fix Approved Comments Test</h2>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_approved'])) {
    try {
        // Force approve a comment to test the fix
        $stmt = $pdo->query("SELECT id FROM comments WHERE is_approved = false LIMIT 1");
        $commentId = $stmt->fetchColumn();
        
        if ($commentId) {
            $stmt = $pdo->prepare("UPDATE comments SET is_approved = true WHERE id = :id");
            $result = $stmt->execute(['id' => $commentId]);
            
            if ($result) {
                echo "<div style='color: green;'>✓ Successfully approved comment ID: $commentId</div>";
                
                // Test if it shows up in approved list
                $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE is_approved = true");
                $newApprovedCount = $stmt->fetchColumn();
                echo "<div>New approved count: $newApprovedCount</div>";
            } else {
                echo "<div style='color: red;'>✗ Failed to approve comment</div>";
            }
        } else {
            echo "<div>No pending comments to approve</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<form method="POST" style="background: #f0f8ff; padding: 20px; border-radius: 5px;">
    <p>This will approve one pending comment to test the fix:</p>
    <button type="submit" name="fix_approved" value="1" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Approve One Comment for Testing
    </button>
</form>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        line-height: 1.6;
    }
    
    h1, h2 {
        color: #333;
        border-bottom: 2px solid #4f46e5;
        padding-bottom: 5px;
    }
    
    table {
        width: 100%;
        margin: 10px 0;
        font-size: 0.9rem;
    }
    
    th, td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
        vertical-align: top;
    }
    
    th {
        background: #f0f0f0;
        font-weight: bold;
    }
    
    pre {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
        font-size: 0.85rem;
    }
    
    .highlight {
        background: #ffffcc;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>
