<?php
require_once 'config.php';
require_once 'functions.php';

// Check PHP upload settings
echo "<h2>PHP Upload Configuration</h2>";
echo "<strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "<br>";
echo "<strong>post_max_size:</strong> " . ini_get('post_max_size') . "<br>";
echo "<strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "<br>";
echo "<strong>memory_limit:</strong> " . ini_get('memory_limit') . "<br>";

echo "<h2>Upload Directory Check</h2>";
echo "<strong>UPLOAD_DIR:</strong> " . UPLOAD_DIR . "<br>";
echo "<strong>UPLOAD_URL:</strong> " . UPLOAD_URL . "<br>";
echo "<strong>Directory exists:</strong> " . (file_exists(UPLOAD_DIR) ? 'Yes' : 'No') . "<br>";
echo "<strong>Directory writable:</strong> " . (is_writable(UPLOAD_DIR) ? 'Yes' : 'No') . "<br>";

if (!file_exists(UPLOAD_DIR)) {
    echo "<strong>Creating directory...</strong><br>";
    if (mkdir(UPLOAD_DIR, 0755, true)) {
        echo "Directory created successfully<br>";
    } else {
        echo "Failed to create directory<br>";
    }
}

// Set proper permissions
if (file_exists(UPLOAD_DIR)) {
    chmod(UPLOAD_DIR, 0755);
    echo "<strong>Directory permissions set to 755</strong><br>";
}

// Test file upload if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_upload'])) {
    echo "<h2>Upload Test Results</h2>";
    
    $uploadResult = handleFileUpload($_FILES['test_upload']);
    
    if ($uploadResult['success']) {
        echo "<div style='color: green;'>";
        echo "<strong>Upload successful!</strong><br>";
        echo "Filename: " . $uploadResult['filename'] . "<br>";
        echo "URL: " . $uploadResult['url'] . "<br>";
        echo "File path: " . $uploadResult['filepath'] . "<br>";
        echo "File exists: " . (file_exists($uploadResult['filepath']) ? 'Yes' : 'No') . "<br>";
        
        if (file_exists($uploadResult['filepath'])) {
            echo "<img src='" . $uploadResult['url'] . "' style='max-width: 200px; max-height: 200px;' alt='Uploaded image'><br>";
        }
        echo "</div>";
    } else {
        echo "<div style='color: red;'>";
        echo "<strong>Upload failed:</strong> " . $uploadResult['error'] . "<br>";
        echo "</div>";
    }
}

// Test form
?>
<h2>Test File Upload</h2>
<form method="POST" enctype="multipart/form-data">
    <p>Select an image to test upload:</p>
    <input type="file" name="test_upload" accept="image/*" required>
    <br><br>
    <button type="submit">Test Upload</button>
</form>

<h2>Database Comment Test</h2>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_comment'])) {
    require_once 'models.php';
    $blogModel = new BlogModel();
    
    // Get first published post for testing
    $posts = $blogModel->getPosts(1, null, 1);
    if (!empty($posts)) {
        $testPost = $posts[0];
        $commentId = $blogModel->addComment(
            $testPost['id'], 
            'Test User', 
            'test@example.com', 
            'https://example.com', 
            'This is a test comment to verify the comment system is working.'
        );
        
        if ($commentId) {
            echo "<div style='color: green;'>Comment added successfully! Comment ID: $commentId</div>";
        } else {
            echo "<div style='color: red;'>Failed to add comment</div>";
        }
    } else {
        echo "<div style='color: orange;'>No published posts found to test comments</div>";
    }
}
?>

<form method="POST">
    <button type="submit" name="test_comment">Test Comment System</button>
</form>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    
    h2 {
        color: #333;
        border-bottom: 2px solid #4f46e5;
        padding-bottom: 5px;
    }
    
    form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    button {
        background: #4f46e5;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    
    button:hover {
        background: #3730a3;
    }
</style>
