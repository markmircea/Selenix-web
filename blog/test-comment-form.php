<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

$blogModel = new BlogModel();

// Get the first post for testing
$posts = $blogModel->getPosts(1, null, 1);
if (empty($posts)) {
    die('No posts available for testing');
}
$post = $posts[0];

echo "<h1>Comment Form Test</h1>";
echo "<p>Testing comments for post: <strong>" . htmlspecialchars($post['title']) . "</strong></p>";
echo "<p>Post ID: {$post['id']}</p>";

// Handle comment submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    echo "<h3>Form Submission Debug:</h3>";
    echo "<pre>POST Data: " . print_r($_POST, true) . "</pre>";
    
    $name = isset($_POST['comment_name']) ? sanitizeInput($_POST['comment_name']) : '';
    $email = isset($_POST['comment_email']) ? sanitizeInput($_POST['comment_email']) : '';
    $website = isset($_POST['comment_website']) ? sanitizeInput($_POST['comment_website']) : '';
    $content = isset($_POST['comment_content']) ? sanitizeInput($_POST['comment_content']) : '';
    
    echo "<h3>Processed Data:</h3>";
    echo "Name: '$name'<br>";
    echo "Email: '$email'<br>";
    echo "Website: '$website'<br>";
    echo "Content: '$content'<br>";
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Comment content is required';
    }
    
    if (empty($errors)) {
        echo "<h3>Attempting to add comment...</h3>";
        $commentId = $blogModel->addComment($post['id'], $name, $email, $website, $content);
        
        if ($commentId) {
            $message = "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green;'>✓ Comment added successfully! ID: $commentId</div>";
        } else {
            $message = "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red;'>✗ Failed to add comment</div>";
        }
    } else {
        $message = "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red;'>Errors: " . implode(', ', $errors) . "</div>";
    }
}

echo $message;

// Get existing comments
$comments = $blogModel->getComments($post['id']);
echo "<h3>Existing Approved Comments: " . count($comments) . "</h3>";

// Check all comments (including pending)
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC");
$stmt->execute(['post_id' => $post['id']]);
$allComments = $stmt->fetchAll();
echo "<h3>All Comments (including pending): " . count($allComments) . "</h3>";

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
?>

<h3>Test Comment Form</h3>
<form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
    <div style="margin-bottom: 15px;">
        <label for="comment_name">Name *</label><br>
        <input type="text" id="comment_name" name="comment_name" required style="width: 200px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="comment_email">Email *</label><br>
        <input type="email" id="comment_email" name="comment_email" required style="width: 200px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="comment_website">Website (optional)</label><br>
        <input type="url" id="comment_website" name="comment_website" style="width: 200px; padding: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="comment_content">Comment *</label><br>
        <textarea id="comment_content" name="comment_content" rows="4" required style="width: 400px; padding: 5px;" placeholder="Write your comment here..."></textarea>
    </div>
    
    <button type="submit" name="submit_comment" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Submit Test Comment
    </button>
</form>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        line-height: 1.6;
    }
    
    h1, h3 {
        color: #333;
    }
    
    pre {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }
    
    label {
        font-weight: bold;
        color: #333;
    }
    
    input, textarea {
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    
    input:focus, textarea:focus {
        border-color: #007cba;
        outline: none;
    }
</style>
