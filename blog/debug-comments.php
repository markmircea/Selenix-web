<?php
require_once 'config.php';
require_once 'database.php';
require_once 'models.php';
require_once 'functions.php';

echo "<h1>Comment System Debug</h1>";

// Test database connection
try {
    $db = Database::getInstance();
    echo "<div style='color: green;'>✓ Database connection successful</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

// Check if comments table exists
$pdo = $db->getConnection();
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments");
    echo "<div style='color: green;'>✓ Comments table exists</div>";
    echo "<div>Total comments in database: " . $stmt->fetchColumn() . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Comments table missing or inaccessible: " . $e->getMessage() . "</div>";
}

// Check comments table structure
try {
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'comments' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Comments Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
    foreach ($columns as $column) {
        echo "<tr><td>{$column['column_name']}</td><td>{$column['data_type']}</td><td>{$column['is_nullable']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<div style='color: red;'>Error checking table structure: " . $e->getMessage() . "</div>";
}

// Get sample posts for testing
$blogModel = new BlogModel();
$posts = $blogModel->getPosts(1, null, 5);

echo "<h3>Available Posts for Testing:</h3>";
if (empty($posts)) {
    echo "<div style='color: orange;'>No published posts found</div>";
} else {
    echo "<ul>";
    foreach ($posts as $post) {
        echo "<li>ID: {$post['id']} - <a href='post.php?slug={$post['slug']}' target='_blank'>{$post['title']}</a></li>";
    }
    echo "</ul>";
}

// Test comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_comment'])) {
    $postId = intval($_POST['post_id']);
    $name = 'Debug Test User';
    $email = 'debug@test.com';
    $website = 'https://test.com';
    $content = 'This is a debug test comment submitted at ' . date('Y-m-d H:i:s');
    
    echo "<h3>Testing Comment Submission:</h3>";
    echo "<div>Post ID: $postId</div>";
    echo "<div>Name: $name</div>";
    echo "<div>Email: $email</div>";
    echo "<div>Content: $content</div>";
    
    // Test the addComment function directly
    try {
        $commentId = $blogModel->addComment($postId, $name, $email, $website, $content);
        
        if ($commentId) {
            echo "<div style='color: green;'>✓ Comment added successfully! Comment ID: $commentId</div>";
            
            // Check if comment exists in database
            $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
            $stmt->execute(['id' => $commentId]);
            $comment = $stmt->fetch();
            
            if ($comment) {
                echo "<div style='color: green;'>✓ Comment found in database</div>";
                echo "<pre>" . print_r($comment, true) . "</pre>";
            } else {
                echo "<div style='color: red;'>✗ Comment not found in database</div>";
            }
        } else {
            echo "<div style='color: red;'>✗ Failed to add comment</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>✗ Exception during comment insertion: " . $e->getMessage() . "</div>";
    }
}

// Test getting comments for a post
if (!empty($posts)) {
    $testPost = $posts[0];
    echo "<h3>Testing Comment Retrieval for Post: {$testPost['title']}</h3>";
    
    try {
        $comments = $blogModel->getComments($testPost['id']);
        echo "<div>Found " . count($comments) . " approved comments</div>";
        
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                echo "<strong>{$comment['name']}</strong> ({$comment['email']})<br>";
                echo "{$comment['content']}<br>";
                echo "<small>Posted: {$comment['created_at']}</small>";
                echo "</div>";
            }
        }
        
        // Also check ALL comments (including unapproved)
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC");
        $stmt->execute(['post_id' => $testPost['id']]);
        $allComments = $stmt->fetchAll();
        
        echo "<h4>All Comments (including pending): " . count($allComments) . "</h4>";
        if (!empty($allComments)) {
            foreach ($allComments as $comment) {
                $status = $comment['is_approved'] === 't' ? 'Approved' : 'Pending';
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                echo "<strong>{$comment['name']}</strong> ({$comment['email']}) - <em>$status</em><br>";
                echo "{$comment['content']}<br>";
                echo "<small>Posted: {$comment['created_at']}</small>";
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error retrieving comments: " . $e->getMessage() . "</div>";
    }
}

// Show recent comments from admin perspective
echo "<h3>Recent Comments (Admin View):</h3>";
try {
    $recentComments = $blogModel->getRecentComments(10);
    echo "<div>Found " . count($recentComments) . " recent comments</div>";
    
    if (!empty($recentComments)) {
        foreach ($recentComments as $comment) {
            $status = $comment['is_approved'] === 't' ? 'Approved' : 'Pending';
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
            echo "<strong>{$comment['name']}</strong> on <em>{$comment['post_title']}</em> - <strong>$status</strong><br>";
            echo "{$comment['content']}<br>";
            echo "<small>Posted: {$comment['created_at']}</small>";
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error getting recent comments: " . $e->getMessage() . "</div>";
}
?>

<!-- Test Form -->
<?php if (!empty($posts)): ?>
<h3>Submit Test Comment</h3>
<form method="POST" style="background: #f0f0f0; padding: 20px; border-radius: 5px;">
    <label for="post_id">Select Post:</label>
    <select name="post_id" required>
        <?php foreach ($posts as $post): ?>
            <option value="<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <button type="submit" name="test_comment">Submit Test Comment</button>
</form>
<?php endif; ?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        line-height: 1.6;
    }
    
    h1, h3 {
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
    
    button {
        background: #4f46e5;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    
    select, input {
        padding: 5px;
        margin: 5px;
    }
</style>
