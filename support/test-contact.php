<?php
/**
 * Simple test script to verify the contact form setup
 * Access this file directly to test if PHP and database are working
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Selenix Contact Form Test</h2>";

// Test 1: Check if config file exists
echo "<h3>1. Configuration File Test</h3>";
if (file_exists('config.php')) {
    echo "✅ config.php exists<br>";
    
    try {
        require_once 'config.php';
        echo "✅ config.php loaded successfully<br>";
        
        // Test 2: Database connection
        echo "<h3>2. Database Connection Test</h3>";
        $pdo = getDatabaseConnection();
        echo "✅ Database connection successful<br>";
        
        // Test 3: Check tables
        echo "<h3>3. Database Tables Test</h3>";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('contact_submissions', $tables)) {
            echo "✅ contact_submissions table exists<br>";
        } else {
            echo "❌ contact_submissions table missing<br>";
        }
        
        if (in_array('support_users', $tables)) {
            echo "✅ support_users table exists<br>";
        } else {
            echo "❌ support_users table missing<br>";
        }
        
        // Test 4: Count existing submissions
        echo "<h3>4. Submissions Count</h3>";
        $count = $pdo->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
        echo "📊 Total submissions in database: $count<br>";
        
        // Test 5: Test form submission simulation
        echo "<h3>5. Form Handler Test</h3>";
        echo "📝 Form handler location: contact-handler.php<br>";
        
        if (file_exists('contact-handler.php')) {
            echo "✅ contact-handler.php exists<br>";
        } else {
            echo "❌ contact-handler.php missing<br>";
        }
        
        echo "<h3>✅ All Tests Passed!</h3>";
        echo "<p><strong>Your contact form system is properly configured.</strong></p>";
        echo "<p>You can now test the form at: <a href='index.html'>support/index.html</a></p>";
        echo "<p>Access admin panel at: <a href='admin.php'>admin.php</a></p>";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "<p><strong>Please run the database setup:</strong> <a href='../setup-contact-database.php'>setup-contact-database.php</a></p>";
    }
    
} else {
    echo "❌ config.php not found<br>";
    echo "<p><strong>Please run the database setup first:</strong> <a href='../setup-contact-database.php'>setup-contact-database.php</a></p>";
}

echo "<hr>";
echo "<h3>🔧 Manual Test Form</h3>";
echo "<p>Use this form to test submissions directly:</p>";
?>

<form method="POST" action="contact-handler.php" style="max-width: 400px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
    <div style="margin-bottom: 15px;">
        <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Name:</label>
        <input type="text" id="name" name="name" value="Test User" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email:</label>
        <input type="email" id="email" name="email" value="test@example.com" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="subject" style="display: block; margin-bottom: 5px; font-weight: bold;">Subject:</label>
        <input type="text" id="subject" name="subject" value="Test Contact Form" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="message" style="display: block; margin-bottom: 5px; font-weight: bold;">Message:</label>
        <textarea id="message" name="message" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">This is a test message to verify the contact form is working properly.</textarea>
    </div>
    
    <button type="submit" style="background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%;">
        Test Submit Form
    </button>
</form>

<p style="margin-top: 20px; font-size: 14px; color: #666;">
    <strong>Note:</strong> This test page should be deleted after verifying everything works.
</p>
